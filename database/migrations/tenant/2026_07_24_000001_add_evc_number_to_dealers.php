<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            if (! Schema::hasColumn('dealers', 'evc_number')) {
                // The dealer's own EVC WinOLS user/customer number, used to
                // allocate purchased EVC credits to their real EVC account.
                $table->string('evc_number', 50)->nullable()->after('evc_credit_balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            if (Schema::hasColumn('dealers', 'evc_number')) {
                $table->dropColumn('evc_number');
            }
        });
    }
};
