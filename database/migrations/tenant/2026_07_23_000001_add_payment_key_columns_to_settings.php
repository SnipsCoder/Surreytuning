<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Encrypted secrets are much longer than the plaintext — widen the
            // existing secret columns to TEXT so the ciphertext fits.
            $table->text('stripe_secret_key')->nullable()->change();
            $table->text('evc_password')->nullable()->change();

            if (! Schema::hasColumn('settings', 'stripe_webhook_secret')) {
                $table->text('stripe_webhook_secret')->nullable()->after('stripe_secret_key');
            }

            if (! Schema::hasColumn('settings', 'paypal_client_id')) {
                $table->string('paypal_client_id')->nullable()->after('evc_password');
            }

            if (! Schema::hasColumn('settings', 'paypal_secret')) {
                $table->text('paypal_secret')->nullable()->after('paypal_client_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['stripe_webhook_secret', 'paypal_client_id', 'paypal_secret'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }

            $table->string('stripe_secret_key')->nullable()->change();
            $table->string('evc_password')->nullable()->change();
        });
    }
};
