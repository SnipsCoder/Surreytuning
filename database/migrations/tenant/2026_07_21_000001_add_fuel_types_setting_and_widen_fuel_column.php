<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'fuel_types')) {
                $table->json('fuel_types')->nullable()->after('vat_rate');
            }
        });

        // Seed the default managed list onto the single settings row so the
        // upload form has options immediately after deploy.
        DB::table('settings')->whereNull('fuel_types')->update([
            'fuel_types' => json_encode(['Petrol', 'Diesel', 'Electric', 'Hybrid']),
        ]);

        // Fuel is now an owner-managed free-text list, so the column can no
        // longer be a fixed ENUM. Widen it to VARCHAR, preserving existing rows.
        DB::statement("ALTER TABLE file_requests MODIFY COLUMN fuel VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        // Best-effort revert: rows with custom fuel values that are not in the
        // original enum set will be coerced by MySQL, which is acceptable for a
        // rollback.
        DB::statement("ALTER TABLE file_requests MODIFY COLUMN fuel ENUM('petrol', 'diesel') NOT NULL");

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'fuel_types')) {
                $table->dropColumn('fuel_types');
            }
        });
    }
};
