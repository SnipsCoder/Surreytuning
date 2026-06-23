<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_stage_id')->nullable()->constrained('file_stages')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_net', 10, 2)->default(0.00);
            $table->boolean('vat_applicable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_options');
    }
};
