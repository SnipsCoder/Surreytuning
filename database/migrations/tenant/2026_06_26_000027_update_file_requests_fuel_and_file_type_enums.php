<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE file_requests MODIFY COLUMN file_type ENUM('ecu', 'tcu', 'adblue', 'other') NOT NULL DEFAULT 'ecu'");
            DB::statement("ALTER TABLE file_requests MODIFY COLUMN fuel ENUM('petrol', 'diesel') NOT NULL");

            return;
        }

        // SQLite enforces enum() via a CHECK constraint that can't be altered in place,
        // so the column has to be dropped and recreated as a plain string.
        Schema::table('file_requests', function (Blueprint $table) {
            $table->dropColumn(['file_type', 'fuel']);
        });

        Schema::table('file_requests', function (Blueprint $table) {
            $table->string('file_type')->default('ecu')->after('bhp_before');
            $table->string('fuel')->after('engine_code');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE file_requests MODIFY COLUMN fuel ENUM('petrol', 'diesel', 'electric', 'hybrid') NOT NULL");
            DB::statement("ALTER TABLE file_requests MODIFY COLUMN file_type ENUM('ecu', 'tcu', 'other') NOT NULL DEFAULT 'ecu'");

            return;
        }

        Schema::table('file_requests', function (Blueprint $table) {
            $table->dropColumn(['file_type', 'fuel']);
        });

        Schema::table('file_requests', function (Blueprint $table) {
            $table->enum('file_type', ['ecu', 'tcu', 'other'])->default('ecu')->after('bhp_before');
            $table->enum('fuel', ['petrol', 'diesel', 'electric', 'hybrid'])->after('engine_code');
        });
    }
};
