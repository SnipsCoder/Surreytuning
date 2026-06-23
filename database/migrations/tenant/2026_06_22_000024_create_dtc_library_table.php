<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtc_library', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->index();
            $table->text('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtc_library');
    }
};
