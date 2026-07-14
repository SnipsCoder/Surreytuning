# Platform Build — Per-Phase Test Plans

Companion to `PLATFORM_BUILD_PLAN.md`. A phase is not complete until all three layers pass:

1. **Automated** — tests Sonnet writes in the phase; listed here as the minimum set. Full suite green + Pint clean.
2. **Manual verification** — a click-through script the operator (Dean) runs before sign-off.
3. **Regression gate** — proves existing behaviour didn't break.

Conventions: manual scripts assume two local tenants — `surrey-build` (`surreytuning-build.test`) and a second tenant `demo` (`demo.surreytuning-build.test`) created in Phase 0. Stripe steps use test mode + Stripe CLI (`stripe listen`). Never run destructive commands against the central DB.

---

## Phase 0 — Audit & baseline

**Automated (existing suite):**
- Full suite green at the recorded baseline count. No new tests required — this phase records, not changes.

**Manual verification:**
1. `php artisan test` — record count/assertions in `docs/phase-0-audit.md`.
2. Create the second tenant: `php artisan tenants:create demo demo.surreytuning-build.test`.
3. Log into both tenants with their seeded admins in two browser sessions.
4. In `surrey-build`: create a dealer, a file request, and a settings change (brand name). Confirm none of it appears in `demo`, and vice versa.
5. Attempt to open a `surrey-build` file-request URL while authenticated to `demo` → expect 404/403.
6. Run `backup:tenants` — confirm backup artefacts produced; note restore steps in the audit doc.
7. Confirm git tag `pre-platform-build` exists after the dirty tree is resolved.

**Regression gate:** n/a (baseline).
**Sign-off:** audit doc committed; both tenants isolated; baseline recorded.

---

## Phase 0.5 — Defect remediation

**Automated (minimum new tests):**
- **A (R2 prefixing):** storing the same `dealerId/requestNumber/filename` under two initialized tenants produces two distinct object keys, each starting `tenants/{tenant_key}/`. Migration command test: seeded legacy-path attachment rows are rewritten and (with a fake disk) objects moved; command is idempotent (safe to re-run).
- **B (settings cache):** with two tenants, a queued job dispatched in tenant A then tenant B resolves each tenant's own `Setting` values (assert brand name differs); direct assertion that `TenancyEnded` clears the memoised instance.
- **C (invoice numbering):** first invoice = `invoice_start_number`; second = first + 1; third = second + 1 (exact equality, not "greater than").
- **D (credit locking):** deduction re-reads the dealer inside the transaction (assert fresh balance used); deduction below balance still throws `InsufficientCreditsException`; balances remain correct after sequential mixed add/deduct operations.

**Manual verification:**
1. Upload a file in each tenant; in the R2/local storage backend confirm both keys exist under their own `tenants/{key}/...` prefix.
2. Run the R2 migration command against seeded legacy data twice — second run reports nothing to do.
3. Start a queue worker; trigger a notification in tenant A then tenant B; confirm each email/log shows the right tenant's brand.
4. Create two invoices in the UI; confirm consecutive numbers.

**Regression gate:** full suite green; file download via signed URL still works in both tenants; existing invoice PDFs still render.
**Sign-off:** all five §5 fixes merged as separate commits, each with its tests.

---

## Phase 1 — Per-tenant Stripe

**Automated (minimum new tests):**
- Settings secret columns round-trip through `encrypted` casts; raw DB value is not plaintext.
- `StripeService` resolves keys from the current tenant's settings; with tenant A initialized it never uses tenant B's keys (assert on the configured client/key).
- Payment routes return a clear, branded error (not a 500) when the tenant has no Stripe keys configured.
- Webhook: signature signed with the wrong tenant's secret → 400; correct secret → 200 and processed exactly once (idempotency preserved — reuse existing `processed_stripe_events` tests, now per-tenant).
- Settings UI: secret fields are write-only (response never contains the stored secret; shows last-4 only).
- Config: no code path for tenant payments references `services.stripe.*` (static grep test or architecture test).

**Manual verification:**
1. In tenant `surrey-build` settings → Payments: enter Stripe **test** keys; "test connection" button succeeds; wrong key fails cleanly.
2. `stripe listen --forward-to surreytuning-build.test/webhooks/stripe` with that account's webhook secret in settings.
3. Buy credits with card `4242 4242 4242 4242` → webhook fires → balance updates → invoice generated and marked paid.
4. In tenant `demo` with **no keys configured**: attempt purchase → friendly error page, no exception in Sentry/log beyond the expected warning.
5. Confirm the displayed webhook URL is the tenant's own domain.

**Regression gate:** all Phase 0.5 tests still green; non-payment areas (file requests, messaging) untouched and green.
**Sign-off:** end-to-end test purchase completed on a tenant's own Stripe account; no global tenant-payment keys anywhere.

---

## Phase 2 — Central platform application

**Automated (minimum new tests):**
- Guard separation: tenant user credentials cannot authenticate to `/platform/*`; platform user cannot authenticate to a tenant portal; platform routes 302→login when unauthenticated, 403 for wrong guard.
- Platform user 2FA: setup, challenge, and enforcement (no bypass by direct URL).
- Tenant CRUD via UI: create provisions DB + domain + record; duplicate slug rejected; suspend/reactivate flips `status` and timestamps.
- Central migrations create no tables in tenant DBs (assert tenant schema unchanged after central migrate).
- `platform_audit_logs` row written for login, create, suspend, delete.

**Manual verification:**
1. Register the first platform admin (seeder/command), complete 2FA, log in at `/platform`.
2. Dashboard lists both tenants with correct statuses and domains.
3. Create tenant `smoketest` from the browser → automatically provisioned; log into it; then delete it (confirm phrase + backup) → domain gone, DB dropped.
4. Try `/platform` while logged in as a tenant owner → blocked.
5. Confirm `/healthz` still returns healthy.

**Regression gate:** both tenant portals unaffected (spot-check dashboard, file request, settings in each).
**Sign-off:** operator can run the platform from the browser; audit trail present.

---

## Phase 3 — Platform subscription billing

**Automated (minimum new tests):**
- Cashier models resolve on the **central** connection even while a tenant DB is initialized (regression test for the stancl+Cashier pinning rule).
- Webhook transitions: `invoice.paid`→active, `invoice.payment_failed`→past_due, `subscription.deleted`→suspended; each idempotent; unknown events acknowledged without state change.
- Enforcement middleware: active → normal; past_due → banner visible, portal usable; suspended → all tenant routes return the 402 page except login/billing; cancelled → 410. Status cache invalidates on change (webhook then immediate request reflects new state).
- Trial tenant behaves as active; trial expiry without payment method → past_due path.
- Platform admin override (comp/extend trial) writes audit log and changes state.

**Manual verification (Stripe test clock or CLI-triggered events):**
1. New tenant starts on trial; dashboard shows trial end date.
2. Add a test card via the Stripe Customer Portal link from the tenant's "Platform subscription" page.
3. Trigger `invoice.payment_failed` → tenant shows the past_due banner; portal still works.
4. Trigger `customer.subscription.deleted` → tenant portal shows the suspended page; login still possible; billing link works.
5. Trigger `invoice.paid` → tenant restored within the cache window (≤60s).
6. Master dashboard shows MRR and per-tenant subscription states matching Stripe.

**Regression gate:** tenant-level Stripe purchases (Phase 1) unaffected by platform billing events — run one credit purchase after the suspension drill.
**Sign-off:** full lifecycle demonstrated with zero manual state changes.

---

## Phase 4 — Automated onboarding

**Automated (minimum new tests):**
- Signup validation: slug format, reserved words (`www`, `admin`, `platform`, `api`, `mail`, existing tenant slugs), duplicate domain, weak password.
- Signup creates `pending` tenant with **no database**; admins notified once.
- Approval dispatches `ProvisionTenant`; rejection emails the applicant with reason and does not provision.
- Provisioning job: success creates DB + domain + trial subscription + welcome email; simulated failure rolls back (no orphan DB/record) and flags `provision_failed`; retry from the dashboard works.
- Wizard gating: payment routes blocked until the Stripe step passes; wizard state persists; completed wizard never reappears.
- Each lifecycle email fires exactly once per event (signup received, approved, rejected, provision failed, trial ending, payment failed, suspended, reactivated).

**Manual verification:**
1. Sign up as "Kent Tuning" with slug `kent` from the public page.
2. Approve in the master portal → watch the queue worker provision it → welcome email received (Mailpit/log).
3. Complete the wizard: branding, test Stripe keys with "test connection", opening hours → go live.
4. Make a test credit purchase as a customer of `kent` — full loop within minutes of approval, operator effort = one click.
5. Sign up again with slug `kent` → rejected as taken; slug `admin` → rejected as reserved.
6. Simulate a provisioning failure (e.g. revoke DB create permission locally) → dashboard shows failed state; retry succeeds after restoring permission.

**Regression gate:** existing tenants unaffected; `tenants:create` CLI still works (shares the same service as the job).
**Sign-off:** signup→trading demonstrated end-to-end with one operator click.

---

## Phase 5 — Production deployment & go-live

**Automated:** CI pipeline green on the release commit; deploy script idempotent (running it twice changes nothing).

**Manual verification (production, in order):**
1. **Infrastructure:** wildcard DNS resolves; `https://{platform}` and `https://anything.{platform}` serve valid SSL; cert auto-renew dry-run passes (`certbot renew --dry-run`); HTTP→HTTPS redirect.
2. **Services:** supervisor shows queue worker running; `schedule:run` cron firing (check backup timestamps); Sentry receives a test event; Resend domain verified (SPF/DKIM pass on a test email).
3. **Data migration rehearsal (before cutover):** restore production backup into staging tenant; `tenants:migrate`; run R2 re-prefix + encryption backfill; verify logins, balances, invoice numbers, and file downloads against production values; schema diff between production and `platform-build` = migrations only.
4. **Cutover:** maintenance page up → final backup → restore → migrate → one-off commands → smoke test (login, file upload+download, credit purchase £1 live then refund, invoice PDF) → DNS switch → monitor Sentry for 24h.
5. **Platform drills in production:** onboard a real test tenant end-to-end; suspension drill on it; backup + restore drill; delete it.
6. **Security spot-checks:** tenant A session cookie replayed against tenant B domain fails; `/platform` unreachable without 2FA; `.env` not web-accessible; debug mode off.

**Regression gate:** full smoke test on the migrated `surrey-tuning` tenant matches pre-cutover behaviour.
**Sign-off:** go-live checklist in `docs/deploy-hostinger.md` fully ticked; rollback path documented and rehearsed.
