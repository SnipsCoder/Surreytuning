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
use App\Policies\FileRequestPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(FileRequest::class, FileRequestPolicy::class);

        Event::listen(FileRequestSubmitted::class, NotifyOwnerNewFileRequest::class);
        Event::listen(FileRequestSubmitted::class, NotifyDealerFileReceived::class);
        Event::listen(FileRequestStatusChanged::class, NotifyDealerStatusChanged::class);
        Event::listen(NewMessagePosted::class, NotifyRecipientNewMessage::class);
        Event::listen(DealerApplicationApproved::class, SendDealerApprovalEmail::class);
        Event::listen(DealerApplicationRejected::class, SendDealerRejectionEmail::class);
        Event::listen(PaymentConfirmed::class, SendPaymentConfirmationEmail::class);
    }
}
