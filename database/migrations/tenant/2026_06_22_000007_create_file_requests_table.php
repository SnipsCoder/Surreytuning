<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('request_number')->unique();
            $table->foreignId('dealer_id')->constrained('dealers');
            $table->foreignId('submitted_by_user_id')->constrained('users');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('file_type', ['ecu', 'tcu', 'other'])->default('ecu');
            $table->enum('status', ['pending', 'progress', 'responded', 'on_hold', 'requires_support', 'returned', 'closed', 'void'])->default('pending');
            $table->string('registration', 20)->nullable();
            $table->string('vin_number', 50)->nullable();
            $table->string('make', 100);
            $table->string('model', 100);
            $table->string('engine', 50);
            $table->string('engine_code', 50)->nullable();
            $table->year('year');
            $table->enum('fuel', ['petrol', 'diesel', 'electric', 'hybrid']);
            $table->string('euro_status', 10)->nullable();
            $table->enum('transmission', ['manual', 'semi_auto', 'automatic']);
            $table->decimal('bhp_before', 8, 2)->nullable();
            $table->decimal('torque_before_nm', 8, 2)->nullable();
            $table->string('ecu_model_no', 100)->nullable();
            $table->foreignId('file_stage_id')->nullable()->constrained('file_stages')->nullOnDelete();
            $table->foreignId('tool_id')->nullable()->constrained('tuning_tools')->nullOnDelete();
            $table->text('client_notes')->nullable();
            $table->decimal('price_net', 10, 2)->default(0.00);
            $table->decimal('vat_amount', 10, 2)->default(0.00);
            $table->decimal('price_gross', 10, 2)->default(0.00);
            $table->boolean('is_charged')->default(false);
            $table->timestamp('client_downloaded_at')->nullable();
            $table->string('void_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_requests');
    }
};
