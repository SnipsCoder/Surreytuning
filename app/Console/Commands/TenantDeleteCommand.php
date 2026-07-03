<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class TenantDeleteCommand extends Command
{
    protected $signature = 'tenants:delete
        {id : The tenant id to permanently delete}
        {--force : Skip the interactive confirmation prompt}
        {--skip-backup : Delete without taking a final backup first (DANGEROUS)}';

    protected $description = 'Permanently offboard a tenant: take a final backup, then delete its database, domains and central record (GDPR erasure)';

    public function handle(): int
    {
        $id = $this->argument('id');

        $tenant = Tenant::find($id);

        if (! $tenant) {
            $this->error("No tenant found with id [{$id}].");

            return self::FAILURE;
        }

        $this->warn("This will PERMANENTLY delete tenant [{$id}] — its database, all domains, and its central record.");
        $this->warn('This is irreversible. A final backup is taken first unless --skip-backup is set.');

        if (! $this->option('force') && ! $this->confirm("Permanently delete tenant [{$id}]?")) {
            $this->info('Aborted. No changes made.');

            return self::SUCCESS;
        }

        if (! $this->option('skip-backup')) {
            $this->line("Taking a final backup of tenant [{$id}]...");

            $exitCode = Artisan::call('backup:tenants', [
                '--tenants' => [$id],
            ], $this->getOutput());

            if ($exitCode !== self::SUCCESS) {
                $this->error('Final backup failed — aborting deletion. Fix the backup, or pass --skip-backup to override.');

                return self::FAILURE;
            }
        }

        try {
            // Deleting the tenant fires TenantDeleted → DeleteDatabase
            // synchronously, dropping the tenant database. The domains FK
            // cascades on delete, removing the domain rows with the record.
            $tenant->delete();
        } catch (Throwable $e) {
            $this->error("Deletion failed: {$e->getMessage()}");

            report($e);

            return self::FAILURE;
        }

        $this->info("Tenant [{$id}] permanently deleted (database, domains and central record).");

        return self::SUCCESS;
    }
}
