<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL stores `role` as a native ENUM, so a new value must be added
        // explicitly. SQLite (used in the test suite) stores it as a plain
        // string with no enum constraint, so there is nothing to alter there.
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'technician', 'tuner', 'dealer_owner', 'dealer_user') NOT NULL DEFAULT 'dealer_owner'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'technician', 'dealer_owner', 'dealer_user') NOT NULL DEFAULT 'dealer_owner'");
    }
};
