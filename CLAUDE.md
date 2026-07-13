# Surrey Tuning Services Portal — Claude Code Build Plan
## Senior Developer Specification | v1.1 | June 2026

---

## ⚠️ ARCHITECTURE OVERRIDE — READ THIS FIRST (Added after Phase 1)

The architecture was updated during Phase 1 to support **multi-tenancy via Stancl/Tenancy (database-per-tenant)**. This portal is a white-label SaaS product. Every section of this document must be interpreted in light of the following. These rules override any conflicting instructions in the phase prompts below.

### Multi-Tenancy Facts
- Package: `stancl/tenancy` (already installed)
- Each business customer is a **tenant** with their own isolated database
- The Surrey Tuning instance: tenant ID = `surrey-tuning`, domain = `surreytuning.test`, tenant DB = `tenantsurrey-tuning`
- Central DB (`surreytuning`): contains only `tenants`, `domains`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations` — **no application tables**
- Tenant DB (`tenantsurrey-tuning`): contains all 24+ Surrey Tuning application tables

### Migration Rules
- **Tenant migrations** (application tables) go in: `database/migrations/tenant/`
- **Central migrations** (tenancy infrastructure only) go in: `database/migrations/`
- To run tenant migrations: `php artisan tenants:migrate --tenants=surrey-tuning`
- To fresh migrate + reseed a tenant: `php artisan tenants:migrate-fresh --tenants=surrey-tuning` then `php artisan tenants:seed --tenants=surrey-tuning`
- **NEVER run** `php artisan migrate:fresh --seed` — this wipes the central tenants/domains tables

### Routing & Middleware
- All portal routes (`routes/web.php`) are served under the tenant domain `surreytuning.test`
- `InitializeTenancyByDomain` and `PreventAccessFromCentralDomains` middleware run globally before all web routes
- By the time any controller runs, the tenant DB connection is already active — no manual tenancy initialization needed in controllers
- Middleware aliases (`owner`, `client`, `dealer_approved`) are registered in `bootstrap/app.php`

### Authentication
- The `users` table is in the **tenant DB** — Breeze auth queries the tenant connection automatically
- Sessions table is in the tenant DB
- Cache table is in the tenant DB (added during Phase 1 fix)
- Password reset tokens are in the tenant DB

### Artisan Commands for Tenant Context
- List tenants: `php artisan tenants:list`
- Run any command in tenant context: `php artisan tenants:run --tenants=surrey-tuning -- {command}`
- Tinker in tenant context: start `php artisan tinker`, then run `tenancy()->initialize(App\Models\Tenant::find('surrey-tuning'));`

### Phase Completion Status
- **Phase 0** ✅ Complete (commit: initial setup)
- **Phase 1** ✅ Complete (commit: e1526b6 — database and models, multi-tenant)
- **Phase 2** ✅ Complete (auth & routing, middleware, tenant routes)
- **Phase 3** ✅ Complete (core services: CreditService, InvoiceService, FileStorageService, StripeService)
- **Phase 4** ✅ Complete (owner file requests, dealer management, kanban/list views)
- **Phase 5** ✅ Complete (commit: b7f7ba2 — owner configuration, settings, all CRUD pages)
- **Phase 6** ✅ Complete (browser tested — file upload, messaging, kanban/list views, cross-dealer security confirmed)
- **Phase 7** ✅ Complete (browser tested — credits, products, invoices, Stripe webhook handler built; Stripe payment flows pending live keys)
- **Phase 9** ✅ Complete (browser tested — DTC search with enriched data, vehicle stats, dealer registration, what's new, client settings, portal users)

---

## 🎨 UI DESIGN SPECIFICATIONS (Added after Phase 1)

Custom UI designs have been provided for the portal. These must be followed during Phase 4 (owner portal) and Phase 6 (client portal). Do not use generic Breeze styling for these sections.

### Design Decisions (non-negotiable)
- **Dark theme is the default** — build dark-first throughout. Do not add dark mode as an afterthought in Phase 10. All layouts, components, and views must render correctly in dark mode from the start.
- **Primary accent colour**: `#e63012` (already set in Settings seeder as `theme_colour`)
- **Background**: deep dark (`#0f172a` / `#1e293b` range)
- **Sidebar**: dark navy with red active state highlight
- **Cards**: dark surface (`#1e293b`) with subtle borders

### Phase 4 — Owner Portal UI
The owner file requests view must include:
- **Kanban board** as the default view with columns: Pending, In Progress, Completed, On Hold (and other statuses)
- Each kanban card shows: vehicle make logo, make/model, stage, job reference number, time since created
- **List view** toggle (table layout) as an alternative
- Red/amber/green status dot indicators on kanban column headers
- "+ N more" pagination within kanban columns

### Phase 6 — Client Portal UI
The client dashboard must include:
- Welcome banner with dealer name and business status badge
- Four stat cards: Pending Files, In Progress, Completed (this year), Credit Balance
- **Spend over time chart** — use Chart.js (already available via CDN). Line chart showing monthly spend with red gradient fill.
- Recent file requests table with status badges, vehicle logos, job references, and Open buttons
- Right panel: Recent Notifications list, Account Summary (File Credits balance + Top Up button, EVC Credits + Buy button, Total Spent)
- Sidebar sections grouped: FILE SERVICE, FINANCIAL, TOOLS & DATA, ACCOUNT

### Chart.js Usage
- Load Chart.js from CDN: `https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js`
- Spend chart: type line, red (`#e63012`) stroke, red gradient fill, smooth curves, dark background
- Pass chart data from controller as a JSON-encoded PHP array (monthly totals from invoices)

### Vehicle Make Logos
- Use `https://logo.clearbit.com/{brand}.com` as a fallback for make logos on kanban cards and file request lists (e.g. `bmw.com`, `audi.com`, `ford.com`)
- Wrap in a fallback `<img onerror>` that shows a generic car icon SVG if the logo fails to load

---

## HOW TO USE THIS DOCUMENT

Place this file at the **root of the project** (`C:\Users\Dean PC\Herd\surreytuning\CLAUDE.md`) before starting any Claude Code session.  
Claude Code reads `CLAUDE.md` automatically on startup and uses it as persistent project context throughout every session.

**Working directory for all Claude Code sessions:**
```
C:\Users\Dean PC\Herd\surreytuning
```

**Rules for every session:**
- Start each session by reading this file.
- Complete one phase fully before starting the next.
- Run `php artisan test` and `php artisan route:list` at the end of each phase.
- Never skip the success criteria at the bottom of each phase.

## ⚠️ GIT COMMIT REQUIRED AFTER EVERY PHASE

**This is mandatory. Do not skip it.**

At the end of every phase (and every sub-phase like Part A / Part B), once all tests pass, run:

```
git add .
git commit -m "Phase X complete: [short description of what was built]"
```

Examples:
```
git commit -m "Phase 4A complete: owner file requests, kanban/list views, charge/credit/void actions"
git commit -m "Phase 4B complete: dealer management, applications, approve/reject flow"
git commit -m "Phase 5 complete: owner configuration, settings, CRUD pages"
```

**Why this matters:** Each commit is a restore point. If a later phase breaks something, we can roll back cleanly. Without commits, there is no safety net.

If the phase involved tenant migrations, also note that in the commit message:
```
git commit -m "Phase X complete: [description] — includes tenant migration"
```

---

## PROJECT OVERVIEW

Surrey Tuning Services is an automotive ECU tuning business. This portal has **two distinct authenticated interfaces** sharing one Laravel backend:

- **Client Portal** — `/my/*` — dealers submit ECU files, track jobs, manage credits, buy products.
- **Owner/Admin Portal** — `/*` (no prefix, protected by role middleware) — staff manage jobs, dealers, finances, and all configuration.

A shared login at `/login` routes users to the correct portal based on their role.

---

## ARCHITECTURE DECISIONS (non-negotiable)

These decisions are made upfront to prevent rewrites. Claude Code **must follow these patterns** throughout every phase:

### 1. Authentication & Authorisation
- **Laravel Breeze** (Blade stack) for the auth scaffold, then **heavily customised**.
- **Role-based routing**: roles are `owner`, `technician`, `dealer_owner`, `dealer_user`.
- **Middleware**: `IsOwnerUser` (roles: owner, technician), `IsClientUser` (roles: dealer_owner, dealer_user), `EnsureDealerApproved` (status: approved).
- **Policies** for all model-level access checks (e.g. `FileRequestPolicy`, `InvoicePolicy`). Do not use `Gate::define` inline.
- **Route prefixes**: client routes use `my` prefix (`/my/dashboard`), owner routes use no prefix (`/dashboard`).

### 2. Business Logic
- **Service classes** handle all business logic — controllers are thin (validate → call service → redirect/return).
- Mandatory services: `CreditService`, `InvoiceService`, `FileStorageService`, `StripeService`.
- **Database transactions** are required on any operation that writes to more than one table (credit deductions, invoice creation, etc.).
- Credit balances are stored on the `dealers` table AND as a running snapshot in transaction tables. Both must be updated atomically.

### 3. Validation
- All form validation uses **Form Request classes** (`app/Http/Requests/`). No `$request->validate()` inline in controllers.
- Money fields: always `DECIMAL(10,2)`, never floats. Always cast as `decimal:2` in models.

### 4. Models
- All models use `$fillable` (not `$guarded = []`).
- SoftDeletes on: `users`, `dealers`, `file_requests`, `noticeboards`.
- All money cast as `decimal:2`. All boolean columns cast as `bool`. Enums cast to their PHP Enum type.
- PHP 8.1 **backed Enums** for all ENUM columns (e.g. `App\Enums\FileRequestStatus`).

### 5. Notifications
- All email sending goes via **Laravel Notifications** (not `Mail::send`).
- Notifications dispatched via **queued listeners** — never synchronously inside controllers.
- Queue driver: `database` for development, `redis` for production.

### 6. File Storage
- Cloudflare R2 via S3 driver. All files private. Access via **signed temporary URLs only**.
- File paths: `files/{dealer_id}/{request_number}/{type}/{original_filename}`.
- Disk name: `r2`.

### 7. Frontend
- Blade + Tailwind CSS v3 + Alpine.js v3.
- **No separate frontend framework** (no React, Vue, Inertia).
- Vite for asset compilation.
- Two layouts: `layouts/auth.blade.php`, `layouts/owner.blade.php`, `layouts/client.blade.php`.
- Blade components for reusable UI: `<x-stat-card>`, `<x-status-badge>`, `<x-modal>`, `<x-data-table>`.

### 8. Testing
- Feature tests for every controller action (at minimum: happy path + auth guard).
- Unit tests for service classes.
- Use `RefreshDatabase` trait.

---

## PHP ENUMS (create these first, referenced everywhere)

Create in `app/Enums/`:

```php
// UserRole.php
enum UserRole: string {
    case Owner = 'owner';
    case Technician = 'technician';
    case DealerOwner = 'dealer_owner';
    case DealerUser = 'dealer_user';
}

// DealerStatus.php  
enum DealerStatus: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
}

// FileRequestStatus.php
enum FileRequestStatus: string {
    case Pending = 'pending';
    case Progress = 'progress';
    case Responded = 'responded';
    case OnHold = 'on_hold';
    case RequiresSupport = 'requires_support';
    case Returned = 'returned';
    case Closed = 'closed';
    case Void = 'void';
    
    public function label(): string { ... }
    public function colour(): string { ... }  // Tailwind colour class
}

// MessageType.php
enum MessageType: string {
    case Message = 'message';
    case System = 'system';
    case InternalNote = 'internal_note';
    case ChargeEvent = 'charge_event';
    case CreditEvent = 'credit_event';
}

// InvoiceType.php
enum InvoiceType: string {
    case CreditTopUp = 'credit_top_up';
    case EvcBundle = 'evc_bundle';
    case Product = 'product';
    case Manual = 'manual';
}

// InvoiceStatus.php
enum InvoiceStatus: string {
    case Issued = 'issued';
    case Paid = 'paid';
    case Void = 'void';
}

// AttachmentType.php
enum AttachmentType: string {
    case Original = 'original';
    case Returned = 'returned';
    case Supporting = 'supporting';
    case Certificate = 'certificate';
    case Ini = 'ini';
}

// PortalStatusEnum.php
enum PortalStatusEnum: string {
    case Available = 'available';
    case Busy = 'busy';
    case Delayed = 'delayed';
    case SupportOnly = 'support_only';
    case FilesOnly = 'files_only';
    case Closed = 'closed';
    case Noticeboard = 'noticeboard';
}
```

---

## DATABASE SCHEMA (authoritative — use Section 6 of spec)

24 tables. Migration order matters due to foreign keys:

1. `users`
2. `dealers`
3. `dealer_applications`
4. `file_stages`
5. `file_options`
6. `tuning_tools`
7. `file_requests`
8. `file_request_options`
9. `file_request_messages`
10. `file_request_attachments`
11. `dtc_codes` (per-job DTC codes)
12. `file_credit_transactions`
13. `evc_credit_transactions`
14. `winols_bundles`
15. `products`
16. `product_orders`
17. `invoices`
18. `noticeboards`
19. `opening_hours`
20. `portal_status`
21. `settings`
22. `vehicle_stats`
23. `bosch_ecus`
24. `dtc_library`

**Important:** The `users` table modifies Laravel's default — it adds `dealer_id`, `first_name`, `last_name` (replacing `name`), `role`, `is_primary_contact`, `can_view_pricing`, `avatar`, `status`, `notify_*` preferences, `whatsapp_number`, `last_login_at`, and `deleted_at`.

---

## PHASE 0: Environment & Foundation
**Goal:** Clean project skeleton with all packages installed, layouts in place, and assets compiling.  
**Pre-condition:** Fresh Laravel 13.8 install exists at `C:\Users\Dean PC\Herd\surreytuning`. DB `surreytuning` exists in MySQL.

---

### PHASE 0 PROMPT — paste into Claude Code

```
I am building a dealer portal for an automotive ECU tuning company called Surrey Tuning Services.
Project location: C:\Users\Dean PC\Herd\surreytuning
Local URL: http://surreytuning.test
Stack: Laravel 13.8, Blade, Tailwind CSS v3, Alpine.js v3, MySQL, Vite.

Please do the following in order:

1. PACKAGES — Install these Composer packages:
   - laravel/breeze (then run: php artisan breeze:install blade --dark)
   - stripe/stripe-php
   - league/flysystem-aws-s3-v3 (for Cloudflare R2)
   - resend/resend-laravel

2. NPM PACKAGES — Install:
   - @tailwindcss/forms
   - @tailwindcss/typography
   - alpinejs (if not already included by Breeze)
   Then run: npm run build

3. ENUMS — Create PHP 8.1 backed string enums in app/Enums/:
   - UserRole: owner, technician, dealer_owner, dealer_user
   - DealerStatus: pending, approved, rejected, suspended
   - ApplicationStatus: pending, approved, rejected
   - FileRequestStatus: pending, progress, responded, on_hold, requires_support, returned, closed, void
     - Add label() method returning human-readable string
     - Add colour() method returning Tailwind badge colour class (e.g. 'bg-yellow-100 text-yellow-800')
   - MessageType: message, system, internal_note, charge_event, credit_event
   - InvoiceType: credit_top_up, evc_bundle, product, manual
   - InvoiceStatus: issued, paid, void
   - AttachmentType: original, returned, supporting, certificate, ini
   - PortalStatusEnum: available, busy, delayed, support_only, files_only, closed, noticeboard
     - Add label() and colour() methods
   - FuelType: petrol, diesel, electric, hybrid
   - TransmissionType: manual, semi_auto, automatic
   - VehicleType: all, car, van, bike, other
   - TuningToolCategory: obd, bench, boot, bdm, other
   - FileCreditTransactionType: top_up, deduction, manual_credit, refund
   - EvcCreditTransactionType: purchase, manual_credit, refund
   - NoticePriority: low, normal, high
   - ProductPaymentType: file_credits, direct_payment, both

4. LAYOUTS — Create three Blade layout files:

   a) resources/views/layouts/auth.blade.php
      - Full-page centred card layout for login/register
      - Dark background (#111827), white card, Surrey Tuning logo placeholder
      - No sidebar or navigation

   b) resources/views/layouts/owner.blade.php
      - Sidebar (dark navy #1e293b, width 256px) with nav links:
        Dashboard, File Requests, File Archive, Dealers, Dealer Applications,
        Invoices, WinOLS Bundles, File Stages, File Options, Tools,
        Portal Users, Noticeboard, Vehicle Stats, Bosch ECU, DTC Search,
        What's New, Settings
      - Top header bar: portal status badge (reads from cache/settings), logged-in user name, logout button
      - Main content area with white/gray-50 background
      - Flash message component (success/error/warning) rendered inside the layout
      - Light/dark mode toggle stored in localStorage and applied via class on <html>
      - Include Alpine.js and Tailwind

   c) resources/views/layouts/client.blade.php
      - Sidebar (lighter slate #334155, width 256px) with nav links:
        Dashboard, File Requests, Upload File, File Archive,
        File Credits, EVC Credits, Products, Invoices,
        DTC Search, Vehicle Stats, Bosch ECU, Portal Users,
        Settings, Whats New
      - Top header bar: File Credits balance badge, EVC Credits balance badge, user name, logout
      - Credit balances in header pulled from auth()->user()->dealer->file_credit_balance
      - Same flash messages and dark mode toggle

5. BLADE COMPONENTS — Create these reusable Blade components in resources/views/components/:
   - stat-card.blade.php: props: $label, $value, $colour (default 'blue'), $icon (optional)
   - status-badge.blade.php: props: $status (string), $colour (Tailwind classes) 
   - modal.blade.php: Alpine.js modal wrapper using x-show, props: $id, $title
   - data-table.blade.php: basic table wrapper with props: $headers (array)
   - flash-messages.blade.php: renders session flash (success/error/warning) with dismiss
   - page-header.blade.php: props: $title, $subtitle (optional), slot for action buttons

6. HELPERS — Create app/Helpers/ReferenceNumber.php:
   - generateJobReference(): returns STS-{YEAR}-{5-digit-zero-padded-sequence} (e.g. STS-2026-00001)
     Sequence resets each year. Read last number from file_requests table.
   - generateInvoiceReference(): returns INV-{YEAR}-{5-digit-sequence}
     Read last number from invoices table.

7. CONFIGURE .env with these additional keys (placeholder values, add comments):
   # Cloudflare R2
   R2_ACCESS_KEY_ID=
   R2_SECRET_ACCESS_KEY=
   R2_BUCKET=surrey-tuning-files
   R2_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
   R2_URL=
   
   # Resend
   RESEND_API_KEY=
   MAIL_MAILER=resend
   MAIL_FROM_ADDRESS=noreply@surreytuningservices.co.uk
   MAIL_FROM_NAME="Surrey Tuning Services"
   
   # Stripe
   STRIPE_KEY=
   STRIPE_SECRET=
   STRIPE_WEBHOOK_SECRET=

8. FILESYSTEMS — Add the r2 disk to config/filesystems.php:
   'r2' => [
       'driver' => 's3',
       'key' => env('R2_ACCESS_KEY_ID'),
       'secret' => env('R2_SECRET_ACCESS_KEY'),
       'region' => 'auto',
       'bucket' => env('R2_BUCKET'),
       'url' => env('R2_URL'),
       'endpoint' => env('R2_ENDPOINT'),
       'use_path_style_endpoint' => false,
       'throw' => false,
   ],

9. Run: npm run build
   Then run: php artisan config:clear && php artisan view:clear

10. Create a placeholder route in routes/web.php:
    Route::get('/', fn() => redirect('/login'));
    And confirm GET /login serves the Breeze login view.
```

### Phase 0 Success Criteria
- [ ] `http://surreytuning.test` redirects to `/login`
- [ ] Login page renders with no CSS errors
- [ ] `php artisan about` shows no errors
- [ ] All Enum files exist in `app/Enums/`
- [ ] All three layout files exist
- [ ] `npm run build` completes without errors
- [ ] `php artisan config:cache` runs clean

---

## PHASE 1: Database & Models
**Goal:** All 24 migrations run cleanly; all Eloquent models created with full relationships, casts, and scopes.  
**Pre-condition:** Phase 0 complete.

---

### PHASE 1 PROMPT — paste into Claude Code

```
Please create all database migrations and Eloquent models for the Surrey Tuning Services portal.

## IMPORTANT RULES
- One migration file per table
- Migration files must be numbered to run in the correct foreign-key dependency order
- DO NOT modify the existing Laravel Breeze users migration — instead, ADD a new migration that modifies the users table to add our custom columns
- Use the exact column names, types, and enums from the spec below
- All models use $fillable arrays (not $guarded)
- All Enum columns cast to their PHP Enum class (app/Enums/*)
- All DECIMAL columns cast as 'decimal:2'
- All TINYINT(1) columns cast as 'boolean'
- Models with SoftDeletes: User, Dealer, FileRequest, Noticeboard
- Money amounts always DECIMAL(10,2)

## MIGRATIONS (in this exact order)

### Modify users table (new migration, don't touch Breeze's)
Add to users: 
  dealer_id BIGINT UNSIGNED NULL FK → dealers.id ON DELETE SET NULL (add AFTER all other columns)
  first_name VARCHAR(255) — rename 'name' column to 'first_name', add 'last_name' VARCHAR(255)
  role ENUM('owner','technician','dealer_owner','dealer_user') DEFAULT 'dealer_owner'
  is_primary_contact TINYINT(1) DEFAULT 0
  can_view_pricing TINYINT(1) DEFAULT 1
  avatar VARCHAR(255) NULL
  status ENUM('active','inactive') DEFAULT 'active'
  notify_comments_email TINYINT(1) DEFAULT 1
  notify_file_requests_email TINYINT(1) DEFAULT 1
  notify_file_requests_sms TINYINT(1) DEFAULT 0
  whatsapp_number VARCHAR(20) NULL
  last_login_at TIMESTAMP NULL
  deleted_at TIMESTAMP NULL

### dealers table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  company_name VARCHAR(255)
  country VARCHAR(255) DEFAULT 'United Kingdom'
  invoice_address TEXT NULL
  file_credit_balance DECIMAL(10,2) DEFAULT 0.00
  evc_credit_balance DECIMAL(10,2) DEFAULT 0.00
  status ENUM('pending','approved','rejected','suspended') DEFAULT 'pending'
  approved_at TIMESTAMP NULL
  approved_by BIGINT UNSIGNED NULL FK → users.id ON DELETE SET NULL
  rejection_reason VARCHAR(255) NULL
  terms_accepted_at TIMESTAMP NULL
  notes TEXT NULL
  timestamps
  deleted_at TIMESTAMP NULL

### dealer_applications table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  company_name VARCHAR(255)
  contact_name VARCHAR(255)
  email VARCHAR(255)
  phone VARCHAR(50) NULL
  country VARCHAR(255) DEFAULT 'United Kingdom'
  message TEXT NULL
  status ENUM('pending','approved','rejected') DEFAULT 'pending'
  reviewed_by BIGINT UNSIGNED NULL FK → users.id ON DELETE SET NULL
  reviewed_at TIMESTAMP NULL
  rejection_reason VARCHAR(255) NULL
  terms_accepted_at TIMESTAMP NULL
  timestamps

### file_stages table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  name VARCHAR(255)
  description TEXT NULL
  vehicle_type ENUM('all','car','van','bike','other') DEFAULT 'all'
  price_net DECIMAL(10,2) DEFAULT 0.00
  vat_applicable TINYINT(1) DEFAULT 0
  turnaround_hours SMALLINT UNSIGNED NULL
  sort_order SMALLINT UNSIGNED DEFAULT 0
  is_active TINYINT(1) DEFAULT 1
  timestamps

### file_options table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  file_stage_id BIGINT UNSIGNED NULL FK → file_stages.id ON DELETE SET NULL
  name VARCHAR(255)
  description TEXT NULL
  price_net DECIMAL(10,2) DEFAULT 0.00
  vat_applicable TINYINT(1) DEFAULT 0
  is_required TINYINT(1) DEFAULT 0
  sort_order SMALLINT UNSIGNED DEFAULT 0
  is_active TINYINT(1) DEFAULT 1
  timestamps

### tuning_tools table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  name VARCHAR(255)
  category ENUM('obd','bench','boot','bdm','other') DEFAULT 'obd'
  sort_order SMALLINT UNSIGNED DEFAULT 0
  is_active TINYINT(1) DEFAULT 1
  timestamps

### file_requests table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  request_number INT UNSIGNED UNIQUE  -- display number e.g. STS-2026-00001 (store just integer, format on display)
  dealer_id BIGINT UNSIGNED NOT NULL FK → dealers.id
  submitted_by_user_id BIGINT UNSIGNED NOT NULL FK → users.id
  assigned_technician_id BIGINT UNSIGNED NULL FK → users.id ON DELETE SET NULL
  file_type ENUM('ecu','tcu','other') DEFAULT 'ecu'
  status ENUM('pending','progress','responded','on_hold','requires_support','returned','closed','void') DEFAULT 'pending'
  registration VARCHAR(20) NULL
  vin_number VARCHAR(50) NULL
  make VARCHAR(100) NOT NULL
  model VARCHAR(100) NOT NULL
  engine VARCHAR(50) NOT NULL
  engine_code VARCHAR(50) NULL
  year YEAR NOT NULL
  fuel ENUM('petrol','diesel','electric','hybrid') NOT NULL
  euro_status VARCHAR(10) NULL
  transmission ENUM('manual','semi_auto','automatic') NOT NULL
  bhp_before DECIMAL(8,2) NULL
  torque_before_nm DECIMAL(8,2) NULL
  ecu_model_no VARCHAR(100) NULL
  file_stage_id BIGINT UNSIGNED NULL FK → file_stages.id ON DELETE SET NULL
  tool_id BIGINT UNSIGNED NULL FK → tuning_tools.id ON DELETE SET NULL
  client_notes TEXT NULL
  price_net DECIMAL(10,2) DEFAULT 0.00
  vat_amount DECIMAL(10,2) DEFAULT 0.00
  price_gross DECIMAL(10,2) DEFAULT 0.00
  is_charged TINYINT(1) DEFAULT 0
  client_downloaded_at TIMESTAMP NULL
  void_reason VARCHAR(255) NULL
  closed_at TIMESTAMP NULL
  timestamps
  deleted_at TIMESTAMP NULL

### file_request_options table (pivot with price snapshot)
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  file_request_id BIGINT UNSIGNED NOT NULL FK → file_requests.id ON DELETE CASCADE
  file_option_id BIGINT UNSIGNED NOT NULL FK → file_options.id ON DELETE CASCADE
  price_net DECIMAL(10,2) NOT NULL  -- snapshot of price at time of submission
  timestamps

### file_request_messages table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  file_request_id BIGINT UNSIGNED NOT NULL FK → file_requests.id ON DELETE CASCADE
  sender_user_id BIGINT UNSIGNED NOT NULL FK → users.id
  type ENUM('message','system','internal_note','charge_event','credit_event') DEFAULT 'message'
  body TEXT NULL
  is_internal TINYINT(1) DEFAULT 0
  is_system TINYINT(1) DEFAULT 0
  timestamps

### file_request_attachments table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  file_request_id BIGINT UNSIGNED NOT NULL FK → file_requests.id ON DELETE CASCADE
  message_id BIGINT UNSIGNED NULL FK → file_request_messages.id ON DELETE SET NULL
  uploader_user_id BIGINT UNSIGNED NOT NULL FK → users.id
  attachment_type ENUM('original','returned','supporting','certificate','ini') DEFAULT 'original'
  original_filename VARCHAR(255) NOT NULL
  stored_filename VARCHAR(255) NOT NULL
  file_path VARCHAR(500) NOT NULL  -- R2 object key
  file_size_bytes BIGINT UNSIGNED NOT NULL
  mime_type VARCHAR(100) NOT NULL
  first_downloaded_at TIMESTAMP NULL
  created_at TIMESTAMP

### dtc_codes table (per-job codes submitted with a file request)
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  file_request_id BIGINT UNSIGNED NOT NULL FK → file_requests.id ON DELETE CASCADE
  code VARCHAR(10) NOT NULL
  description VARCHAR(255) NULL
  created_at TIMESTAMP

### file_credit_transactions table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  dealer_id BIGINT UNSIGNED NOT NULL FK → dealers.id
  user_id BIGINT UNSIGNED NOT NULL FK → users.id
  file_request_id BIGINT UNSIGNED NULL FK → file_requests.id ON DELETE SET NULL
  type ENUM('top_up','deduction','manual_credit','refund') NOT NULL
  amount DECIMAL(10,2) NOT NULL  -- positive = credit in, negative = deduction
  reason VARCHAR(255) NULL
  balance_after DECIMAL(10,2) NOT NULL
  created_at TIMESTAMP

### evc_credit_transactions table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  dealer_id BIGINT UNSIGNED NOT NULL FK → dealers.id
  user_id BIGINT UNSIGNED NOT NULL FK → users.id
  winols_bundle_id BIGINT UNSIGNED NULL FK → winols_bundles.id ON DELETE SET NULL
  type ENUM('purchase','manual_credit','refund') NOT NULL
  amount DECIMAL(10,2) NOT NULL
  reason VARCHAR(255) NULL
  balance_after DECIMAL(10,2) NOT NULL
  created_at TIMESTAMP

### winols_bundles table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  name VARCHAR(255) NOT NULL
  credits INT UNSIGNED NOT NULL
  price_net DECIMAL(10,2) NOT NULL
  is_active TINYINT(1) DEFAULT 1
  timestamps

### products table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  name VARCHAR(255) NOT NULL
  description TEXT NULL
  price_net DECIMAL(10,2) NOT NULL
  vat_applicable TINYINT(1) DEFAULT 0
  payment_type ENUM('file_credits','direct_payment','both') DEFAULT 'both'
  stock INT UNSIGNED NULL  -- NULL = unlimited
  is_active TINYINT(1) DEFAULT 1
  image_path VARCHAR(255) NULL
  sort_order SMALLINT UNSIGNED DEFAULT 0
  timestamps

### product_orders table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  dealer_id BIGINT UNSIGNED NOT NULL FK → dealers.id
  user_id BIGINT UNSIGNED NOT NULL FK → users.id
  product_id BIGINT UNSIGNED NOT NULL FK → products.id
  quantity SMALLINT UNSIGNED DEFAULT 1
  unit_price_net DECIMAL(10,2) NOT NULL
  vat_amount DECIMAL(10,2) DEFAULT 0.00
  total_gross DECIMAL(10,2) NOT NULL
  payment_method ENUM('file_credits','stripe') NOT NULL
  stripe_payment_intent_id VARCHAR(255) NULL
  status ENUM('pending','paid','fulfilled','refunded') DEFAULT 'pending'
  timestamps

### invoices table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  dealer_id BIGINT UNSIGNED NOT NULL FK → dealers.id
  user_id BIGINT UNSIGNED NULL FK → users.id ON DELETE SET NULL
  invoice_number INT UNSIGNED UNIQUE NOT NULL
  description TEXT NOT NULL
  amount_net DECIMAL(10,2) NOT NULL
  vat_amount DECIMAL(10,2) DEFAULT 0.00
  amount_gross DECIMAL(10,2) NOT NULL
  type ENUM('credit_top_up','evc_bundle','product','manual') NOT NULL
  related_id BIGINT UNSIGNED NULL  -- polymorphic
  related_type VARCHAR(255) NULL
  status ENUM('issued','paid','void') DEFAULT 'issued'
  stripe_payment_intent_id VARCHAR(255) NULL
  paid_at TIMESTAMP NULL
  timestamps

### noticeboards table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  created_by_user_id BIGINT UNSIGNED NOT NULL FK → users.id
  title VARCHAR(255) NOT NULL
  body TEXT NOT NULL
  priority ENUM('low','normal','high') DEFAULT 'normal'
  show_from DATE NULL
  show_until DATE NULL
  is_active TINYINT(1) DEFAULT 1
  timestamps
  deleted_at TIMESTAMP NULL

### opening_hours table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  day_of_week TINYINT UNSIGNED NOT NULL  -- 0=Monday, 6=Sunday
  open_time TIME DEFAULT '09:00:00'
  close_time TIME DEFAULT '18:00:00'
  is_open TINYINT(1) DEFAULT 1
  timestamps

### portal_status table (always single row, id=1)
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  status ENUM('available','busy','delayed','support_only','files_only','closed','noticeboard') DEFAULT 'available'
  updated_by BIGINT UNSIGNED NULL FK → users.id ON DELETE SET NULL
  timestamps

### settings table (always single row, id=1)
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  invoice_address TEXT NULL
  returns_address TEXT NULL
  vat_number VARCHAR(50) NULL
  vat_rate DECIMAL(5,2) DEFAULT 20.00
  company_number VARCHAR(50) NULL
  bcc_invoice_email VARCHAR(255) NULL
  invoice_start_number INT UNSIGNED DEFAULT 10000
  invoice_reference_prefix VARCHAR(20) DEFAULT 'INV'
  logo_light VARCHAR(255) NULL
  logo_dark VARCHAR(255) NULL
  login_background VARCHAR(255) NULL
  theme_colour VARCHAR(7) DEFAULT '#e63012'
  dealer_auto_onboard TINYINT(1) DEFAULT 0
  terms_and_conditions TEXT NULL
  stripe_public_key VARCHAR(255) NULL
  stripe_secret_key VARCHAR(255) NULL
  evc_account_number VARCHAR(50) NULL
  evc_password VARCHAR(255) NULL
  whatsapp_business_number VARCHAR(20) NULL
  updated_at TIMESTAMP

### vehicle_stats table
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  make VARCHAR(100) NOT NULL
  model VARCHAR(100) NOT NULL
  year_from SMALLINT UNSIGNED NOT NULL
  year_to SMALLINT UNSIGNED NOT NULL
  engine VARCHAR(50) NOT NULL
  fuel ENUM('petrol','diesel','electric','hybrid') NOT NULL
  bhp_before DECIMAL(8,2) NOT NULL
  bhp_after DECIMAL(8,2) NOT NULL
  torque_before_nm DECIMAL(8,2) NOT NULL
  torque_after_nm DECIMAL(8,2) NOT NULL
  stage TINYINT UNSIGNED NOT NULL  -- 1, 2, 3
  notes TEXT NULL
  timestamps

### bosch_ecus table (seeded — 13,366 records)
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  manufacturer_number VARCHAR(50) NOT NULL INDEX
  model VARCHAR(100) NOT NULL
  car_producer VARCHAR(255) NOT NULL
  image_path VARCHAR(255) NULL
  timestamps

### dtc_library table (seeded — 7,698 records)
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  code VARCHAR(10) NOT NULL INDEX
  description TEXT NOT NULL

## MODELS — create all models with:

User model (extend existing):
  $fillable: first_name, last_name, email, password, role, dealer_id, is_primary_contact, can_view_pricing, avatar, status, notify_comments_email, notify_file_requests_email, notify_file_requests_sms, whatsapp_number, last_login_at
  Casts: role → UserRole::class, status → cast string
  SoftDeletes
  Relationships: belongsTo Dealer, hasMany FileRequest (submitted), hasMany FileRequestMessage
  Accessor: getFullNameAttribute() returns "$first_name $last_name"
  Scopes: scopeOwnerTeam() (role in [owner, technician]), scopeClients() (role in [dealer_owner, dealer_user])

Dealer model:
  $fillable: all columns
  Casts: file_credit_balance decimal:2, evc_credit_balance decimal:2, status → DealerStatus::class
  SoftDeletes
  Relationships: hasMany User, hasMany FileRequest, hasMany Invoice, hasMany FileCreditTransaction, hasMany EvcCreditTransaction, hasOne primaryContact (User where is_primary_contact=1)
  Scope: scopeApproved()

DealerApplication model: $fillable all, cast status → ApplicationStatus

FileStage model: $fillable all, cast is_active bool, hasMany FileRequest, hasMany FileOption

FileOption model: $fillable all, belongsTo FileStage, belongsToMany FileRequest via file_request_options

TuningTool model: $fillable all

FileRequest model:
  $fillable: all
  SoftDeletes
  Casts: status → FileRequestStatus, fuel → FuelType, transmission → TransmissionType, price_net/vat_amount/price_gross decimal:2, is_charged bool
  Relationships: belongsTo Dealer, belongsTo User (submittedBy, assignedTechnician), belongsTo FileStage, belongsTo TuningTool, hasMany FileRequestMessage, hasMany FileRequestAttachment, hasMany FileRequestOption, hasMany DtcCode (per-job)
  Accessor: getRequestNumberFormattedAttribute() — returns "STS-{year}-{zero_padded_number}"
  Scopes: scopeActive(), scopeArchived() (closed/void older than 30 days for clients, 90 days for owner)

FileRequestMessage model: $fillable all, cast type → MessageType, is_internal/is_system bool
FileRequestAttachment model: $fillable all, cast attachment_type → AttachmentType
FileRequestOption model: $fillable all, cast price_net decimal:2
DtcCode model: $fillable all
FileCreditTransaction model: $fillable all, casts decimal:2, type → FileCreditTransactionType
EvcCreditTransaction model: $fillable all, casts decimal:2, type → EvcCreditTransactionType
WinolsBundle model: $fillable all
Product model: $fillable all, cast payment_type → ProductPaymentType
ProductOrder model: $fillable all, casts decimal:2
Invoice model: $fillable all, casts decimal:2, type → InvoiceType, status → InvoiceStatus
Noticeboard model: $fillable all, SoftDeletes, scope: scopeActive() (active=1, show_from<=today, show_until>=today or null)
OpeningHour model: $fillable all
PortalStatus model: $fillable all, cast status → PortalStatusEnum. Add static method current() that returns the single row (id=1), creating it if missing.
Setting model: $fillable all. Add static method get() that returns the single row (id=1), creating defaults if missing.
VehicleStat model: $fillable all
BoschEcu model: $fillable all
DtcLibrary model: $fillable all, $table = 'dtc_library'

## SEEDERS — create and run:

DatabaseSeeder.php runs: SettingsSeeder, OpeningHoursSeeder, PortalStatusSeeder, FileStagesSeeder, TuningToolsSeeder, AdminUserSeeder

SettingsSeeder: creates Settings row (id=1) with defaults (vat_rate=20, invoice_start_number=10000, theme_colour='#e63012')

OpeningHoursSeeder: 7 rows (0-6), Monday-Friday is_open=1 open=09:00 close=17:30, Saturday-Sunday is_open=0

PortalStatusSeeder: single row (id=1) status='available'

FileStagesSeeder:
  Stage 1 Remap - price_net=0, sort_order=1, is_active=1
  Stage 2 Remap - price_net=0, sort_order=2, is_active=1
  Stage 3 Remap - price_net=0, sort_order=3, is_active=1
  DPF Off - price_net=0, sort_order=4, is_active=1
  EGR Off - price_net=0, sort_order=5, is_active=1
  Adblue Off - price_net=0, sort_order=6, is_active=1
  (owner will set prices via admin UI)

TuningToolsSeeder: Autotuner OBD (obd,1), Autotuner Bench (bench,2), Autotuner Boot (boot,3), Kess3 OBD (obd,4), Kess3 Bench (bench,5), CMD OBD (obd,6), CMD Bench (bench,7), CMD Boot (boot,8)

AdminUserSeeder:
  Creates user: first_name=Admin, last_name=User, email=admin@surreytuningservices.co.uk
  password=ChangeMe123! (hashed), role=owner, status=active, email_verified_at=now()

After creating all files, run:
  php artisan migrate:fresh --seed
  
Then confirm by running:
  php artisan tinker --execute="echo User::count() . ' users, ' . FileStage::count() . ' stages';"
```

### Phase 1 Success Criteria
- [ ] `php artisan migrate:fresh --seed` runs without errors
- [ ] 24 tables exist in the database
- [ ] 7 file stages, 8 tuning tools, 1 admin user, 7 opening hour rows exist
- [ ] All Enum classes can be instantiated without errors
- [ ] All model `$fillable` arrays are complete
- [ ] SoftDeletes present on User, Dealer, FileRequest, Noticeboard

---

## PHASE 2: Authentication & Routing
**Goal:** Login works, roles redirect correctly, all route groups defined, all middleware registered.  
**Pre-condition:** Phase 1 complete.

---

### PHASE 2 PROMPT — paste into Claude Code

```
Please build the full authentication and routing system for the Surrey Tuning Services portal.

## AUTHENTICATION

1. MODIFY the Breeze login controller/action so that after successful login:
   - Users with role owner or technician → redirect to /dashboard (owner portal)
   - Users with role dealer_owner or dealer_user → redirect to /my/dashboard (client portal)
   - Suspended dealers (dealer.status = 'suspended') → redirect back to /login with error "Your account has been suspended. Please contact us."
   - Pending dealers (dealer.status = 'pending') → redirect back to /login with error "Your account is pending approval."
   
2. Update the User model's updateLastLogin() method — called on every successful login — that sets last_login_at = now().

3. CREATE MIDDLEWARE at app/Http/Middleware/:

   IsOwnerUser.php:
   - Passes if auth()->user()->role is 'owner' or 'technician'
   - Redirects unauthenticated to /login
   - Returns 403 for authenticated non-owner users

   IsClientUser.php:
   - Passes if auth()->user()->role is 'dealer_owner' or 'dealer_user'
   - Redirects unauthenticated to /login
   - Returns 403 for authenticated non-client users

   EnsureDealerApproved.php:
   - Only runs for client users
   - Checks auth()->user()->dealer->status === 'approved'
   - Redirects to /login with error message if not approved

4. REGISTER the middleware in bootstrap/app.php (Laravel 11 style):
   ->withMiddleware(function (Middleware $middleware) {
       $middleware->alias([
           'owner' => IsOwnerUser::class,
           'client' => IsClientUser::class,
           'dealer_approved' => EnsureDealerApproved::class,
       ]);
   })

## ROUTES — create complete route structure in routes/web.php:

```php
// Public
Route::get('/', fn() => redirect('/login'));

// Auth routes (Breeze handles these)
// POST /login, GET /login, POST /logout, GET /forgot-password, POST /forgot-password, GET /reset-password/{token}, POST /reset-password

// Public dealer registration
Route::get('/apply', [DealerApplicationController::class, 'create'])->name('apply.create');
Route::post('/apply', [DealerApplicationController::class, 'store'])->name('apply.store');
Route::get('/apply/received', fn() => view('auth.application-received'))->name('apply.received');

// Owner/admin portal (no prefix)
Route::middleware(['auth', 'owner'])->group(function () {
    Route::get('/dashboard', [Owner\DashboardController::class, 'index'])->name('owner.dashboard');
    
    // File Requests
    Route::resource('file-requests', Owner\FileRequestController::class)->only(['index', 'show', 'update']);
    Route::get('/file-requests/archive', [Owner\FileRequestController::class, 'archive'])->name('owner.file-requests.archive');
    Route::post('/file-requests/{fileRequest}/status', [Owner\FileRequestController::class, 'updateStatus'])->name('owner.file-requests.status');
    Route::post('/file-requests/{fileRequest}/assign', [Owner\FileRequestController::class, 'assign'])->name('owner.file-requests.assign');
    Route::post('/file-requests/{fileRequest}/charge', [Owner\FileRequestController::class, 'addCharge'])->name('owner.file-requests.charge');
    Route::post('/file-requests/{fileRequest}/credit', [Owner\FileRequestController::class, 'addCredit'])->name('owner.file-requests.credit');
    Route::post('/file-requests/{fileRequest}/void', [Owner\FileRequestController::class, 'void'])->name('owner.file-requests.void');
    Route::post('/file-requests/{fileRequest}/respond', [Owner\FileRequestController::class, 'respond'])->name('owner.file-requests.respond');
    
    // Messages
    Route::post('/file-requests/{fileRequest}/messages', [Owner\FileRequestMessageController::class, 'store'])->name('owner.messages.store');
    
    // Dealers
    Route::resource('dealers', Owner\DealerController::class)->only(['index', 'show', 'update']);
    Route::post('/dealers/{dealer}/credits', [Owner\DealerController::class, 'adjustCredits'])->name('owner.dealers.credits');
    Route::post('/dealers/{dealer}/suspend', [Owner\DealerController::class, 'suspend'])->name('owner.dealers.suspend');
    Route::post('/dealers/{dealer}/reactivate', [Owner\DealerController::class, 'reactivate'])->name('owner.dealers.reactivate');
    
    // Dealer Applications
    Route::resource('dealer-applications', Owner\DealerApplicationController::class)->only(['index', 'show']);
    Route::post('/dealer-applications/{dealerApplication}/approve', [Owner\DealerApplicationController::class, 'approve'])->name('owner.dealer-applications.approve');
    Route::post('/dealer-applications/{dealerApplication}/reject', [Owner\DealerApplicationController::class, 'reject'])->name('owner.dealer-applications.reject');
    
    // Invoices
    Route::resource('invoices', Owner\InvoiceController::class)->only(['index', 'show', 'store']);
    Route::post('/invoices/{invoice}/void', [Owner\InvoiceController::class, 'void'])->name('owner.invoices.void');
    Route::post('/invoices/{invoice}/mark-paid', [Owner\InvoiceController::class, 'markPaid'])->name('owner.invoices.mark-paid');
    
    // Configuration
    Route::resource('winols-bundles', Owner\WinolsBundleController::class)->except(['show']);
    Route::resource('file-stages', Owner\FileStageController::class)->except(['show']);
    Route::resource('file-options', Owner\FileOptionController::class)->except(['show']);
    Route::resource('tools', Owner\TuningToolController::class)->except(['show']);
    Route::resource('products', Owner\ProductController::class)->except(['show']);
    Route::resource('portal-users', Owner\PortalUserController::class)->except(['show']);
    Route::resource('noticeboards', Owner\NoticeboardController::class)->except(['show']);
    Route::resource('vehicle-stats', Owner\VehicleStatController::class)->except(['show']);
    
    // Reference tools
    Route::get('/bosch-ecu', [Owner\BoschEcuController::class, 'index'])->name('owner.bosch-ecu.index');
    Route::get('/dtc-search', [Owner\DtcSearchController::class, 'index'])->name('owner.dtc-search.index');
    Route::get('/dtc-search/results', [Owner\DtcSearchController::class, 'search'])->name('owner.dtc-search.results');
    
    // Settings
    Route::get('/settings', [Owner\SettingsController::class, 'index'])->name('owner.settings.index');
    Route::patch('/settings', [Owner\SettingsController::class, 'update'])->name('owner.settings.update');
    Route::patch('/settings/opening-hours', [Owner\SettingsController::class, 'updateHours'])->name('owner.settings.hours');
    Route::patch('/settings/branding', [Owner\SettingsController::class, 'updateBranding'])->name('owner.settings.branding');
    
    // Portal status
    Route::post('/portal-status', [Owner\PortalStatusController::class, 'update'])->name('owner.portal-status.update');
    
    // What's New
    Route::resource('whats-new', Owner\WhatsNewController::class)->except(['show']);
});

// Client portal (my prefix)
Route::prefix('my')->middleware(['auth', 'client', 'dealer_approved'])->name('client.')->group(function () {
    Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('dashboard');
    
    // File upload (multi-step)
    Route::get('/upload', [Client\FileUploadController::class, 'create'])->name('upload.create');
    Route::post('/upload', [Client\FileUploadController::class, 'store'])->name('upload.store');
    
    // File requests
    Route::get('/file-requests', [Client\FileRequestController::class, 'index'])->name('file-requests.index');
    Route::get('/file-requests/archive', [Client\FileRequestController::class, 'archive'])->name('file-requests.archive');
    Route::get('/file-requests/{fileRequest}', [Client\FileRequestController::class, 'show'])->name('file-requests.show');
    Route::post('/file-requests/{fileRequest}/messages', [Client\FileRequestMessageController::class, 'store'])->name('messages.store');
    
    // File downloads (signed temp URLs)
    Route::get('/download/{attachment}', [Client\FileDownloadController::class, 'download'])->name('download');
    
    // Credits
    Route::get('/credits/file', [Client\FileCreditController::class, 'index'])->name('credits.file');
    Route::post('/credits/file/checkout', [Client\FileCreditController::class, 'checkout'])->name('credits.file.checkout');
    Route::get('/credits/evc', [Client\EvcCreditController::class, 'index'])->name('credits.evc');
    Route::post('/credits/evc/checkout', [Client\EvcCreditController::class, 'checkout'])->name('credits.evc.checkout');
    
    // Products
    Route::get('/products', [Client\ProductController::class, 'index'])->name('products.index');
    Route::post('/products/{product}/purchase', [Client\ProductController::class, 'purchase'])->name('products.purchase');
    
    // Invoices
    Route::get('/invoices', [Client\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [Client\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/pay', [Client\InvoiceController::class, 'pay'])->name('invoices.pay');
    
    // Stripe return
    Route::get('/payment/success', [Client\PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [Client\PaymentController::class, 'cancel'])->name('payment.cancel');
    
    // Reference tools
    Route::get('/dtc-search', [Client\DtcSearchController::class, 'index'])->name('dtc-search.index');
    Route::get('/dtc-search/results', [Client\DtcSearchController::class, 'search'])->name('dtc-search.results');
    Route::get('/vehicle-stats', [Client\VehicleStatController::class, 'index'])->name('vehicle-stats.index');
    Route::get('/bosch-ecu', [Client\BoschEcuController::class, 'index'])->name('bosch-ecu.index');
    
    // Account management
    Route::get('/portal-users', [Client\PortalUserController::class, 'index'])->name('portal-users.index');
    Route::post('/portal-users/invite', [Client\PortalUserController::class, 'invite'])->name('portal-users.invite');
    Route::delete('/portal-users/{user}', [Client\PortalUserController::class, 'destroy'])->name('portal-users.destroy');
    Route::get('/settings', [Client\SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [Client\SettingsController::class, 'update'])->name('settings.update');
    Route::get('/whats-new', [Client\WhatsNewController::class, 'index'])->name('whats-new.index');
});

// Stripe webhook (outside auth middleware, CSRF exempt)
Route::post('/webhooks/stripe', [Webhooks\StripeWebhookController::class, 'handle'])
    ->name('webhooks.stripe')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
```

Create stub controllers for every route above. Each stub should:
- Have the correct namespace (App\Http\Controllers\Owner\, App\Http\Controllers\Client\, etc.)
- Have the method defined and returning a simple view or redirect placeholder
- NOT contain any business logic yet — that comes in later phases

After creating all routes and stub controllers, run:
  php artisan route:list --columns=method,uri,name,action
  
Confirm there are no missing controller method errors.
```

### Phase 2 Success Criteria
- [ ] `php artisan route:list` shows no errors
- [ ] `/login` → successful login with admin account → redirects to `/dashboard`
- [ ] Accessing `/my/dashboard` without auth redirects to `/login`
- [ ] Accessing `/dashboard` as client role returns 403
- [ ] All three middleware classes exist and are registered
- [ ] Stripe webhook route is CSRF-exempt

---

## PHASE 3: Core Services
**Goal:** Business logic services built and unit tested before any UI is built on top of them.  
**Pre-condition:** Phase 2 complete.

---

### PHASE 3 PROMPT — paste into Claude Code

```
Please build the four core service classes for the Surrey Tuning Services portal. These handle all business logic. Controllers must ONLY call these services — never put business logic in controllers.

## SERVICE 1: app/Services/CreditService.php

Methods (all wrapped in DB::transaction()):

addFileCredits(Dealer $dealer, float $amount, string $reason, ?User $performedBy = null, ?int $fileRequestId = null): FileCreditTransaction
- Adds $amount to dealer->file_credit_balance
- Creates FileCreditTransaction: type='top_up', amount=$amount, balance_after=new_balance
- Updates dealer record
- Returns the transaction

deductFileCredits(Dealer $dealer, float $amount, string $reason, User $performedBy, ?int $fileRequestId = null): FileCreditTransaction
- Throws InsufficientCreditsException if balance < amount
- Deducts $amount from dealer->file_credit_balance (makes balance negative direction)
- Creates FileCreditTransaction: type='deduction', amount=-$amount, balance_after=new_balance
- Returns the transaction

manualAdjustFileCredits(Dealer $dealer, float $amount, string $reason, User $performedBy): FileCreditTransaction
- Can be positive (credit) or negative (deduction) — determined by sign of $amount
- type = 'manual_credit' if positive, 'deduction' if negative
- Same transaction + balance update logic

addEvcCredits(Dealer $dealer, float $amount, string $reason, ?User $performedBy = null, ?int $winolsBundleId = null): EvcCreditTransaction
- Same pattern as file credits

deductEvcCredits(Dealer $dealer, float $amount, string $reason, User $performedBy): EvcCreditTransaction

hasSufficientFileCredits(Dealer $dealer, float $amount): bool
- Returns dealer->file_credit_balance >= $amount

Create app/Exceptions/InsufficientCreditsException.php extending RuntimeException.

## SERVICE 2: app/Services/InvoiceService.php

Methods:

createInvoice(Dealer $dealer, string $description, float $amountNet, InvoiceType $type, ?User $raisedBy = null, ?int $relatedId = null, ?string $relatedType = null): Invoice
- Gets vat_rate from Setting::get()->vat_rate
- Calculates vat_amount and amount_gross
- Gets next invoice number: Setting::get()->invoice_start_number + Invoice::max('invoice_number') ?? 0
- Creates Invoice record
- Returns Invoice

markPaid(Invoice $invoice, ?string $stripePaymentIntentId = null): Invoice
- Sets status='paid', paid_at=now(), stripe_payment_intent_id if provided
- Returns updated Invoice

voidInvoice(Invoice $invoice): Invoice
- Sets status='void'
- Returns updated Invoice

## SERVICE 3: app/Services/FileStorageService.php

Methods:

storeFile(UploadedFile $file, string $dealerId, string $requestNumber, AttachmentType $type): array
- Generates path: files/{dealerId}/{requestNumber}/{type->value}/{sanitised_filename}
- Stores on 'r2' disk (private)
- Returns ['path' => '...', 'stored_filename' => '...', 'original_filename' => '...', 'file_size_bytes' => ..., 'mime_type' => '...']

getTemporaryUrl(string $path, int $minutes = 30): string
- Returns Storage::disk('r2')->temporaryUrl($path, now()->addMinutes($minutes))

deleteFile(string $path): bool
- Deletes from R2

getAllowedMimeTypes(): array
- Returns: ['application/octet-stream', 'application/x-binary', 'text/plain']

getAllowedExtensions(): array
- Returns: ['bin', 'hex', 'ori', 'mod', 'kp', 'frf', 'ols']

validateFile(UploadedFile $file): void
- Throws \InvalidArgumentException if extension not in allowed list
- Max size 50MB (52428800 bytes)

## SERVICE 4: app/Services/StripeService.php

Methods (use \Stripe\Stripe::setApiKey(config('services.stripe.secret')) in constructor):

createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl, array $metadata = []): \Stripe\Checkout\Session
- Creates a Stripe Checkout Session
- Mode: 'payment'
- Currency: 'gbp'
- Returns the Session object

constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
- Uses \Stripe\Webhook::constructEvent($payload, $sigHeader, config('services.stripe.webhook_secret'))
- Throws \Stripe\Exception\SignatureVerificationException on invalid sig

Add to config/services.php:
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],

## FORM REQUESTS — create these now (used across multiple phases):

app/Http/Requests/FileRequest/StoreFileRequestRequest.php:
  Rules: make required|string|max:100, model required|string|max:100, year required|integer|min:1990|max:2030,
  engine required|string|max:50, fuel required|Enum(FuelType), transmission required|Enum(TransmissionType),
  file_stage_id required|exists:file_stages,id, tool_id required|exists:tuning_tools,id,
  file required|file|max:51200|mimes:bin,hex,ori,mod,kp,frf,ols,
  registration nullable|string|max:20, vin_number nullable|string|max:50,
  engine_code nullable|string|max:50, bhp_before nullable|numeric|min:0,
  client_notes nullable|string|max:2000

app/Http/Requests/Owner/AddChargeRequest.php:
  Rules: description required|string|max:255, amount_net required|numeric|min:0.01, apply_vat boolean

app/Http/Requests/Owner/AddCreditRequest.php:
  Rules: credit_type required|in:file,evc, amount required|numeric|min:0.01, reason required|string|max:255

app/Http/Requests/Owner/UpdateFileRequestStatusRequest.php:
  Rules: status required|Enum(FileRequestStatus)

app/Http/Requests/Dealer/StoreDealerApplicationRequest.php:
  Rules: company_name required|string|max:255, contact_name required|string|max:255,
  email required|email|unique:dealer_applications,email, phone nullable|string|max:50,
  country required|string|max:100, message nullable|string|max:2000,
  terms_accepted required|accepted

## WRITE UNIT TESTS:

tests/Unit/Services/CreditServiceTest.php:
- Test addFileCredits updates balance and creates transaction
- Test deductFileCredits fails when insufficient balance (throws InsufficientCreditsException)
- Test deductFileCredits succeeds and updates balance correctly
- Test DB transaction rolls back on failure

tests/Unit/Services/InvoiceServiceTest.php:
- Test createInvoice calculates VAT correctly
- Test createInvoice assigns correct sequential invoice number

Run: php artisan test tests/Unit/
```

### Phase 3 Success Criteria
- [ ] `php artisan test tests/Unit/` — all unit tests pass
- [ ] `CreditService::addFileCredits()` can be called from tinker without error
- [ ] `InsufficientCreditsException` is thrown when balance is insufficient
- [ ] All four Form Request files exist with correct rules

---

## PHASE 4: Owner Portal — File Requests & Dealer Management
**Goal:** The two most critical sections of the owner portal are fully functional.  
**Pre-condition:** Phase 3 complete.

---

### PHASE 4 PROMPT — paste into Claude Code

```
Please build the owner portal File Requests and Dealer Management sections. Use the owner layout (layouts/owner.blade.php). All controllers are in App\Http\Controllers\Owner\.

## POLICIES — create before controllers:

app/Policies/FileRequestPolicy.php:
  view(User $user, FileRequest $fileRequest): bool — owner/technician can view all; client only own dealer's
  respond(User $user, FileRequest $fileRequest): bool — owner/technician only
  addCharge(User $user, FileRequest $fileRequest): bool — owner/technician only
  addCredit(User $user, FileRequest $fileRequest): bool — owner only
  void(User $user, FileRequest $fileRequest): bool — owner only
  viewInternalNotes(User $user): bool — owner/technician only

Register all policies in AuthServiceProvider (or boot method in AppServiceProvider for Laravel 11).

## OWNER — FILE REQUESTS

FileRequestController@index (GET /file-requests):
- Loads all file_requests with dealer, submittedBy, assignedTechnician, fileStage eager loaded
- Paginates: 25 per page
- Filters: search (request_number, make, model, dealer.company_name LIKE), status, dealer_id, assigned_technician_id
- Passes $stages (all file stages) and $technicians (owner-team users) for filter dropdowns
- View: owner/file-requests/index.blade.php
- View has two modes toggled by Alpine.js: TABLE view (default) and KANBAN view
  - TABLE: sortable columns — #, Dealer, Vehicle (make model year), Stage badge, Assigned, Created, Actions link
  - KANBAN: columns grouped by FileRequestStatus enum value. Cards show: request number, dealer name, vehicle, time since created
  - Toggle between views with icons (list/grid), preference stored in localStorage
- Include search bar and filter dropdowns above both views

FileRequestController@show (GET /file-requests/{fileRequest}):
- Loads fileRequest with all relationships: dealer, messages.senderUser, attachments.uploaderUser, fileStage, tool, options.fileOption
- View: owner/file-requests/show.blade.php with these sections:
  LEFT PANEL (1/3 width):
    - Vehicle details card: make, model, year, engine, fuel, transmission, registration, VIN, ECU model
    - Job details card: request number, stage (dropdown to change inline), assigned technician (dropdown), tool used
    - Options selected card: list of file_request_options with prices
    - File downloads: original file(s), returned file(s) — buttons call getTemporaryUrl via controller
    - WhatsApp button: if settings->whatsapp_business_number is set, show "Open WhatsApp" button linking to https://wa.me/{number}?text=Re%3A+Job+{requestNumberFormatted}
  RIGHT PANEL (2/3 width):
    - Header: request number, dealer name, status badge, created date
    - ACTION TABS (Alpine.js tabs):
      Tab 1 "Message": text area + send button. Shows message thread below (chronological, owner messages right-aligned). System messages centred grey. Internal notes shown with lock icon and amber background.
      Tab 2 "Respond": file upload zone (returned file) + message textarea + submit button
      Tab 3 "Add Charge": description input, amount_net input, apply_vat checkbox (shows VAT calculation), submit → calls addCharge
      Tab 4 "Add Credit": credit_type select (File/EVC), amount input, reason input → calls addCredit  
      Tab 5 "Internal Note": textarea for owner-only note → posts as MessageType::InternalNote
      Tab 6 "Void": reason textarea + confirm button (only shown if status != void)

FileRequestController@updateStatus (POST /file-requests/{fileRequest}/status):
- Validates using UpdateFileRequestStatusRequest
- Updates status
- Creates a system message: "Status changed to {new_status} by {user_name}"
- Dispatches FileRequestStatusChanged event (to be wired to notification in Phase 8)
- Returns back with success flash

FileRequestController@addCharge (POST /file-requests/{fileRequest}/charge):
- Validates using AddChargeRequest  
- Creates Invoice via InvoiceService::createInvoice() — type='manual', related to fileRequest
- Creates a charge_event message in the thread
- Returns back with success

FileRequestController@addCredit (POST /file-requests/{fileRequest}/credit):
- Validates using AddCreditRequest
- Calls CreditService::addFileCredits() or addEvcCredits() based on credit_type
- Creates a credit_event message
- Returns back with success

FileRequestController@respond (POST /file-requests/{fileRequest}/respond):
- Validates: message nullable|string, file nullable|file|max:51200
- If file: stores using FileStorageService, creates FileRequestAttachment (type=returned)
- Creates FileRequestMessage (type=message, body=message text if provided)  
- Updates status to FileRequestStatus::Responded
- Dispatches NewMessagePosted event
- Returns back with success

FileRequestController@void (POST /file-requests/{fileRequest}/void):
- Validates: void_reason required|string|max:500
- Sets status=void, void_reason, closed_at=now()
- Creates system message about voiding
- Returns back with success

FileRequestController@archive (GET /file-requests/archive):
- Shows closed/void jobs older than 90 days
- Table view only, searchable

FileRequestMessageController@store (POST /file-requests/{fileRequest}/messages):
- Validates: body required|string|max:5000, is_internal boolean
- Creates FileRequestMessage
- If is_internal=false: dispatch NewMessagePosted event
- Returns back

## OWNER — DEALER MANAGEMENT

DealerController@index (GET /dealers):
- Lists all dealers with credit balances, job count, status badges
- Search by company_name or email (via primaryContact user)
- Filter by status
- View: owner/dealers/index.blade.php — table with columns: Company, Status badge, File Credits, EVC Credits, Jobs, Joined, Actions

DealerController@show (GET /dealers/{dealer}):
- View: owner/dealers/show.blade.php with tab navigation (Alpine.js):
  Tab "Overview": company details, balances, approved/suspended/reactivated actions
  Tab "File Requests": paginated table of dealer's jobs
  Tab "Invoices": paginated table of dealer's invoices
  Tab "Credits": current balances prominently. File Credit history table. EVC Credit history table. "Adjust Credits" button opens modal with: credit_type (file/evc), amount (can be negative for deduction), reason
  Tab "Notes": dealer->notes textarea (direct update), no separate notes model needed

DealerController@adjustCredits (POST /dealers/{dealer}/credits):
- Validates: credit_type in:file,evc, amount required|numeric, reason required|string|max:255
- Calls CreditService::manualAdjustFileCredits or manualAdjustEvcCredits  

DealerController@suspend (POST /dealers/{dealer}/suspend):
- Sets dealer->status = DealerStatus::Suspended

DealerController@reactivate (POST /dealers/{dealer}/reactivate):
- Sets dealer->status = DealerStatus::Approved

DealerApplicationController@index (GET /dealer-applications):
- Lists all applications, filter by status, default shows pending first
- View: table with company name, contact, email, applied date, status badge, review button

DealerApplicationController@show (GET /dealer-applications/{dealerApplication}):
- Shows all application fields
- If status=pending: show Approve and Reject buttons

DealerApplicationController@approve (POST /dealer-applications/{dealerApplication}/approve):
- Within DB::transaction():
  1. Sets application->status = ApplicationStatus::Approved, reviewed_by, reviewed_at
  2. Creates Dealer record from application data, status=approved, approved_at=now(), approved_by=auth user
  3. Creates User record: first_name from contact_name, email from application, role=dealer_owner, dealer_id=new dealer
  4. Sets dealer->approved_by to auth user
  5. Dispatches DealerApplicationApproved event (carries dealer and user — notification wired in Phase 8)
- Returns to index with success

DealerApplicationController@reject (POST /dealer-applications/{dealerApplication}/reject):
- Validates: rejection_reason required|string|max:500
- Sets application->status=rejected, reviewed_by, reviewed_at, rejection_reason
- Dispatches DealerApplicationRejected event
- Returns back with success

## VIEWS — all use owner layout, all use Blade components

Create all blade views listed above. Each view must:
- Extend layouts/owner.blade.php
- Use <x-page-header> for the page title
- Use <x-status-badge> for all status badges
- Use <x-modal> for confirmation modals
- Use pagination: {{ $records->links() }}
- Show empty states (not blank pages)
```

### Phase 4 Success Criteria
- [ ] `/dashboard` (owner) loads without error (stub is fine)
- [ ] `/file-requests` — table and kanban toggle both render
- [ ] `/file-requests/{id}` — all tabs render, file request details display
- [ ] Status change works end-to-end
- [ ] Add Charge creates an Invoice record
- [ ] Add Credit calls CreditService, updates dealer balance
- [ ] `/dealers` lists dealers
- [ ] `/dealer-applications` — approve flow creates dealer + user records
- [ ] `php artisan test --filter=FileRequest` passes

---

## PHASE 5: Owner Portal — Configuration & Settings
**Goal:** All owner configuration pages built so the portal can be fully configured before dealers use it.  
**Pre-condition:** Phase 4 complete.

---

### PHASE 5 PROMPT — paste into Claude Code

```
Please build the owner portal configuration and settings sections. All controllers in App\Http\Controllers\Owner\.

## PAGES TO BUILD:

### Owner Dashboard (GET /dashboard)
View: owner/dashboard.blade.php
Stats row (4 cards): Pending File Requests, File Requests Today, Active Dealers, Revenue This Month (sum of paid invoices amount_gross)
Recent File Requests: table of last 10 with status badges and dealer names
Dealer Applications Badge: count of pending applications shown in sidebar nav next to "Dealer Applications"

### Settings (GET /settings)
View: owner/settings/index.blade.php with Alpine.js tab navigation (6 tabs):

Tab 1 "Account":
  invoice_address (textarea), returns_address (textarea), vat_number, vat_rate (decimal input), company_number, bcc_invoice_email
  PATCH /settings → updates Setting model

Tab 2 "Opening Hours":
  For each day (Monday–Sunday): toggle is_open, time inputs open_time and close_time
  PATCH /settings/opening-hours → updates all 7 OpeningHour records

Tab 3 "Branding":
  logo_light file upload (stores to R2 under branding/logo-light.{ext}), logo_dark file upload, login_background file upload
  theme_colour colour picker (text input #rrggbb)
  PATCH /settings/branding

Tab 4 "Dealer":
  dealer_auto_onboard toggle (if true, applications are auto-approved without review)
  
Tab 5 "Invoice":
  invoice_start_number, invoice_reference_prefix, bcc_invoice_email

Tab 6 "T&Cs":
  terms_and_conditions rich textarea (plain textarea is fine for MVP)

### File Stages (CRUD at /file-stages)
Table: name, vehicle_type badge, price_net, is_active, sort_order, actions
Add/Edit via modal (Alpine.js): name, description, vehicle_type select, price_net, vat_applicable toggle, turnaround_hours, sort_order
Delete: confirmation modal, only allowed if no file_requests reference this stage

### File Options (CRUD at /file-options)
Table: name, linked stage, price_net, is_required badge, is_active, actions
Add/Edit modal: name, description, file_stage_id (select of active stages), price_net, vat_applicable, is_required, is_active

### WinOLS Bundles (CRUD at /winols-bundles)
Table: name, credits, price_net, is_active, actions
Add/Edit modal: name, credits (integer), price_net, is_active toggle

### Products (CRUD at /products)
Table: name, type badge, price_net, stock, is_active, actions
Add/Edit modal: name, description, price_net, vat_applicable, payment_type, stock (blank=unlimited), is_active, sort_order
Image upload: optional, stores to R2 under products/{id}.{ext}

### Tuning Tools (CRUD at /tools)
Table: name, category badge, is_active, sort_order, actions
Add/Edit modal: name, category select, sort_order, is_active

### Portal Users (GET/POST /portal-users)
Table: name, email, role badge, status, actions
Invite: modal with email input. Creates User with role=owner, sends password reset email.
Deactivate: sets user->status=inactive

### Noticeboard (CRUD at /noticeboards)
Table: title, priority badge, show_from, show_until, is_active, created date, actions
Add/Edit modal: title, body (textarea), priority (low/normal/high), show_from date, show_until date, is_active

### Portal Status (widget in owner layout header):
Dropdown with all PortalStatusEnum values and their labels.
POST /portal-status updates PortalStatus::current()
The current status badge should appear in the header of the owner layout AND client layout.
Cache the status for 5 minutes (Cache::remember).

### Vehicle Stats (CRUD at /vehicle-stats)
Search: make, model, year, fuel dropdowns
Table: make, model, year range, engine, stage, BHP before→after, torque before→after
Add/Edit: full form with all fields. No modal for this — use a dedicated create/edit page due to field count.

### Bosch ECU Search (GET /bosch-ecu)
Search bar: searches bosch_ecus.manufacturer_number and bosch_ecus.car_producer LIKE query
Results: table of matches (manufacturer_number, model, car_producer) with pagination
Note: table has 13,366 seeded records — use pagination (25 per page), never load all.
Search fires on form submit (not live search) to avoid DB hammering.

### DTC Search (GET /dtc-search)
Search bar: searches dtc_library.code and dtc_library.description
Results: table (code, description) with pagination (25 per page)
Alpine.js debounced search (500ms) that calls GET /dtc-search/results?q=... via fetch and replaces the results div.

All CRUD controllers must use Form Request classes for validation. Create them in app/Http/Requests/Owner/.
All delete actions must check for dependencies (e.g. don't delete a file stage that has active jobs).
```

### Phase 5 Success Criteria
- [ ] Settings page saves to database for all 6 tabs
- [ ] File Stages CRUD works (create, edit, delete with dependency check)
- [ ] WinOLS Bundles CRUD works
- [ ] Portal Status toggle updates the header badge in real-time
- [ ] DTC Search returns results with debounce
- [ ] Bosch ECU Search is paginated and doesn't time out

---

## PHASE 6: Client Portal — Core
**Goal:** Dealers can log in, submit files, and track their jobs.  
**Pre-condition:** Phase 5 complete.

---

### PHASE 6 PROMPT — paste into Claude Code

```
Please build the client-facing portal. All controllers in App\Http\Controllers\Client\. All routes are prefixed /my/ and use the client layout (layouts/client.blade.php). Clients can ONLY see their own dealer's data — enforce this via Policy checks on every controller.

## POLICIES

Update FileRequestPolicy (already created in Phase 4):
  Ensure view() returns false if client tries to access another dealer's file request.
  Use: $fileRequest->dealer_id === $user->dealer_id

## CLIENT DASHBOARD (GET /my/dashboard)

DashboardController@index:
- Queries restricted to auth()->user()->dealer_id
- Stats: Active Jobs (status not in closed,void), Completed Jobs (status=closed), File Credit Balance, EVC Credit Balance
- Recent file requests: last 5 for this dealer (with stage badge, status badge)
- Active noticeboard messages: Noticeboard::scopeActive()->orderBy('priority','desc')->take(3)
- Portal status: PortalStatus::current() — show banner if not 'available'
- Opening hours: today's OpeningHour record, show if currently open

View: client/dashboard.blade.php using <x-stat-card> components.

## FILE UPLOAD (GET /my/upload, POST /my/upload)

FileUploadController — multi-step form using Alpine.js (no page reloads between steps):

Step 1 — Vehicle Details:
  Fields: make (text, required), model (text, required), year (select 1990–current+1, required), 
  registration (text, optional), VIN (text, optional), engine (text, required), 
  engine_code (text, optional), fuel (select from FuelType enum), 
  transmission (select from TransmissionType enum), bhp_before (number, optional),
  torque_before_nm (number, optional), ecu_model_no (text, optional)

Step 2 — Service Selection:
  File Stage selector: cards/tiles showing each active FileStage with name, description, price
  File Options: checkboxes grouped under the selected stage showing name, description, price_net per option
  Running total shown live (Alpine.js x-text showing selected stage price + sum of checked options)
  Selected tool: dropdown from active TuningTools
  If dealer has insufficient file credits to cover total: show warning banner with "Top Up Credits" link. Do NOT block submission — owner can still process and invoice separately.
  DTC Codes section: ability to add multiple DTC codes (code + optional description). Dynamic add/remove with Alpine.js.

Step 3 — File Upload & Review:
  File drop zone: drag and drop OR click to browse. Accepted: .bin .hex .ori .mod .kp .frf .ols. Max 50MB.
  Client notes textarea.
  Summary review: vehicle details, selected stage, options, total cost, file name.
  Submit button.

On POST /my/upload (FileUploadController@store):
- Validate using StoreFileRequestRequest
- Within DB::transaction():
  1. Generate request_number using ReferenceNumber::generateJobReference()
  2. Create FileRequest record
  3. Store uploaded file using FileStorageService::storeFile() — type=original
  4. Create FileRequestAttachment record
  5. Create FileRequestOption records (snapshot prices from file_options at time of submission)
  6. Create any DtcCode records
  7. Create system FileRequestMessage: "File request submitted by {dealer_name}"
  8. Dispatch FileRequestSubmitted event (notification wired in Phase 8)
- Redirect to /my/file-requests/{id} with success flash

## CLIENT FILE REQUESTS (GET /my/file-requests)

FileRequestController@index:
- Only this dealer's file requests
- Default: active jobs (status NOT in closed, void)
- Same table/kanban toggle as owner view but read-only (no action dropdowns)
- Filter by status only
- View: client/file-requests/index.blade.php

FileRequestController@archive:
- Completed/void jobs older than 30 days
- Table only, searchable

FileRequestController@show (GET /my/file-requests/{fileRequest}):
- Policy check: this dealer's job only
- LEFT PANEL: vehicle details (read only), selected stage and options, file downloads (original and returned)
  - Download button calls FileDownloadController@download which returns getTemporaryUrl redirect
  - Show "Downloaded at: {date}" when client_downloaded_at is set
- RIGHT PANEL: message thread (chronological). Internal messages NOT shown. System messages shown in grey.
  - Text input + send button at bottom
  - WhatsApp button if settings->whatsapp_business_number is set
- No action tabs — client cannot change status, add charges, or view internal data
- Show invoice(s) associated with this job at the bottom if any exist

FileRequestMessageController@store (POST /my/file-requests/{fileRequest}/messages):
- Policy check
- Creates message (type=message, is_internal=false)
- Dispatches NewMessagePosted event
- Returns back

FileDownloadController@download (GET /my/download/{attachment}):
- Policy check that this attachment belongs to this dealer's file request
- For returned files: update client_downloaded_at on the FileRequest (if first download)
- Returns redirect to getTemporaryUrl (30 min expiry)
- Do NOT serve the file through PHP — redirect to the signed R2 URL

All views: extend layouts/client.blade.php. No raw data from other dealers ever appears.
```

### Phase 6 Success Criteria
- [ ] Client login → `/my/dashboard` loads with correct stats
- [ ] File upload multi-step form works (all 3 steps, no page reloads)
- [ ] File is stored in R2 and a FileRequestAttachment record is created
- [ ] `/my/file-requests` shows only this dealer's jobs
- [ ] Client cannot access another dealer's file request (returns 403)
- [ ] Download link generates a signed URL and redirects

---

## PHASE 7: Client Portal — Commerce (Credits, Products, Invoices)
**Goal:** Dealers can top up credits and purchase products via Stripe.  
**Pre-condition:** Phase 6 complete. Stripe keys must be configured in `.env`.

---

### PHASE 7 PROMPT — paste into Claude Code

```
Please build the credits, products, and invoices sections of the client portal, plus the Stripe checkout flow and webhook handler.

## STRIPE CHECKOUT FLOWS

For all checkout flows:
- Use Stripe Checkout (hosted payment page) — not Stripe Elements
- Success URL: route('client.payment.success')+'?session_id={CHECKOUT_SESSION_ID}'
- Cancel URL: route('client.payment.cancel')
- All amounts in pence (multiply GBP by 100)
- Store stripe_payment_intent_id on the related record before redirecting

### File Credits Top-Up (GET /my/credits/file, POST /my/credits/file/checkout)

FileCreditController@index:
- Current file_credit_balance shown prominently
- List active products where payment_type in ('file_credits','both') and type implies credit top-up
  (For now: show WinOLS bundles are EVC only; create at least one Product seeded for file credit top-up as example)
- Transaction history table: FileCreditTransaction for this dealer, paginated 25/page
  Columns: Date, Type badge, Reason, Amount (green=positive, red=negative), Balance After

FileCreditController@checkout (POST /my/credits/file/checkout):
- Validates: product_id required|exists:products,id
- Creates Stripe Checkout Session via StripeService
- Stores: session_id → do NOT create the credit yet (wait for webhook)
- Redirects to Stripe checkout URL

### EVC Credits Purchase (GET /my/credits/evc, POST /my/credits/evc/checkout)

EvcCreditController@index:
- Current evc_credit_balance shown prominently
- List active WinolsBundles (name, credits, price)
- Transaction history: EvcCreditTransaction for this dealer

EvcCreditController@checkout (POST /my/credits/evc/checkout):
- Validates: winols_bundle_id required|exists:winols_bundles,id|where:is_active,1
- Creates Stripe Checkout Session
- Redirects to Stripe checkout URL

### Invoices (GET /my/invoices, GET /my/invoices/{invoice}, POST /my/invoices/{invoice}/pay)

InvoiceController@index:
- All invoices for this dealer, paginated, newest first
- Filter by status (issued/paid/void)
- Table: Invoice #, Description, Net, VAT, Gross, Status badge, Date, Actions

InvoiceController@show:
- Full invoice detail view
- If status=issued: show "Pay Now" button

InvoiceController@pay (POST /my/invoices/{invoice}/pay):
- Creates Stripe Checkout Session for the invoice amount_gross
- Redirects to Stripe

### Products (GET /my/products, POST /my/products/{product}/purchase)

ProductController@index:
- Grid of active products with image (if set), name, description, price
- Payment method badge: "Pay with Credits" / "Card Payment" / "Credits or Card"

ProductController@purchase (POST /my/products/{product}/purchase):
- Validates: payment_method required|in:file_credits,stripe, quantity optional|integer|min:1
- If payment_method=file_credits: check sufficient balance, deduct via CreditService, create ProductOrder, create Invoice, return success
- If payment_method=stripe: create Stripe Checkout Session, redirect

### Payment Return Pages

PaymentController@success (GET /my/payment/success):
- Retrieves the Stripe Checkout Session from ?session_id param
- Verifies session status is 'complete'
- Shows "Payment successful" confirmation
- Note: actual credit/invoice update happens via webhook, not here

PaymentController@cancel (GET /my/payment/cancel):
- Shows "Payment cancelled" message with link back

## STRIPE WEBHOOK HANDLER

StripeWebhookController@handle (POST /webhooks/stripe):
- Route is CSRF exempt (already set up in Phase 2)
- Retrieves raw request body and Stripe-Signature header
- Calls StripeService::constructWebhookEvent() — return 400 on invalid signature
- Handle these events:

  checkout.session.completed:
    - Get session->metadata to determine what was purchased (type: 'file_credits', 'evc_bundle', 'product', 'invoice')
    - If type='file_credits': 
        Find product from metadata->product_id
        Call CreditService::addFileCredits() with amount from product->credit_value
        Create Invoice via InvoiceService (type=credit_top_up)
        InvoiceService::markPaid() with payment_intent_id
    - If type='evc_bundle':
        Find bundle from metadata->winols_bundle_id
        Call CreditService::addEvcCredits() with bundle->credits
        Create Invoice (type=evc_bundle), mark paid
    - If type='product':
        Create ProductOrder, mark paid
        Create Invoice (type=product), mark paid
    - If type='invoice':
        Find invoice from metadata->invoice_id
        InvoiceService::markPaid()
    - In all cases: dispatch PaymentConfirmed event (notification in Phase 8)
    - Return 200

  payment_intent.payment_failed:
    - Log the failure: Log::error('Stripe payment failed', ['intent_id' => ...])
    - Return 200

- All event handling wrapped in try/catch — always return 200 unless signature check fails
- Return 400 for invalid signatures, 422 for missing metadata, 200 for everything else

## METADATA PATTERN for Stripe Sessions

When creating a checkout session, always include metadata:
  type: 'file_credits'|'evc_bundle'|'product'|'invoice'
  dealer_id: $dealer->id
  product_id: (for file_credits)
  winols_bundle_id: (for evc_bundle)
  product_order_id: (for product — create ProductOrder record with status=pending BEFORE checkout)
  invoice_id: (for invoice payment)
```

### Phase 7 Success Criteria
- [ ] Stripe Checkout session created successfully (test mode)
- [ ] Webhook handler verifies signature and returns 200
- [ ] `checkout.session.completed` webhook updates dealer credit balance
- [ ] Invoice is created and marked paid on successful webhook
- [ ] Client can pay an outstanding invoice
- [ ] Payment with file credits deducts balance immediately

---

## PHASE 8: Notifications & File Storage
**Goal:** All email notifications wired and working. R2 file storage verified end-to-end.  
**Pre-condition:** Phase 7 complete. Resend API key must be configured.

---

### PHASE 8 PROMPT — paste into Claude Code

```
Please wire up all email notifications and verify R2 file storage is working end to end.

## EVENTS & LISTENERS

Create Events in app/Events/:
  FileRequestSubmitted(FileRequest $fileRequest)
  FileRequestStatusChanged(FileRequest $fileRequest, FileRequestStatus $oldStatus)
  NewMessagePosted(FileRequestMessage $message)
  DealerApplicationApproved(DealerApplication $application, Dealer $dealer, User $user)
  DealerApplicationRejected(DealerApplication $application)
  PaymentConfirmed(Invoice $invoice, Dealer $dealer)

Register listeners in AppServiceProvider (Laravel 13 — there is no EventServiceProvider by default. Use the listen array in AppServiceProvider or ShouldHandleEventsAfterCommit interface):
  FileRequestSubmitted → [NotifyOwnerNewFileRequest::class, NotifyDealerFileReceived::class]
  FileRequestStatusChanged → [NotifyDealerStatusChanged::class]
  NewMessagePosted → [NotifyRecipientNewMessage::class]
  DealerApplicationApproved → [SendDealerApprovalEmail::class]
  DealerApplicationRejected → [SendDealerRejectionEmail::class]
  PaymentConfirmed → [SendPaymentConfirmationEmail::class]

All listeners implement ShouldQueue.

## NOTIFICATION CLASSES (app/Notifications/)

Create a base email template at resources/views/emails/layout.blade.php:
  Header: Surrey Tuning Services logo (uses Settings logo_light), deep red/dark header bar
  Content area: white, readable
  Footer: "Surrey Tuning Services | surreytuningservices.co.uk"
  Unsubscribe note if applicable

Create Notification classes:

1. NewFileRequestOwnerNotification (Mailable/Notification to owner team)
   Subject: "New File Request — {requestNumberFormatted}"
   Body: dealer name, vehicle (make model year), selected stage, time submitted, link to owner portal /file-requests/{id}
   Sent to: all User where role in [owner, technician] AND notify_file_requests_email=1

2. FileReceivedDealerNotification (to submitting dealer user)
   Subject: "File Request Received — {requestNumberFormatted}"
   Body: confirms receipt, lists vehicle and selected stage, link to /my/file-requests/{id}
   Sent to: submitting user if notify_file_requests_email=1

3. StatusChangedNotification (to dealer)
   Subject: "Job Update — {requestNumberFormatted}"
   Body: "Your job {number} for {vehicle} has been updated to: {new status label}", link to job
   Sent to: dealer's primary contact if notify_file_requests_email=1

4. NewMessageNotification (to recipient)
   Subject: "New Message on Job {requestNumberFormatted}"
   Body: sender name, message preview (first 200 chars), link to job
   Logic: if message sent by owner/technician → send to dealer submitter; if sent by dealer → send to all owner/technician users with notify_comments_email=1
   Respect notify_comments_email preference

5. DealerApprovedNotification (to new dealer user)
   Subject: "Your Account Has Been Approved — Surrey Tuning Services"
   Body: welcome message, their email address, button to set password (use Laravel password reset link: Password::createToken($user) → url('/reset-password/{token}?email={email}'))
   Critical: this is how the dealer first accesses the portal

6. DealerRejectedNotification (to applicant email — NOT a user record)
   Subject: "Your Application — Surrey Tuning Services"
   Body: thank them for applying, politely inform of rejection, include reason if provided
   Send to: application->email (not via User model — no user exists)

7. PaymentConfirmedNotification (to dealer)
   Subject: "Payment Confirmed — Invoice {invoice_number}"
   Body: confirms payment, amount paid, what was purchased (credits added if applicable), link to invoice

## RESEND CONFIGURATION

In config/mail.php add resend mailer:
  'resend' => [
      'transport' => 'resend',
  ],

Ensure MAIL_MAILER=resend in .env. Wrap Resend setup in the resend/resend-laravel package docs.

## QUEUE CONFIGURATION

Set QUEUE_CONNECTION=database in .env.
NOTE: Laravel 13 already includes the jobs migration (0001_01_01_000002_create_jobs_table.php) — do NOT run php artisan queue:table again, it already exists.
For development: run php artisan queue:work in a separate terminal.

## R2 FILE STORAGE VERIFICATION

1. Run a test upload through the file upload form
2. Verify the file appears in R2 bucket (check via Cloudflare dashboard or tinker)
3. Test the download redirect: GET /my/download/{attachment} must return a 302 to a signed URL
4. Verify signed URL expires after 30 minutes
5. If R2 credentials not yet available: create a FakeFileStorageService that stores files on local disk under storage/app/r2-test/ instead. The real R2 storage will be swapped in on deployment.

## TEST: verify notifications
  php artisan tinker
  $dealer = Dealer::find(1); // adjust
  $user = User::where('role','owner')->first();
  // Test notification without queue:
  $user->notify(new \App\Notifications\NewFileRequestOwnerNotification(FileRequest::first()));
```

### Phase 8 Success Criteria
- [ ] All Event and Listener classes exist and are registered
- [ ] `php artisan queue:work` processes jobs without errors
- [ ] Owner receives email when file request is submitted
- [ ] Dealer receives approval email with working password reset link
- [ ] Download links expire and are not publicly accessible
- [ ] `php artisan test` — all existing tests still pass

---

## PHASE 9: Reference Tools & Dealer Registration
**Goal:** DTC search, vehicle stats, Bosch ECU, and the public dealer registration form.  
**Pre-condition:** Phase 8 complete.

---

### PHASE 9 PROMPT — paste into Claude Code

```
Please complete the reference tools and the public dealer registration/application flow.

## DTC LIBRARY (7,698 records — already seeded)

Client DtcSearchController@index (GET /my/dtc-search):
- Render a search page with a search input
- On input change (Alpine.js 500ms debounce), fire fetch to GET /my/dtc-search/results?q={query}
- Results endpoint returns JSON: { data: [{code, description}], total: n }
- Client-side: update results div from JSON response (Alpine.js x-html or x-for)
- Show loading spinner during fetch
- Show "No results for {query}" if empty
- Show "Search the DTC code library — enter a code or keyword" as placeholder state
- Paginate: show up to 50 results per search, show "Showing X of Y results"
- Searching both code (exact match first) and description (LIKE match)

Owner version at /dtc-search is identical — same controller logic, different layout.

## BOSCH ECU SEARCH (13,366 records — already seeded)

Both client (/my/bosch-ecu) and owner (/bosch-ecu) — identical functionality:
- Search form: manufacturer_number text input, car_producer text input — submit on button click (NOT live search — too many records)
- Results: table with manufacturer_number, model, car_producer, paginated 25/page
- Use GET with query params so pagination links work
- Show "Enter a manufacturer number or producer name to search" as empty state
- If no search performed: show the empty state (do not load all 13,366 records)

## VEHICLE STATS

Owner view (/vehicle-stats):
- Table: make, model, year range, engine, stage, BHP before/after, torque before/after
- Filters: make, model, fuel type
- Add/Edit via full page form (not modal — too many fields)
- Create/edit route: /vehicle-stats/create, /vehicle-stats/{id}/edit

Client view (/my/vehicle-stats):
- Search: make, model, fuel, year inputs — submit on button
- Results table: same columns as owner
- Read-only — no add/edit

## DEALER REGISTRATION (PUBLIC)

DealerApplicationController@create (GET /apply):
- Public page, no auth required
- View: auth/apply.blade.php using layouts/auth.blade.php
- Multi-field form:
  Company Name (required)
  Contact Name (required)
  Email Address (required, unique check against dealer_applications)
  Phone Number (optional)
  Country (default: United Kingdom)
  Message / How can we help? (optional)
  Terms & Conditions checkbox (required) — shows terms from Settings::get()->terms_and_conditions
- Submit button: "Apply for an Account"

DealerApplicationController@store (POST /apply):
- Validate using StoreDealerApplicationRequest
- Check if dealer_auto_onboard enabled (Setting::get()->dealer_auto_onboard):
  - If true: auto-approve — create Dealer and User immediately, dispatch DealerApplicationApproved
  - If false: create DealerApplication with status=pending, dispatch notification to owner
- Redirect to /apply/received

GET /apply/received:
- Simple view: "Thank you, {contact_name}. Your application has been received. We'll be in touch shortly."

## WHATS NEW

Owner WhatsNewController (CRUD at /whats-new):
  List table: title, version, published_at, actions
  Create/Edit form: title, body (textarea), version (e.g. "1.2.0"), published_at (date)
  Delete with confirmation

Client WhatsNewController (GET /my/whats-new):
  Shows all entries ordered by published_at DESC
  Card layout: version badge, title, body, date

## PORTAL USERS (both portals)

Owner PortalUserController (/portal-users):
  Already built in Phase 5 — ensure invite email uses DealerApprovedNotification (or a new PortalInviteNotification)
  Invited user gets a "Set your password" email via Password::createToken

Client PortalUserController (/my/portal-users):
  Shows users linked to auth()->user()->dealer_id
  Dealer owners can invite new dealer_user type users
  Invite: creates User with role=dealer_owner (or dealer_user?), dealer_id, sends password set email
  Remove: soft-deletes user (sets status=inactive, does not delete)

## CLIENT ACCOUNT SETTINGS (GET /my/settings)

SettingsController@index:
  Tab 1 "Account": update dealer->company_name, dealer->invoice_address, dealer->country
  Tab 2 "Profile": update user->first_name, user->last_name, user->phone, user->whatsapp_number
  Tab 3 "Security": change password form (current_password, password, password_confirmation)
  Tab 4 "Notifications": toggles for notify_comments_email, notify_file_requests_email
```

### Phase 9 Success Criteria
- [ ] DTC search returns results within 500ms for common codes
- [ ] Bosch ECU search paginates correctly
- [ ] `/apply` form submits and creates a DealerApplication
- [ ] Auto-onboard flow creates user immediately when enabled
- [ ] What's New entries display correctly on both portals
- [ ] Client can change their own password via settings

---

## PHASE 10: Polish, Performance & Security
**Goal:** Production-ready hardening, branding system, dark mode, and final UX polish.  
**Pre-condition:** Phases 1–9 complete and all feature tests passing.

---

### PHASE 10 PROMPT — paste into Claude Code

```
Please complete the final production-readiness items for the Surrey Tuning Services portal.

## BRANDING SYSTEM

The Settings model has logo_light, logo_dark, login_background, and theme_colour fields.
The owner uploads these in Settings > Branding tab (already built in Phase 5).

1. Create a BrandingService or use Settings::get() directly to inject branding into layouts:
   - In layouts/owner.blade.php and layouts/client.blade.php:
     Logo in sidebar: if Settings::get()->logo_dark is set, use <img src="{{ Storage::disk('r2')->url(Settings::get()->logo_dark) }}">; else show text "Surrey Tuning"
   - In layouts/auth.blade.php:
     Background image: if login_background is set, apply as CSS background on the page
     Logo: use logo_light or logo_dark as appropriate for the background
   - Theme colour: inject Settings::get()->theme_colour as a CSS custom property:
     <style>:root { --brand-colour: {{ Settings::get()->theme_colour ?? '#e63012' }}; }</style>
     Use var(--brand-colour) in Tailwind for active sidebar items, primary buttons, and badges.
   - Cache Settings::get() result for 10 minutes: Cache::remember('settings', 600, fn() => Setting::first())

2. Portal Status Banner:
   If PortalStatus::current()->status !== 'available', show a banner on the client portal:
   "The portal is currently {status label}. Some services may be limited."
   Colour the banner based on status: available=green, busy=amber, closed=red, etc.

3. Business Hours Check:
   On the client dashboard, show today's opening hours and whether currently open.
   "Open today: 09:00–17:30 (closes in 2h 15m)" or "Closed today"

## DARK MODE

Already scaffolded in the layout (toggle in header). Complete the implementation:
1. HTML root element gets class 'dark' when dark mode is active (toggled by Alpine.js, stored in localStorage)
2. Sidebar: dark:bg-gray-900, content area: dark:bg-gray-800, cards: dark:bg-gray-700
3. Text: dark:text-gray-100 for primary, dark:text-gray-300 for secondary
4. Inputs: dark:bg-gray-700 dark:border-gray-600 dark:text-white
5. Tables: dark:bg-gray-800 dark:border-gray-700 rows dark:hover:bg-gray-700

## PERFORMANCE

1. Add database indexes (create a new migration):
   file_requests: INDEX on (dealer_id, status), INDEX on (status), INDEX on (request_number)
   file_credit_transactions: INDEX on (dealer_id, created_at)
   evc_credit_transactions: INDEX on (dealer_id, created_at)
   invoices: INDEX on (dealer_id, status), INDEX on (invoice_number)
   file_request_messages: INDEX on (file_request_id, created_at)
   bosch_ecus: FULLTEXT INDEX on (manufacturer_number, car_producer) — for search performance
   dtc_library: FULLTEXT INDEX on (code, description)

2. Eager loading audit — scan all controllers for N+1 queries:
   File request index: ->with(['dealer', 'fileStage', 'assignedTechnician'])
   File request show: ->with(['dealer', 'messages.senderUser', 'attachments', 'fileStage', 'tool', 'options.fileOption', 'dtcCodes'])
   Dealer index: ->withCount('fileRequests')
   
3. Add route caching reminder in deployment docs (do not cache in development)

## SECURITY HARDENING

1. Ensure all file downloads check Policy before generating signed URL
2. Add rate limiting to auth routes (already provided by Breeze, verify it's active):
   RateLimiter::for('login', fn(Request $request) => Limit::perMinute(5)->by($request->input('email')));
3. Add rate limiting to dealer application form: Limit::perHour(3)->by($request->ip())
4. Validate all file uploads server-side (extension + MIME — not just extension):
   FileStorageService::validateFile() should check both finfo_file() mime and extension
5. Ensure Stripe webhook signature verification is active (already done in Phase 7 — verify)
6. Add HTTPS redirect in AppServiceProvider if in production:
   if (app()->environment('production')) { URL::forceScheme('https'); }
7. Ensure .env is in .gitignore (it should be by default)

## FINAL TESTS

Run the full test suite:
  php artisan test --coverage

Write any missing feature tests to reach coverage of:
- All controller index/show/store/update/destroy actions: pass
- Auth guards on all protected routes: verified
- CreditService: all methods tested
- InvoiceService: invoice creation and VAT calculation tested
- Stripe webhook handler: checkout.session.completed tested with a mock event

## DEPLOYMENT CHECKLIST (add to README.md)

Create README.md at project root with:
1. php artisan key:generate
2. php artisan migrate --force
3. php artisan db:seed --force (only on fresh installs)
4. php artisan storage:link
5. php artisan config:cache && php artisan route:cache && php artisan view:cache
6. npm run build (or assets already compiled)
7. Set QUEUE_CONNECTION=redis in production .env
8. Start queue worker: php artisan queue:work --daemon
9. Set up Stripe webhook endpoint in Stripe Dashboard pointing to https://yourdomain.com/webhooks/stripe
10. Configure Cloudflare R2 bucket with private access
11. Set APP_ENV=production, APP_DEBUG=false in .env
```

### Phase 10 Success Criteria
- [ ] `php artisan test` — 100% of written tests pass, no failures
- [ ] Dark mode toggle works on both portals
- [ ] Branding (logo, colour) is applied from Settings
- [ ] Rate limiting blocks >5 failed login attempts
- [ ] `php artisan route:cache` succeeds (no closures in routes)
- [ ] `php artisan optimize` runs without errors
- [ ] All N+1 queries eliminated (check with Laravel Debugbar in dev)

---

## PHASE DEPENDENCY MAP

```
Phase 0 (Environment)
    └── Phase 1 (Database & Models)
            └── Phase 2 (Auth & Routing)
                    └── Phase 3 (Core Services)
                            ├── Phase 4 (Owner: File Requests & Dealers)
                            │       └── Phase 5 (Owner: Config & Settings)
                            │               └── Phase 6 (Client: Core)
                            │                       └── Phase 7 (Client: Commerce + Stripe)
                            │                               └── Phase 8 (Notifications + R2)
                            │                                       └── Phase 9 (Reference Tools + Registration)
                            │                                               └── Phase 10 (Polish & Security)
```

---

## THINGS DELIBERATELY LEFT FOR PHASE 2 (post-MVP)

Do NOT build these in any phase above:
- PDF invoice generation/download
- SMS notifications
- VRM/DVLA auto-lookup on file upload
- Two-factor authentication (2FA)
- Audit log table
- Per-dealer pricing overrides
- Scheduled/targeted noticeboard messages
- File version history
- Satisfaction ratings
- Push/browser notifications
- Real-time chat (Laravel Reverb) — messaging is DB-driven email-notified only for MVP
- Reports section
- PayPal gateway
- Multi-tenant support
- API / webhooks

If asked to implement any of these during the main build, add a TODO comment and move on.

---

## QUICK REFERENCE — KEY FILE LOCATIONS

```
app/
  Enums/               — All PHP backed enums
  Exceptions/          — InsufficientCreditsException, etc.
  Http/
    Controllers/
      Auth/            — Login, Logout, ForgotPassword
      Client/          — All /my/* controllers
      Owner/           — All /* owner controllers
      Webhooks/        — StripeWebhookController
    Middleware/        — IsOwnerUser, IsClientUser, EnsureDealerApproved
    Requests/          — All Form Request classes
  Models/              — All Eloquent models
  Notifications/       — All email notifications
  Policies/            — FileRequestPolicy, InvoicePolicy, etc.
  Services/            — CreditService, InvoiceService, FileStorageService, StripeService
  Helpers/             — ReferenceNumber
resources/
  views/
    layouts/           — auth.blade.php, owner.blade.php, client.blade.php
    components/        — stat-card, status-badge, modal, data-table, flash-messages, page-header
    auth/              — login, forgot-password, apply, application-received
    owner/             — all owner portal views
    client/            — all client portal views
    emails/            — layout + all email templates
routes/
  web.php              — complete route file (no api.php usage)
database/
  migrations/          — numbered 000001–000024+
  seeders/             — all seeders
tests/
  Unit/Services/       — CreditServiceTest, InvoiceServiceTest
  Feature/             — controller tests
```

---

*Document version 1.0 — Created June 2026*  
*Based on Surrey Tuning Services Portal Specification v1.0*
