<?php

namespace App\Services;

use App\Models\ArtifactScoringRule;
use App\Models\ArtifactSet;
use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Models\InventoryCharacter;
use App\Models\InventoryMaterial;
use App\Models\InventoryWeapon;
use App\Models\material as Material;
use App\Models\Weapon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service untuk mengelola Ekspor dan Impor berkas berformat
 * GOOD (Genshin Open Object Description) v1 / v2.
 * Kompatibel dengan Genshin Optimizer, Seelie, Akasha, Mona Uranus, Inventory Kamera, dll.
 */
class GoodFormatService
{
    public function __construct(
        protected ArtifactScoringService $scorer
    ) {}

    /**
     * Map dari GOOD Main Stat Key ke internal DB main_stat_key
     */
    protected const GOOD_TO_INTERNAL_MAIN_STAT = [
        'hp'            => 'hp',
        'atk'           => 'atk',
        'def'           => 'def_percent',
        'hp_'           => 'hp_percent',
        'atk_'          => 'atk_percent',
        'def_'          => 'def_percent',
        'eleMas'        => 'elemental_mastery',
        'enerRech_'     => 'energy_recharge',
        'critRate_'     => 'crit_rate',
        'critDMG_'      => 'crit_dmg',
        'heal_'         => 'healing_bonus',
        'pyro_dmg_'     => 'pyro_dmg',
        'hydro_dmg_'    => 'hydro_dmg',
        'cryo_dmg_'     => 'cryo_dmg',
        'electro_dmg_'  => 'electro_dmg',
        'anemo_dmg_'    => 'anemo_dmg',
        'geo_dmg_'      => 'geo_dmg',
        'dendro_dmg_'   => 'dendro_dmg',
        'physical_dmg_' => 'physical_dmg',
    ];

    /**
     * Map dari internal DB main_stat_key ke GOOD Main Stat Key
     */
    protected const INTERNAL_TO_GOOD_MAIN_STAT = [
        'hp'                => 'hp',
        'atk'               => 'atk',
        'def'               => 'def_',
        'hp_percent'        => 'hp_',
        'atk_percent'       => 'atk_',
        'def_percent'       => 'def_',
        'elemental_mastery' => 'eleMas',
        'energy_recharge'   => 'enerRech_',
        'crit_rate'         => 'critRate_',
        'crit_dmg'          => 'critDMG_',
        'healing_bonus'     => 'heal_',
        'pyro_dmg'          => 'pyro_dmg_',
        'hydro_dmg'         => 'hydro_dmg_',
        'cryo_dmg'          => 'cryo_dmg_',
        'electro_dmg'       => 'electro_dmg_',
        'anemo_dmg'         => 'anemo_dmg_',
        'geo_dmg'           => 'geo_dmg_',
        'dendro_dmg'        => 'dendro_dmg_',
        'physical_dmg'      => 'physical_dmg_',
    ];

    /**
     * Map dari GOOD Substat Key ke internal DB sub_stat key
     */
    protected const GOOD_TO_INTERNAL_SUB_STAT = [
        'critRate_' => 'crit_rate',
        'critDMG_'  => 'crit_dmg',
        'atk_'      => 'atk_pct',
        'hp_'       => 'hp_pct',
        'def_'      => 'def_pct',
        'eleMas'    => 'em',
        'enerRech_' => 'er',
        'atk'       => 'flat_atk',
        'hp'        => 'flat_hp',
        'def'       => 'flat_def',
    ];

    /**
     * Map dari internal DB sub_stat key ke GOOD Substat Key
     */
    protected const INTERNAL_TO_GOOD_SUB_STAT = [
        'crit_rate'   => 'critRate_',
        'crit_dmg'    => 'critDMG_',
        'atk_pct'     => 'atk_',
        'hp_pct'      => 'hp_',
        'def_pct'     => 'def_',
        'em'          => 'eleMas',
        'er'          => 'enerRech_',
        'flat_atk'    => 'atk',
        'flat_hp'     => 'hp',
        'flat_def'    => 'def',
        // Fallbacks
        'atk'         => 'atk',
        'hp'          => 'hp',
        'def'         => 'def',
        'atk_percent' => 'atk_',
        'hp_percent'  => 'hp_',
        'def_percent' => 'def_',
    ];

    /**
     * Normalisasi string untuk pencocokan key (lowercase alfanumerik)
     */
    public static function normalizeKey(string $str): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $str));
    }

    /**
     * Ubah nama menjadi format PascalCase / StudlyCase untuk format GOOD
     */
    public static function toGoodKey(string $name): string
    {
        // Bersihkan tanda petik, tanda hubung, dan karakter non-alfanumerik
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        return Str::studly($cleaned);
    }

    /**
     * Ekspor inventori akun game ke array struktur GOOD
     */
    public function export(GameAccount $account, array $options = []): array
    {
        $includeCharacters = $options['include_characters'] ?? true;
        $includeWeapons    = $options['include_weapons'] ?? true;
        $includeArtifacts  = $options['include_artifacts'] ?? true;
        $includeMaterials  = $options['include_materials'] ?? true;

        $good = [
            'format'     => 'GOOD',
            'version'    => 2,
            'source'     => 'Genshin App (' . ($account->nickname ?: 'UID ' . $account->uid) . ')',
            'characters' => [],
            'weapons'    => [],
            'artifacts'  => [],
        ];

        // 1. Ekspor Karakter
        if ($includeCharacters) {
            $invChars = InventoryCharacter::with('character')
                ->where('game_account_id', $account->id)
                ->orderBy('level', 'desc')
                ->get();

            foreach ($invChars as $invChar) {
                if (!$invChar->character) {
                    continue;
                }

                $charName = $invChar->character->name;
                $key = self::toGoodKey($charName);

                $good['characters'][] = [
                    'key'           => $key,
                    'level'         => (int) $invChar->level,
                    'constellation' => (int) $invChar->constellation,
                    'ascension'     => (int) $invChar->ascension,
                    'talent'        => [
                        'auto'  => (int) max(1, $invChar->talent_attack),
                        'skill' => (int) max(1, $invChar->talent_skill),
                        'burst' => (int) max(1, $invChar->talent_burst),
                    ],
                ];
            }
        }

        // 2. Ekspor Senjata
        if ($includeWeapons) {
            $invWeapons = InventoryWeapon::with(['weapon', 'equippedCharacter'])
                ->where('game_account_id', $account->id)
                ->orderBy('level', 'desc')
                ->get();

            foreach ($invWeapons as $invWeapon) {
                if (!$invWeapon->weapon) {
                    continue;
                }

                $weaponName = $invWeapon->weapon->name;
                $key = self::toGoodKey($weaponName);

                $location = '';
                if ($invWeapon->equippedCharacter) {
                    $location = self::toGoodKey($invWeapon->equippedCharacter->name);
                }

                $good['weapons'][] = [
                    'key'        => $key,
                    'level'      => (int) $invWeapon->level,
                    'ascension'  => (int) $invWeapon->ascension,
                    'refinement' => (int) max(1, $invWeapon->refinement),
                    'location'   => $location,
                    'lock'       => false,
                ];
            }
        }

        // 3. Ekspor Artefak
        if ($includeArtifacts) {
            $invArtifacts = InventoryArtifact::with(['artifactSet', 'equippedCharacter'])
                ->where('game_account_id', $account->id)
                ->orderBy('rarity', 'desc')
                ->orderBy('level', 'desc')
                ->get();

            foreach ($invArtifacts as $art) {
                if (!$art->artifactSet) {
                    continue;
                }

                $setName = $art->artifactSet->name;
                $setKey = self::toGoodKey($setName);

                $location = '';
                if ($art->equippedCharacter) {
                    $location = self::toGoodKey($art->equippedCharacter->name);
                }

                // Map main stat
                $mainStatKey = self::INTERNAL_TO_GOOD_MAIN_STAT[$art->main_stat_key] ?? $art->main_stat_key;

                // Map substats
                $substats = [];
                if (is_array($art->sub_stats)) {
                    foreach ($art->sub_stats as $sub) {
                        $rawKey = $sub['key'] ?? '';
                        $goodSubKey = self::INTERNAL_TO_GOOD_SUB_STAT[$rawKey] ?? $rawKey;
                        $substats[] = [
                            'key'   => $goodSubKey,
                            'value' => (float) ($sub['value'] ?? 0),
                        ];
                    }
                }

                $good['artifacts'][] = [
                    'setKey'      => $setKey,
                    'slotKey'     => $art->slot_key,
                    'rarity'      => (int) $art->rarity,
                    'level'       => (int) $art->level,
                    'mainStatKey' => $mainStatKey,
                    'location'    => $location,
                    'lock'        => false,
                    'substats'    => $substats,
                ];
            }
        }

        // 4. Ekspor Material (jika ada)
        if ($includeMaterials) {
            $invMaterials = InventoryMaterial::with('material')
                ->where('game_account_id', $account->id)
                ->get();

            if ($invMaterials->isNotEmpty()) {
                $materialsMap = [];
                foreach ($invMaterials as $invMat) {
                    if ($invMat->material) {
                        $matKey = self::toGoodKey($invMat->material->name);
                        $materialsMap[$matKey] = (int) $invMat->amount;
                    }
                }
                if (!empty($materialsMap)) {
                    $good['materials'] = $materialsMap;
                }
            }
        }

        return $good;
    }

    /**
     * Impor data GOOD ke akun game
     *
     * @param GameAccount $account
     * @param array $goodData Data JSON yang sudah di-decode
     * @param array $options Opsi impor: mode ('merge'|'replace'), flags komponen
     * @return array Ringkasan hasil impor
     */
    public function import(GameAccount $account, array $goodData, array $options = []): array
    {
        $mode             = $options['mode'] ?? 'merge'; // 'merge' atau 'replace'
        $importCharacters = $options['import_characters'] ?? true;
        $importWeapons    = $options['import_weapons'] ?? true;
        $importArtifacts  = $options['import_artifacts'] ?? true;
        $importMaterials  = $options['import_materials'] ?? true;

        $stats = [
            'characters' => ['imported' => 0, 'updated' => 0, 'skipped' => 0],
            'weapons'    => ['imported' => 0, 'updated' => 0, 'skipped' => 0],
            'artifacts'  => ['imported' => 0, 'updated' => 0, 'skipped' => 0],
            'materials'  => ['imported' => 0, 'updated' => 0, 'skipped' => 0],
        ];
        $warnings = [];

        // Buat Lookup Dictionary untuk Master Data
        $characterLookup   = $this->buildCharacterLookup();
        $weaponLookup      = $this->buildWeaponLookup();
        $artifactSetLookup = $this->buildArtifactSetLookup();
        $materialLookup    = $this->buildMaterialLookup();

        DB::beginTransaction();
        try {
            // Mode Replace: Hapus data lama yang dipilih
            if ($mode === 'replace') {
                if ($importCharacters) {
                    InventoryCharacter::where('game_account_id', $account->id)->delete();
                }
                if ($importWeapons) {
                    InventoryWeapon::where('game_account_id', $account->id)->delete();
                }
                if ($importArtifacts) {
                    InventoryArtifact::where('game_account_id', $account->id)->delete();
                }
                if ($importMaterials) {
                    InventoryMaterial::where('game_account_id', $account->id)->delete();
                }
            }

            // 1. Proses Impor Karakter
            if ($importCharacters && !empty($goodData['characters']) && is_array($goodData['characters'])) {
                foreach ($goodData['characters'] as $charItem) {
                    $rawKey = $charItem['key'] ?? '';
                    $character = $this->resolveCharacter($rawKey, $characterLookup);

                    if (!$character) {
                        $stats['characters']['skipped']++;
                        $warnings[] = "Karakter '{$rawKey}' tidak ditemukan di database master.";
                        continue;
                    }

                    $level         = (int) ($charItem['level'] ?? 1);
                    $ascension     = (int) ($charItem['ascension'] ?? $this->estimateAscension($level));
                    $constellation = (int) ($charItem['constellation'] ?? 0);
                    $talentAuto    = (int) ($charItem['talent']['auto'] ?? 1);
                    $talentSkill   = (int) ($charItem['talent']['skill'] ?? 1);
                    $talentBurst   = (int) ($charItem['talent']['burst'] ?? 1);

                    $existing = InventoryCharacter::where('game_account_id', $account->id)
                        ->where('character_id', $character->id)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'level'         => max($existing->level, $level),
                            'ascension'     => max($existing->ascension, $ascension),
                            'constellation' => max($existing->constellation, $constellation),
                            'talent_attack' => max($existing->talent_attack, $talentAuto),
                            'talent_skill'  => max($existing->talent_skill, $talentSkill),
                            'talent_burst'  => max($existing->talent_burst, $talentBurst),
                            'scanned_at'    => now(),
                        ]);
                        $stats['characters']['updated']++;
                    } else {
                        InventoryCharacter::create([
                            'game_account_id' => $account->id,
                            'character_id'    => $character->id,
                            'level'           => $level,
                            'ascension'       => $ascension,
                            'constellation'   => $constellation,
                            'talent_attack'   => $talentAuto,
                            'talent_skill'    => $talentSkill,
                            'talent_burst'    => $talentBurst,
                            'scanned_at'      => now(),
                        ]);
                        $stats['characters']['imported']++;
                    }
                }
            }

            // 2. Proses Impor Senjata
            if ($importWeapons && !empty($goodData['weapons']) && is_array($goodData['weapons'])) {
                foreach ($goodData['weapons'] as $weaponItem) {
                    $rawKey = $weaponItem['key'] ?? '';
                    $weapon = $this->resolveWeapon($rawKey, $weaponLookup);

                    if (!$weapon) {
                        $stats['weapons']['skipped']++;
                        $warnings[] = "Senjata '{$rawKey}' tidak ditemukan di database master.";
                        continue;
                    }

                    $level      = (int) ($weaponItem['level'] ?? 1);
                    $ascension  = (int) ($weaponItem['ascension'] ?? $this->estimateAscension($level));
                    $refinement = (int) ($weaponItem['refinement'] ?? 1);
                    
                    // Resolusi karakter yang memakai
                    $locationRaw = $weaponItem['location'] ?? '';
                    $equippedChar = $locationRaw ? $this->resolveCharacter($locationRaw, $characterLookup) : null;

                    $existing = null;
                    if ($equippedChar) {
                        $existing = InventoryWeapon::where('game_account_id', $account->id)
                            ->where('equipped_character_id', $equippedChar->id)
                            ->first();
                    }

                    if ($existing) {
                        $existing->update([
                            'weapon_id'             => $weapon->id,
                            'level'                 => $level,
                            'ascension'             => $ascension,
                            'refinement'            => $refinement,
                            'equipped_character_id' => $equippedChar->id,
                            'scanned_at'            => now(),
                        ]);
                        $stats['weapons']['updated']++;
                    } else {
                        InventoryWeapon::create([
                            'game_account_id'       => $account->id,
                            'weapon_id'             => $weapon->id,
                            'level'                 => $level,
                            'ascension'             => $ascension,
                            'refinement'            => $refinement,
                            'equipped_character_id' => $equippedChar?->id,
                            'scanned_at'            => now(),
                        ]);
                        $stats['weapons']['imported']++;
                    }
                }
            }

            // 3. Proses Impor Artefak
            $importedArtifacts = [];
            if ($importArtifacts && !empty($goodData['artifacts']) && is_array($goodData['artifacts'])) {
                foreach ($goodData['artifacts'] as $artItem) {
                    $setKey = $artItem['setKey'] ?? '';
                    $artSet = $this->resolveArtifactSet($setKey, $artifactSetLookup);

                    if (!$artSet) {
                        $stats['artifacts']['skipped']++;
                        $warnings[] = "Artifact Set '{$setKey}' tidak ditemukan di database master.";
                        continue;
                    }

                    $slotKey = strtolower($artItem['slotKey'] ?? 'flower');
                    if (!in_array($slotKey, ['flower', 'plume', 'sands', 'goblet', 'circlet'])) {
                        $stats['artifacts']['skipped']++;
                        continue;
                    }

                    $rarity = (int) ($artItem['rarity'] ?? 5);
                    $level  = (int) ($artItem['level'] ?? 0);

                    // Resolusi Main Stat
                    $rawMainKey  = $artItem['mainStatKey'] ?? 'hp';
                    $mainStatKey = self::GOOD_TO_INTERNAL_MAIN_STAT[$rawMainKey] ?? $rawMainKey;
                    $mainStatVal = $this->calculateMainStatValue($mainStatKey, $rarity, $level);

                    // Resolusi Substats
                    $subStats = [];
                    if (!empty($artItem['substats']) && is_array($artItem['substats'])) {
                        foreach ($artItem['substats'] as $sub) {
                            $subKeyRaw = $sub['key'] ?? '';
                            $mappedKey = self::GOOD_TO_INTERNAL_SUB_STAT[$subKeyRaw] ?? $subKeyRaw;
                            $val = (float) ($sub['value'] ?? 0);
                            if ($mappedKey && $val > 0) {
                                $subStats[] = [
                                    'key'   => $mappedKey,
                                    'value' => $val,
                                ];
                            }
                        }
                    }

                    // Resolusi Equip Location
                    $locationRaw = $artItem['location'] ?? '';
                    $equippedChar = $locationRaw ? $this->resolveCharacter($locationRaw, $characterLookup) : null;

                    $existing = null;
                    if ($equippedChar) {
                        $existing = InventoryArtifact::where('game_account_id', $account->id)
                            ->where('equipped_character_id', $equippedChar->id)
                            ->where('slot_key', $slotKey)
                            ->first();
                    }

                    if ($existing) {
                        $existing->update([
                            'artifact_set_id'       => $artSet->id,
                            'slot_key'              => $slotKey,
                            'rarity'                => $rarity,
                            'level'                 => $level,
                            'main_stat_key'         => $mainStatKey,
                            'main_stat_value'       => $mainStatVal,
                            'sub_stats'             => $subStats,
                            'equipped_character_id' => $equippedChar->id,
                            'scanned_at'            => now(),
                        ]);
                        $importedArtifacts[] = $existing;
                        $stats['artifacts']['updated']++;
                    } else {
                        $created = InventoryArtifact::create([
                            'game_account_id'       => $account->id,
                            'artifact_set_id'       => $artSet->id,
                            'slot_key'              => $slotKey,
                            'rarity'                => $rarity,
                            'level'                 => $level,
                            'main_stat_key'         => $mainStatKey,
                            'main_stat_value'       => $mainStatVal,
                            'sub_stats'             => $subStats,
                            'equipped_character_id' => $equippedChar?->id,
                            'scanned_at'            => now(),
                        ]);
                        $importedArtifacts[] = $created;
                        $stats['artifacts']['imported']++;
                    }
                }
            }

            // 4. Proses Impor Material
            if ($importMaterials && !empty($goodData['materials']) && is_array($goodData['materials'])) {
                foreach ($goodData['materials'] as $matKey => $amount) {
                    $material = $this->resolveMaterial((string) $matKey, $materialLookup);
                    if (!$material) {
                        continue;
                    }

                    $existing = InventoryMaterial::where('game_account_id', $account->id)
                        ->where('material_id', $material->id)
                        ->first();

                    if ($existing) {
                        $existing->update(['amount' => (int) $amount]);
                        $stats['materials']['updated']++;
                    } else {
                        InventoryMaterial::create([
                            'game_account_id' => $account->id,
                            'material_id'     => $material->id,
                            'amount'          => (int) $amount,
                        ]);
                        $stats['materials']['imported']++;
                    }
                }
            }

            // Update timestamp akun
            $account->update(['last_synced_at' => now()]);

            DB::commit();

            // Auto-Score Artefak yang baru diimpor
            foreach ($importedArtifacts as $artModel) {
                try {
                    $this->scorer->scoreAndSave($artModel, $artModel->equippedCharacter);
                } catch (\Throwable) {
                    // Lanjutkan jika scoring opsional gagal
                }
            }

            return [
                'success'  => true,
                'mode'     => $mode,
                'stats'    => $stats,
                'warnings' => array_slice(array_unique($warnings), 0, 10),
                'message'  => 'Impor data GOOD berhasil diselesaikan!',
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat memproses impor data GOOD: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Taksir fase ascension berdasarkan level
     */
    protected function estimateAscension(int $level): int
    {
        return match (true) {
            $level > 80 => 6,
            $level > 70 => 5,
            $level > 60 => 4,
            $level > 50 => 3,
            $level > 40 => 2,
            $level > 20 => 1,
            default     => 0,
        };
    }

    /**
     * Hitung nilai main stat artefak berdasarkan jenis stat, rarity, dan level
     */
    public function calculateMainStatValue(string $statKey, int $rarity, int $level): string
    {
        $maxLevel = $rarity === 5 ? 20 : 16;
        $level = min($maxLevel, max(0, $level));
        $ratio = $maxLevel > 0 ? ($level / $maxLevel) : 0;

        // Spesifikasi nilai (Level 0 -> Max Level)
        $ranges = [
            'hp'                => ['base' => 717,  'max5' => 4780, 'max4' => 3571, 'is_pct' => false],
            'atk'               => ['base' => 47,   'max5' => 311,  'max4' => 232,  'is_pct' => false],
            'hp_percent'        => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'atk_percent'       => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'def_percent'       => ['base' => 8.7,  'max5' => 58.3, 'max4' => 43.5, 'is_pct' => true],
            'energy_recharge'   => ['base' => 7.8,  'max5' => 51.8, 'max4' => 38.7, 'is_pct' => true],
            'elemental_mastery' => ['base' => 28.0, 'max5' => 187,  'max4' => 139,  'is_pct' => false],
            'crit_rate'         => ['base' => 4.7,  'max5' => 31.1, 'max4' => 23.2, 'is_pct' => true],
            'crit_dmg'          => ['base' => 9.3,  'max5' => 62.2, 'max4' => 46.4, 'is_pct' => true],
            'healing_bonus'     => ['base' => 5.4,  'max5' => 35.9, 'max4' => 26.8, 'is_pct' => true],
            'pyro_dmg'          => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'hydro_dmg'         => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'cryo_dmg'          => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'electro_dmg'       => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'anemo_dmg'         => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'geo_dmg'           => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'dendro_dmg'        => ['base' => 7.0,  'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true],
            'physical_dmg'      => ['base' => 8.7,  'max5' => 58.3, 'max4' => 43.5, 'is_pct' => true],
        ];

        $cfg = $ranges[$statKey] ?? ['base' => 7.0, 'max5' => 46.6, 'max4' => 34.8, 'is_pct' => true];
        $max = $rarity === 5 ? $cfg['max5'] : $cfg['max4'];
        $val = $cfg['base'] + ($max - $cfg['base']) * $ratio;

        if ($cfg['is_pct']) {
            return number_format($val, 1) . '%';
        }

        return (string) round($val);
    }

    /**
     * Membangun map lookup karakter (name & alias normalized -> Character model)
     */
    protected function buildCharacterLookup(): array
    {
        $lookup = [];
        $characters = Character::all();

        foreach ($characters as $char) {
            $lookup[self::normalizeKey($char->name)] = $char;
            $lookup[self::normalizeKey($char->slug)] = $char;
        }

        // Alias umum GOOD
        $traveler = $characters->firstWhere('slug', 'traveler') ?? $characters->firstWhere('name', 'Traveler');
        if ($traveler) {
            $lookup['traveleranemo']   = $traveler;
            $lookup['travelergeo']     = $traveler;
            $lookup['travelerelectro'] = $traveler;
            $lookup['travelerdendro']  = $traveler;
            $lookup['travelerhydro']   = $traveler;
            $lookup['travelerpyro']    = $traveler;
            $lookup['lumine']          = $traveler;
            $lookup['aether']          = $traveler;
        }

        return $lookup;
    }

    /**
     * Membangun map lookup senjata
     */
    protected function buildWeaponLookup(): array
    {
        $lookup = [];
        $weapons = Weapon::all();

        foreach ($weapons as $w) {
            $lookup[self::normalizeKey($w->name)] = $w;
            $lookup[self::normalizeKey($w->slug)] = $w;
        }

        return $lookup;
    }

    /**
     * Membangun map lookup artifact set
     */
    protected function buildArtifactSetLookup(): array
    {
        $lookup = [];
        $sets = ArtifactSet::all();

        foreach ($sets as $s) {
            $lookup[self::normalizeKey($s->name)] = $s;
            $lookup[self::normalizeKey($s->slug)] = $s;
        }

        return $lookup;
    }

    /**
     * Membangun map lookup material
     */
    protected function buildMaterialLookup(): array
    {
        $lookup = [];
        $materials = Material::all();

        foreach ($materials as $m) {
            $lookup[self::normalizeKey($m->name)] = $m;
        }

        return $lookup;
    }

    /**
     * Resolusi karakter berdasarkan raw key
     */
    protected function resolveCharacter(string $rawKey, array $lookup): ?Character
    {
        $norm = self::normalizeKey($rawKey);
        if (isset($lookup[$norm])) {
            return $lookup[$norm];
        }

        // Deteksi Traveler prefix
        if (str_starts_with($norm, 'traveler') && isset($lookup['traveler'])) {
            return $lookup['traveler'];
        }

        return null;
    }

    /**
     * Resolusi senjata berdasarkan raw key
     */
    protected function resolveWeapon(string $rawKey, array $lookup): ?Weapon
    {
        $norm = self::normalizeKey($rawKey);
        if (isset($lookup[$norm])) {
            return $lookup[$norm];
        }

        // Variasi The Catch / Catch
        if ($norm === 'catch' && isset($lookup['thecatch'])) {
            return $lookup['thecatch'];
        }

        return null;
    }

    /**
     * Resolusi set artefak berdasarkan raw key
     */
    protected function resolveArtifactSet(string $rawKey, array $lookup): ?ArtifactSet
    {
        $norm = self::normalizeKey($rawKey);
        return $lookup[$norm] ?? null;
    }

    /**
     * Resolusi material berdasarkan raw key
     */
    protected function resolveMaterial(string $rawKey, array $lookup): ?Material
    {
        $norm = self::normalizeKey($rawKey);
        return $lookup[$norm] ?? null;
    }
}
