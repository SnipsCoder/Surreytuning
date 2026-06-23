<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_request_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_request_id')->constrained('file_requests')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users');
            $table->enum('type', ['message', 'system', 'internal_note', 'charge_event', 'credit_event'])->default('message');
            $table->text('body')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_request_messages');
    }
};
