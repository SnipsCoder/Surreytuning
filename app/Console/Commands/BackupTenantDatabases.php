<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class BackupTenantDatabases extends Command
{
    protected $signature = 'backup:tenants
        {--tenants=* : Limit the backup to these tenant ids (default: all tenants)}';

    protected $description = 'Back up every tenant database to the private R2 backup bucket (one folder per tenant)';

    public function handle(): int
    {
        $query = Tenant::query();

        if ($ids = $this->option('tenants')) {
            $query->whereIn('id', $ids);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found to back up.');

            return self::SUCCESS;
        }

        // Snapshot the central backup config so we can restore it between
        // tenants and after the loop finishes.
        $originalName = config('backup.backup.name');
        $originalDatabases = config('backup.backup.source.databases');

        $failures = [];

        foreach ($tenants as $tenant) {
            $this->line("Backing up tenant [{$tenant->getTenantKey()}]...");

            try {
                tenancy()->initialize($tenant);

                // Inside tenant context the dynamic `tenant` connection points
                // at this tenant's database. Give each tenant its own backup
                // folder on the disk so restores are unambiguous.
                config([
                    'backup.backup.name' => 'tenant-'.$tenant->getTenantKey(),
                    'backup.backup.source.databases' => ['tenant'],
                ]);

                $exitCode = Artisan::call('backup:run', [
                    '--only-db' => true,
                    '--disable-notifications' => true,
                ], $this->getOutput());

                if ($exitCode !== 0) {
                    $failures[] = $tenant->getTenantKey();
                }
            } catch (Throwable $e) {
                $failures[] = $tenant->getTenantKey();
                $this->error("  Failed: {$e->getMessage()}");
                report($e);
            } finally {
                tenancy()->end();

                config([
                    'backup.backup.name' => $originalName,
                    'backup.backup.source.databases' => $originalDatabases,
                ]);
            }
        }

        if ($failures !== []) {
            $this->error('Tenant backups failed for: '.implode(', ', $failures));

            return self::FAILURE;
        }

        $this->info("Backed up {$tenants->count()} tenant database(s) to the R2 backup bucket.");

        return self::SUCCESS;
    }
}
