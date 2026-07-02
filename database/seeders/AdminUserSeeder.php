<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'admin1234';

        $user = User::firstOrCreate(
            ['email' => 'admin@praxxys.ph'],
            [
                'name' => 'PRAXXYS Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Admin created: {$user->email} / {$password}");
        $this->command->warn("Change this password immediately after first login.");
    }
}
