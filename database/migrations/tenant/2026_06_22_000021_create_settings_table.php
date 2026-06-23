<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('invoice_address')->nullable();
            $table->text('returns_address')->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->decimal('vat_rate', 5, 2)->default(20.00);
            $table->string('company_number', 50)->nullable();
            $table->string('bcc_invoice_email')->nullable();
            $table->unsignedInteger('invoice_start_number')->default(10000);
            $table->string('invoice_reference_prefix', 20)->default('INV');
            $table->string('logo_light')->nullable();
            $table->string('logo_dark')->nullable();
            $table->string('login_background')->nullable();
            $table->string('theme_colour', 7)->default('#e63012');
            $table->boolean('dealer_auto_onboard')->default(false);
            $table->text('terms_and_conditions')->nullable();
            $table->string('stripe_public_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->string('evc_account_number', 50)->nullable();
            $table->string('evc_password')->nullable();
            $table->string('whatsapp_business_number', 20)->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
