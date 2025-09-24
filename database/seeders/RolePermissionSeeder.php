<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Permissions
        Permission::firstOrCreate(['name' => 'view dashboard']);
        Permission::firstOrCreate(['name' => 'manage users']);

        // Roles
        $admin   = Role::firstOrCreate(['name' => 'admin']);
        $finance_batik  = Role::firstOrCreate(['name' => 'finance_batik']);
        $finance_tourism  = Role::firstOrCreate(['name' => 'finance_tourism']);
        $visitor = Role::firstOrCreate(['name' => 'visitor']);

        // Grant permissions
        $admin->givePermissionTo(['view dashboard', 'manage users']);
        $finance_batik->givePermissionTo(['view dashboard']);
        $finance_tourism->givePermissionTo(['view dashboard']);
        $visitor->givePermissionTo(['view dashboard']);
    }
}
