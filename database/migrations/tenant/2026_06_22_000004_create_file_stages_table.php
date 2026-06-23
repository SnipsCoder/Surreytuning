<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('vehicle_type', ['all', 'car', 'van', 'bike', 'other'])->default('all');
            $table->decimal('price_net', 10, 2)->default(0.00);
            $table->boolean('vat_applicable')->default(false);
            $table->unsignedSmallInteger('turnaround_hours')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_stages');
    }
};
