<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Technician role has been consolidated into Tuner: Tuner is now the
        // sole staff worker role alongside Owner. Migrate any existing technician
        // rows first — a MySQL ENUM value cannot be dropped while rows still use it.
        DB::table('users')->where('role', 'technician')->update(['role' => 'tuner']);

        // MySQL stores `role` as a native ENUM, so removing a value requires an
        // explicit column redefinition. SQLite (test suite) has no enum
        // constraint, so the data update above is all that is needed there.
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'tuner', 'dealer_owner', 'dealer_user') NOT NULL DEFAULT 'dealer_owner'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        // Re-add the technician value to the enum. Rows are not reverted — the
        // original technician/tuner split is not recoverable once consolidated.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'technician', 'tuner', 'dealer_owner', 'dealer_user') NOT NULL DEFAULT 'dealer_owner'");
    }
};
