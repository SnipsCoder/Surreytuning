<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_net', 10, 2);
            $table->boolean('vat_applicable')->default(false);
            $table->enum('payment_type', ['slave_credits', 'direct_payment', 'both'])->default('both');
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
