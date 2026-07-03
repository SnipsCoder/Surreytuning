<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/login'));

// Deep health check for uptime monitoring / load balancers. Unlike `/up`
// (framework-boot only), this verifies the central database and cache are
// reachable. Optionally guarded by HEALTH_CHECK_TOKEN. Invokable controller
// (not a closure) so `route:cache` works in production.
Route::get('/healthz', HealthController::class)->name('healthz');
