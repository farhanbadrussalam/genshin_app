<?php

namespace App\Services;

use App\Models\Character;
use App\Models\InventoryMaterial;
use Carbon\Carbon;

class TalentMaterialService
{
    protected static $cachedMap = null;

    public static function getTalentMap()
    {
        if (self::$cachedMap !== null) {
            return self::$cachedMap;
        }

        $path = storage_path('app/genshin_talent_materials.json');
        if (file_exists($path)) {
            self::$cachedMap = json_decode(file_get_contents($path), true) ?? [];
        } else {
            self::$cachedMap = [];
        }

        return self::$cachedMap;
    }

    public static function getCharacterTalentData($characterId)
    {
        $map = self::getTalentMap();
        if (isset($map[$characterId])) {
            return $map[$characterId];
        }

        $char = Character::find($characterId);
        if (!$char) return null;

        foreach ($map as $item) {
            if (strtolower($item['name'] ?? '') === strtolower($char->name)) {
                return $item;
            }
        }

        return null;
    }

    public static function calculateRequirements($characterId, $currentLevel = 1, $targetLevel = 8, $talentCount = 1, $accountId = null)
    {
        $charData = self::getCharacterTalentData($characterId);
        if (!$charData) {
            return [
                'success' => false,
                'message' => 'Data talent untuk karakter ini belum tersedia.',
                'materials' => [],
            ];
        }

        $currentLevel = max(1, min(10, (int) $currentLevel));
        $targetLevel = max($currentLevel, min(10, (int) $targetLevel));
        $talentCount = max(1, min(3, (int) $talentCount));

        $today = Carbon::now(config('app.timezone'))->locale('id');
        $todayName = $today->isoFormat('dddd');

        $invMap = [];
        if (!$accountId) {
            $accountId = session('active_game_account_id');
        }
        if ($accountId) {
            $invMap = InventoryMaterial::where('game_account_id', $accountId)->pluck('amount', 'material_id')->toArray();
        }

        $totalMats = [];
        $levelCosts = $charData['level_costs'] ?? [];

        for ($lvl = $currentLevel + 1; $lvl <= $targetLevel; $lvl++) {
            $matsForLvl = $levelCosts[$lvl] ?? [];
            foreach ($matsForLvl as $m) {
                $matId = $m['material_id'] ?? null;
                $name = $m['name'];
                $key = $matId ? ('id_' . $matId) : ('name_' . $name);
                $count = (int) $m['count'] * $talentCount;

                if (!isset($totalMats[$key])) {
                    $days = json_decode($m['daysofweek'] ?? '[]', true) ?? [];
                    $isToday = in_array($todayName, $days) || in_array('Minggu', $days);
                    $stock = $matId ? ($invMap[$matId] ?? 0) : 0;

                    $totalMats[$key] = [
                        'material_id' => $matId,
                        'name' => $name,
                        'rarity' => $m['rarity'] ?? 1,
                        'images' => $m['images'] ?? '',
                        'dropdomain' => $m['dropdomain'] ?? '',
                        'daysofweek' => $days,
                        'is_available_today' => $isToday,
                        'required' => 0,
                        'stock' => $stock,
                    ];
                }

                $totalMats[$key]['required'] += $count;
            }
        }

        $materialsList = collect($totalMats)->map(function ($i) {
            $i['missing'] = max(0, $i['required'] - $i['stock']);
            return $i;
        })->sortBy([
            ['rarity', 'asc'],
            ['name', 'asc'],
        ])->values()->toArray();

        return [
            'success' => true,
            'character_id' => $charData['character_id'],
            'character_name' => $charData['name'],
            'icon_url' => $charData['icon_url'],
            'current_level' => $currentLevel,
            'target_level' => $targetLevel,
            'talent_count' => $talentCount,
            'materials' => $materialsList,
        ];
    }

    public static function getPresetsForCharacter($characterId)
    {
        return [
            ['id' => 'lv6_one', 'label' => 'Lv 1 - 6 (1 Talent - Budget)', 'current' => 1, 'target' => 6, 'count' => 1],
            ['id' => 'lv8_one', 'label' => 'Lv 1 - 8 (1 Talent - Standar)', 'current' => 1, 'target' => 8, 'count' => 1],
            ['id' => 'lv10_one', 'label' => 'Lv 1 - 10 (1 Talent - Max/Crown)', 'current' => 1, 'target' => 10, 'count' => 1],
            ['id' => 'lv8_triple', 'label' => 'Lv 1 - 8 (3 Talent - All Optimal)', 'current' => 1, 'target' => 8, 'count' => 3],
            ['id' => 'lv10_triple', 'label' => 'Lv 1 - 10 (Triple Crown - 3 Talent)', 'current' => 1, 'target' => 10, 'count' => 3],
        ];
    }
}
