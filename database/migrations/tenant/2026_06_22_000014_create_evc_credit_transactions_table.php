<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evc_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('winols_bundle_id')->nullable()->constrained('winols_bundles')->nullOnDelete();
            $table->enum('type', ['purchase', 'deduction', 'manual_credit', 'refund']);
            $table->decimal('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->decimal('balance_after', 10, 2);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evc_credit_transactions');
    }
};
