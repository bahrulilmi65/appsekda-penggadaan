<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@sekda.go.id',
                'password' => Hash::make('password123'),
                'phone' => '081234567890',
                'is_active' => true,
                'role' => 'superadmin',
            ],
            [
                'name' => 'Admin Sekda',
                'email' => 'admin@sekda.go.id',
                'password' => Hash::make('password123'),
                'phone' => '081234567891',
                'is_active' => true,
                'role' => 'admin',
            ],
            [
                'name' => 'Bendahara Sekda',
                'email' => 'bendahara@sekda.go.id',
                'password' => Hash::make('password123'),
                'phone' => '081234567892',
                'is_active' => true,
                'role' => 'bendahara',
            ],
            [
                'name' => 'Petugas Lapangan',
                'email' => 'petugas@sekda.go.id',
                'password' => Hash::make('password123'),
                'phone' => '081234567893',
                'is_active' => true,
                'role' => 'petugas',
            ],
            [
                'name' => 'Sekretaris Daerah',
                'email' => 'pimpinan@sekda.go.id',
                'password' => Hash::make('password123'),
                'phone' => '081234567894',
                'is_active' => true,
                'role' => 'pimpinan',
            ],
            [
                'name' => 'Ahmad Budiman',
                'email' => 'user@example.com',
                'password' => Hash::make('password123'),
                'phone' => '081234567895',
                'is_active' => true,
                'role' => 'penyewa',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(['email' => $userData['email']], $userData);
            $user->syncRoles([$role]);
        }
    }
}
