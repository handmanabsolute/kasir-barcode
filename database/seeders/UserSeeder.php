<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed default application users.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Kasir',
                'username' => 'admin',
                'email' => 'admin@kasir.local',
                'password' => 'admin123',
            ]
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'password' => Hash::make($user['password']),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
