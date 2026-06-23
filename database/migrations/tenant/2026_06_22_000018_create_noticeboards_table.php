<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noticeboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->string('title');
            $table->text('body');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->date('show_from')->nullable();
            $table->date('show_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticeboards');
    }
};
