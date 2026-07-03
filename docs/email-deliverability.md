# Email Deliverability

Surrey Tuning Services sends transactional email (dealer approval, payment
confirmations, file-request notifications, new-message alerts, 2FA codes).
These are business-critical: if they land in spam or fail to send, dealers miss
the information they need to operate. This document covers how mail is
configured and the DNS work required for reliable inbox placement.

## How mail is wired in the app

- **Notifications** (`app/Notifications/*`) implement `ShouldQueue` and render
  branded HTML via the shared `<x-emails.layout>` component. They are dispatched
  from queued listeners (`app/Listeners/*`) to each dealer's primary contact.
- **2FA codes** (`app/Mail/TwoFactorCodeMail`) are sent **synchronously** on
  purpose — they are time-sensitive and must not wait behind the queue.
- Because most mail is queued, a **queue worker must be running in production**
  (`php artisan queue:work`), or notifications will never be delivered.
- The global From address/name comes from `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME`
  (see `config/mail.php`).

## Choosing a provider

Do **not** send production mail straight from the app server over raw SMTP to
recipients — it will almost certainly be filtered as spam. Use an authenticated
relay that manages sending reputation:

| Provider   | Driver     | Notes                                            |
| ---------- | ---------- | ------------------------------------------------ |
| Amazon SES | `ses`      | Cheapest at volume; reuses the AWS_* credentials |
| Postmark   | `postmark` | Excellent transactional deliverability           |
| Resend     | `resend`   | Simple setup, good for smaller volumes           |
| Generic    | `smtp`     | Any authenticated SMTP relay (TLS on 587)        |

Set the matching `MAIL_MAILER` and provider credentials in `.env`. Examples for
each are documented in `.env.example`.

## Required DNS records (operator responsibility)

These are configured in the DNS zone for the domain used in
`MAIL_FROM_ADDRESS` (e.g. `surreytuning.co.uk`). They are **not** managed by the
application and must be set up by whoever controls DNS.

1. **SPF** — a single TXT record on the root domain authorising your provider's
   servers to send on your behalf, e.g.
   `v=spf1 include:amazonses.com ~all` (SES) — use the include value your
   provider specifies.
2. **DKIM** — CNAME (or TXT) records provided by your mail provider that let
   receivers cryptographically verify your mail. SES/Postmark/Resend all give
   you the exact records to paste in during domain verification.
3. **DMARC** — a TXT record at `_dmarc.<domain>` that tells receivers what to do
   with mail failing SPF/DKIM and where to send reports. Start in monitoring
   mode and tighten once you confirm alignment:
   `v=DMARC1; p=none; rua=mailto:dmarc@surreytuning.co.uk`
   then move to `p=quarantine` and eventually `p=reject`.

Verify all three with your provider's domain dashboard and with a tool such as
[mxtoolbox.com](https://mxtoolbox.com) before going live.

## Verifying configuration after deploy

Once `.env` mail credentials and DNS are in place, confirm the mailer and From
address work end-to-end:

```bash
php artisan mail:test you@example.com
```

The command reports the active mailer and From address, then sends a plain test
message through the configured transport. A non-zero exit or an error message
means the credentials or transport are misconfigured — fix before relying on
transactional email.

Also confirm the queue worker is running, otherwise queued notifications will
sit unsent:

```bash
php artisan queue:work --stop-when-empty   # one-off drain to sanity-check
```
