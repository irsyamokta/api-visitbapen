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
                'password' => Hash::make('@Visitbapen53195!'),
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

        // Finance Batik
        $finance_batik = User::updateOrCreate(
            ['email' => 'financebatik@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Finance Batik',
                'phone' => '1234567890',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'finance_batik',
                'avatar' => null,
            ]
        );

        $finance_batik->assignRole('finance_batik');

        // Finance Tourism
        $finance_tourism = User::updateOrCreate(
            ['email' => 'financetourism@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Finance Tourism',
                'phone' => '1234567897',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'finance_tourism',
                'avatar' => null,
            ]
        );

        $finance_tourism->assignRole('finance_tourism');

        // Admin Batik
        $admin_batik = User::updateOrCreate(
            ['email' => 'adminbatik@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin Batik',
                'phone' => '1234567789',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'admin_batik',
                'avatar' => null,
            ]
        );

        $admin_batik->assignRole('admin_batik');

        // Admin Tourism
        $admin_tourism = User::updateOrCreate(
            ['email' => 'admintourism@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin Tourism',
                'phone' => '1234567678',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'admin_tourism',
                'avatar' => null,
            ]
        );

        $admin_tourism->assignRole('admin_tourism');
    }
}
