<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Facades\Tenancy;

trait InteractsWithTenancy
{
    protected Tenant $tenant;

    protected function setUpTenancy(): void
    {
        $this->removeStaleTenantDatabase();

        $this->tenant = Tenant::create(['id' => 'testing']);
        $this->tenant->domains()->create(['domain' => 'surreytuning.test']);

        Tenancy::initialize($this->tenant);
    }

    protected function tearDownTenancy(): void
    {
        Tenancy::end();
        $this->tenant->delete();

        $this->removeStaleTenantDatabase();
    }

    private function removeStaleTenantDatabase(): void
    {
        DB::purge('tenant');

        $stalePath = database_path('tenanttesting');
        if (file_exists($stalePath)) {
            @unlink($stalePath);
        }
    }
}
