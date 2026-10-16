<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'GDG', 'name' => 'Gedung', 'icon' => 'fa-building', 'color' => '#3498db', 'sort_order' => 1, 'description' => 'Gedung dan ruangan untuk kegiatan'],
            ['code' => 'FRN', 'name' => 'Furniture', 'icon' => 'fa-couch', 'color' => '#e67e22', 'sort_order' => 2, 'description' => 'Kursi, meja, dan furnitur lainnya'],
            ['code' => 'PRL', 'name' => 'Perlengkapan', 'icon' => 'fa-box', 'color' => '#2ecc71', 'sort_order' => 3, 'description' => 'Sarung meja, sarung kursi, karpet, dll'],
            ['code' => 'ELK', 'name' => 'Elektronik', 'icon' => 'fa-plug', 'color' => '#9b59b6', 'sort_order' => 4, 'description' => 'Proyektor, sound system, AC portable'],
            ['code' => 'LAN', 'name' => 'Panggung & Tenda', 'icon' => 'fa-tent', 'color' => '#e74c3c', 'sort_order' => 5, 'description' => 'Panggung, tenda, backdrop'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['code' => $cat['code']], array_merge($cat, ['is_active' => true]));
        }
    }
}
