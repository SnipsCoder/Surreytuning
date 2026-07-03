# Pre-Deploy Checklist

Work top to bottom before pointing production traffic at the app. Anything left
unchecked is a known risk, not a blocker you've forgotten.

## 1. `.env` on the production server (never commit this file)

`.env` is gitignored — copy `.env.example` to `.env` on the server and set real
values. The values below are the production-critical deltas from local dev.

| Key | Local (dev) | Production |
| --- | --- | --- |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | **`false`** (leaks stack traces + config if left on) |
| `APP_URL` | `http://surreytuning.test` | `https://surreytuning.co.uk` (real HTTPS host) |
| `APP_KEY` | present | run `php artisan key:generate` once; back it up (encrypted data is unrecoverable without it) |
| `LOG_LEVEL` | `debug` | `warning` or `error` |
| `SESSION_SECURE_COOKIE` | `false` | **`true`** (requires TLS to be live first) |
| `SESSION_DOMAIN` | `null` | `.surreytuning.co.uk` so sessions work across the wildcard subdomains |
| `STRIPE_KEY` / `STRIPE_SECRET` | test keys / blank | **live** `pk_live_…` / `sk_live_…` |
| `STRIPE_WEBHOOK_SECRET` | blank | value from the Stripe dashboard webhook endpoint |
| `MAIL_MAILER` | `log` | `smtp` / `ses` / `resend` with real credentials |
| `MAIL_FROM_ADDRESS` | example.com | a domain you control with SPF/DKIM/DMARC set |
| `FILE_STORAGE_DISK` / `R2_*` | blank | real R2 bucket + keys (private) |
| `R2_BACKUP_*` + `BACKUP_ARCHIVE_PASSWORD` | blank | dedicated backup bucket + strong archive password, stored OFF the backup bucket |
| `SENTRY_LARAVEL_DSN` | blank | real DSN (blank = error monitoring disabled) |
| `HEALTH_CHECK_TOKEN` | blank | strong random token so `/healthz` isn't world-readable |
| `GDPR_PRIVACY_CONTACT_EMAIL` | blank | real contact shown on the privacy policy |

## 2. Infrastructure (outside the codebase)

- [ ] **TLS certificate** covering the apex **and** wildcard (`*.surreytuning.co.uk`)
      — tenants resolve by subdomain, so a single-host cert isn't enough.
- [ ] **Cron for the scheduler** — `* * * * * php artisan schedule:run` (drives
      daily backups, backup monitoring, and the GDPR prune). See `routes/console.php`.
- [ ] **Persistent queue worker** — `QUEUE_CONNECTION=database`, so jobs (invoice
      emails, Stripe processing) only run if a worker is alive. Use supervisor /
      systemd to keep `php artisan queue:work` running. See `docs/queue-workers.md`.
- [ ] **Backup destination reachable** — confirm the R2 backup bucket exists and
      `php artisan backup:run --only-db` succeeds; then verify a restore.
- [ ] Web server document root points at `public/`, not the project root.

## 3. Build & cache (run on the server after each deploy)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force            # central DB
php artisan tenants:migrate --force    # every tenant DB
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Never run `php artisan migrate:fresh` in production — it drops the central
tenants/domains tables.

## 4. Smoke test after go-live

- [ ] `php artisan mail:test you@example.com` lands in an inbox (not spam).
- [ ] `/healthz?token=<HEALTH_CHECK_TOKEN>` returns healthy.
- [ ] Owner portal rejects a non-owner account (owner protection intact).
- [ ] A test Stripe payment completes and the webhook marks the invoice paid.
- [ ] Confirm `APP_DEBUG=false` by hitting a bad URL — you should get a plain 404,
      not a stack trace.
