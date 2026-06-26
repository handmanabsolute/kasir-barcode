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
                'password' => 'password',
            ],
            [
                'name' => 'Kasir Demo',
                'username' => 'kasir',
                'email' => 'kasir@example.com',
                'password' => 'password',
            ],
            [
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'email' => 'budi@example.com',
                'password' => 'password',
            ],
            [
                'name' => 'Siti Rahayu',
                'username' => 'siti',
                'email' => 'siti@example.com',
                'password' => 'password',
            ],
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
