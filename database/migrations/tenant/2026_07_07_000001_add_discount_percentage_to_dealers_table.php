<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            // Per-dealer discount applied to pricing offered to this dealer.
            // 0.00 = full price, 100.00 = free.
            $table->decimal('discount_percentage', 5, 2)->default(0.00)->after('evc_credit_balance');
        });
    }

    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
        });
    }
};
