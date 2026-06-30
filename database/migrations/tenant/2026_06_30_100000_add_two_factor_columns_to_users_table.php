<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_method')->nullable()->after('password'); // 'totp' or 'email'
            $table->text('two_factor_secret')->nullable()->after('two_factor_method');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->string('email_otp_code', 8)->nullable()->after('two_factor_confirmed_at');
            $table->timestamp('email_otp_expires_at')->nullable()->after('email_otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_method',
                'two_factor_secret',
                'two_factor_confirmed_at',
                'email_otp_code',
                'email_otp_expires_at',
            ]);
        });
    }
};
