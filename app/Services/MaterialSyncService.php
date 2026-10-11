<?php

namespace App\Services;

use App\Models\material;
use App\Models\family;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaterialSyncService
{
    /**
     * Sinkronisasi katalog data master material & family dari API eksternal
     */
    public function syncAllMaterials(): array
    {
        $urlId = 'https://genshin-db-api.vercel.app/api/v5/materials?query=names&matchCategories=true&verboseCategories=true&resultLanguage=Indonesia';
        
        $response = null;
        try {
            $response = Http::timeout(45)->get($urlId);
        } catch (\Throwable $e) {
            Log::warning("[MaterialSyncService] Gagal fetch Indonesian API: " . $e->getMessage());
        }

        if (!$response || !$response->successful()) {
            $urlEn = 'https://genshin-db-api.vercel.app/api/v5/materials?query=names&matchCategories=true&verboseCategories=true&resultLanguage=English';
            $response = Http::timeout(45)->get($urlEn);
        }

        if (!$response || !$response->successful()) {
            throw new \RuntimeException("Gagal mengambil data material dari API Genshin-DB (" . ($response?->status() ?? 'No response') . ")");
        }

        $items = $response->json();
        if (!is_array($items)) {
            throw new \RuntimeException("Format respons data API tidak berupa array.");
        }

        $otherFamily = family::firstOrCreate(['name' => 'Other']);
        $familyList = family::all();
        $familyMap = [];
        foreach ($familyList as $f) {
            $familyMap[strtolower(trim($f->name))] = $f;
        }

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($items as $item) {
            if (!is_array($item) || empty($item['name'])) {
                continue;
            }

            $itemName = trim($item['name']);
            $itemDesc = $item['description'] ?? '';
            $typeText = $item['typeText'] ?? '';
            $category = $item['category'] ?? '';

            // Tentukan Family yang cocok berdasarkan nama family yang ada
            $matchedFamilyId = $otherFamily->id;

            foreach ($familyList as $famObj) {
                if ($famObj->name === 'Other') continue;

                $cleanFamName = trim(preg_replace('/\s*\(.*?\)/', '', $famObj->name));
                if (empty($cleanFamName)) continue;

                if (stripos($itemName, $cleanFamName) !== false ||
                    stripos($itemDesc, $cleanFamName) !== false ||
                    stripos($typeText, $cleanFamName) !== false) {
                    $matchedFamilyId = $famObj->id;
                    break;
                }
            }

            // Jika masih 'Other', coba buat family otomatis untuk kelompok material penting
            if ($matchedFamilyId === $otherFamily->id && !empty($typeText)) {
                $groupName = trim($typeText);
                if (in_array($groupName, [
                    'Material Penguatan Karakter',
                    'Material Talent Karakter',
                    'Material Penguatan Senjata',
                    'Spesialisasi Lokal',
                    'Bahan Masakan',
                    'Material Tempa'
                ])) {
                    $famKey = strtolower($groupName);
                    if (!isset($familyMap[$famKey])) {
                        $newFam = family::firstOrCreate(['name' => $groupName]);
                        $familyMap[$famKey] = $newFam;
                        $familyList->push($newFam);
                    }
                    $matchedFamilyId = $familyMap[$famKey]->id;
                }
            }

            // Resolusi URL Gambar
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

            $daysOfWeek = isset($item['daysOfWeek']) ? json_encode($item['daysOfWeek']) : (isset($item['daysofweek']) ? json_encode($item['daysofweek']) : '[]');
            $sourceData = isset($item['sources']) ? json_encode($item['sources']) : (isset($item['source']) ? json_encode($item['source']) : '[]');

            $data = [
                'name'         => $itemName,
                'familie_id'   => $matchedFamilyId,
                'rarity'       => (int) ($item['rarity'] ?? 0),
                'category'     => $category ?: ($typeText ?: 'MATERIAL'),
                'materialtype' => $item['materialtype'] ?? ($typeText ?: null),
                'dropdomain'   => $item['dropdomain'] ?? ($item['dropDomainName'] ?? null),
                'description'  => $itemDesc,
                'daysofweek'   => $daysOfWeek,
                'source'       => $sourceData,
            ];

            if (!empty($imgUrl)) {
                $data['images'] = $imgUrl;
            }

            $existing = material::where('name', $itemName)->first();
            if ($existing) {
                $existing->update($data);
                $updatedCount++;
            } else {
                $data['amount'] = 0;
                if (empty($data['images'])) {
                    $data['images'] = 'https://gi.yatta.moe/assets/UI/UI_ItemIcon_0.png';
                }
                material::create($data);
                $createdCount++;
            }
        }

        $totalProcessed = $createdCount + $updatedCount;
        $totalFamilies = family::count();

        return [
            'success'          => true,
            'total_processed'  => $totalProcessed,
            'created'          => $createdCount,
            'updated'          => $updatedCount,
            'total_families'   => $totalFamilies,
            'message'          => "Berhasil mensinkronkan {$totalProcessed} material ({$createdCount} item baru, {$updatedCount} diperbarui) dan {$totalFamilies} kelompok Family dari API!"
        ];
    }
}