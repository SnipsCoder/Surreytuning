<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtc_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_request_id')->constrained('file_requests')->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtc_codes');
    }
};
