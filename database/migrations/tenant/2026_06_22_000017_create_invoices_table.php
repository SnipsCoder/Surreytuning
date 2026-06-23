<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('invoice_number')->unique();
            $table->text('description');
            $table->decimal('amount_net', 10, 2);
            $table->decimal('vat_amount', 10, 2)->default(0.00);
            $table->decimal('amount_gross', 10, 2);
            $table->enum('type', ['credit_top_up', 'evc_bundle', 'product', 'manual']);
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->enum('status', ['issued', 'paid', 'void'])->default('issued');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
