# Database Backups & Restore

Surrey Tuning backs up **every database** (the central DB plus one DB per tenant)
to a **separate private Cloudflare R2 bucket** using
[`spatie/laravel-backup`](https://spatie.be/docs/laravel-backup). Backups are the
last line of defence for a business that runs on this system daily — treat the
restore drill below as something you have actually run, not just read.

## What gets backed up

| Scope | Command | Backup folder (on `r2_backups` disk) | Schedule |
| --- | --- | --- | --- |
| Central DB (`surreytuning`) | `php artisan backup:run --only-db` | `surreytuning-central/` | daily 01:30 |
| Every tenant DB | `php artisan backup:tenants` | `tenant-<id>/` (one per tenant) | daily 02:00 |
| Prune old backups | `php artisan backup:clean` | — | daily 01:00 |
| Health monitor | `php artisan backup:monitor` | — | daily 08:00 |

- **Disk:** `r2_backups` (config/filesystems.php) — a *dedicated private* R2
  bucket, distinct from the `r2` upload bucket, so a leaked app credential can
  neither read nor delete backups.
- **`--only-db`:** archives contain only SQL dumps. Uploaded files already live
  durably in R2; application code lives in git. The irreplaceable asset is the
  data.
- **Encryption:** each zip is AES-256 encrypted with `BACKUP_ARCHIVE_PASSWORD`.
  **Store that password outside the backup bucket** — without it the archives
  cannot be opened.

## Prerequisites

- `mysqldump` / `mysql` client binaries on the server `PATH` (present on the
  Linux production host; not required for the SQLite test suite).
- A running scheduler:

  ```cron
  * * * * * cd /var/www/surreytuning && php artisan schedule:run >> /dev/null 2>&1
  ```

- `.env` values: `R2_BACKUP_ACCESS_KEY_ID`, `R2_BACKUP_SECRET_ACCESS_KEY`,
  `R2_BACKUP_BUCKET`, `R2_BACKUP_ENDPOINT`, `BACKUP_ARCHIVE_PASSWORD`, and
  `BACKUP_NOTIFICATION_EMAIL` (see `.env.example`).

## Running a backup manually

```bash
# Central database only
php artisan backup:run --only-db

# All tenant databases
php artisan backup:tenants

# A single tenant (id = surrey-tuning)
php artisan backup:tenants --tenants=surrey-tuning
```

## Restore procedure (tested)

Backups are password-protected zips containing a single `.sql` dump. Restoring
is: download → unzip → import into the target database.

### 1. Download the archive

List and pull the newest archive from R2 (any S3-compatible client works; the
example uses the AWS CLI pointed at the R2 endpoint):

```bash
aws --endpoint-url "$R2_BACKUP_ENDPOINT" s3 ls "s3://$R2_BACKUP_BUCKET/surreytuning-central/"
aws --endpoint-url "$R2_BACKUP_ENDPOINT" s3 cp \
  "s3://$R2_BACKUP_BUCKET/surreytuning-central/2026-07-01-01-30-00.zip" ./restore.zip
```

For a tenant, use its folder instead, e.g. `tenant-surrey-tuning/`.

### 2. Unzip (encrypted)

```bash
unzip -P "$BACKUP_ARCHIVE_PASSWORD" restore.zip -d restore/
# → restore/db-dumps/mysql-surreytuning.sql   (central)
# → restore/db-dumps/tenant-*.sql             (tenant)
```

### 3. Import into the target database

**Central:**

```bash
mysql -u root -p surreytuning < restore/db-dumps/mysql-surreytuning.sql
```

**Tenant** (tenant DB name = `tenant` + id, e.g. `tenantsurrey-tuning`):

```bash
mysql -u root -p 'tenantsurrey-tuning' < restore/db-dumps/tenant-surrey-tuning.sql
```

> Never restore a tenant dump into the central DB or vice-versa — they have
> different schemas and mixing them corrupts tenancy.

### 4. Verify

```bash
# Central: tenants + domains should be present
php artisan tinker --execute="echo App\Models\Tenant::count();"

# Tenant: run inside tenant context
php artisan tinker --execute="tenancy()->initialize(App\Models\Tenant::find('surrey-tuning')); echo \App\Models\User::count();"
```

## Monitoring & alerts

- `backup:monitor` fires `UnhealthyBackupWasFound` (→ email to
  `BACKUP_NOTIFICATION_EMAIL`, and Sentry via the exception handler) if the
  newest central backup is older than 1 day or storage exceeds 5000 MB.
- Backup/cleanup failures email the same address.
- **The tenant loop only alerts on failure via logs/Sentry** — `backup:tenants`
  disables per-run success notifications to avoid a flood; a non-zero exit code
  from the scheduled run is reported through the exception handler.

## Retention

`config/backup.php` default strategy: keep all for 7 days, then daily for 16
days, weekly for 8 weeks, monthly for 4 months, yearly for 2 years; delete
oldest once a scope exceeds 5000 MB.
