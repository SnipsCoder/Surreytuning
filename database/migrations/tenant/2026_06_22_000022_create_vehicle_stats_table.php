<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_stats', function (Blueprint $table) {
            $table->id();
            $table->string('make', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year_from');
            $table->unsignedSmallInteger('year_to');
            $table->string('engine', 50);
            $table->enum('fuel', ['petrol', 'diesel', 'electric', 'hybrid']);
            $table->decimal('bhp_before', 8, 2);
            $table->decimal('bhp_after', 8, 2);
            $table->decimal('torque_before_nm', 8, 2);
            $table->decimal('torque_after_nm', 8, 2);
            $table->unsignedTinyInteger('stage');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_stats');
    }
};
