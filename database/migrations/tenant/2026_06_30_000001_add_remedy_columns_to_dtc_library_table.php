<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dtc_library', function (Blueprint $table) {
            $table->text('possible_causes')->nullable()->after('description');
            $table->text('possible_remedies')->nullable()->after('possible_causes');
            $table->string('severity_estimate', 50)->nullable()->after('possible_remedies');
        });
    }

    public function down(): void
    {
        Schema::table('dtc_library', function (Blueprint $table) {
            $table->dropColumn(['possible_causes', 'possible_remedies', 'severity_estimate']);
        });
    }
};
