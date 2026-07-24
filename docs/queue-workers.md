# Queue Workers & Background Jobs

Surrey Tuning runs all outbound email and other side-effects through Laravel's
**database queue** (`QUEUE_CONNECTION=database`). A worker process must be
running in production or **no emails will be sent** and queued work will pile up
in the `jobs` table.

## What is queued

Every notification and every event listener implements `ShouldQueue`, so the
following happen off the web request:

| Trigger | Listener | Notification |
| --- | --- | --- |
| Payment confirmed | `SendPaymentConfirmationEmail` | `PaymentConfirmedNotification` |
| New file request | `NotifyOwnerNewFileRequest` | `NewFileRequestOwnerNotification` |
| File received | `NotifyDealerFileReceived` | `FileReceivedDealerNotification` |
| Status changed | `NotifyDealerStatusChanged` | `StatusChangedNotification` |
| New message posted | `NotifyRecipientNewMessage` | `NewMessageNotification` |
| Dealer approved | `SendDealerApprovalEmail` | `DealerApprovedNotification` |
| Dealer rejected | `SendDealerRejectionEmail` | `DealerRejectedNotification` |

### Tenant awareness

`QueueTenancyBootstrapper` is enabled in `config/tenancy.php`. Jobs dispatched
inside a tenant context serialize the tenant key, and the worker re-initializes
tenancy before running the job. **No manual tenant plumbing is required** — a
single worker safely drains jobs for the central app and every tenant.

## Running the worker

### Local / development

```bash
php artisan queue:work --tries=3 --backoff=10 --max-time=3600
```

Because the test suite and local `.env` may use `QUEUE_CONNECTION=sync`, jobs
run inline and no worker is needed there. Production must use `database`.

### Production (Supervisor — Linux)

Create `/etc/supervisor/conf.d/surreytuning-worker.conf`:

```ini
[program:surreytuning-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/user/htdocs/tuning-portal/current/artisan queue:work --tries=3 --backoff=10 --max-time=3600 --sleep=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=user
numprocs=2
redirect_stderr=true
stdout_logfile=/home/user/htdocs/tuning-portal/shared/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start surreytuning-worker:*
```

`--max-time=3600` recycles each worker hourly to release memory. `stopwaitsecs`
gives an in-flight job up to an hour to finish before it is killed.

### Deploys

The automated pipeline runs `php artisan queue:restart` as a post-flip task on
every deploy (and after every rollback), so workers pick up new code without
manual intervention — see [deploy-runbook.md](deploy-runbook.md).

For break-glass manual deploys, run it yourself from the live release:

```bash
cd /home/user/htdocs/tuning-portal/current && php artisan queue:restart
```

## Failed jobs

Failed jobs land in the `failed_jobs` table after exhausting `--tries`.

```bash
# List failures
php artisan queue:failed

# Retry a single failure
php artisan queue:retry <uuid>

# Retry everything
php artisan queue:retry all

# Delete one / flush all
php artisan queue:forget <uuid>
php artisan queue:flush
```

Failures are also reported to Sentry (Phase 2). Monitor the `failed_jobs`
table — a growing count means email delivery is broken (bad Resend key, DNS,
etc.). A scheduled check should alert if `failed_jobs` is non-empty.

## Why file uploads are NOT queued

`FileStorageService::storeFile()` writes to R2 **synchronously** inside the
request on purpose: the upload flow needs the returned storage path and
metadata to persist the `FileRequestAttachment` row and give the user immediate
confirmation the file was stored. Queuing the R2 put would require holding the
raw upload somewhere first and reconciling the attachment record afterward —
more failure modes for no user-facing benefit. Uploads stay synchronous; only
notifications/side-effects are queued.
