# Deploy Runbook

Surrey Tuning (tuning-portal.com) deploys **automatically**. The normal path is:

> **Merge/push to `master` → CI runs → if green, GitHub Actions deploys to
> production.** Nobody SSHes in to deploy.

This document covers what the pipeline does, the migration policy it imposes,
how to roll back, and a break-glass manual procedure for when GitHub is down.

> **Never run `php artisan migrate:fresh` or `migrate:refresh` in production.**
> They drop the central `tenants`/`domains` tables and orphan every tenant DB.
> All migrations are forward-only.

## Normal deploys

1. Push (or merge a PR) to `master`.
2. The **CI** workflow runs Pint, PHPUnit, and the Vite build.
3. If CI is green, the **Deploy** workflow (`.github/workflows/deploy.yml`)
   SSHes into the VPS and runs Deployer against the exact commit CI tested.
4. Watch it under the repo's **Actions** tab. A red Deploy run is a page —
   never ignore it.

Controls:

- **Kill-switch:** the Actions repository *variable* `DEPLOY_ENABLED` gates the
  deploy job. Set it to anything other than `true`
  (`gh variable set DEPLOY_ENABLED --body "false"`) to pause all deploys;
  runs are *skipped*, not failed.
- **Manual trigger:** Actions → Deploy → *Run workflow* (`workflow_dispatch`)
  deploys the current tip of `master` without waiting for a new push. CI is
  bypassed on this path, so use it only to re-deploy a commit CI already
  passed.
- ⚠️ **Renaming things breaks deploys silently.** `deploy.yml` triggers on the
  workflow *named* `CI` and the branch *named* `master`. Rename either and
  deploys simply stop firing — no error anywhere.

## What a deploy does

Server layout (base path `/home/user/htdocs/tuning-portal`):

```
releases/<N>/   one directory per deploy, built in full before going live
shared/.env     symlinked into every release — APP_KEY lives here; losing it
                bricks all encrypted data. No task ever runs key:generate.
shared/storage/ symlinked into every release
current  →  releases/<N>   the ONLY thing the web server sees (current/public)
```

Deployer task order (defined in `deploy.php` at the repo root):

1. `deploy:prepare` — new `releases/<N>`, checkout the tested commit, link
   shared `.env` + `storage`, take the deploy lock.
2. `deploy:vendors` — `composer install --no-dev --optimize-autoloader`.
3. `npm:build` — `npm ci && npm run build`, then `node_modules` is deleted
   (output lives in `public/build`).
4. `artisan:storage:link`.
5. **Central migrations** — `php artisan migrate --force`.
6. **Tenant migrations** — `php artisan tenants:migrate --force`, every tenant
   DB, sequentially.
7. Cache rebuild — `route:cache`, `event:cache`, `view:cache`, then
   `config:cache` **last** (once config is cached, `env()` returns null
   outside config files).
8. `deploy:owner` — chown the release to the runtime user.
9. **`deploy:symlink`** — the atomic flip. Everything above happened in
   `releases/<N>` while the old release kept serving; a failure anywhere
   above aborts the deploy with `current` untouched.
10. Post-flip: `queue:restart` (workers pick up new code), php-fpm reload
    (opcache flush), then an external health check against
    `https://tuning-portal.com/healthz?token=…` with retry/backoff.

A failed health check turns the workflow red but does **not** auto-rollback —
migrations have already run, and code-rollback against a new schema can be
worse than a forward fix. The operator decides: forward-fix or `dep rollback`.

Old releases are pruned automatically (`keep_releases: 5`).

## Migration policy (multi-tenant safety)

Migrations run **before** the symlink flips, central first, then
`tenants:migrate` per tenant. Because tenants migrate sequentially against
live databases while the *old* code keeps serving:

- **Additive-only by default.** New tables, nullable/defaulted columns, and
  indexes are always safe under old code.
- **Destructive changes are two-phase.** Renames, drops, and type changes:
  ship code tolerant of both schemas first, run the destructive migration in
  a later release.
- **If `tenants:migrate` fails halfway:** the deploy aborts, `current` never
  moves, the old release keeps serving. Tenants migrated before the failure
  now run new schema under old code — safe if you kept migrations additive.
  Each tenant DB has its own `migrations` table, so fixing the migration and
  pushing again resumes cleanly (already-migrated tenants are no-ops).
- **Single-tenant repair:**
  `php artisan tenants:migrate --tenants=<tenant-id> --force` from the release
  directory. MySQL has no transactional DDL, so a failed multi-statement
  migration may need manual SQL first — inspect that tenant's `migrations`
  table and schema before re-running.

## Rollback (emergency)

Rollback flips `current` back to the previous release. Migrations are **not**
reverted — the additive-only policy makes that safe. Prefer forward fixes over
`migrate:rollback`/`tenants:rollback` on live multi-tenant MySQL.

### 1. Normal: one command from the dev machine

From WSL or Git Bash (Deployer on native Windows is flaky), in the repo root:

```bash
# one-time: fetch the same pinned phar the workflow uses
curl -fsSL -o dep https://github.com/deployphp/deployer/releases/download/v7.5.12/deployer.phar
chmod +x dep

./dep releases production      # list release candidates
./dep rollback production      # flip current back one release
```

The `after('rollback', …)` hooks in `deploy.php` restart the queue workers and
reload php-fpm automatically; the health check token is read from the server's
`shared/.env` if `HEALTH_CHECK_TOKEN` isn't set locally.

To roll *forward* again: Actions → Deploy → *Run workflow*.

### 2. Pure-SSH fallback (GitHub/Deployer unavailable)

```bash
cd /home/user/htdocs/tuning-portal
ls -1 releases/                          # pick the previous release, e.g. 7
ln -sfn releases/7 current_tmp && mv -Tf current_tmp current   # atomic swap
cd current && php artisan queue:restart
systemctl reload php8.3-fpm
curl -fsS "https://tuning-portal.com/healthz?token=..."
```

## Break-glass: manual deploy (GitHub down)

Do **not** revert to in-place `git pull` in a live directory. Build a fresh
release by hand in the same canonical order, then flip:

```bash
cd /home/user/htdocs/tuning-portal
TS=$(date +%Y%m%d%H%M%S)
git clone --depth 1 --branch master git@github.com:SnipsCoder/Surreytuning.git releases/$TS
cd releases/$TS
ln -sfn ../../shared/.env .env
rm -rf storage && ln -sfn ../../shared/storage storage
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci && npm run build && rm -rf node_modules
php artisan storage:link
php artisan migrate --force
php artisan tenants:migrate --force
php artisan route:cache && php artisan event:cache && php artisan view:cache
php artisan config:cache                 # LAST
chown -R user:user .
cd /home/user/htdocs/tuning-portal
ln -sfn releases/$TS current_tmp && mv -Tf current_tmp current
cd current && php artisan queue:restart
systemctl reload php8.3-fpm
curl -fsS "https://tuning-portal.com/healthz?token=..."
```

## Server layout & moving parts

| Thing | Where / value |
| --- | --- |
| Base path | `/home/user/htdocs/tuning-portal` |
| Web docroot | `/home/user/htdocs/tuning-portal/current/public` |
| Shared `.env` | `shared/.env` — holds `APP_KEY`; never regenerate it |
| Deploy user | `root` over SSH (dedicated key in GitHub Actions secrets) |
| Runtime user | `user` (releases chowned to it before the flip) |
| php-fpm service | `php8.3-fpm` (`systemctl reload`) |
| Queue workers | Supervisor, see [queue-workers.md](queue-workers.md) |
| Stuck deploy lock | a killed run can leave Deployer's lock: `./dep deploy:unlock production` |

### GitHub secrets/variables the pipeline uses

| Name | Type | Purpose |
| --- | --- | --- |
| `DEPLOY_SSH_KEY` | secret | private key, Actions → VPS root |
| `SSH_KNOWN_HOSTS` | secret | pinned host key for the VPS |
| `HEALTH_CHECK_TOKEN` | secret | must equal `HEALTH_CHECK_TOKEN` in `shared/.env` |
| `DEPLOY_ENABLED` | variable | `true` enables deploys; anything else skips them |

## Scheduler (cron)

```cron
* * * * * cd /home/user/htdocs/tuning-portal/current && php artisan schedule:run >> /dev/null 2>&1
```

Confirm registered schedule with `php artisan schedule:list`.

## Post-deploy verification

```bash
# Framework boots + central DB and cache reachable → HTTP 200, {"status":"ok"}
curl -fsS "https://tuning-portal.com/healthz?token=..."

# Queue is draining (should be empty or shrinking)
cd /home/user/htdocs/tuning-portal/current && php artisan queue:failed

# Mail transport works end-to-end
php artisan mail:test you@example.com
```

If `/healthz` returns 503, the JSON `checks` object names the failing
dependency (`database` or `cache`). Errors are also reported to Sentry.

## Pre-flight (once per environment)

- `shared/.env` is populated from `.env.example` — every key present, secrets
  set. Confirm `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` set.
- `QUEUE_CONNECTION=database`, and a queue worker is provisioned
  (see [queue-workers.md](queue-workers.md)).
- Mail transport + DNS configured (see [email-deliverability.md](email-deliverability.md)).
- Backups configured to the private R2 bucket (see [backups.md](backups.md)).
- The scheduler cron is installed (see **Scheduler** above).

## Staging

Run an identical environment on a separate host/subdomain with
`APP_ENV=staging`, its **own** database server and its **own** R2 buckets —
never point staging at production data or the production backup bucket.
Deploy to staging first, smoke-test `/healthz`, a dealer login, a file upload,
and a Stripe test-mode payment before promoting the release to production.
