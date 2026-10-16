<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user penyewa dan customer
        $penyewaUser = User::where('email', 'user@example.com')->first();

        if ($penyewaUser) {
            Customer::updateOrCreate(
                ['user_id' => $penyewaUser->id],
                [
                    'code' => 'CST/2026/0001',
                    'name' => 'Ahmad Budiman',
                    'type' => 'masyarakat',
                    'nik' => '6371010101010001',
                    'address' => 'Jl. Ahmad Yani No. 10',
                    'city' => 'Banjarmasin',
                    'province' => 'Kalimantan Selatan',
                    'phone' => '081234567895',
                    'email' => 'user@example.com',
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'is_active' => true,
                ]
            );
        }

        // Buat beberapa customer demo OPD
        $opdCustomers = [
            [
                'code' => 'CST/2026/0002',
                'name' => 'Kepala Dinas Pendidikan',
                'type' => 'opd',
                'institution_name' => 'Dinas Pendidikan Kab/Kota',
                'nip' => '196501011990031001',
                'address' => 'Jl. Pendidikan No. 1',
                'city' => 'Banjarmasin',
                'phone' => '0511-1234567',
                'email' => 'disdik@pemda.go.id',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_active' => true,
            ],
            [
                'code' => 'CST/2026/0003',
                'name' => 'Kepala Dinas Kesehatan',
                'type' => 'opd',
                'institution_name' => 'Dinas Kesehatan Kab/Kota',
                'nip' => '197001011995031002',
                'address' => 'Jl. Kesehatan No. 5',
                'city' => 'Banjarmasin',
                'phone' => '0511-2345678',
                'email' => 'dinkes@pemda.go.id',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_active' => true,
            ],
        ];

        foreach ($opdCustomers as $customer) {
            Customer::updateOrCreate(['code' => $customer['code']], $customer);
        }

        // Customer contoh tambahan agar daftar & paginasi bermakna
        $extraCustomers = [
            ['code' => 'CST/2026/0004', 'name' => 'Hj. Siti Aminah', 'type' => 'masyarakat', 'nik' => '6371010202020002', 'address' => 'Jl. Pangeran No. 25', 'city' => 'Banjarmasin', 'phone' => '081345678901', 'email' => 'siti.aminah@example.com', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0005', 'name' => 'Budi Santoso', 'type' => 'masyarakat', 'nik' => '6371010303030003', 'address' => 'Jl. Lambung Mangkurat No. 8', 'city' => 'Banjarmasin', 'phone' => '081345678902', 'email' => 'budi.s@example.com', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0006', 'name' => 'Kepala Dinas Sosial', 'type' => 'opd', 'institution_name' => 'Dinas Sosial Kab/Kota', 'nip' => '197502022000032003', 'address' => 'Jl. Sosial No. 3', 'city' => 'Banjarmasin', 'phone' => '0511-3456789', 'email' => 'dinsos@pemda.go.id', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0007', 'name' => 'Kepala Dinas PUPR', 'type' => 'opd', 'institution_name' => 'Dinas PUPR Kab/Kota', 'nip' => '197803032001033004', 'address' => 'Jl. Pembangunan No. 12', 'city' => 'Banjarmasin', 'phone' => '0511-4567890', 'email' => 'pupr@pemda.go.id', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0008', 'name' => 'Camat Banjarmasin Timur', 'type' => 'opd', 'institution_name' => 'Kecamatan Banjarmasin Timur', 'nip' => '198004042002044005', 'address' => 'Jl. Lingkar Dalam No. 1', 'city' => 'Banjarmasin', 'phone' => '0511-5678901', 'email' => 'camtim@pemda.go.id', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0009', 'name' => 'PT Maju Bersama', 'type' => 'perusahaan', 'institution_name' => 'PT Maju Bersama', 'address' => 'Jl. A. Yani Km. 5 No. 100', 'city' => 'Banjarmasin', 'phone' => '0511-6789012', 'email' => 'info@majubersama.co.id', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0010', 'name' => 'CV Berkah Jaya', 'type' => 'perusahaan', 'institution_name' => 'CV Berkah Jaya', 'address' => 'Jl. S. Parman No. 45', 'city' => 'Banjarmasin', 'phone' => '0511-7890123', 'email' => 'berkahjaya@example.com', 'verification_status' => 'pending', 'verified_at' => null],
            ['code' => 'CST/2026/0011', 'name' => 'Karang Taruna Mekar Sari', 'type' => 'organisasi', 'institution_name' => 'Karang Taruna Mekar Sari', 'address' => 'Jl. Melati No. 7', 'city' => 'Banjarmasin', 'phone' => '082345678903', 'email' => 'mekarsari@example.com', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0012', 'name' => 'Majelis Taklim Al-Hidayah', 'type' => 'organisasi', 'institution_name' => 'Majelis Taklim Al-Hidayah', 'address' => 'Jl. Dahlia No. 9', 'city' => 'Banjarmasin', 'phone' => '082345678904', 'email' => 'alhidayah@example.com', 'verification_status' => 'pending', 'verified_at' => null],
            ['code' => 'CST/2026/0013', 'name' => 'Dewi Lestari', 'type' => 'masyarakat', 'nik' => '6371010404040004', 'address' => 'Jl. Anggrek No. 17', 'city' => 'Banjarmasin', 'phone' => '081345678905', 'email' => 'dewi.l@example.com', 'verification_status' => 'pending', 'verified_at' => null],
            ['code' => 'CST/2026/0014', 'name' => 'H. Muhammad Noor', 'type' => 'masyarakat', 'nik' => '6371010505050005', 'address' => 'Jl. Kenanga No. 21', 'city' => 'Banjarmasin', 'phone' => '081345678906', 'email' => 'm.noor@example.com', 'verification_status' => 'verified', 'verified_at' => now()],
            ['code' => 'CST/2026/0015', 'name' => 'SMK Negeri 1 Banjarmasin', 'type' => 'instansi_pemerintah', 'institution_name' => 'SMK Negeri 1 Banjarmasin', 'address' => 'Jl. Veteran No. 33', 'city' => 'Banjarmasin', 'phone' => '0511-8901234', 'email' => 'smkn1@example.com', 'verification_status' => 'verified', 'verified_at' => now()],
        ];

        foreach ($extraCustomers as $customer) {
            Customer::updateOrCreate(
                ['code' => $customer['code']],
                array_merge($customer, ['province' => 'Kalimantan Selatan', 'is_active' => true])
            );
        }
    }
}
