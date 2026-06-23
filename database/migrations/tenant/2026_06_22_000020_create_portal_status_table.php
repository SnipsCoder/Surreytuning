<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_status', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['available', 'busy', 'delayed', 'support_only', 'files_only', 'closed', 'noticeboard'])->default('available');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_status');
    }
};
