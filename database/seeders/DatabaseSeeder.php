<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            SettingsSeeder::class,
            CategorySeeder::class,
            FacilitySeeder::class,
            FacilityImageSeeder::class,
            CustomerSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
