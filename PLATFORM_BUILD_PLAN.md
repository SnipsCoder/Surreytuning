# Surrey Tuning — SaaS Platform Build Plan
## Master-Hosted Multi-Tenant Platform | v1.0 | July 2026

This document is the build specification for converting the Surrey Tuning portal into a **commercial SaaS platform**: we host the master platform, assign companies (tenants) their own sub-business on it, and they pay a monthly fee for access. It is written to be executed phase-by-phase by Claude (Sonnet) with sign-off gates between phases.

**Read `CLAUDE.md` and `COMMERCIAL_HARDENING.md` first.** All rules in those documents remain in force (tenant migration rules, never `migrate:fresh` on central, dark-first UI, Pint + green tests after every phase). This document extends them; where anything conflicts, this document wins.

---

## 1. Current state (verified against the codebase, July 2026)

Already built and NOT to be redone:

- **Multi-tenancy**: `stancl/tenancy` v3, database-per-tenant, domain-based routing (`InitializeTenancyByDomain` + `PreventAccessFromCentralDomains`). Central DB holds only tenancy infrastructure; each tenant DB holds all 24+ application tables.
- **Tenant lifecycle (operator CLI)**: `tenants:create` (hardened, rolls back partial provisions), `tenants:delete` (final backup, GDPR erasure), `backup:tenants`.
- **Hardening complete (10/10 phases)**: CI (GitHub Actions), Sentry (tenant-tagged), database queue with tenant-aware jobs, spatie backups → Cloudflare R2, security pass, Stripe webhook idempotency (`processed_stripe_events`), GDPR export/erase/prune, email deliverability (Resend), ops polish. Suite green at 105 tests / 280 assertions.
- **Per-tenant branding**: `settings` table has `brand_name`, `support_email`, `theme_colour`.
- **Per-tenant Stripe keys — schema only**: `settings` has `stripe_public_key` / `stripe_secret_key`, **but `StripeService` still reads the global `config('services.stripe.*')`** — the per-tenant keys are not wired in (see Phase 1).

## 2. What this plan adds (the gaps)

1. **Per-tenant Stripe, completed** — each tenant transacts on their own Stripe account end-to-end (Phase 1).
2. **Central platform application** — master admin portal on the central domain: tenant management, health, metrics (Phase 2).
3. **Platform subscription billing** — tenants pay us monthly via Stripe (our account, Laravel Cashier); non-payment automatically suspends the tenant (Phase 3).
4. **Automated onboarding** — self-serve signup → operator one-click approval → automatic provisioning → guided tenant setup wizard (Phase 4).
5. **Production deployment** — Hostinger VPS, wildcard subdomains + SSL, supervised workers, go-live runbook (Phase 5).

## 3. Locked decisions (do not re-litigate during build)

| Decision | Value | Rationale |
|---|---|---|
| Tenant payment mechanism | **Per-tenant Stripe API keys** (existing schema approach, completed) | Already half-built; DB-per-tenant makes per-tenant keys coherent; webhook URL is already per-tenant (tenant domain). Stripe Connect Standard remains a possible future migration — do not build it now. |
| Platform fee | **Single flat monthly plan** via Laravel Cashier on the central app | Tiers can be added later without rework. |
| Tenant identity | **Subdomain per tenant** (`{slug}.PLATFORM_DOMAIN`), custom domains later | Wildcard DNS + one wildcard cert; `domains` table already supports arbitrary domains when custom domains come. |
| Email uniqueness | Per-tenant (inherent — users live in the tenant's own DB) | Already satisfied by DB-per-tenant. No change needed. |
| Signup approval | **Manual approval gate** in master portal before provisioning | One click per tenant; fraud protection while the platform is young. Everything after approval is automatic. |
| Provisioning execution | **Queued** (revisits the documented sync decision) | Self-serve signup can't hold an HTTP request through DB creation + migration + seed. Reuse the hardened rollback logic inside the job. |
| Hosting | Hostinger VPS (nginx, PHP-FPM, MySQL, supervisor, certbot) | Client decision. |

## 4. Ground rules for the build agent

- Work phase by phase, **in order**. Do not start a phase until the previous phase's exit gate is met and signed off.
- One feature branch per phase: `phase-1-tenant-stripe`, `phase-2-central-portal`, etc. Small, reviewable commits.
- **Tests are the gate.** Every phase adds tests; the full suite must be green (and Pint clean) before a phase is complete. Never weaken or delete an existing test to get to green — if a test fails, the code is wrong or the test needs a justified update, explained in the commit message.
- **Never** run `php artisan migrate:fresh` against the central database. Tenant schema changes go in `database/migrations/tenant/`; central changes in `database/migrations/`.
- New central tables must not leak into tenant DBs and vice versa. Every migration states its target in a comment header.
- No new packages without stating why in the commit; prefer first-party Laravel (Cashier, Fortify patterns already in use via Breeze).
- Secrets: never commit keys. New env vars go in `.env.example` with a comment, and are listed in the phase's exit notes.
- Update `PLATFORM_BUILD_PLAN.md`'s status table (§7) after every phase, same discipline as `COMMERCIAL_HARDENING.md`.
- **Every phase must pass its section of `PLATFORM_TEST_PLANS.md`** — the automated minimum set, the operator's manual verification script, and the regression gate — before it is marked complete. The manual script is run by the operator; request sign-off explicitly.

---

## 5. Known landmines (found in pre-build code sweep, July 2026)

These are verified against the code, not hypothetical. Phase 0.5 fixes them **before** any platform work begins. Sonnet: do not skip or reorder these — A and B are the two defects most likely to cause a catastrophic multi-tenant incident later.

**A — CRITICAL: cross-tenant file collisions on R2.** `FileStorageService::storeFile()` writes to `files/{dealerId}/{requestNumber}/...` on the shared `r2` disk. The `FilesystemTenancyBootstrapper` only suffixes the `local`/`public` disks — s3/r2 is not tenant-aware (commented out in `config/tenancy.php`). Dealer IDs and request numbers are auto-increment **per tenant DB**, so tenant A's `files/1/...` and tenant B's `files/1/...` collide in the same bucket: silent overwrites and cross-tenant signed-URL access. Invisible today only because one tenant exists.
*Fix:* prefix all R2 object keys with the tenant key (`tenants/{tenant_id}/files/...`) inside `FileStorageService`; write a one-off command to move existing `surrey-tuning` objects to the new prefix and update `file_request_attachments.file_path`; add a test asserting two tenants storing the same dealer/request path produce distinct keys.

**B — HIGH: `Setting::$instance` static cache leaks across tenants in queue workers.** The memoised singleton is only cleared in `SettingsController`. `QueueTenancyBootstrapper` switches the DB per job, but a long-running worker keeps the PHP process — tenant A's settings (brand, VAT rate, and after Phase 1 **their Stripe keys**) would be used for tenant B's job.
*Fix:* register listeners on `TenancyInitialized` and `TenancyEnded` that call `Setting::clearCache()` (in `TenancyServiceProvider::events()`); test with two tenants that a queued job resolves each tenant's own settings.

**C — MEDIUM: invoice numbering formula is wrong.** `InvoiceService`: `invoice_number = invoice_start_number + max(invoice_number)` produces 10000, 20000, 30000… (the unit test only asserts "greater than", so it passes). Also `Invoice::lockForUpdate()->max()` provides no lock on an empty table.
*Fix:* `next = max(invoice_number) + 1`, falling back to `invoice_start_number`; keep the unique index as the backstop; tighten the test to assert consecutive numbers.

**D — MEDIUM: credit deduction race.** `CreditService::deduct*Credits()` checks the balance on the in-memory model without `lockForUpdate()` — two concurrent deductions can both pass the check and drive the balance negative.
*Fix:* re-fetch the dealer with `lockForUpdate()` inside the transaction before the balance check, in all four deduct/adjust methods. Note: SQLite (test env) ignores row locks, so assert the logic change, not the race itself.

**E — MEDIUM: plaintext secrets in `settings`.** `evc_password` (and the orphaned Stripe key columns) are unencrypted `VARCHAR(255)`. Laravel `encrypted` casts produce payloads longer than 255 chars.
*Fix (folds into Phase 1):* tenant migration changing secret columns to `TEXT`; add `encrypted` casts for `evc_password`, `stripe_public_key`, `stripe_secret_key`, `stripe_webhook_secret`; one-off re-encrypt for any existing values. **APP_KEY becomes unrecoverable-loss-critical — confirm it is in the ops secrets vault and referenced in the backup/restore runbook.**

**F — HYGIENE: dirty working tree.** ~10 modified files uncommitted (vehicle stats, portal users, layouts, routes). Commit or intentionally discard before the `pre-platform-build` tag, so the rollback point is meaningful.

**G — TEST-ENV PARITY (standing note):** tests run on in-memory SQLite; production is MySQL. Enum changes, row locking, and `ON DELETE` behaviour differ. Any migration touching enums or locks must be manually verified against MySQL before deploy (document in the phase's exit notes).

**Cashier + stancl integration note (for Phase 3):** Cashier's `Billable` models (subscriptions, etc.) must be pinned to the **central** connection (`Stancl\Tenancy\Database\Concerns\CentralConnection` on any Cashier-related model, and the `Tenant` billable itself is central). Never let Cashier queries run while a tenant DB is the default connection without explicit connection pinning — this is the classic stancl+Cashier failure mode.

---

## Phase 0 — Audit, baseline and safety net

**Objective:** verify this document's assumptions against the code as it stands; establish the rollback position.

Tasks:
1. Run the full test suite and record the baseline (expected ~105 tests green). Fix nothing yet; if the suite is red, stop and report.
2. Verify the §1 findings: per-tenant Stripe keys unwired, no central app tables, no subscription billing, CLI-only provisioning. Note any drift in a short `docs/phase-0-audit.md`.
3. Confirm local dev works end-to-end with two tenants: create a second tenant (`tenants:create demo-tuning demo.surreytuning.test`), log into both, confirm complete isolation (users, file requests, settings, branding).
4. Take a full backup (`backup:tenants` + central DB dump). Record restore steps in the audit doc.
5. Tag the repo: `git tag pre-platform-build`.

**Exit gate:** audit doc committed; two-tenant isolation demonstrated; baseline green; tag pushed.

---

## Phase 0.5 — Defect remediation (fix the landmines before building anything)

**Objective:** clear §5 items A–D and F so the platform build starts from a sound base. E folds into Phase 1.

Tasks (each is its own commit with its own tests):
1. **F** — resolve the dirty working tree (commit or discard, stated in the commit message), then tag `pre-platform-build`.
2. **A** — tenant-prefix all R2 object keys in `FileStorageService`; migration command for existing objects + `file_request_attachments.file_path` rows; isolation test.
3. **B** — clear `Setting::$instance` on `TenancyInitialized`/`TenancyEnded`; two-tenant queue test.
4. **C** — fix invoice numbering to true sequential; tighten the unit test to assert exact consecutive numbers.
5. **D** — `lockForUpdate()` re-fetch in all CreditService deduct/adjust methods.

**Exit gate:** all five fixes merged with tests; full suite green; the two-tenant isolation demo from Phase 0 re-run including a file upload in each tenant (verifying distinct R2 keys).

---

## Phase 1 — Per-tenant Stripe (complete the existing approach)

**Objective:** every tenant transacts on **their own** Stripe account. The platform never touches tenant revenue.

Tasks:
1. **Tenant migration**: add `stripe_webhook_secret` to `settings` (the two key columns exist; the webhook secret does not). Encrypt all three at rest using Laravel's `encrypted` cast on the `Setting` model.
2. **Rework `StripeService`**: constructor takes keys from the current tenant's settings (`Setting` lookup, cached per request), not `config('services.stripe.*')`. Fail loudly with a clear exception if a tenant has no keys configured and a payment path is hit.
3. **Webhook verification per tenant**: `StripeWebhookController` verifies the signature with the tenant's own `stripe_webhook_secret`. The route is already on the tenant domain, so Stripe events arrive pre-scoped — keep the existing `processed_stripe_events` idempotency exactly as is.
4. **Owner settings UI**: add a "Payments" section in owner settings — publishable key, secret key (write-only display: show last 4 only), webhook secret, plus the tenant's webhook URL displayed for copy-paste (`https://{tenant-domain}/webhooks/stripe`) and the list of events to subscribe to.
5. **Config cleanup**: remove tenant-payment usage of global Stripe config. The global keys will be reused by Phase 3 for *platform* billing — rename config keys to make the two unmistakable: `services.stripe_platform.*` (central) vs per-tenant settings (tenant).
6. **Tests**: settings encryption round-trip; StripeService resolves tenant keys; webhook rejects a signature signed with the wrong tenant's secret; payment paths fail cleanly when keys are missing; existing payment tests updated to seed tenant keys.

**Exit gate:** a tenant with test-mode keys completes a real Stripe test purchase (credits or product) end-to-end, webhook processed, invoice generated — with no global Stripe key set for tenants anywhere. Suite green.

---

## Phase 2 — Central platform application (master portal)

**Objective:** a master admin area on the central domain to run the platform. Until now the central app has no UI at all.

Tasks:
1. **Central schema** (central migrations): `platform_users` (master operators; separate table and auth guard — never mix with tenant users), and extend `tenants` (stancl stores custom attrs in its JSON `data` column — add first-class columns via migration for: `name`, `slug`, `status` [`pending|active|past_due|suspended|cancelled`], `contact_name`, `contact_email`, timestamps for status changes). Add a `TenantStatus` enum.
2. **Central auth**: dedicated guard + login at `admin.{PLATFORM_DOMAIN}` or `/platform` on the central domain (choose `/platform` prefix — simpler nginx). 2FA mandatory for platform users (reuse the existing google2fa implementation pattern from tenant users).
3. **Master dashboard**: tenant list (status, domain, created, last activity), tenant detail page (status history, domains, backup status, quick links), platform stats (tenant count by status; MRR arrives in Phase 3).
4. **Tenant actions from the UI**: create (wraps the hardened provisioning logic), suspend/reactivate (sets status — enforcement comes in Phase 3), delete/offboard (wraps `tenants:delete` semantics: confirm phrase + final backup, queued).
5. **Central routing hygiene**: `routes/web.php` currently redirects `/` to `/login` which doesn't exist centrally — make `/` a minimal platform landing page (Phase 4 adds signup to it) and keep `/healthz` untouched.
6. **Audit logging**: platform actions (create/suspend/delete tenant, login) logged to a central `platform_audit_logs` table, mirroring the tenant `audit_logs` design.
7. **Tests**: guard separation (tenant creds cannot log into platform and vice versa), tenant CRUD via UI, suspend sets status, audit rows written.

**Exit gate:** operator can log into the master portal, see all tenants, create a new tenant from the browser, and suspend/reactivate it. Suite green.

---

## Phase 3 — Platform subscription billing (tenants pay us monthly)

**Objective:** automated monthly billing of tenants on **our** Stripe account, with non-payment handled without operator involvement.

Tasks:
1. **Install Laravel Cashier** (central connection only). Billable entity: the `Tenant` model. One flat plan — price ID in `services.stripe_platform.price_id`.
2. **Subscription lifecycle**: on tenant activation (Phase 4 signup or manual create), create the Stripe customer + subscription with a configurable trial (`PLATFORM_TRIAL_DAYS`, default 14). Store Cashier's columns on the central `tenants`/`subscriptions` tables.
3. **Central webhook** `POST /platform/webhooks/stripe` (central domain, platform keys, own signing secret, own idempotency table): `invoice.paid` → status `active`; `invoice.payment_failed` → status `past_due` (Stripe Smart Retries does the dunning); `customer.subscription.deleted` → status `suspended`.
4. **Enforcement middleware** (tenant side): after `InitializeTenancyByDomain`, check the central tenant record's status. `past_due` → yellow banner in tenant UI ("payment issue — service continues until {date}"); `suspended` → all tenant routes return a branded 402 "account suspended" page (allow login + a billing link only); `cancelled` → 410 page. Cache the status lookup (60s) so every tenant request doesn't hit the central DB.
5. **Billing portal**: tenant owner gets a "Platform subscription" page that redirects to the Stripe Customer Portal (Cashier `billingPortalUrl`) for card updates, invoices, cancellation.
6. **Master portal integration**: MRR + subscription status on the dashboard; manual override buttons (comp a tenant, extend trial) with audit logging.
7. **Tests**: webhook transitions status correctly (idempotent); middleware blocks suspended tenants and shows the banner for past_due; trial tenant is active; cached status invalidates on change.

**Exit gate:** with Stripe test clocks (or simulated webhooks), a tenant goes trial → active → past_due → suspended → reactivated with zero manual steps, and the tenant UI reflects each state. Suite green.

---

## Phase 4 — Automated onboarding (signup → live)

**Objective:** a company signs up, you click approve, and everything else is automatic.

Tasks:
1. **Public signup** on the central domain: company name, contact, desired subdomain (validated: slug format, reserved-word list, uniqueness against `domains`), password. Creates a tenant record with status `pending` — **no database provisioned yet**. Notify platform admins (email + dashboard badge).
2. **Approval gate**: master portal "Pending signups" queue — approve or reject (with reason, emailed). Approval dispatches the provisioning job.
3. **Queued provisioning job**: refactor the hardened logic from `TenantCreateCommand` into a `ProvisionTenant` job/service both the command and the queue use (single source of truth, keep the rollback-on-failure behaviour). On success: create the subdomain domain record, start the Cashier trial subscription (Phase 3), send the welcome email with the tenant's URL and a setup link. On failure: status `provision_failed`, Sentry alert, operator sees it in the dashboard with a retry button.
4. **First-run setup wizard** (tenant side, forced until complete): step 1 owner account confirm + 2FA; step 2 branding (brand name, colour, support email — prefilled); step 3 Stripe keys (the Phase 1 Payments panel, with a "test connection" button that calls Stripe's balance endpoint); step 4 operational settings (opening hours, file options/stages review); step 5 go-live confirmation. Wizard state stored in `settings`; a tenant can transact only after step 3 passes.
5. **Lifecycle emails** (Resend, queued): signup received, approved/welcome, rejected, provisioning failed (internal), trial ending (3 days before), payment failed, suspended, reactivated.
6. **Tests**: signup validation (slug collisions, reserved words); approval dispatches job; provisioning failure rolls back and flags; wizard gating blocks payments until Stripe verified; each email fires exactly once per event.

**Exit gate:** end-to-end demo on local/staging: sign up → approve in master portal → tenant provisioned automatically → wizard completed → tenant takes a Stripe test payment from a customer. Operator involvement: one click. Suite green.

---

## Phase 5 — Production deployment (Hostinger VPS) & go-live

**Objective:** the platform running in production with wildcard subdomains, SSL, supervised workers, and a tested runbook.

Tasks:
1. **Server build** (document every step in `docs/deploy-hostinger.md`): Ubuntu LTS, nginx, PHP 8.3-FPM, MySQL 8, Redis (optional — queue stays database per the locked decision), certbot. Non-root deploy user; UFW (22/80/443); fail2ban.
2. **DNS + SSL**: `A` record for the platform domain + wildcard `*.{PLATFORM_DOMAIN}` to the VPS. Wildcard certificate via certbot **DNS-01** (requires API access to the DNS provider — if the domain's DNS is at Hostinger, use the Hostinger DNS API; document the token setup). Auto-renew verified.
3. **nginx**: one vhost for the central domain, one wildcard vhost (`server_name *.{PLATFORM_DOMAIN}`) — both to the same Laravel app; tenancy resolves the rest. HTTP→HTTPS redirect; sane body size for file uploads (match current upload limits).
4. **App deployment**: deploy script (git pull, `composer install --no-dev`, `npm ci && npm run build`, config/route/view cache, `php artisan migrate --force` central + `tenants:migrate`, `queue:restart`). Wire into the existing GitHub Actions as a deploy job (SSH). Zero-downtime niceties (symlinked releases) optional — document the choice.
5. **Workers & schedule**: supervisor for `queue:work` (per `docs/queue-workers.md`); cron for `schedule:run` (backups, GDPR prune, trial-ending checks).
6. **Production config**: Sentry DSN, Resend production domain + SPF/DKIM verified, R2 backup credentials, platform Stripe live keys + webhook endpoint registered, `PLATFORM_DOMAIN`, central domains list updated in `config/tenancy.php`.
7. **Migration of the existing Surrey tenant (production customer data carries over)**: the current build will be live in production while this platform is built, so real customer data (dealers, users, file requests, credits, invoices, R2 files) must migrate at cutover. Procedure — rehearse on a copy first, then execute: maintenance window → final production backup (`backup:tenants` + central dump) → restore the tenant DB into the new platform as tenant `surrey-tuning` → `tenants:migrate` to apply this build's migrations on top → run the one-off data commands (Phase 0.5-A R2 re-prefix incl. `file_request_attachments.file_path` updates; Phase 1 encryption backfill) → smoke test (login, file download, credit balance, invoice PDF) → DNS switch (TTL lowered in advance). **Preconditions:** production `APP_KEY` carried over (or backfill re-encrypts under the new key), and every production hotfix made during the parallel build has been merged into `platform-build` — verify with a schema diff before cutover. Historical invoice numbers keep their values; the fixed sequence continues from the current max.
8. **Go-live checklist + smoke test**: signup flow with a real test company, tenant payment with a live-mode £1 product (refunded), platform subscription with a real card (cancelled), backup + restore drill, suspension drill.

**Exit gate:** production serves the master portal and at least two tenants over HTTPS on subdomains; a new tenant can be onboarded end-to-end in production; runbook and rollback documented. Suite green in CI.

---

## Phase 6 (optional, post-launch backlog — do not build now)

- Custom domains per tenant (domains table already supports it; needs per-domain certbot HTTP-01 automation)
- Tiered plans + usage limits (users, storage, file requests/month)
- Stripe Connect Standard migration (removes stored tenant keys)
- Master portal analytics (per-tenant usage, churn, cohort revenue)
- Tenant data export self-service (GDPR export exists as CLI)
- White-label email domains per tenant (Resend domain auth per tenant)

---

## 6. Effort guide (for planning, not a quota)

| Phase | Estimate (AI-assisted, based on this codebase's historic pace) |
|---|---|
| 0 — Audit & baseline | 0.5 week |
| 0.5 — Defect remediation (§5 landmines) | 0.5–1 week |
| 1 — Per-tenant Stripe | 1 week |
| 2 — Central portal | 1–1.5 weeks |
| 3 — Platform billing | 1 week |
| 4 — Automated onboarding | 1–1.5 weeks |
| 5 — Deployment & go-live | 1 week |
| **Total** | **~6–7.5 weeks** |

The heavy lifting (multi-tenancy itself) is already done and hardened — this plan is additive: it never restructures tenant data, so risk to the existing Surrey Tuning instance is low and it keeps running throughout.

## 7. Status (update after every phase)

| Phase | Status | Notes |
|---|---|---|
| 0 — Audit & baseline | ⬜ Not started | |
| 0.5 — Defect remediation | ⬜ Not started | §5 landmines A–D, F |
| 1 — Per-tenant Stripe | ⬜ Not started | includes §5-E |
| 2 — Central portal | ⬜ Not started | |
| 3 — Platform billing | ⬜ Not started | |
| 4 — Automated onboarding | ⬜ Not started | |
| 5 — Deployment & go-live | ⬜ Not started | |
