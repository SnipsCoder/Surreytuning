<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class HealthController extends Controller
{
    /**
     * Deep health check for uptime monitoring / load balancers.
     *
     * Unlike Laravel's default `/up` (which only confirms the framework boots),
     * this verifies the central database and cache store are actually reachable
     * — the dependencies without which no tenant can be served. Returns 200 when
     * healthy and 503 when any critical dependency is down, so a monitor can page.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $expected = config('app.health_check_token');

        if (! empty($expected) && ! hash_equals((string) $expected, (string) $request->query('token'))) {
            abort(404);
        }

        $checks = [
            'database' => $this->check(function () {
                DB::connection(config('tenancy.database.central_connection'))->select('select 1');
            }),
            'cache' => $this->check(function () {
                $key = 'healthz:'.Str::random(8);
                Cache::put($key, '1', 5);
                Cache::forget($key);
            }),
        ];

        $healthy = ! in_array('down', $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'down',
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function check(callable $probe): string
    {
        try {
            $probe();

            return 'ok';
        } catch (Throwable $e) {
            report($e);

            return 'down';
        }
    }
}
