<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Foto REAL khas Indonesia untuk setiap fasilitas, disesuaikan isi datanya.
 * Sumber: Wikimedia Commons (lisensi bebas), foto produk Tokopedia,
 * situs vendor persewaan Indonesia (Bawu Furniture, Studio Pelangi,
 * NetworkHT), dan blog persewaan tenda (blogspot/Google).
 * File diunduh ke storage lokal agar tidak hotlink & tetap jalan offline.
 */
class FacilityImageSeeder extends Seeder
{
    /** facility_code => [url, caption][] */
    private array $photos = [
        'GDG-001' => [
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/5/57/Gedung_Kesenian_%28Aula%29.jpg/960px-Gedung_Kesenian_%28Aula%29.jpg', 'Suasana dalam aula'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/7/73/Pendopo_Bupati_Sabha_Swagatha_Blambangan%2C_Banyuwangi%2C_Jawa_Timur.jpg/960px-Pendopo_Bupati_Sabha_Swagatha_Blambangan%2C_Banyuwangi%2C_Jawa_Timur.jpg', 'Pendopo kabupaten'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/c/ca/Sasana_Sehati_Keuskupan_Banjarmasin.jpg/960px-Sasana_Sehati_Keuskupan_Banjarmasin.jpg', 'Aula pertemuan besar'],
        ],
        'GDG-002' => [
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/9/9d/Kantor_Ruang_Rapat_Bupati_Dan_Wakil_Bupati_Kab.Kebumen.jpg/960px-Kantor_Ruang_Rapat_Bupati_Dan_Wakil_Bupati_Kab.Kebumen.jpg', 'Ruang rapat bupati'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/7/79/Ruang_Rapat_Gubernur.jpg/960px-Ruang_Rapat_Gubernur.jpg', 'Ruang rapat pimpinan'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/d/d8/Foto_ruang_rapat_di_Novotel_Suites_Yogyakarta_Malioboro.jpg/960px-Foto_ruang_rapat_di_Novotel_Suites_Yogyakarta_Malioboro.jpg', 'Ruang rapat modern'],
        ],
        'GDG-003' => [
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/e/ec/Aula_pertemuan_di_Taman_Gajan_Mada.jpg/960px-Aula_pertemuan_di_Taman_Gajan_Mada.jpg', 'Aula pertemuan kecil'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/f/f0/Baitus_Syukri.jpg/960px-Baitus_Syukri.jpg', 'Ruang pertemuan terbatas'],
        ],
        'GDG-004' => [
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/a/a5/Gedung_Serbaguna_Desa_Karang_Tanjung_Alian_Kebumen_Jateng_Indonesia.jpg/960px-Gedung_Serbaguna_Desa_Karang_Tanjung_Alian_Kebumen_Jateng_Indonesia.jpg', 'Gedung serbaguna tampak depan'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/5/5e/Gedung_Serbaguna_Desa_Lebakwangi%2C_Lebakwangi%2C_Kuningan_-_panoramio.jpg/960px-Gedung_Serbaguna_Desa_Lebakwangi%2C_Lebakwangi%2C_Kuningan_-_panoramio.jpg', 'Gedung serbaguna desa'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/8/8e/Gedung_Serbaguna_Kec.Poncowarno_Kebumen.jpg/960px-Gedung_Serbaguna_Kec.Poncowarno_Kebumen.jpg', 'Gedung serbaguna kecamatan'],
        ],
        'FRN-001' => [
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/2/2b/Monobloc_Chairs_1.jpg/960px-Monobloc_Chairs_1.jpg', 'Jajaran kursi monobloc'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/2/2e/White_Monobloc_chair.jpg/960px-White_Monobloc_chair.jpg', 'Detail kursi plastik'],
        ],
        'FRN-002' => [
            ['https://p16-images-sign-sg.tokopedia-static.net/tos-alisg-i-aphluv4xwc-sg/ad69772cf85844f8bdcd6f0478e601c7~tplv-aphluv4xwc-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348037&x-signature=vJlt2qcqWmtzbEh9OaiRvDb8UoE%3D&x-signature-webp=B93%2F5yCNTbTiSMJzUpLy%2Bq%2Bcz3Y%3D', 'Meja lipat 120x60'],
            ['https://p16-images-common-sign-sg.tokopedia-static.net/tos-maliva-i-o3syd03w52-us/8b95adb125874284aec02a368320bdbf~tplv-o3syd03w52-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348039&x-signature=MSmox%2BQOYUTemEiWPeS0ZuCvlZU%3D&x-signature-webp=71QM5NtYQu0VEhV2ZdUxRTM2SEY%3D', 'Meja lipat portable'],
        ],
        'FRN-003' => [
            ['https://www.bawufurniture.com/wp-content/uploads/2023/11/jual-podium-masjid-model-minimalis-dari-kayu-jati-BF-3402.jpg', 'Podium jati minimalis'],
            ['https://www.bawufurniture.com/wp-content/uploads/2023/11/jual-podium-masjid-model-minimalis-dari-kayu-jati-BF-3402-1.jpg', 'Podium tampak samping'],
        ],
        'PRL-001' => [
            ['https://p16-images-common-sign-sg.tokopedia-static.net/tos-maliva-i-o3syd03w52-us/659e98ec74804410a129f72728fc1407~tplv-o3syd03w52-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348018&x-signature=P0HheHVG71%2BZ%2FlruAfqFkcgAcKI%3D&x-signature-webp=5JOMzk%2FYQtEItDl5TB5HfNAADWM%3D', 'Sarung meja prasmanan'],
            ['https://p16-images-sign-sg.tokopedia-static.net/tos-alisg-i-aphluv4xwc-sg/a60a1e8623ef4603b26d9bdd802933d9~tplv-aphluv4xwc-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348254&x-signature=%2BTYUqxaU%2Fj%2Bg4taB7PddDAIzUFo%3D&x-signature-webp=LiqD%2FLL0MNWdq9%2BhS9ytz4NaHvs%3D', 'Paket sarung meja kursi'],
        ],
        'PRL-002' => [
            ['https://p16-images-sign-sg.tokopedia-static.net/tos-alisg-i-aphluv4xwc-sg/8c53f2b9fddf4f088e8c9f5fa71ec413~tplv-aphluv4xwc-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791347967&x-signature=7kYeoKHliICJZRHiF%2F2lAEDpD5c%3D&x-signature-webp=Sfe14J4sjwhKkxTR44mrxhqtqgk%3D', 'Sarung kursi napoli putih'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/a/a8/DZ6_0468_Spacious_elegantly_lit_banquet_hall_set_up_with_rows_of_white-covered_chairs_facing_a_stage_ready_for_a_large_conference_or_formal_event.jpg/960px-DZ6_0468_Spacious_elegantly_lit_banquet_hall_set_up_with_rows_of_white-covered_chairs_facing_a_stage_ready_for_a_large_conference_or_formal_event.jpg', 'Deretan kursi bersarung'],
        ],
        'PRL-003' => [
            ['https://p16-images-sign-sg.tokopedia-static.net/tos-alisg-i-aphluv4xwc-sg/efac36cf8c384cf981bc25124c9459e7~tplv-aphluv4xwc-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348283&x-signature=hnb7%2FRr52cG1EYnAfGS9vBSewcg%3D&x-signature-webp=%2FAC65B6WIYEXxL1dXgdPX4gHy6M%3D', 'Karpet bludru merah panggung'],
            ['https://p16-images-sign-sg.tokopedia-static.net/tos-alisg-i-aphluv4xwc-sg/ea0e2fcd994946a2a995ed515341e68d~tplv-aphluv4xwc-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348129&x-signature=ABSK8XXYXMh1XkdOZA5%2FXKO1Of4%3D&x-signature-webp=mY4hEUgOXzszgQDF7EZ1NtGtKko%3D', 'Karpet merah pameran wedding'],
        ],
        'ELK-001' => [
            ['https://networkht.com/wp-content/uploads/2023/06/3000watt.jpg', 'Paket sound system hajatan'],
            ['https://networkht.com/wp-content/uploads/2023/06/5000watt.jpg', 'Sound system outdoor'],
        ],
        'ELK-002' => [
            ['https://p16-images-sign-sg.tokopedia-static.net/tos-alisg-i-aphluv4xwc-sg/4da8aca22c6e46f4b7fba74eaa8fd5af~tplv-aphluv4xwc-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348092&x-signature=olA0PoldrjFbt4rYKUHmuuA2%2Bj0%3D&x-signature-webp=2Vsn07kgL%2F1prFhQHu1da5fp8nw%3D', 'Proyektor LCD'],
            ['https://p16-images-sign-sg.tokopedia-static.net/tos-alisg-i-aphluv4xwc-sg/6700e7fee9384ac0b4f1e56aca55be2d~tplv-aphluv4xwc-resize-jpeg:700:0.jpeg?lk3s=0ccea506&x-expires=1791348096&x-signature=AcfE1vVh6XG55DFyrNblSXmMPJ4%3D&x-signature-webp=z18HnXgNuyxp5uQPmznPMs0WM6E%3D', 'Proyektor mini portable'],
        ],
        'ELK-003' => [
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/a/ac/Portable_Air_Con.jpg/960px-Portable_Air_Con.jpg', 'Unit AC portable'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/9/96/Portable_air_conditioner_at_a_school.jpg/960px-Portable_air_conditioner_at_a_school.jpg', 'AC portable terpasang'],
        ],
        'LAN-001' => [
            ['https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjB2meJ4hZgCDYIBmMD0RJqCn7AUS5WKHsAbuIYYpcegXMQEalkMDNS9llL4DZ8_qsOJOcXezJxve8KL_6iSLAVamu3rWm9kEhmBcbEHaH49L5DUlsy6gXBPSxy2B8tUcfgpHfkFCs4WU1t/s1600/49785001_2022083587884871_8337158771444809728_n.jpg', 'Tenda pleton terpasang'],
            ['https://thumb.wikimedia.org/wikipedia/commons/thumb/5/5b/Downtown_Summer_Party_Tent_%284785118339%29.jpg/960px-Downtown_Summer_Party_Tent_%284785118339%29.jpg', 'Tenda acara outdoor'],
        ],
        'LAN-002' => [
            ['https://studiopelangi.id/wp-content/uploads/2025/06/Menyelenggarakan-Acara-Sederhana-dengan-Panggung-Portable.jpg', 'Panggung portable acara'],
            ['https://studiopelangi.id/wp-content/uploads/2024/09/panggung-portable-dan-rigging1-1024x683.jpg', 'Panggung dan rigging'],
        ],
    ];

    public function run(): void
    {
        $count = 0;
        foreach ($this->photos as $code => $list) {
            $facility = Facility::where('code', $code)->first();
            if (! $facility) {
                $this->command->warn("Fasilitas {$code} tidak ditemukan, dilewati.");
                continue;
            }
            if ($this->seedForFacility($facility, $list)) {
                $count++;
            }
        }
        $this->command->info("Foto Indonesia berhasil dipasang untuk {$count} fasilitas.");
    }

    private function seedForFacility(Facility $facility, array $list): bool
    {
        // Unduh dulu semuanya; hanya ganti foto lama bila minimal 1 berhasil.
        // (URL bertanda tangan Tokopedia bisa kedaluwarsa saat seed ulang.)
        $downloaded = [];
        foreach ($list as [$url, $caption]) {
            $binary = $this->download($url);
            if (! $binary) {
                $this->command->warn("Gagal unduh: ".mb_substr($url, 0, 90));
                continue;
            }
            $downloaded[] = [$url, $caption, $binary];
        }

        if (empty($downloaded)) {
            $this->command->warn("{$facility->code}: semua unduhan gagal, foto lama dipertahankan.");
            return false;
        }

        // Hapus foto kelolaan seeder (bukan upload manual admin)
        $old = FacilityImage::where('facility_id', $facility->id)
            ->where(function ($q) {
                $q->where('file_path', 'like', '%dummy-%')
                    ->orWhere('original_name', 'like', 'unsplash-%')
                    ->orWhere('original_name', 'like', 'src-%');
            })->get();
        foreach ($old as $img) {
            Storage::disk('public')->delete($img->file_path);
            $img->delete();
        }

        $i = 0;
        foreach ($downloaded as [$url, $caption, $binary]) {
            $host = parse_url($url, PHP_URL_HOST) ?: 'web';
            $shortHost = str_replace(['www.', '.com', '.id'], '', $host);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $caption));
            $ext = $this->detectExt($binary);
            $filename = "{$facility->code}-{$slug}.{$ext}";
            $filePath = "facilities/{$facility->id}/{$filename}";
            Storage::disk('public')->put($filePath, $binary);

            FacilityImage::updateOrCreate(
                ['facility_id' => $facility->id, 'file_path' => $filePath],
                [
                    'original_name' => "src-{$shortHost}-".substr(md5($url), 0, 8).".{$ext}",
                    'caption' => "{$facility->name} — {$caption}",
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]
            );
            $i++;
        }

        // Tepat 1 primary (query builder, hindari model stale)
        $firstId = FacilityImage::where('facility_id', $facility->id)->orderBy('sort_order')->value('id');
        if ($firstId) {
            FacilityImage::where('facility_id', $facility->id)->update(['is_primary' => false]);
            FacilityImage::where('id', $firstId)->update(['is_primary' => true]);
            return true;
        }

        return false;
    }

    private function download(string $url): ?string
    {
        try {
            $res = Http::timeout(30)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            if (! $res->successful()) {
                return null;
            }
            $type = $res->header('Content-Type', '');
            if (! str_starts_with($type, 'image/')) {
                return null;
            }
            $body = $res->body();
            return strlen($body) > 10240 ? $body : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function detectExt(string $binary): string
    {
        $info = @getimagesizefromstring($binary);
        return match ($info['mime'] ?? '') {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
    }
}
