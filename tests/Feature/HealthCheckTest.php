<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_healthz_reports_ok_when_dependencies_are_up(): void
    {
        config(['app.health_check_token' => null]);

        $response = $this->getJson('/healthz');

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'checks' => [
                    'database' => 'ok',
                    'cache' => 'ok',
                ],
            ]);
    }

    public function test_healthz_returns_404_when_token_is_missing_or_wrong(): void
    {
        config(['app.health_check_token' => 'secret-token']);

        $this->getJson('/healthz')->assertNotFound();
        $this->getJson('/healthz?token=wrong')->assertNotFound();
    }

    public function test_healthz_allows_access_with_the_correct_token(): void
    {
        config(['app.health_check_token' => 'secret-token']);

        $this->getJson('/healthz?token=secret-token')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
