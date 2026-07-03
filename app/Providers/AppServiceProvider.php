<?php

namespace App\Providers;

use App\Events\DealerApplicationApproved;
use App\Events\DealerApplicationRejected;
use App\Events\FileRequestStatusChanged;
use App\Events\FileRequestSubmitted;
use App\Events\NewMessagePosted;
use App\Events\PaymentConfirmed;
use App\Listeners\NotifyDealerFileReceived;
use App\Listeners\NotifyDealerStatusChanged;
use App\Listeners\NotifyOwnerNewFileRequest;
use App\Listeners\NotifyRecipientNewMessage;
use App\Listeners\SendDealerApprovalEmail;
use App\Listeners\SendDealerRejectionEmail;
use App\Listeners\SendPaymentConfirmationEmail;
use App\Models\FileRequest;
use App\Models\Invoice;
use App\Policies\FileRequestPolicy;
use App\Policies\InvoicePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Sentry\State\Scope;
use Stancl\Tenancy\Events\TenancyEnded;
use Stancl\Tenancy\Events\TenancyInitialized;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(FileRequest::class, FileRequestPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);

        Event::listen(FileRequestSubmitted::class, NotifyOwnerNewFileRequest::class);
        Event::listen(FileRequestSubmitted::class, NotifyDealerFileReceived::class);
        Event::listen(FileRequestStatusChanged::class, NotifyDealerStatusChanged::class);
        Event::listen(NewMessagePosted::class, NotifyRecipientNewMessage::class);
        Event::listen(DealerApplicationApproved::class, SendDealerApprovalEmail::class);
        Event::listen(DealerApplicationRejected::class, SendDealerRejectionEmail::class);
        Event::listen(PaymentConfirmed::class, SendPaymentConfirmationEmail::class);

        $this->tagSentryWithTenant();
    }

    /**
     * Tag every Sentry event with the active tenant so errors can be traced
     * back to the correct tenant database in a multi-tenant deployment.
     */
    protected function tagSentryWithTenant(): void
    {
        if (! app()->bound('sentry')) {
            return;
        }

        Event::listen(TenancyInitialized::class, function (TenancyInitialized $event): void {
            \Sentry\configureScope(function (Scope $scope) use ($event): void {
                $scope->setTag('tenant_id', (string) $event->tenancy->tenant->getTenantKey());
            });
        });

        Event::listen(TenancyEnded::class, function (): void {
            \Sentry\configureScope(function (Scope $scope): void {
                $scope->setTag('tenant_id', 'central');
            });
        });
    }
}
