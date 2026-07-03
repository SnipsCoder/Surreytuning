<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled backups (Phase 4 — commercial hardening)
|--------------------------------------------------------------------------
|
| Requires a running scheduler in production:
|   * * * * * cd /var/www/surreytuning && php artisan schedule:run >> /dev/null 2>&1
|
| All backups target the private `r2_backups` disk. `--only-db` keeps the
| archives lean — uploaded files already live durably in R2, and application
| code lives in git; the irreplaceable data is the databases.
| See docs/backups.md for the restore procedure.
*/

// Prune old backups first so a run never trips the storage health check.
Schedule::command('backup:clean')
    ->daily()
    ->at('01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Central database (surreytuning) — tenants + domains + billing.
Schedule::command('backup:run --only-db')
    ->daily()
    ->at('01:30')
    ->withoutOverlapping()
    ->runInBackground();

// Every tenant database, one folder per tenant on the backup bucket.
Schedule::command('backup:tenants')
    ->daily()
    ->at('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Alert if the newest central backup is missing/stale or storage is bloated.
Schedule::command('backup:monitor')
    ->daily()
    ->at('08:00');

/*
|--------------------------------------------------------------------------
| Data-retention pruning (Phase 8 — legal / GDPR)
|--------------------------------------------------------------------------
|
| Enforces the storage-limitation principle: removes rejected dealer
| applications past their retention window and clears expired one-time email
| 2FA codes. Runs across every tenant so each business's data is pruned in
| its own database. See config/gdpr.php for the retention windows.
*/
Schedule::command('tenants:run gdpr:prune')
    ->daily()
    ->at('03:00')
    ->withoutOverlapping()
    ->runInBackground();
