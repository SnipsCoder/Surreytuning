<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Records every Stripe event id we have already processed so that a
        // redelivered webhook (Stripe retries on any non-2xx, and may deliver
        // the same event more than once) is a no-op. The unique constraint on
        // event_id is the idempotency guarantee — a concurrent duplicate loses
        // the insert race and is treated as already handled.
        Schema::create('processed_stripe_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('type');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_stripe_events');
    }
};
