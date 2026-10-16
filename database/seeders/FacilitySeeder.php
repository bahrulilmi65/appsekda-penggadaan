<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Facility;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $gdg = Category::where('code', 'GDG')->first();
        $frn = Category::where('code', 'FRN')->first();
        $prl = Category::where('code', 'PRL')->first();
        $elk = Category::where('code', 'ELK')->first();
        $lan = Category::where('code', 'LAN')->first();

        $facilities = [
            // Gedung
            [
                'category_id' => $gdg->id,
                'code' => 'GDG-001',
                'name' => 'Aula Gedung Sanggam',
                'description' => 'Aula utama Sekretariat Daerah dengan kapasitas besar, cocok untuk acara besar, seminar, dan rapat pleno.',
                'type' => 'building',
                'unit' => 'unit',
                'total_stock' => 1,
                'price' => 2500000,
                'price_type' => 'per_day',
                'capacity' => 500,
                'location' => 'Gedung Utama Sekretariat Daerah',
                'floor' => 'Lantai 1',
                'facilities_list' => "AC Central\nSound System\nLayar Proyektor\nKursi\nMeja VIP\nPanggung",
                'is_bookable' => true,
                'requires_approval' => true,
                'is_active' => true,
                'min_booking_hours' => 4,
            ],
            [
                'category_id' => $gdg->id,
                'code' => 'GDG-002',
                'name' => 'Ruang Rapat Utama',
                'description' => 'Ruang rapat utama dengan fasilitas lengkap untuk meeting tingkat tinggi.',
                'type' => 'building',
                'unit' => 'unit',
                'total_stock' => 1,
                'price' => 1500000,
                'price_type' => 'per_day',
                'capacity' => 100,
                'location' => 'Gedung Utama Sekretariat Daerah',
                'floor' => 'Lantai 2',
                'facilities_list' => "AC\nSound System\nLCD Projector\nWhiteboard\nKursi\nMeja",
                'is_bookable' => true,
                'requires_approval' => true,
                'is_active' => true,
                'min_booking_hours' => 2,
            ],
            [
                'category_id' => $gdg->id,
                'code' => 'GDG-003',
                'name' => 'Ruang Rapat Kecil',
                'description' => 'Ruang rapat kecil untuk diskusi tim atau meeting internal.',
                'type' => 'building',
                'unit' => 'unit',
                'total_stock' => 1,
                'price' => 750000,
                'price_type' => 'per_day',
                'capacity' => 30,
                'location' => 'Gedung Sayap Barat Sekretariat Daerah',
                'floor' => 'Lantai 2',
                'facilities_list' => "AC\nLCD Projector\nWhiteboard\nKursi\nMeja",
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
                'min_booking_hours' => 1,
            ],
            [
                'category_id' => $gdg->id,
                'code' => 'GDG-004',
                'name' => 'Gedung Serbaguna',
                'description' => 'Gedung serbaguna untuk berbagai acara, pameran, dan kegiatan masyarakat.',
                'type' => 'building',
                'unit' => 'unit',
                'total_stock' => 1,
                'price' => 3000000,
                'price_type' => 'per_day',
                'capacity' => 800,
                'location' => 'Kompleks Sekretariat Daerah',
                'floor' => 'Lantai 1',
                'facilities_list' => "AC Portable\nParkir Luas\nToilet\nMusala",
                'is_bookable' => true,
                'requires_approval' => true,
                'is_active' => true,
                'min_booking_hours' => 4,
            ],

            // Furniture
            [
                'category_id' => $frn->id,
                'code' => 'FRN-001',
                'name' => 'Kursi Lipat',
                'description' => 'Kursi lipat standar untuk kegiatan indoor/outdoor.',
                'type' => 'asset',
                'unit' => 'pcs',
                'total_stock' => 1000,
                'minimum_stock' => 50,
                'price' => 2000,
                'price_type' => 'per_item',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'category_id' => $frn->id,
                'code' => 'FRN-002',
                'name' => 'Meja Lipat',
                'description' => 'Meja lipat ukuran 60x120 cm.',
                'type' => 'asset',
                'unit' => 'pcs',
                'total_stock' => 200,
                'minimum_stock' => 10,
                'price' => 5000,
                'price_type' => 'per_item',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'category_id' => $frn->id,
                'code' => 'FRN-003',
                'name' => 'Podium',
                'description' => 'Podium untuk pembicara/moderator.',
                'type' => 'asset',
                'unit' => 'unit',
                'total_stock' => 5,
                'price' => 50000,
                'price_type' => 'per_day',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],

            // Perlengkapan
            [
                'category_id' => $prl->id,
                'code' => 'PRL-001',
                'name' => 'Sarung Meja',
                'description' => 'Sarung meja putih standar, ukuran 60x120 cm.',
                'type' => 'asset',
                'unit' => 'pcs',
                'total_stock' => 500,
                'minimum_stock' => 50,
                'price' => 5000,
                'price_type' => 'per_item',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'category_id' => $prl->id,
                'code' => 'PRL-002',
                'name' => 'Sarung Kursi',
                'description' => 'Sarung kursi putih standar.',
                'type' => 'asset',
                'unit' => 'pcs',
                'total_stock' => 1000,
                'minimum_stock' => 100,
                'price' => 3000,
                'price_type' => 'per_item',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'category_id' => $prl->id,
                'code' => 'PRL-003',
                'name' => 'Karpet',
                'description' => 'Karpet merah untuk acara formal, lebar 2 meter.',
                'type' => 'asset',
                'unit' => 'meter',
                'total_stock' => 100,
                'price' => 10000,
                'price_type' => 'per_item',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],

            // Elektronik
            [
                'category_id' => $elk->id,
                'code' => 'ELK-001',
                'name' => 'Sound System',
                'description' => 'Paket sound system lengkap: speaker aktif, mixer, mikrofon, stand.',
                'type' => 'equipment',
                'unit' => 'set',
                'total_stock' => 5,
                'price' => 500000,
                'price_type' => 'per_day',
                'is_bookable' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'category_id' => $elk->id,
                'code' => 'ELK-002',
                'name' => 'LCD Projector',
                'description' => 'Proyektor 3000 lumen, resolusi Full HD.',
                'type' => 'equipment',
                'unit' => 'unit',
                'total_stock' => 8,
                'price' => 300000,
                'price_type' => 'per_day',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'category_id' => $elk->id,
                'code' => 'ELK-003',
                'name' => 'AC Portable',
                'description' => 'AC portable 1 PK untuk pendingin ruangan tambahan.',
                'type' => 'equipment',
                'unit' => 'unit',
                'total_stock' => 10,
                'price' => 200000,
                'price_type' => 'per_day',
                'is_bookable' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],

            // Panggung & Tenda
            [
                'category_id' => $lan->id,
                'code' => 'LAN-001',
                'name' => 'Tenda Pleton',
                'description' => 'Tenda pleton ukuran 4x8 meter, cocok untuk outdoor.',
                'type' => 'asset',
                'unit' => 'unit',
                'total_stock' => 20,
                'price' => 150000,
                'price_type' => 'per_day',
                'is_bookable' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'category_id' => $lan->id,
                'code' => 'LAN-002',
                'name' => 'Panggung Portable',
                'description' => 'Panggung modular, ukuran dapat disesuaikan, tinggi 60 cm.',
                'type' => 'equipment',
                'unit' => 'set',
                'total_stock' => 3,
                'price' => 1000000,
                'price_type' => 'per_day',
                'is_bookable' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($facilities as $data) {
            $facility = Facility::updateOrCreate(['code' => $data['code']], $data);

            // Update inventory jika sudah ada
            $inv = Inventory::where('facility_id', $facility->id)->first();
            if ($inv) {
                $inv->qty_total = $data['total_stock'];
                $inv->qty_available = $data['total_stock'];
                $inv->save();
            }
        }
    }
}
