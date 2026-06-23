<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_request_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_request_id')->constrained('file_requests')->cascadeOnDelete();
            $table->foreignId('file_option_id')->constrained('file_options')->cascadeOnDelete();
            $table->decimal('price_net', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_request_options');
    }
};
