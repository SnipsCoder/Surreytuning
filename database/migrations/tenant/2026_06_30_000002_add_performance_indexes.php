<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_requests', function (Blueprint $table) {
            $table->index(['dealer_id', 'status']);
            $table->index('status');
            $table->index('request_number');
        });

        Schema::table('file_credit_transactions', function (Blueprint $table) {
            $table->index(['dealer_id', 'created_at']);
        });

        Schema::table('evc_credit_transactions', function (Blueprint $table) {
            $table->index(['dealer_id', 'created_at']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['dealer_id', 'status']);
            $table->index('invoice_number');
        });

        Schema::table('file_request_messages', function (Blueprint $table) {
            $table->index(['file_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('file_requests', function (Blueprint $table) {
            $table->dropIndex(['dealer_id', 'status']);
            $table->dropIndex(['status']);
            $table->dropIndex(['request_number']);
        });

        Schema::table('file_credit_transactions', function (Blueprint $table) {
            $table->dropIndex(['dealer_id', 'created_at']);
        });

        Schema::table('evc_credit_transactions', function (Blueprint $table) {
            $table->dropIndex(['dealer_id', 'created_at']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['dealer_id', 'status']);
            $table->dropIndex(['invoice_number']);
        });

        Schema::table('file_request_messages', function (Blueprint $table) {
            $table->dropIndex(['file_request_id', 'created_at']);
        });
    }
};
