<?php

namespace Tests\Feature\Console;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Facades\Tenancy;
use Tests\TestCase;

class TenantLifecycleCommandTest extends TestCase
{
    private string $newTenantId = 'acme-tuning';

    protected function tearDown(): void
    {
        // The commands under test create a second tenant alongside the base
        // `testing` tenant; make sure its DB + record never leak between tests.
        if (tenancy()->initialized) {
            Tenancy::end();
        }

        if ($tenant = Tenant::find($this->newTenantId)) {
            $tenant->delete();
        }

        $this->removeTenantDatabaseFile($this->newTenantId);

        parent::tearDown();
    }

    public function test_create_command_provisions_database_domain_and_record(): void
    {
        Tenancy::end();

        $this->artisan('tenants:create', [
            'name' => 'Acme Tuning',
            'domain' => 'acme.surreytuning.test',
            '--id' => $this->newTenantId,
        ])->assertSuccessful();

        $tenant = Tenant::find($this->newTenantId);

        $this->assertNotNull($tenant);
        $this->assertSame(1, $tenant->domains()->where('domain', 'acme.surreytuning.test')->count());
        $this->assertFileExists(database_path('tenant'.$this->newTenantId));
    }

    public function test_create_command_rejects_a_duplicate_id(): void
    {
        Tenancy::end();

        $this->artisan('tenants:create', [
            'name' => 'Acme Tuning',
            'domain' => 'acme.surreytuning.test',
            '--id' => $this->newTenantId,
        ])->assertSuccessful();

        $this->artisan('tenants:create', [
            'name' => 'Acme Tuning Again',
            'domain' => 'acme2.surreytuning.test',
            '--id' => $this->newTenantId,
        ])->assertFailed();
    }

    public function test_delete_command_removes_tenant_and_database(): void
    {
        Tenancy::end();

        $this->artisan('tenants:create', [
            'name' => 'Acme Tuning',
            'domain' => 'acme.surreytuning.test',
            '--id' => $this->newTenantId,
        ])->assertSuccessful();

        $this->assertNotNull(Tenant::find($this->newTenantId));

        $this->artisan('tenants:delete', [
            'id' => $this->newTenantId,
            '--force' => true,
            '--skip-backup' => true,
        ])->assertSuccessful();

        $this->assertNull(Tenant::find($this->newTenantId));
        // Domains cascade with the central record via the FK.
        $this->assertSame(0, DB::table('domains')->where('tenant_id', $this->newTenantId)->count());
    }

    public function test_delete_command_fails_for_an_unknown_tenant(): void
    {
        Tenancy::end();

        $this->artisan('tenants:delete', [
            'id' => 'does-not-exist',
            '--force' => true,
            '--skip-backup' => true,
        ])->assertFailed();
    }

    private function removeTenantDatabaseFile(string $id): void
    {
        DB::purge('tenant');

        $path = database_path('tenant'.$id);
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
