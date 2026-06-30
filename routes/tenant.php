<?php

declare(strict_types=1);

use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\DealerApplicationController;
use App\Http\Controllers\Owner;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhooks;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are resolved against the current tenant's own database.
| They are loaded by the TenantRouteServiceProvider and only match when
| the request is made against a tenant's domain.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Two-factor authentication — exempt from 2FA enforcement itself
        Route::prefix('two-factor')->name('two-factor.')->group(function () {
            Route::get('/setup', [Auth\TwoFactorController::class, 'setup'])->name('setup');
            Route::post('/setup/totp', [Auth\TwoFactorController::class, 'initTotp'])->name('setup.totp');
            Route::post('/setup/email', [Auth\TwoFactorController::class, 'initEmail'])->name('setup.email');
            Route::post('/confirm', [Auth\TwoFactorController::class, 'confirm'])->name('confirm');
            Route::get('/challenge', [Auth\TwoFactorController::class, 'challenge'])->name('challenge');
            Route::post('/verify', [Auth\TwoFactorController::class, 'verify'])->name('verify');
            Route::post('/resend', [Auth\TwoFactorController::class, 'resend'])->name('resend');
            Route::post('/disable', [Auth\TwoFactorController::class, 'disable'])->name('disable');
        });
    });

    require __DIR__.'/auth.php';

    // Public dealer registration
    Route::get('/apply', [DealerApplicationController::class, 'create'])->name('apply.create');
    Route::post('/apply', [DealerApplicationController::class, 'store'])->name('apply.store')->middleware('throttle:3,60');
    Route::get('/apply/received', fn () => view('auth.application-received'))->name('apply.received');

    // Owner/admin portal (no prefix)
    Route::middleware(['auth', 'two_factor', 'owner'])->group(function () {
        Route::get('/dashboard', [Owner\DashboardController::class, 'index'])->name('owner.dashboard');

        // File Requests
        Route::get('/file-requests/archive', [Owner\FileRequestController::class, 'archive'])->name('owner.file-requests.archive');
        Route::resource('file-requests', Owner\FileRequestController::class)->only(['index', 'show', 'update']);
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
    Route::prefix('my')->middleware(['auth', 'two_factor', 'client', 'dealer_approved'])->name('client.')->group(function () {
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
        Route::get('/credits/slave', [Client\SlaveCreditController::class, 'index'])->name('credits.slave');
        Route::post('/credits/slave/checkout', [Client\SlaveCreditController::class, 'checkout'])->name('credits.slave.checkout');
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
        ->withoutMiddleware([VerifyCsrfToken::class]);
});
