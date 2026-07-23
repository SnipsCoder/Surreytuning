<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'whatsapp_phone_number_id')) {
                $table->string('whatsapp_phone_number_id')->nullable()->after('whatsapp_business_number');
            }
            if (! Schema::hasColumn('settings', 'whatsapp_access_token')) {
                // Encrypted (long ciphertext) — the Meta permanent access token.
                $table->text('whatsapp_access_token')->nullable()->after('whatsapp_phone_number_id');
            }
            if (! Schema::hasColumn('settings', 'whatsapp_template_name')) {
                $table->string('whatsapp_template_name')->nullable()->after('whatsapp_access_token');
            }
            if (! Schema::hasColumn('settings', 'whatsapp_template_language')) {
                $table->string('whatsapp_template_language', 10)->nullable()->default('en_GB')->after('whatsapp_template_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['whatsapp_phone_number_id', 'whatsapp_access_token', 'whatsapp_template_name', 'whatsapp_template_language'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
