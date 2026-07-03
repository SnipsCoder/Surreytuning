<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Throwable;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenants:create {name : The business/dealer name} {domain : The domain or subdomain the tenant will be served on} {--id= : Optional explicit tenant id (defaults to a slug of the name)}';

    protected $description = 'Provision a new tuning business as an isolated tenant (own database, own domain)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $id = $this->option('id') ?: str($name)->slug()->toString();

        if (Tenant::find($id)) {
            $this->error("A tenant with id [{$id}] already exists.");

            return self::FAILURE;
        }

        $tenant = null;

        try {
            // Tenant::create() fires TenantCreated, which runs the
            // CreateDatabase → MigrateDatabase → SeedDatabase pipeline
            // synchronously (see TenancyServiceProvider). If any step throws,
            // we must not leave an orphaned central record or half-built DB.
            $tenant = Tenant::create([
                'id' => $id,
                'name' => $name,
            ]);

            $tenant->domains()->create(['domain' => $domain]);
        } catch (Throwable $e) {
            $this->error("Provisioning failed: {$e->getMessage()}");

            if ($tenant && Tenant::find($id)) {
                // Deleting the tenant fires TenantDeleted → DeleteDatabase,
                // dropping any database that was created and cascading the
                // domain row, leaving the central DB clean for a retry.
                $this->warn('Rolling back the partially-provisioned tenant...');
                $tenant->delete();
            }

            report($e);

            return self::FAILURE;
        }

        $this->info("Tenant [{$name}] provisioned with id [{$id}] on domain [{$domain}].");
        $this->info('Database created, migrated, and seeded automatically.');

        return self::SUCCESS;
    }
}
