<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin',
                'phone' => '1234567892',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
                'avatar' => null,
            ]
        );

        $admin->assignRole('admin');

        // Visitor
        $visitor = User::updateOrCreate(
            ['email' => 'visitor@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Visitor',
                'phone' => '1234567891',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'visitor',
                'avatar' => null,
            ]
        );

        $visitor->assignRole('visitor');

        // Finance
        $finance = User::updateOrCreate(
            ['email' => 'finance@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Finance',
                'phone' => '1234567890',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'finance',
                'avatar' => null,
            ]
        );

        $finance->assignRole('finance');
    }
}
