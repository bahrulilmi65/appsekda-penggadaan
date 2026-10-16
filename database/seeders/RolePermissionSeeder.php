<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'view dashboard',
            // Facilities
            'view facilities', 'create facilities', 'edit facilities', 'delete facilities',
            // Categories
            'view categories', 'create categories', 'edit categories', 'delete categories',
            // Inventory
            'view inventory', 'manage inventory', 'adjust inventory',
            // Customers
            'view customers', 'create customers', 'edit customers', 'delete customers', 'verify customers',
            // Bookings
            'view bookings', 'create bookings', 'edit bookings', 'delete bookings',
            'approve bookings', 'reject bookings', 'cancel bookings',
            // Payments
            'view payments', 'verify payments', 'manage payments',
            // Deliveries
            'view deliveries', 'manage deliveries',
            // Returns
            'view returns', 'manage returns',
            // Laundry
            'view laundry', 'manage laundry',
            // Damages
            'view damages', 'manage damages',
            // Reports
            'view reports', 'export reports',
            // Settings
            'view settings', 'manage settings',
            // Users
            'view users', 'create users', 'edit users', 'delete users',
            // Logs
            'view logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Buat roles
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $bendahara = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);
        $petugas = Role::firstOrCreate(['name' => 'petugas', 'guard_name' => 'web']);
        $pimpinan = Role::firstOrCreate(['name' => 'pimpinan', 'guard_name' => 'web']);
        $penyewa = Role::firstOrCreate(['name' => 'penyewa', 'guard_name' => 'web']);

        // Superadmin: semua permission
        $superadmin->syncPermissions(Permission::all());

        // Admin: hampir semua kecuali settings dan users
        $admin->syncPermissions(Permission::whereNotIn('name', [
            'manage settings', 'view users', 'create users', 'edit users', 'delete users', 'view logs',
        ])->get());

        // Bendahara: keuangan
        $bendahara->syncPermissions([
            'view dashboard', 'view bookings', 'view payments', 'verify payments',
            'manage payments', 'view reports', 'export reports', 'view customers',
        ]);

        // Petugas: operasional
        $petugas->syncPermissions([
            'view dashboard', 'view bookings', 'view inventory', 'manage inventory',
            'view deliveries', 'manage deliveries', 'view returns', 'manage returns',
            'view laundry', 'manage laundry', 'view damages', 'manage damages',
        ]);

        // Pimpinan: baca saja
        $pimpinan->syncPermissions([
            'view dashboard', 'view bookings', 'view reports', 'export reports',
            'view payments', 'view facilities', 'view customers', 'view inventory',
        ]);

        // Penyewa: booking sendiri
        $penyewa->syncPermissions([
            'view facilities', 'create bookings', 'view bookings',
        ]);
    }
}
