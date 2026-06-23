<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bosch_ecus', function (Blueprint $table) {
            $table->id();
            $table->string('manufacturer_number', 50)->index();
            $table->string('model', 100);
            $table->string('car_producer');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bosch_ecus');
    }
};
