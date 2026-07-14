<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_stats', function (Blueprint $table) {
            $table->string('generation', 100)->nullable()->after('year_to');
            $table->unsignedSmallInteger('year_from')->nullable()->change();
            $table->unsignedSmallInteger('year_to')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_stats', function (Blueprint $table) {
            $table->dropColumn('generation');
            $table->unsignedSmallInteger('year_from')->nullable(false)->change();
            $table->unsignedSmallInteger('year_to')->nullable(false)->change();
        });
    }
};
