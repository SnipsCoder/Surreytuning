<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithTenancy;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithTenancy;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('tenants')) {
            Artisan::call('migrate', ['--force' => true]);
        }

        $this->setUpTenancy();
    }

    protected function tearDown(): void
    {
        $this->tearDownTenancy();

        parent::tearDown();
    }
}
