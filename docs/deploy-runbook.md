# Deploy Runbook

Surrey Tuning is a multi-tenant Laravel app (database-per-tenant, Stancl
Tenancy). Deploys must migrate **both** the central database and every tenant
database, rebuild caches, and restart the queue workers. This runbook is the
canonical, ordered procedure — follow it top to bottom.

> **Never run `php artisan migrate:fresh` or `migrate:refresh` in production.**
> They drop the central `tenants`/`domains` tables and orphan every tenant DB.
> Use the forward-only steps below.

## Pre-flight (once per environment)

- `.env` is populated from `.env.example` — every key present, secrets set.
  Confirm `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` set.
- `QUEUE_CONNECTION=database`, and a queue worker is provisioned
  (see [queue-workers.md](queue-workers.md)).
- Mail transport + DNS configured (see [email-deliverability.md](email-deliverability.md)).
- Backups configured to the private R2 bucket (see [backups.md](backups.md)).
- The scheduler cron is installed (see **Scheduler** below).

## Deploy steps (run in order)

```bash
# 0. Pull the new release
git pull --ff-only            # or your release/symlink mechanism
composer install --no-dev --optimize-autoloader
npm ci && npm run build       # compile front-end assets

# 1. Maintenance mode (optional, for schema-breaking releases)
php artisan down --render="errors::503" --retry=15

# 2. Central database migrations (forward-only)
php artisan migrate --force

# 3. Tenant database migrations — every tenant
php artisan tenants:migrate --force

# 4. Rebuild caches (do config LAST so it picks up the fresh routes/events)
php artisan route:cache
php artisan event:cache
php artisan view:cache
php artisan config:cache

# 5. Restart queue workers so they load the new code
php artisan queue:restart

# 6. Bring the app back up
php artisan up
```

### Notes

- **`config:cache` must run last.** Once config is cached, `env()` returns null
  everywhere outside config files — so any `route:cache`/`event:cache` that
  reads config must run before it, and `.env` changes require re-running
  `config:cache` to take effect.
- **`route:cache` requires no closures in route files.** `/healthz` uses the
  invokable `HealthController` class precisely so route caching succeeds.
- **`tenants:migrate` runs the tenant-connection migrations against every tenant
  DB.** To migrate a single tenant: `php artisan tenants:migrate --tenants=surrey-tuning`.
- Restart the queue worker on **every** deploy — a long-running worker holds the
  old code in memory until `queue:restart` tells it to gracefully die and respawn.

## Scheduler (cron)

Laravel's scheduler runs backups, retention pruning, and queue maintenance. Add
a single system cron entry (Linux):

```cron
* * * * * cd /var/www/surreytuning && php artisan schedule:run >> /dev/null 2>&1
```

Confirm registered schedule with `php artisan schedule:list`.

## Post-deploy verification

```bash
# Framework boots + central DB and cache reachable → HTTP 200, {"status":"ok"}
curl -fsS https://surreytuning.co.uk/healthz            # or add ?token=... if HEALTH_CHECK_TOKEN is set

# Queue is draining (should be empty or shrinking)
php artisan queue:failed

# Mail transport works end-to-end
php artisan mail:test you@example.com
```

If `/healthz` returns 503, the JSON `checks` object names the failing
dependency (`database` or `cache`). Errors are also reported to Sentry.

## Rollback

1. `php artisan down`
2. Re-point the release symlink to the previous build (or `git checkout` the
   prior tag) and `composer install --no-dev`.
3. **Only roll back migrations if the new release added reversible ones you must
   undo** — `php artisan migrate:rollback --force` (central) and
   `php artisan tenants:rollback` (tenants). Prefer forward fixes; rolling back
   schema on a live multi-tenant DB is risky.
4. Rebuild caches (step 4 above) and `php artisan queue:restart`.
5. `php artisan up`.

## Staging

Run an identical environment on a separate host/subdomain (e.g.
`staging.surreytuning.co.uk`) with `APP_ENV=staging`, its **own** database
server and its **own** R2 buckets — never point staging at production data or
the production backup bucket. Deploy to staging first using this exact runbook,
smoke-test `/healthz`, a dealer login, a file upload, and a Stripe test-mode
payment before promoting the release to production.
```
