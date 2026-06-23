<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'first_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('first_name');
            $table->foreignId('dealer_id')->nullable()->after('id')->constrained('dealers')->nullOnDelete();
            $table->enum('role', ['owner', 'technician', 'dealer_owner', 'dealer_user'])->default('dealer_owner')->after('password');
            $table->boolean('is_primary_contact')->default(false)->after('role');
            $table->boolean('can_view_pricing')->default(true)->after('is_primary_contact');
            $table->string('avatar')->nullable()->after('can_view_pricing');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('avatar');
            $table->boolean('notify_comments_email')->default(true)->after('status');
            $table->boolean('notify_file_requests_email')->default(true)->after('notify_comments_email');
            $table->boolean('notify_file_requests_sms')->default(false)->after('notify_file_requests_email');
            $table->string('whatsapp_number', 20)->nullable()->after('notify_file_requests_sms');
            $table->timestamp('last_login_at')->nullable()->after('whatsapp_number');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dealer_id');
            $table->dropColumn([
                'last_name', 'role', 'is_primary_contact', 'can_view_pricing',
                'avatar', 'status', 'notify_comments_email', 'notify_file_requests_email',
                'notify_file_requests_sms', 'whatsapp_number', 'last_login_at', 'deleted_at',
            ]);
            $table->renameColumn('first_name', 'name');
        });
    }
};
