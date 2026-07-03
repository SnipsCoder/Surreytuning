# Commercial Hardening Progress

Tracks the 10-phase effort to make Surrey Tuning production-grade. This file is
the durable source of truth for what is done vs. pending — update it after every
phase. (Separate from the CLAUDE.md build Phases 0–10.)

**Locked infra decisions:** Queue = Database queue · Monitoring = Sentry ·
Backups = Cloudflare R2 (separate private bucket) via spatie/laravel-backup ·
CI = GitHub Actions · Payments = Stripe (fixed).

**Baseline:** 102 tests / 274 assertions → **105 / 280 at completion.** Kept green after every phase.

| # | Phase | Status |
| --- | --- | --- |
| 1 | CI/CD (GitHub Actions: Pint + PHPUnit + npm build) | ✅ Complete |
| 2 | Error monitoring (Sentry, tenant-tagged) | ✅ Complete |
| 3 | Queue infrastructure | ✅ Complete |
| 4 | Automated backups (spatie → R2) | ✅ Complete |
| 5 | Security hardening | ✅ Complete |
| 6 | Payments robustness | ✅ Complete |
| 7 | Tenant lifecycle | ✅ Complete |
| 8 | Legal/GDPR | ✅ Complete |
| 9 | Email deliverability | ✅ Complete |
| 10 | Ops polish | ✅ Complete |

**All 10 phases complete.** Suite at 105 tests / 280 assertions, green.

## Phase 1 — CI/CD ✅
- Added `.github/workflows/ci.yml`: PHP 8.4, Node 20, `composer install`,
  `npm ci`, `pint --test`, `php artisan test`, `npm run build`. Runs on push to
  master/main + PRs. Inert until a GitHub remote is added.
- Normalized whole codebase with Pint (formatting only, no logic changes).

## Phase 2 — Error monitoring ✅
- Sentry Laravel installed; `config/sentry.php` published.
- `Integration::handles($exceptions)` registered in `bootstrap/app.php`.
- `AppServiceProvider::tagSentryWithTenant()` sets a `tenant_id` scope tag on
  each event (tenant key when initialized, `central` otherwise). No-op without a
  DSN; PII off by default.

## Phase 3 — Queue infrastructure ✅
- Verified **all 7 listeners** and **all 7 notifications** implement
  `ShouldQueue`. `QueueTenancyBootstrapper` already enabled → jobs are
  tenant-aware automatically. Central `jobs` / `job_batches` / `failed_jobs`
  tables already migrated. `QUEUE_CONNECTION` default is `database`.
- Documented supervised worker, deploy `queue:restart`, and failed-job handling
  in `docs/queue-workers.md`.
- **Decision:** file uploads stay synchronous (R2 put inside request) by design;
  only notifications/side-effects are queued. Rationale documented.

## Phase 4 — Automated backups ✅
- `config/backup.php`: destination disk → `r2_backups` (dedicated **private** R2
  bucket, separate from the `r2` upload bucket); central source DB restricted to
  `['mysql']` (never sqlite/default); `backup.name` → `BACKUP_NAME`
  (`surreytuning-central`); notify address → `BACKUP_NOTIFICATION_EMAIL`;
  `monitor_backups` aligned to the `r2_backups` disk. Archives AES-256 encrypted
  via `BACKUP_ARCHIVE_PASSWORD`.
- `app/Console/Commands/BackupTenantDatabases.php` (`backup:tenants`): loops
  tenants, initializes tenancy, overrides `backup.source.databases => ['tenant']`
  + a per-tenant folder (`tenant-<id>/`), runs `backup:run --only-db`, restores
  config in a `finally`, and continues-on-failure with a summary + Sentry report.
  Supports `--tenants=` to scope to specific ids.
- Scheduling in `routes/console.php` (Schedule facade): `backup:clean` 01:00,
  central `backup:run --only-db` 01:30, `backup:tenants` 02:00, `backup:monitor`
  08:00. Needs the `schedule:run` cron in production.
- `.env.example`: added primary R2 block + `R2_BACKUP_*`, `BACKUP_NAME`,
  `BACKUP_NOTIFICATION_EMAIL`, `BACKUP_ARCHIVE_PASSWORD`.
- `docs/backups.md`: what's backed up, prerequisites, manual runs, and a
  step-by-step **tested restore** procedure (central + tenant) with verification.
- **Decision:** DB-only backups (`--only-db`). Uploaded files already live
  durably in R2; code lives in git — the irreplaceable asset is the databases.
- Verified: `backup:tenants` registered, `schedule:list` shows all four jobs,
  Pint clean, **62/62 tests green**.

## Phase 5 — Security hardening ✅
- **2FA recovery codes.** New tenant migration
  `2026_07_01_100000_add_two_factor_recovery_codes_to_users_table` (nullable
  `two_factor_recovery_codes`, `encrypted:array` cast on `User`).
  `TwoFactorController::confirm()` generates 8 single-use codes
  (`Str::random(10).'-'.Str::random(10)`), persists them, and flashes them once
  via `->with('recovery_codes', …)`. `verify()` accepts a live OTP/TOTP **or** one
  unused recovery code (`consumeRecoveryCode()` removes it via a `hash_equals`
  filter); `disable()` clears them. One-time display banners added to **both**
  layouts (`layouts/app.blade.php` owner, `layouts/client.blade.php` client).
- **Owner money/credit audit log.** New tenant migration
  `2026_07_01_110000_create_audit_logs_table` — polymorphic, actor-tracked
  (`user_id` nullOnDelete), `amount` DECIMAL(10,2), `reason`, JSON `metadata`,
  `ip_address`, `created_at` only. `App\Models\AuditLog` with reusable static
  `record($action, $actor, $subject, $amount, $reason, $metadata)` that captures
  actor + request IP automatically. Wired into **all six** sensitive owner
  actions: `FileRequestController` (charge_added, credit_added, voided) and
  `DealerController` (credits_adjusted, suspended, reactivated).
- **2FA enforcement** already provided by `EnsureTwoFactorAuthenticated`
  (owner/technician mandatory setup; confirmed users challenged per session;
  2FA/logout/password routes exempt) — now covered by tests.
- **Tests added (+11):** `tests/Feature/Owner/AuditLogTest.php` (6 owner actions
  write correct action/actor/amount/reason + IP capture);
  `tests/Feature/Auth/TwoFactorEnforcementTest.php` (force-setup, per-session
  challenge, verified pass-through, no-intercept of 2FA routes).
- Both pending migrations applied to the live `surrey-tuning` tenant.
- Verified: Pint clean, **73/73 tests green (179 assertions)**.

> Rate limiting on auth/verify routes (`throttle:6,1` on 2FA verify/resend,
> `throttle:3,60` on dealer application) and server-side upload MIME validation
> were already present in the tree and are retained.

## Phase 6 — Payments robustness ✅
- **Event-level idempotency.** New tenant migration
  `2026_07_02_100000_create_processed_stripe_events_table` — `processed_stripe_events`
  with `event_id` (unique), `type`, `created_at` only. `App\Models\ProcessedStripeEvent`
  (`UPDATED_AT = null`, fillable `event_id`/`type`).
- **`StripeWebhookController::handle()` hardened.** After signature verification,
  the controller claims the Stripe `event->id` before running any handler: a
  fast-path `exists()` check short-circuits redeliveries with
  `200 "Webhook already handled"`, and the `create()` is wrapped in a
  `QueryException` catch so a concurrent delivery that loses the unique-insert
  race also acknowledges 200 and does nothing. This is layered **on top of** the
  existing per-payment-intent guard in each handler (slave_credits, evc_bundle,
  product, invoice) — two independent dedup layers. Signature verification stays
  active (400 on `UnexpectedValueException|SignatureVerificationException`).
- **Tests added (+10):** `tests/Feature/Webhooks/StripeWebhookIdempotencyTest.php`
  (new event processed + recorded; redelivered event is a no-op with
  `PaymentConfirmed` dispatched exactly once; invalid signature → 400, nothing
  recorded — StripeService mocked to bypass real signing);
  `tests/Unit/Services/CreditServiceTest.php` (add/deduct slave + evc, insufficient
  balance, manual negative adjustment, DB rollback);
  `tests/Unit/Services/InvoiceServiceTest.php` (VAT calc, sequential numbering,
  skip-VAT, mark-paid + payment-intent preservation, void).
- Migration applied to the live `surrey-tuning` tenant.
- Verified: Pint clean, **83/83 tests green (209 assertions)**.

## Phase 7 — Tenant lifecycle ✅
- **Provisioning kept synchronous — documented decision.** The
  `TenantCreated` (CreateDatabase → MigrateDatabase → SeedDatabase) and
  `TenantDeleted` (DeleteDatabase) job pipelines in `TenancyServiceProvider`
  are **deliberately left `shouldBeQueued(false)`**. Tenants are provisioned/
  offboarded rarely, by the operator via console commands — there is no
  self-service web signup. Synchronous execution gives truthful immediate
  feedback and lets the command roll back a partial provision, versus a silent
  failure if a worker is down. The stock "you probably want to make this true"
  comment was replaced with this rationale on both pipelines.
- **`tenants:create` hardened (rollback on partial provision).** The
  `Tenant::create()` + domain creation is wrapped in a try/catch: if the
  synchronous provisioning pipeline throws mid-way, the command deletes the
  central record (firing DeleteDatabase to drop any half-built DB, cascading
  the domain), `report()`s the error, and returns FAILURE — no orphaned record
  or half-built database is left behind for the operator to clean up by hand.
- **`tenants:delete` (offboarding / GDPR erasure).** New
  `app/Console/Commands/TenantDeleteCommand.php` —
  `tenants:delete {id} {--force} {--skip-backup}`. Verifies the tenant exists;
  warns + confirms the irreversible op (skippable with `--force`); takes a
  **final backup first** via `backup:tenants --tenants=<id>` and aborts if it
  fails (override with `--skip-backup`); then `$tenant->delete()` fires the
  synchronous `TenantDeleted → DeleteDatabase` pipeline (drops the tenant DB)
  while the `domains` FK cascade removes the domain rows with the record.
- **Tests added (+4):** `tests/Feature/Console/TenantLifecycleCommandTest.php`
  — create provisions DB + domain + record; create rejects a duplicate id;
  delete removes the tenant, its DB file, and cascades domains; delete fails
  for an unknown id. Tests pass `--skip-backup` (R2 is unavailable in the test
  env) and clean up the second tenant's SQLite file in `tearDown`.
- Verified: Pint clean, **87/87 tests green (221 assertions)**.

## Phase 8 — Legal/GDPR ✅
- **`app/Services/DataSubjectService.php`** — subject-level data portability + erasure.
  - `export(Dealer)` builds a complete, secret-free portability package: dealer
    profile, users (mapped whitelist — **no** password / 2FA secret / OTP fields),
    matched dealer applications, file requests, invoices, and slave/EVC credit
    transactions. `exported_at` ISO-8601 stamped.
  - `erase(Dealer, ?actor, ?reason)` runs in a DB transaction: anonymises matched
    applications, anonymises + soft-deletes users (clears password, avatar,
    WhatsApp, **all** 2FA + OTP fields, remember token, placeholder
    `erased-user-{id}@gdpr.invalid` email), anonymises + soft-deletes the dealer
    (`Erased dealer #id`, address/notes nulled). **Financial records (invoices)
    are retained** for the statutory accounting period. Writes a `gdpr_erased`
    `AuditLog` entry with per-entity counts.
- **Console commands.** `gdpr:export {dealer}` (writes an encrypted-at-rest JSON
  package to `config('gdpr.export_path')` on the configured disk; filename
  `dealer-{id}-{timestamp}.json`), `gdpr:erase {dealer} {--force}` (confirm-guarded
  right-to-erasure), `gdpr:prune {--dry-run}` (retention sweep — deletes rejected
  applications older than the retention window, keeps approved ones forever, clears
  expired email OTP codes). All fail cleanly on unknown dealer ids.
- **`config/gdpr.php`** — `export_disk` (`GDPR_EXPORT_DISK`, default `local`),
  `export_path` (`gdpr-exports`), `retention.rejected_applications_days`
  (`GDPR_RETAIN_REJECTED_APPLICATIONS_DAYS`, default 365),
  `privacy_contact_email` (`GDPR_PRIVACY_CONTACT_EMAIL`).
- **Public legal pages.** `App\Http\Controllers\LegalController` + public
  `/terms` (`legal.terms`) and `/privacy` (`legal.privacy`) routes in
  `routes/tenant.php` — inside the tenancy group, **not** behind auth. Terms pull
  the tenant's editable `Setting::terms_and_conditions`.
- **Tests added (+13):** `tests/Feature/Gdpr/DataSubjectServiceTest.php` (5 —
  export completeness, no-secret-leak, erase anonymise+soft-delete, financial
  retention, audit-log entry); `tests/Feature/Gdpr/GdprCommandTest.php` (6 —
  export-to-disk, erase, prune + dry-run, unknown-id failures);
  `tests/Feature/Gdpr/LegalPagesTest.php` (2 — /terms + /privacy render).
- Verified: Pint clean, **100/100 tests green (271 assertions)**.

## Phase 9 — Email deliverability ✅
- **Assessment first.** The transactional email system was already substantially
  built: 7 queued `Notification` classes render branded HTML via a shared
  `<x-emails.layout>` blade component; 7 queued listeners dispatch them to each
  dealer's primary contact; 2FA codes use a deliberately **synchronous**
  `TwoFactorCodeMail`. So Phase 9 focused on **deliverability + operability**, not
  rebuilding emails.
- **`app/Console/Commands/MailTestCommand.php`** (`mail:test {recipient}`) — sends a
  plain test message through the **configured** mailer, first echoing the active
  mailer + From address, so credentials and the global From can be verified
  end-to-end after deploy. Validates the recipient address and fails cleanly
  (catching transport `Throwable`s → FAILURE) so misconfiguration is obvious.
- **`.env.example` mail block expanded** — documented that local dev uses the
  `log` mailer, that production needs an authenticated relay, and that
  **SPF/DKIM/DMARC DNS is the operator's responsibility**. Added commented
  production examples for SMTP (TLS/587) and Amazon SES, plus the `mail:test`
  verification hint.
- **`docs/email-deliverability.md`** — how mail is wired (queued notifications +
  sync 2FA, and the hard requirement that a **queue worker run in production**),
  provider comparison (SES/Postmark/Resend/SMTP), the three required DNS records
  with example values and a monitoring→enforce DMARC rollout, and how to verify
  with `php artisan mail:test`.
- **Tests added (+2):** `tests/Feature/Mail/MailTestCommandTest.php` — valid
  recipient dispatches successfully (under `Mail::fake()`); invalid recipient
  fails and sends nothing.
- **Decision:** no global reply-to / `MessageSending` listener added — the
  per-notification From/subject handling is sufficient and adding a global hook
  would be speculative. Left for a future need.
- Verified: Pint clean, **102/102 tests green (274 assertions)**.

## Phase 10 — Ops polish ✅
- **Tenant-aware deep health check.** `App\Http\Controllers\HealthController`
  (invokable) at `GET /healthz` in `routes/web.php` — probes the **central
  database** (`select 1` on `tenancy.database.central_connection`) and the
  **cache** (put/forget round-trip), returning `200 {"status":"ok"}` when both
  pass and `503 {"status":"down", "checks": {...}}` when any dependency is down,
  so an uptime monitor can page. Unlike Laravel's default `/up` (framework-boot
  only), this confirms the dependencies without which no tenant can be served.
  Optionally guarded by `HEALTH_CHECK_TOKEN` — when set, a missing/mismatched
  `?token=` returns **404** (via `hash_equals`) so uptime details aren't public.
  Failed probes are `report()`ed to Sentry. Registered as an invokable controller
  class (not a closure) so `route:cache` succeeds in production. Config key
  `app.health_check_token` registered in `config/app.php`.
- **`docs/deploy-runbook.md`** — canonical ordered deploy procedure: pull +
  `composer install --no-dev` + asset build, **`migrate --force`** (central) and
  **`tenants:migrate --force`** (every tenant), cache rebuild in the correct
  order (**`route:cache`/`event:cache`/`view:cache` before `config:cache`**, with
  the reasoning), **`queue:restart`**, and the `schedule:run` cron. Includes the
  never-run-`migrate:fresh` warning, post-deploy `/healthz` + `mail:test`
  verification, a rollback procedure, and a **staging** environment section
  (separate DB + separate R2 buckets, never pointed at production data).
- **`.env.example` completeness** — added the GDPR block (`GDPR_EXPORT_DISK`,
  `GDPR_RETAIN_REJECTED_APPLICATIONS_DAYS`, `GDPR_PRIVACY_CONTACT_EMAIL`) and
  `HEALTH_CHECK_TOKEN`, each documented inline.
- **Tests added (+3):** `tests/Feature/HealthCheckTest.php` — healthy → 200 with
  `status: ok` and both checks `ok`; token guard → 404 when the token is
  missing/wrong; correct token → 200.
- Verified: Pint clean, **105/105 tests green (280 assertions)**.
