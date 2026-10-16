<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'app_name', 'value' => 'SIPF Sekretariat Daerah', 'group' => 'general', 'type' => 'string', 'label' => 'Nama Aplikasi'],
            ['key' => 'organization_name', 'value' => 'Sekretariat Daerah Kabupaten/Kota', 'group' => 'general', 'type' => 'string', 'label' => 'Nama Organisasi'],
            ['key' => 'organization_address', 'value' => 'Jl. Ahmad Yani No. 1, Kota', 'group' => 'general', 'type' => 'string', 'label' => 'Alamat'],
            ['key' => 'organization_phone', 'value' => '0511-XXXXXX', 'group' => 'general', 'type' => 'string', 'label' => 'Telepon'],
            ['key' => 'organization_email', 'value' => 'sekda@pemda.go.id', 'group' => 'general', 'type' => 'string', 'label' => 'Email'],
            ['key' => 'logo', 'value' => '', 'group' => 'general', 'type' => 'string', 'label' => 'Logo'],

            // Booking
            ['key' => 'booking_hold_minutes', 'value' => '30', 'group' => 'booking', 'type' => 'integer', 'label' => 'Waktu Hold Booking (menit)'],
            ['key' => 'booking_advance_days', 'value' => '2', 'group' => 'booking', 'type' => 'integer', 'label' => 'Minimal Hari Pemesanan Sebelumnya'],
            ['key' => 'auto_approve', 'value' => '0', 'group' => 'booking', 'type' => 'boolean', 'label' => 'Auto Approve Booking'],
            ['key' => 'payment_deadline_days', 'value' => '3', 'group' => 'booking', 'type' => 'integer', 'label' => 'Batas Pembayaran (hari)'],

            // Bank
            ['key' => 'bank_name', 'value' => 'Bank BPD', 'group' => 'payment', 'type' => 'string', 'label' => 'Nama Bank'],
            ['key' => 'bank_account_number', 'value' => '1234567890', 'group' => 'payment', 'type' => 'string', 'label' => 'Nomor Rekening'],
            ['key' => 'bank_account_name', 'value' => 'Kas Daerah Sekretariat Daerah', 'group' => 'payment', 'type' => 'string', 'label' => 'Atas Nama'],

            // Invoice
            ['key' => 'invoice_prefix', 'value' => 'INV', 'group' => 'invoice', 'type' => 'string', 'label' => 'Prefix Invoice'],
            ['key' => 'invoice_footer', 'value' => 'Terima kasih atas kepercayaan Anda.', 'group' => 'invoice', 'type' => 'string', 'label' => 'Footer Invoice'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
