<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_request_id')->constrained('file_requests')->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('file_request_messages')->nullOnDelete();
            $table->foreignId('uploader_user_id')->constrained('users');
            $table->enum('attachment_type', ['original', 'returned', 'supporting', 'certificate', 'ini'])->default('original');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('mime_type', 100);
            $table->timestamp('first_downloaded_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_request_attachments');
    }
};
