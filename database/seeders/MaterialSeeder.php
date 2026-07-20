<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;
use App\Models\Family;
use Illuminate\Support\Facades\Http;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $otherFamily = Family::firstOrCreate(['name' => 'Other']);
        $familyMap = Family::all()->keyBy('name');

        // Fetch seluruh data material sekaligus
        $url = 'https://genshin-db-api.vercel.app/api/v5/materials?query=names&matchCategories=true&verboseCategories=true&resultLanguage=Indonesia';
        $response = Http::get($url);

        if (!$response->successful()) {
            $this->command->error("Gagal mengambil data dari API.");
            return;
        }

        $items = $response->json();
        if (!is_array($items)) {
            $this->command->error("Format data API tidak sesuai.");
            return;
        }

        $this->command->info("Memproses " . count($items) . " material...");

        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['name'])) continue;

            $itemName = $item['name'];
            $itemDesc = $item['description'] ?? '';
            $typeText = $item['typeText'] ?? '';

            // Cari family yang cocok
            $matchedFamilyId = $otherFamily->id;

            foreach ($familyMap as $famName => $famObj) {
                if ($famName === 'Other') continue;

                // Ambil kata kunci pencarian
                $cleanFamName = trim(preg_replace('/\s*\(.*?\)/', '', $famName));

                if (stripos($itemName, $cleanFamName) !== false ||
                    stripos($itemDesc, $cleanFamName) !== false ||
                    stripos($typeText, $cleanFamName) !== false) {
                    $matchedFamilyId = $famObj->id;
                    break;
                }
            }

            // Tentukan gambar terbaik
            $imgUrl = '';
            if (isset($item['images']) && is_array($item['images'])) {
                $imgs = $item['images'];
                if (!empty($imgs['fandom'])) {
                    $imgUrl = $imgs['fandom'];
                } elseif (!empty($imgs['redirect'])) {
                    $imgUrl = $imgs['redirect'];
                } elseif (!empty($imgs['hoyowiki_icon'])) {
                    $imgUrl = $imgs['hoyowiki_icon'];
                } elseif (!empty($imgs['icon'])) {
                    $imgUrl = $imgs['icon'];
                } elseif (!empty($imgs['filename_icon'])) {
                    $imgUrl = 'https://gi.yatta.moe/assets/UI/' . $imgs['filename_icon'] . '.png';
                }
            }

            $data = [
                'name'         => $itemName,
                'familie_id'   => $matchedFamilyId,
                'rarity'       => $item['rarity'] ?? 0,
                'category'     => $item['category'] ?? ($typeText ?: 'MATERIAL'),
                'materialtype' => $item['materialtype'] ?? ($typeText ?: null),
                'dropdomain'   => $item['dropdomain'] ?? ($item['dropDomainName'] ?? null),
                'amount'       => 0,
                'description'  => $itemDesc,
                'images'       => $imgUrl,
                'daysofweek'   => isset($item['daysOfWeek']) ? json_encode($item['daysOfWeek']) : (isset($item['daysofweek']) ? json_encode($item['daysofweek']) : '[]'),
                'source'       => isset($item['sources']) ? json_encode($item['sources']) : (isset($item['source']) ? json_encode($item['source']) : '[]'),
            ];

            Material::updateOrCreate(['name' => $itemName], $data);
        }

        $this->command->info("Selesai memasukkan semua material ke database.");
    }
}
