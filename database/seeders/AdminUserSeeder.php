<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@surreytuningservices.co.uk'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('ChangeMe123!'),
                'role' => 'owner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
