<?php

namespace App\Services;

use App\Models\ArtifactScoringRule;
use App\Models\ArtifactSet;
use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Models\InventoryCharacter;
use App\Models\InventoryWeapon;
use App\Models\Weapon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EnkaNetworkService
{
    protected ArtifactScoringService $scorer;

    public function __construct(ArtifactScoringService $scorer)
    {
        $this->scorer = $scorer;
    }

    /**
     * Map Enka Set ID ke Nama Set Artifact
     */
    protected array $setMap = [
        14001 => 'Blizzard Strayer',
        14002 => 'Heart of Depth',
        14003 => 'Retracing Bolide',
        14004 => 'Maiden Beloved',
        15001 => "Gladiator's Finale",
        15002 => 'Viridescent Venerer',
        15003 => "Wanderer's Troupe",
        15005 => 'Bloodstained Chivalry',
        15006 => 'Crimson Witch of Flames',
        15007 => 'Noblesse Oblige',
        15008 => 'Archaic Petra',
        15009 => 'Thundering Fury',
        15010 => 'Thundersoother',
        15011 => 'Lavawalker',
        15017 => 'Pale Flame',
        15018 => 'Tenacity of the Millelith',
        15019 => "Shimenawa's Reminiscence",
        15020 => 'Emblem of Severed Fate',
        15021 => 'Husk of Opulent Dreams',
        15022 => 'Ocean-Hued Clam',
        15023 => 'Vermillion Hereafter',
        15024 => 'Echoes of an Offering',
        15025 => 'Deepwood Memories',
        15026 => 'Gilded Dreams',
        15027 => 'Desert Pavilion Chronicle',
        15028 => 'Flower of Paradise Lost',
        15029 => "Nymph's Dream",
        15030 => "Vourukasha's Glow",
        15031 => 'Marechaussee Hunter',
        15032 => 'Golden Troupe',
        15033 => 'Song of Days Past',
        15034 => 'Nighttime Whispers in the Echoing Woods',
        15035 => 'Fragment of Harmonic Whimsy',
        15036 => 'Unfinished Reverie',
        15037 => 'Scroll of the Hero of Cinder City',
        15038 => 'Obsidian Codex',
        10001 => 'Berserker',
        10002 => 'Instructor',
        10003 => 'The Exile',
        10004 => 'Resolution of Sojourner',
        10005 => 'Martial Artist',
        10006 => "Defender's Will",
        10007 => 'Tiny Miracle',
        10008 => 'Brave Heart',
        10009 => 'Gambler',
        10010 => 'Scholar',
        10011 => 'Traveling Doctor',
        10012 => 'Lucky Dog',
        10013 => 'Adventurer',
    ];

    /**
     * Map Slot Enka ke Slot Internal
     */
    protected array $slotMap = [
        'EQUIP_BRACER'   => 'flower',
        'EQUIP_NECKLACE' => 'plume',
        'EQUIP_SHOES'    => 'sands',
        'EQUIP_RING'     => 'goblet',
        'EQUIP_DRESS'    => 'circlet',
    ];

    /**
     * Map Fight Prop ID ke Sub-Stat Key
     */
    protected array $subStatPropMap = [
        'FIGHT_PROP_CRITICAL'           => 'crit_rate',
        'FIGHT_PROP_CRITICAL_HURT'      => 'crit_dmg',
        'FIGHT_PROP_ATTACK_PERCENT'     => 'atk_pct',
        'FIGHT_PROP_HP_PERCENT'         => 'hp_pct',
        'FIGHT_PROP_DEFENSE_PERCENT'    => 'def_pct',
        'FIGHT_PROP_ELEMENT_MASTERY'    => 'em',
        'FIGHT_PROP_CHARGE_EFFICIENCY'  => 'er',
        'FIGHT_PROP_ATTACK'             => 'flat_atk',
        'FIGHT_PROP_HP'                 => 'flat_hp',
        'FIGHT_PROP_DEFENSE'            => 'flat_def',
    ];

    /**
     * Map Fight Prop ID ke Main Stat Key & Format
     */
    protected array $mainStatPropMap = [
        'FIGHT_PROP_HP'                 => ['key' => 'hp', 'is_pct' => false],
        'FIGHT_PROP_ATTACK'             => ['key' => 'atk', 'is_pct' => false],
        'FIGHT_PROP_HP_PERCENT'         => ['key' => 'hp_percent', 'is_pct' => true],
        'FIGHT_PROP_ATTACK_PERCENT'     => ['key' => 'atk_percent', 'is_pct' => true],
        'FIGHT_PROP_DEFENSE_PERCENT'    => ['key' => 'def_percent', 'is_pct' => true],
        'FIGHT_PROP_CRITICAL'           => ['key' => 'crit_rate', 'is_pct' => true],
        'FIGHT_PROP_CRITICAL_HURT'      => ['key' => 'crit_dmg', 'is_pct' => true],
        'FIGHT_PROP_CHARGE_EFFICIENCY'  => ['key' => 'energy_recharge', 'is_pct' => true],
        'FIGHT_PROP_ELEMENT_MASTERY'    => ['key' => 'elemental_mastery', 'is_pct' => false],
        'FIGHT_PROP_HEAL_ADD'           => ['key' => 'healing_bonus', 'is_pct' => true],
        'FIGHT_PROP_FIRE_ADD_HURT'      => ['key' => 'pyro_dmg', 'is_pct' => true],
        'FIGHT_PROP_WATER_ADD_HURT'     => ['key' => 'hydro_dmg', 'is_pct' => true],
        'FIGHT_PROP_GRASS_ADD_HURT'     => ['key' => 'dendro_dmg', 'is_pct' => true],
        'FIGHT_PROP_ELEC_ADD_HURT'      => ['key' => 'electro_dmg', 'is_pct' => true],
        'FIGHT_PROP_WIND_ADD_HURT'      => ['key' => 'anemo_dmg', 'is_pct' => true],
        'FIGHT_PROP_ICE_ADD_HURT'       => ['key' => 'cryo_dmg', 'is_pct' => true],
        'FIGHT_PROP_ROCK_ADD_HURT'      => ['key' => 'geo_dmg', 'is_pct' => true],
        'FIGHT_PROP_PHYSICAL_ADD_HURT'  => ['key' => 'physical_dmg', 'is_pct' => true],
    ];

    /**
     * Map Built-in Avatar ID ke Nama Karakter (Fallback instan)
     */
    protected array $avatarMap = [
        10000002 => 'Kamisato Ayaka',
        10000003 => 'Jean',
        10000005 => 'Traveler',
        10000007 => 'Traveler',
        10000014 => 'Barbara',
        10000015 => 'Kaeya',
        10000016 => 'Diluc',
        10000020 => 'Razor',
        10000021 => 'Amber',
        10000022 => 'Venti',
        10000023 => 'Xiangling',
        10000024 => 'Beidou',
        10000025 => 'Xingqiu',
        10000026 => 'Xiao',
        10000027 => 'Ningguang',
        10000029 => 'Klee',
        10000030 => 'Zhongli',
        10000031 => 'Fischl',
        10000032 => 'Bennett',
        10000033 => 'Tartaglia',
        10000034 => 'Noelle',
        10000035 => 'Qiqi',
        10000036 => 'Chongyun',
        10000037 => 'Ganyu',
        10000038 => 'Albedo',
        10000039 => 'Diona',
        10000041 => 'Mona',
        10000042 => 'Keqing',
        10000043 => 'Sucrose',
        10000044 => 'Xinyan',
        10000045 => 'Rosaria',
        10000046 => 'Hu Tao',
        10000047 => 'Kaedehara Kazuha',
        10000048 => 'Yanfei',
        10000049 => 'Yoimiya',
        10000050 => 'Thoma',
        10000051 => 'Eula',
        10000052 => 'Raiden Shogun',
        10000053 => 'Sayu',
        10000054 => 'Sangonomiya Kokomi',
        10000055 => 'Gorou',
        10000056 => 'Kujou Sara',
        10000057 => 'Arataki Itto',
        10000058 => 'Yae Miko',
        10000059 => 'Shikanoin Heizou',
        10000060 => 'Yelan',
        10000061 => 'Kirara',
        10000062 => 'Aloy',
        10000063 => 'Shenhe',
        10000064 => 'Yun Jin',
        10000065 => 'Kuki Shinobu',
        10000066 => 'Kamisato Ayato',
        10000067 => 'Collei',
        10000068 => 'Dori',
        10000069 => 'Tighnari',
        10000070 => 'Nilou',
        10000071 => 'Cyno',
        10000072 => 'Candace',
        10000073 => 'Nahida',
        10000074 => 'Layla',
        10000075 => 'Wanderer',
        10000076 => 'Faruzan',
        10000077 => 'Yaoyao',
        10000078 => 'Alhaitham',
        10000079 => 'Dehya',
        10000080 => 'Mika',
        10000081 => 'Kaveh',
        10000082 => 'Baizhu',
        10000083 => 'Lynette',
        10000084 => 'Lyney',
        10000085 => 'Freminet',
        10000086 => 'Wriothesley',
        10000087 => 'Neuvillette',
        10000088 => 'Charlotte',
        10000089 => 'Furina',
        10000090 => 'Chevreuse',
        10000091 => 'Navia',
        10000092 => 'Gaming',
        10000093 => 'Xianyun',
        10000094 => 'Chiori',
        10000095 => 'Sigewinne',
        10000096 => 'Arlecchino',
        10000097 => 'Sethos',
        10000098 => 'Clorinde',
        10000099 => 'Emilie',
        10000100 => 'Kachina',
        10000101 => 'Kinich',
        10000102 => 'Mualani',
        10000103 => 'Xilonen',
        10000104 => 'Chasca',
        10000105 => 'Ororon',
        10000106 => 'Mavuika',
        10000107 => 'Citlali',
        10000108 => 'Lanyan',
        10000109 => 'Yumemizuki Mizuki',
        10000110 => 'Iansan',
        10000111 => 'Varesa',
        10000112 => 'Escoffier',
        10000113 => 'Ifa',
        10000114 => 'Dahlia',
        10000115 => 'Skirk',
        10000116 => 'Ineffa',
        10000117 => 'Madame Ping',
        10000118 => 'Guizhong',
        10000119 => 'Cloud Retainer',
        10000120 => 'Flins',
        10000121 => 'Aino',
        10000122 => 'Lauma',
        10000148 => 'Alyosha',
        10000150 => 'Odette',
    ];

    /**
     * Tarik data profil player dari Enka.Network API
     */
    public function fetchEnkaData(string $uid): array
    {
        $uid = trim($uid);
        if (!preg_match('/^\d{9,10}$/', $uid)) {
            throw new \InvalidArgumentException("Format UID tidak valid ({$uid}). UID Genshin Impact terdiri dari 9-10 digit angka.");
        }

        $url = "https://enka.network/api/uid/{$uid}";

        $response = Http::withHeaders([
            'User-Agent' => 'GenshinApp-ScoreTracker/1.0 (contact: info@genshinapp.local)',
            'Accept'     => 'application/json',
        ])->timeout(15)->get($url);

        if ($response->status() === 404) {
            throw new \RuntimeException("UID {$uid} tidak ditemukan di server Genshin Impact.");
        } elseif ($response->status() === 424) {
            throw new \RuntimeException("Server Enka.Network sedang maintenance atau server game tidak merespon.");
        } elseif ($response->status() === 429) {
            throw new \RuntimeException("Terlalu banyak permintaan ke Enka.Network. Mohon tunggu beberapa saat sebelum mencoba lagi.");
        } elseif (!$response->successful()) {
            throw new \RuntimeException("Gagal mengambil data dari Enka.Network (HTTP {$response->status()}).");
        }

        $data = $response->json();
        if (!is_array($data)) {
            throw new \RuntimeException("Respon data dari Enka.Network kosong atau tidak valid.");
        }

        return $data;
    }

    /**
     * Cari karakter dari database berdasarkan avatar_id, atau fetch otomatis dari Amber/Enka jika baru
     */
    public function findOrCreateCharacterByAvatarId(int $avatarId): Character
    {
        // 1. Cek langsung dari database via avatar_id
        $character = Character::where('avatar_id', $avatarId)->first();
        if ($character) {
            return $character;
        }

        // Tangani Traveler (Avatar ID: 10000005 = Aether, 10000007 = Lumine)
        if ($avatarId === 10000005 || $avatarId === 10000007 || str_starts_with((string)$avatarId, '10000005') || str_starts_with((string)$avatarId, '10000007')) {
            $traveler = Character::where('name', 'Traveler')->first();
            if ($traveler) {
                if (!$traveler->avatar_id) {
                    $traveler->update(['avatar_id' => $avatarId]);
                }
                return $traveler;
            }
        }

        // 2. Jika ada di $avatarMap bawaan, cocokkan dengan nama
        if (isset($this->avatarMap[$avatarId])) {
            $name = $this->avatarMap[$avatarId];
            $charByName = Character::where('name', $name)->orWhere('slug', Str::slug($name))->first();
            if ($charByName) {
                $charByName->update(['avatar_id' => $avatarId]);
                return $charByName;
            }
        }

        // 3. Auto-Fetch dari Project Amber API (gi.yatta.moe) untuk karakter baru yang belum terdaftar di DB
        try {
            $amberRes = Http::timeout(8)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get("https://gi.yatta.moe/api/v2/en/avatar/{$avatarId}")->json();
            $amberData = $amberRes['data'] ?? null;
            if ($amberData && !empty($amberData['name'])) {
                $name       = trim($amberData['name']);
                $slug       = Str::slug($name);
                $elementMap = [
                    'Ice'      => 'Cryo',
                    'Fire'     => 'Pyro',
                    'Water'    => 'Hydro',
                    'Wind'     => 'Anemo',
                    'Electric' => 'Electro',
                    'Grass'    => 'Dendro',
                    'Rock'     => 'Geo',
                ];
                $weaponMap = [
                    'WEAPON_SWORD_ONE_HAND' => 'Sword',
                    'WEAPON_CLAYMORE'       => 'Claymore',
                    'WEAPON_POLE'           => 'Polearm',
                    'WEAPON_BOW'            => 'Bow',
                    'WEAPON_CATALYST'       => 'Catalyst',
                ];
                $element    = $elementMap[$amberData['element'] ?? ''] ?? 'Pyro';
                $weaponType = $weaponMap[$amberData['weaponType'] ?? ''] ?? 'Sword';
                $rarity     = (int) ($amberData['rank'] ?? 5);
                $iconName   = $amberData['icon'] ?? '';
                $iconUrl    = $iconName ? "https://gi.yatta.moe/assets/UI/{$iconName}.png" : null;

                $existing = Character::where('name', $name)->orWhere('slug', $slug)->first();
                if ($existing) {
                    $existing->update([
                        'avatar_id'   => $avatarId,
                        'element'     => $element,
                        'weapon_type' => $weaponType,
                        'rarity'      => $rarity,
                        'icon_url'    => $iconUrl ?: $existing->icon_url,
                    ]);
                    return $existing;
                }

                return Character::create([
                    'avatar_id'   => $avatarId,
                    'name'        => $name,
                    'slug'        => $slug,
                    'element'     => $element,
                    'weapon_type' => $weaponType,
                    'rarity'      => $rarity,
                    'icon_url'    => $iconUrl,
                ]);
            }
        } catch (\Throwable $e) {
            // Lanjut ke fallback Enka API
        }

        // 4. Auto-Fetch dari Enka Store characters.json
        $charName = $this->getCharacterName($avatarId);
        $character = Character::where('name', $charName)->orWhere('slug', Str::slug($charName))->first();
        if ($character) {
            $character->update(['avatar_id' => $avatarId]);
            return $character;
        }

        return Character::create([
            'avatar_id'   => $avatarId,
            'name'        => $charName,
            'slug'        => Str::slug($charName),
            'element'     => 'Pyro',
            'weapon_type' => 'Sword',
            'rarity'      => 5,
        ]);
    }

    /**
     * Cari atau buat Senjata berdasarkan Game Item ID (Database -> Yatta API -> Enka Kamus)
     */
    public function findOrCreateWeaponByGameId(int $gameId, array $eq = [], ?Character $character = null, array $locEn = []): Weapon
    {
        // 1. Cek langsung dari database via game_id
        if ($gameId > 0) {
            $weapon = Weapon::where('game_id', $gameId)->first();
            if ($weapon) {
                return $weapon;
            }
        }

        $flat     = $eq['flat'] ?? [];
        $icon     = $flat['icon'] ?? '';
        $nameHash = $flat['nameTextMapHash'] ?? '';
        $wName    = $locEn[$nameHash] ?? null;

        // Cek via icon series jika nama belum ada
        $iconSeries = [
            'Sword_Fossil'      => 'Sacrificial Sword',
            'Claymore_Fossil'   => 'Sacrificial Greatsword',
            'Bow_Fossil'        => 'Sacrificial Bow',
            'Catalyst_Fossil'   => 'Sacrificial Fragments',
            'Sword_Zephyrus'    => 'Favonius Sword',
            'Claymore_Zephyrus' => 'Favonius Greatsword',
            'Pole_Zephyrus'     => 'Favonius Lance',
            'Bow_Zephyrus'      => 'Favonius Warbow',
            'Catalyst_Zephyrus' => 'Favonius Codex',
            'Sword_Mitsurugi'   => 'Fillet Blade',
            'Sword_Amenoma'     => 'Haran Geppaku Futsu',
            'Sword_Bakufu'      => 'Amenoma Kageuchi',
            'Pole_Homa'         => 'Staff of Homa',
            'Pole_Santika'      => 'Calamity Queller',
            'Sword_Falcon'      => 'Aquila Favonia',
            'Sword_Narukami'    => 'Mistsplitter Reforged',
            'Bow_Narukami'      => 'Thundering Pulse',
            'Pole_Narukami'     => 'Engulfing Lightning',
            'Catalyst_Narukami' => "Kagura's Verity",
        ];

        if (!$wName && !empty($icon)) {
            foreach ($iconSeries as $subIcon => $seriesName) {
                if (str_contains($icon, $subIcon)) {
                    $wName = $seriesName;
                    break;
                }
            }
        }

        // Cek via nama jika ada di DB
        if ($wName) {
            $existing = Weapon::where('name', $wName)->orWhere('slug', Str::slug($wName))->first();
            if ($existing) {
                if ($gameId > 0 && !$existing->game_id) {
                    $existing->update(['game_id' => $gameId]);
                }
                return $existing;
            }
        }

        // Cek via icon
        if (!empty($icon)) {
            $existing = Weapon::where('icon_url', 'like', "%{$icon}%")->first();
            if ($existing) {
                if ($gameId > 0 && !$existing->game_id) {
                    $existing->update(['game_id' => $gameId]);
                }
                return $existing;
            }
        }

        // 2. Auto-Fetch dari Project Amber API (gi.yatta.moe) untuk senjata baru
        if ($gameId > 0) {
            try {
                $amberRes = Http::timeout(8)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get("https://gi.yatta.moe/api/v2/en/weapon/{$gameId}")->json();
                $amberData = $amberRes['data'] ?? null;
                if ($amberData && !empty($amberData['name'])) {
                    $name       = trim($amberData['name']);
                    $slug       = Str::slug($name);
                    $weaponMap = [
                        'WEAPON_SWORD_ONE_HAND' => 'Sword',
                        'WEAPON_CLAYMORE'       => 'Claymore',
                        'WEAPON_POLE'           => 'Polearm',
                        'WEAPON_BOW'            => 'Bow',
                        'WEAPON_CATALYST'       => 'Catalyst',
                    ];
                    $type       = $weaponMap[$amberData['weaponType'] ?? ''] ?? ($character?->weapon_type ?? 'Sword');
                    $rarity     = (int) ($amberData['rank'] ?? 4);
                    $iconName   = $amberData['icon'] ?? $icon;
                    $iconUrl    = $iconName ? (str_starts_with($iconName, 'http') ? $iconName : "https://gi.yatta.moe/assets/UI/{$iconName}.png") : null;

                    $existing = Weapon::where('name', $name)->orWhere('slug', $slug)->first();
                    if ($existing) {
                        $existing->update([
                            'game_id'   => $gameId,
                            'type'      => $type,
                            'rarity'    => $rarity,
                            'icon_url'  => $iconUrl ?: $existing->icon_url,
                        ]);
                        return $existing;
                    }

                    return Weapon::create([
                        'game_id'   => $gameId,
                        'name'      => $name,
                        'slug'      => $slug,
                        'type'      => $type,
                        'rarity'    => $rarity,
                        'base_atk'  => (int) ($flat['weaponStats'][0]['statValue'] ?? 454),
                        'icon_url'  => $iconUrl,
                    ]);
                }
            } catch (\Throwable $e) {
                // Abaikan jika offline
            }
        }

        // 3. Fallback jika semua lookup gagal
        $wName = $wName ?: ($gameId > 0 ? "Weapon #{$gameId}" : "Unknown Weapon");
        return Weapon::create([
            'game_id'   => $gameId > 0 ? $gameId : null,
            'name'      => $wName,
            'slug'      => Str::slug($wName),
            'type'      => $character?->weapon_type ?? 'Sword',
            'rarity'    => (int) ($flat['rankLevel'] ?? 4),
            'base_atk'  => (int) ($flat['weaponStats'][0]['statValue'] ?? 454),
            'icon_url'  => !empty($icon) ? "https://enka.network/ui/{$icon}.png" : null,
        ]);
    }

    /**
     * Cari atau buat Artifact Set berdasarkan set_id (Database -> Yatta API -> Enka Map)
     */
    public function findOrCreateArtifactSetBySetId(int $setId, array $flat = []): ArtifactSet
    {
        // 1. Cek langsung dari database via set_id
        if ($setId > 0) {
            $set = ArtifactSet::where('set_id', $setId)->first();
            if ($set) {
                return $set;
            }
        }

        $setName = $this->setMap[$setId] ?? null;

        if (!$setName && !empty($flat['icon'])) {
            // Coba deteksi dari nama icon UI_RelicIcon_15020_4
            if (preg_match('/UI_RelicIcon_(\d+)_/i', $flat['icon'], $m)) {
                $parsedId = (int) $m[1];
                $setName = $this->setMap[$parsedId] ?? null;
            }
        }

        if ($setName) {
            $existing = ArtifactSet::where('name', $setName)->orWhere('slug', Str::slug($setName))->first();
            if ($existing) {
                if ($setId > 0 && !$existing->set_id) {
                    $existing->update(['set_id' => $setId]);
                }
                return $existing;
            }
        }

        // 2. Auto-Fetch dari Project Amber API (gi.yatta.moe) untuk artifact set baru
        if ($setId > 0) {
            try {
                $amberRes = Http::timeout(8)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get("https://gi.yatta.moe/api/v2/en/reliquary/{$setId}")->json();
                $amberData = $amberRes['data'] ?? null;
                if ($amberData && !empty($amberData['name'])) {
                    $officialName = trim($amberData['name']);
                    $slug         = Str::slug($officialName);
                    $affix        = $amberData['affixList'] ?? [];
                    $b2           = reset($affix) ?: null;
                    $b4           = next($affix) ?: null;
                    $icon         = $amberData['icon'] ?? ($flat['icon'] ?? null);
                    $iconUrl      = $icon ? (str_starts_with($icon, 'http') ? $icon : "https://enka.network/ui/{$icon}.png") : null;

                    $existing = ArtifactSet::where('name', $officialName)->orWhere('slug', $slug)->first();
                    if ($existing) {
                        $existing->update([
                            'set_id'           => $setId,
                            'two_piece_bonus'  => $b2 ?: $existing->two_piece_bonus,
                            'four_piece_bonus' => $b4 ?: $existing->four_piece_bonus,
                            'icon_url'         => $iconUrl ?: $existing->icon_url,
                        ]);
                        return $existing;
                    }

                    return ArtifactSet::create([
                        'set_id'           => $setId,
                        'name'             => $officialName,
                        'slug'             => $slug,
                        'max_rarity'       => (int) ($flat['rankLevel'] ?? 5),
                        'two_piece_bonus'  => $b2,
                        'four_piece_bonus' => $b4,
                        'icon_url'         => $iconUrl,
                    ]);
                }
            } catch (\Throwable $e) {
                // Abaikan jika offline
            }
        }

        // 3. Fallback jika semua lookup gagal
        $setName = $setName ?: ($setId > 0 ? "Set #{$setId}" : "Unknown Set");
        return ArtifactSet::create([
            'set_id'     => $setId > 0 ? $setId : null,
            'name'       => $setName,
            'slug'       => Str::slug($setName),
            'max_rarity' => (int) ($flat['rankLevel'] ?? 5),
            'icon_url'   => !empty($flat['icon']) ? "https://enka.network/ui/{$flat['icon']}.png" : null,
        ]);
    }

    /**
     * Dapatkan nama karakter dari Avatar ID (Database -> Built-in -> Fallback Amber / Enka)
     */
    public function getCharacterName(int $avatarId): string
    {
        // 1. Cek langsung dari tabel characters di Database
        $dbName = Character::where('avatar_id', $avatarId)->value('name');
        if ($dbName) {
            return $dbName;
        }

        if (isset($this->avatarMap[$avatarId])) {
            return $this->avatarMap[$avatarId];
        }

        // 2. Coba ambil dari cache atau fetch eksternal jika belum ada
        return Cache::remember("enka_avatar_name_{$avatarId}", 86400, function () use ($avatarId) {
            // Coba ke Project Amber (Yatta.moe) API terlebih dahulu
            try {
                $amberRes = Http::timeout(5)->get("https://gi.yatta.moe/api/v2/en/avatar/{$avatarId}")->json();
                if (!empty($amberRes['data']['name'])) {
                    return $amberRes['data']['name'];
                }
            } catch (\Throwable $e) {
                // Abaikan
            }

            // Coba dari Enka Network API Docs Store
            try {
                $chars = $this->getEnkaCharactersMeta();
                $nameHash = $chars[$avatarId]['NameTextMapHash'] ?? null;
                if ($nameHash) {
                    $loc = $this->getEnkaLocEn();
                    if (!empty($loc[$nameHash])) {
                        return $loc[$nameHash];
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan kesalahan fetch eksternal
            }

            return "Character #{$avatarId}";
        });
    }

    /**
     * Sinkronkan artifact showcase dari Enka.Network ke database lokal akun game
     */
    public function syncArtifactsFromEnka(GameAccount $account, ?string $overrideUid = null): array
    {
        $uid = $overrideUid ?: $account->uid;
        $data = $this->fetchEnkaData($uid);

        $playerInfo = $data['playerInfo'] ?? [];
        $avatarList = $data['avatarInfoList'] ?? [];

        if (empty($avatarList)) {
            return [
                'success'           => false,
                'message'           => 'Profil ditemukan (' . ($playerInfo['nickname'] ?? 'Player') . '), namun tidak ada karakter di Character Showcase atau opsi "Tampilkan Detail Karakter" dinonaktifkan di game.',
                'synced_artifacts'  => 0,
                'synced_characters' => 0,
                'player_info'       => $playerInfo,
            ];
        }

        $syncedArtifactsCount = 0;
        $syncedCharacters = [];

        foreach ($avatarList as $avData) {
            $avatarId = (int) ($avData['avatarId'] ?? 0);
            if (!$avatarId) continue;

            $character = $this->findOrCreateCharacterByAvatarId($avatarId);
            $charName  = $character->name;

            $syncedCharacters[$character->id] = $charName;

            // Hapus artifact lama yang terpasang pada karakter ini di akun ini agar digantikan artefak baru
            InventoryArtifact::where('game_account_id', $account->id)
                ->where('equipped_character_id', $character->id)
                ->delete();

            $equipList = $avData['equipList'] ?? [];
            foreach ($equipList as $eq) {
                if (!isset($eq['reliquary'])) {
                    continue; // Skip jika senjata
                }

                $flat = $eq['flat'] ?? [];
                $reliquary = $eq['reliquary'] ?? [];

                // 1. Slot
                $equipType = $flat['equipType'] ?? '';
                $slotKey = $this->slotMap[$equipType] ?? 'flower';

                // 2. Artifact Set
                $setId = (int) ($flat['setId'] ?? 0);
                if (!$setId && !empty($flat['icon']) && preg_match('/UI_RelicIcon_(\d+)_/i', $flat['icon'], $m)) {
                    $setId = (int) $m[1];
                }
                $artSet = $this->findOrCreateArtifactSetBySetId($setId, $flat);

                // 3. Level & Rarity
                // Di Enka: level 21 adalah +20 (level = reliquary.level - 1)
                $level = max(0, ((int) ($reliquary['level'] ?? 1)) - 1);
                $rarity = (int) ($flat['rankLevel'] ?? 5);

                // 4. Main Stat
                $mainProp = $flat['reliquaryMainstat']['mainPropId'] ?? '';
                $mainVal  = $flat['reliquaryMainstat']['statValue'] ?? 0;
                $mainMapping = $this->mainStatPropMap[$mainProp] ?? ['key' => 'hp', 'is_pct' => false];
                $mainStatKey = $mainMapping['key'];
                $mainStatValue = $mainMapping['is_pct'] ? ($mainVal . '%') : number_format($mainVal);

                // 5. Sub-Stats Asli dari In-Game
                $subStats = [];
                $rawSubstats = $flat['reliquarySubstats'] ?? [];
                foreach ($rawSubstats as $sub) {
                    $propId = $sub['appendPropId'] ?? '';
                    $val    = (float) ($sub['statValue'] ?? 0);
                    $mappedKey = $this->subStatPropMap[$propId] ?? null;

                    if ($mappedKey && $val > 0) {
                        $subStats[] = [
                            'key'   => $mappedKey,
                            'value' => $val,
                        ];
                    }
                }

                // 6. Simpan artifact baru ke inventory_artifacts (artefak lama karakter sudah dihapus)
                $artifact = InventoryArtifact::create(
                    [
                        'game_account_id'       => $account->id,
                        'equipped_character_id' => $character->id,
                        'slot_key'              => $slotKey,
                        'artifact_set_id'       => $artSet->id,
                        'rarity'                => $rarity,
                        'level'                 => $level,
                        'main_stat_key'         => $mainStatKey,
                        'main_stat_value'       => $mainStatValue,
                        'sub_stats'             => $subStats,
                        'scanned_at'            => now(),
                    ]
                );

                // 7. Hitung Skor Kualitas Otomatis
                $rule = ArtifactScoringRule::where('character_id', $character->id)->first();
                $this->scorer->scoreAndSave($artifact, $character, $rule);

                $syncedArtifactsCount++;
            }
        }

        $account->update([
            'last_synced_at' => now(),
            'nickname'       => $playerInfo['nickname'] ?? $account->nickname,
        ]);

        return [
            'success'           => true,
            'message'           => "Berhasil mensinkronkan {$syncedArtifactsCount} artifact lengkap dengan sub-stat asli dari " . count($syncedCharacters) . " karakter showcase!",
            'synced_artifacts'  => $syncedArtifactsCount,
            'synced_characters' => count($syncedCharacters),
            'characters'        => array_values($syncedCharacters),
            'player_nickname'   => $playerInfo['nickname'] ?? $account->nickname,
        ];
    }

    /**
     * Dapatkan metadata katalog karakter dari Enka / cache lokal disk
     */
    public function getEnkaCharactersMeta(): array
    {
        return Cache::remember('enka_characters_json', 604800, function () {
            $backupFile = storage_path('app/enka_characters.json');

            try {
                $response = Http::timeout(15)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) GenshinApp/1.0'])
                    ->get('https://raw.githubusercontent.com/EnkaNetwork/API-docs/master/store/characters.json');

                if ($response->successful()) {
                    $json = $response->json();
                    if (is_array($json) && !empty($json)) {
                        try {
                            @file_put_contents($backupFile, json_encode($json));
                        } catch (\Throwable $e) {
                            // Abaikan error tulis disk
                        }
                        return $json;
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('[EnkaNetworkService] Gagal fetch characters.json dari GitHub: ' . $e->getMessage());
            }

            // Fallback ke file backup lokal jika ada
            if (file_exists($backupFile)) {
                $backupContent = @file_get_contents($backupFile);
                $decoded = json_decode($backupContent, true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            }

            return [];
        });
    }

    /**
     * Dapatkan kamus lokalisasi teks Enka
     */
    public function getEnkaLocEn(): array
    {
        return Cache::remember('enka_loc_en', 604800, function () {
            $backupFile = storage_path('app/enka_loc_en.json');

            try {
                $response = Http::timeout(15)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) GenshinApp/1.0'])
                    ->get('https://raw.githubusercontent.com/EnkaNetwork/API-docs/master/store/loc.json');

                if ($response->successful()) {
                    $json = $response->json()['en'] ?? [];
                    if (is_array($json) && !empty($json)) {
                        try {
                            @file_put_contents($backupFile, json_encode($json));
                        } catch (\Throwable $e) {
                            // Abaikan error tulis disk
                        }
                        return $json;
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('[EnkaNetworkService] Gagal fetch loc.json dari GitHub: ' . $e->getMessage());
            }

            if (file_exists($backupFile)) {
                $backupContent = @file_get_contents($backupFile);
                $decoded = json_decode($backupContent, true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            }

            return [];
        });
    }

    /**
     * Ekstraksi level talenta (Normal Attack, Elemental Skill, Elemental Burst)
     * secara akurat dari data Enka.Network, termasuk bonus konstelasi (proudSkillExtraLevelMap).
     */
    public function extractTalentLevels(array $avData, array $charsMeta): array
    {
        $avatarId   = (int) ($avData['avatarId'] ?? 0);
        $skillMap   = $avData['skillLevelMap'] ?? [];
        $proudExtra = $avData['proudSkillExtraLevelMap'] ?? [];
        $depotId    = $avData['skillDepotId'] ?? null;

        if (empty($skillMap)) {
            return [
                'talent_attack' => 1,
                'talent_skill'  => 1,
                'talent_burst'  => 1,
            ];
        }

        // 1. Cari metadata karakter (tangani juga variasi elemen Traveler berdasarkan skillDepotId)
        $meta = null;
        if ($depotId && isset($charsMeta["{$avatarId}-{$depotId}"])) {
            $meta = $charsMeta["{$avatarId}-{$depotId}"];
        } elseif (isset($charsMeta[$avatarId])) {
            $meta = $charsMeta[$avatarId];
        } elseif (isset($charsMeta[(string)$avatarId])) {
            $meta = $charsMeta[(string)$avatarId];
        }

        $skillOrder = $meta['SkillOrder'] ?? [];
        $proudMap   = $meta['ProudMap'] ?? [];

        $na = 1;
        $es = 1;
        $eb = 1;

        // Opsi A: Jika SkillOrder ditemukan di metadata
        if (!empty($skillOrder) && count($skillOrder) >= 3) {
            $idNa = (string) $skillOrder[0];
            $idEs = (string) $skillOrder[1];
            $idEb = (string) $skillOrder[2];

            $na = (int) ($skillMap[$idNa] ?? 1);
            $es = (int) ($skillMap[$idEs] ?? 1);
            $eb = (int) ($skillMap[$idEb] ?? 1);

            // Tambahkan bonus level dari konstelasi jika ada
            if (!empty($proudExtra) && !empty($proudMap)) {
                $proudNa = (string) ($proudMap[$idNa] ?? '');
                $proudEs = (string) ($proudMap[$idEs] ?? '');
                $proudEb = (string) ($proudMap[$idEb] ?? '');

                if ($proudNa && isset($proudExtra[$proudNa])) {
                    $na += (int) $proudExtra[$proudNa];
                }
                if ($proudEs && isset($proudExtra[$proudEs])) {
                    $es += (int) $proudExtra[$proudEs];
                }
                if ($proudEb && isset($proudExtra[$proudEb])) {
                    $eb += (int) $proudExtra[$proudEb];
                }
            }
        } else {
            // Opsi B: Fallback cerdas jika SkillOrder tidak ditemukan (misal karakter baru atau format ID beda)
            // Urutkan key skill ID secara numerik: Normal Attack < Skill < Burst
            $sortedSkills = $skillMap;
            ksort($sortedSkills, SORT_NUMERIC);
            $skillValues = array_values($sortedSkills);

            if (count($skillValues) >= 3) {
                $na = (int) $skillValues[0];
                $es = (int) $skillValues[1];
                $eb = (int) $skillValues[2];
            } elseif (count($skillValues) === 2) {
                $na = (int) $skillValues[0];
                $es = (int) $skillValues[1];
            } elseif (count($skillValues) === 1) {
                $na = (int) $skillValues[0];
            }

            // Jika ada bonus level konstelasi tapi proudMap tidak tersedia
            // (C3/C5 memberikan +3 level pada Skill dan Burst)
            if (!empty($proudExtra)) {
                $extraValues = array_values($proudExtra);
                if (isset($extraValues[0])) {
                    $es += (int) $extraValues[0];
                }
                if (isset($extraValues[1])) {
                    $eb += (int) $extraValues[1];
                }
            }
        }

        return [
            'talent_attack' => min(15, max(1, $na)),
            'talent_skill'  => min(15, max(1, $es)),
            'talent_burst'  => min(15, max(1, $eb)),
        ];
    }

    /**
     * Sinkronkan karakter showcase dari Enka.Network ke inventory_characters
     */
    public function syncCharactersFromEnka(GameAccount $account, ?string $overrideUid = null): array
    {
        $uid = $overrideUid ?: $account->uid;
        $data = $this->fetchEnkaData($uid);

        $playerInfo = $data['playerInfo'] ?? [];
        $avatarList = $data['avatarInfoList'] ?? [];

        if (empty($avatarList)) {
            return [
                'success'           => false,
                'message'           => 'Profil ditemukan (' . ($playerInfo['nickname'] ?? 'Player') . '), namun tidak ada karakter di Character Showcase atau opsi "Tampilkan Detail Karakter" dinonaktifkan di game.',
                'synced_characters' => 0,
                'player_info'       => $playerInfo,
            ];
        }

        $charsMeta = $this->getEnkaCharactersMeta();

        $syncedCount = 0;
        $syncedNames = [];

        foreach ($avatarList as $avData) {
            $avatarId = (int) ($avData['avatarId'] ?? 0);
            if (!$avatarId) continue;

            $character = $this->findOrCreateCharacterByAvatarId($avatarId);
            $charName  = $character->name;

            $level     = (int) ($avData['propMap']['4001']['val'] ?? 1);
            $ascension = (int) ($avData['propMap']['1002']['val'] ?? 0);
            $const     = count($avData['talentIdList'] ?? []);

            $talents   = $this->extractTalentLevels($avData, $charsMeta);

            InventoryCharacter::updateOrCreate(
                [
                    'game_account_id' => $account->id,
                    'character_id'    => $character->id,
                ],
                [
                    'level'         => $level,
                    'ascension'     => $ascension,
                    'constellation' => $const,
                    'talent_attack' => $talents['talent_attack'],
                    'talent_skill'  => $talents['talent_skill'],
                    'talent_burst'  => $talents['talent_burst'],
                    'scanned_at'    => now(),
                ]
            );

            $syncedNames[] = $charName;
            $syncedCount++;
        }

        $account->update([
            'last_synced_at' => now(),
            'nickname'       => $playerInfo['nickname'] ?? $account->nickname,
        ]);

        return [
            'success'           => true,
            'message'           => "Berhasil mensinkronkan {$syncedCount} karakter showcase lengkap dengan level, konstelasi, dan talent!",
            'synced_characters' => $syncedCount,
            'characters'        => $syncedNames,
            'player_nickname'   => $playerInfo['nickname'] ?? $account->nickname,
        ];
    }

    /**
     * Sinkronkan senjata yang terpasang pada karakter showcase ke inventory_weapons
     */
    public function syncWeaponsFromEnka(GameAccount $account, ?string $overrideUid = null): array
    {
        $uid = $overrideUid ?: $account->uid;
        $data = $this->fetchEnkaData($uid);

        $playerInfo = $data['playerInfo'] ?? [];
        $avatarList = $data['avatarInfoList'] ?? [];

        if (empty($avatarList)) {
            return [
                'success'        => false,
                'message'        => 'Profil ditemukan (' . ($playerInfo['nickname'] ?? 'Player') . '), namun tidak ada karakter/senjata di Character Showcase atau opsi "Tampilkan Detail Karakter" dinonaktifkan di game.',
                'synced_weapons' => 0,
                'player_info'    => $playerInfo,
            ];
        }

        $locEn = $this->getEnkaLocEn();

        $syncedCount = 0;
        $syncedNames = [];

        foreach ($avatarList as $avData) {
            $avatarId = (int) ($avData['avatarId'] ?? 0);
            if (!$avatarId) continue;

            $character = $this->findOrCreateCharacterByAvatarId($avatarId);
            $charName  = $character->name;

            foreach ($avData['equipList'] ?? [] as $eq) {
                if (!isset($eq['weapon'])) {
                    continue;
                }

                $wData     = $eq['weapon'];
                $flat      = $eq['flat'] ?? [];
                $itemId    = (int) ($eq['itemId'] ?? 0);

                $weapon = $this->findOrCreateWeaponByGameId($itemId, $eq, $character, $locEn);

                $level      = (int) ($wData['level'] ?? 1);
                $ascension  = (int) ($wData['promoteLevel'] ?? 0);
                $refinement = (int) (reset($wData['affixMap']) ?? 0) + 1;

                InventoryWeapon::updateOrCreate(
                    [
                        'game_account_id'       => $account->id,
                        'equipped_character_id' => $character->id,
                    ],
                    [
                        'weapon_id'  => $weapon->id,
                        'level'      => $level,
                        'ascension'  => $ascension,
                        'refinement' => $refinement,
                        'scanned_at' => now(),
                    ]
                );

                $syncedNames[] = "{$weapon->name} ({$charName})";
                $syncedCount++;
            }
        }

        $account->update([
            'last_synced_at' => now(),
            'nickname'       => $playerInfo['nickname'] ?? $account->nickname,
        ]);

        return [
            'success'        => true,
            'message'        => "Berhasil mensinkronkan {$syncedCount} senjata showcase lengkap dengan level, ascension, dan refinement!",
            'synced_weapons' => $syncedCount,
            'weapons'        => $syncedNames,
            'player_nickname'=> $playerInfo['nickname'] ?? $account->nickname,
        ];
    }

    /**
     * ⚡ SINKRONISASI 1 TOMBOL: Sinkronkan SELURUH Karakter, Senjata, dan Artefak sekaligus dari Enka.Network
     */
    public function syncAllInventoryFromEnka(GameAccount $account, ?string $overrideUid = null): array
    {
        $uid = $overrideUid ?: $account->uid;
        $data = $this->fetchEnkaData($uid);

        $playerInfo = $data['playerInfo'] ?? [];
        $avatarList = $data['avatarInfoList'] ?? [];

        if (empty($avatarList)) {
            return [
                'success'           => false,
                'message'           => 'Profil ditemukan (' . ($playerInfo['nickname'] ?? 'Player') . '), namun tidak ada karakter di Character Showcase atau opsi "Tampilkan Detail Karakter" dinonaktifkan di game.',
                'synced_characters' => 0,
                'synced_weapons'    => 0,
                'synced_artifacts'  => 0,
                'player_info'       => $playerInfo,
            ];
        }

        $charsMeta = $this->getEnkaCharactersMeta();
        $locEn     = $this->getEnkaLocEn();

        $syncedCharsCount = 0;
        $syncedWeaponsCount = 0;
        $syncedArtifactsCount = 0;
        $syncedCharNames = [];

        foreach ($avatarList as $avData) {
            $avatarId = (int) ($avData['avatarId'] ?? 0);
            if (!$avatarId) continue;

            // 1. CARI ATAU BUAT MASTER KARAKTER
            $character = $this->findOrCreateCharacterByAvatarId($avatarId);
            $charName  = $character->name;

            // SINKRONKAN INVENTORY KARAKTER
            $level     = (int) ($avData['propMap']['4001']['val'] ?? 1);
            $ascension = (int) ($avData['propMap']['1002']['val'] ?? 0);
            $const     = count($avData['talentIdList'] ?? []);

            $talents   = $this->extractTalentLevels($avData, $charsMeta);

            InventoryCharacter::updateOrCreate(
                [
                    'game_account_id' => $account->id,
                    'character_id'    => $character->id,
                ],
                [
                    'level'         => $level,
                    'ascension'     => $ascension,
                    'constellation' => $const,
                    'talent_attack' => $talents['talent_attack'],
                    'talent_skill'  => $talents['talent_skill'],
                    'talent_burst'  => $talents['talent_burst'],
                    'scanned_at'    => now(),
                ]
            );

            $syncedCharNames[] = $charName;
            $syncedCharsCount++;

            // Hapus artifact lama milik karakter ini sebelum memasukkan artefak baru dari Enka
            InventoryArtifact::where('game_account_id', $account->id)
                ->where('equipped_character_id', $character->id)
                ->delete();

            // 2. PROSES EQUIPMENT (SENJATA & ARTIFAK)
            $equipList = $avData['equipList'] ?? [];
            foreach ($equipList as $eq) {
                $flat = $eq['flat'] ?? [];

                // ── A. SENJATA ──
                if (isset($eq['weapon'])) {
                    $wData = $eq['weapon'];
                    $nameTextMapHash = $flat['nameTextMapHash'] ?? null;
                    $wName = $nameTextMapHash && isset($locEn[$nameTextMapHash]) ? $locEn[$nameTextMapHash] : null;
                    $icon  = $flat['icon'] ?? '';

                    $iconSeries = [
                        'Sword_Amenoma'     => 'Haran Geppaku Futsu',
                        'Sword_Bakufu'      => 'Amenoma Kageuchi',
                        'Pole_Homa'         => 'Staff of Homa',
                        'Pole_Santika'      => 'Calamity Queller',
                        'Sword_Falcon'      => 'Aquila Favonia',
                        'Sword_Narukami'    => 'Mistsplitter Reforged',
                        'Bow_Narukami'      => 'Thundering Pulse',
                        'Pole_Narukami'     => 'Engulfing Lightning',
                        'Catalyst_Narukami' => "Kagura's Verity",
                    ];
                    if (!$wName && !empty($icon)) {
                        foreach ($iconSeries as $subIcon => $seriesName) {
                            if (str_contains($icon, $subIcon)) {
                                $wName = $seriesName;
                                break;
                            }
                        }
                    }

                    $weapon = null;
                    if ($wName) {
                        $weapon = Weapon::where('name', $wName)
                            ->orWhere('slug', Str::slug($wName))
                            ->first();
                    }
                    if (!$weapon && !empty($icon)) {
                        $weapon = Weapon::where('icon_url', 'like', "%{$icon}%")->first();
                    }
                    if (!$weapon) {
                        $wName = $wName ?: "Weapon #{$eq['itemId']}";
                        $weapon = Weapon::create([
                            'name'       => $wName,
                            'slug'       => Str::slug($wName),
                            'type'       => $character->weapon_type ?? 'Sword',
                            'rarity'     => (int) ($flat['rankLevel'] ?? 4),
                            'base_atk'   => (int) ($flat['weaponStats'][0]['statValue'] ?? 454),
                            'icon_url'   => !empty($icon) ? "https://enka.network/ui/{$icon}.png" : null,
                        ]);
                    }

                    $wLevel      = (int) ($wData['level'] ?? 1);
                    $wAscension  = (int) ($wData['promoteLevel'] ?? 0);
                    $wRefinement = (int) (reset($wData['affixMap']) ?? 0) + 1;

                    InventoryWeapon::updateOrCreate(
                        [
                            'game_account_id'       => $account->id,
                            'equipped_character_id' => $character->id,
                        ],
                        [
                            'weapon_id'  => $weapon->id,
                            'level'      => $wLevel,
                            'ascension'  => $wAscension,
                            'refinement' => $wRefinement,
                            'scanned_at' => now(),
                        ]
                    );
                    $syncedWeaponsCount++;
                }

                // ── B. ARTIFAK ──
                if (isset($eq['reliquary'])) {
                    $reliq = $eq['reliquary'];
                    $equipType = $flat['equipType'] ?? '';
                    $slotKey = $this->slotMap[$equipType] ?? 'flower';

                    $setId = (int) ($flat['setId'] ?? 0);
                    if (!$setId && !empty($flat['icon']) && preg_match('/UI_RelicIcon_(\d+)_/i', $flat['icon'], $m)) {
                        $setId = (int) $m[1];
                    }
                    $artSet = $this->findOrCreateArtifactSetBySetId($setId, $flat);

                    $level = max(0, ((int) ($reliq['level'] ?? 1)) - 1);
                    $rarity = (int) ($flat['rankLevel'] ?? 5);

                    // Main stat
                    $mainProp = $flat['reliquaryMainstat']['mainPropId'] ?? '';
                    $mainVal  = $flat['reliquaryMainstat']['statValue'] ?? 0;
                    $mainMapping = $this->mainStatPropMap[$mainProp] ?? ['key' => 'hp', 'is_pct' => false];
                    $mainStatKey = $mainMapping['key'];
                    $mainStatValue = $mainMapping['is_pct'] ? ($mainVal . '%') : number_format($mainVal);

                    // Substats
                    $subStats = [];
                    foreach ($flat['reliquarySubstats'] ?? [] as $sub) {
                        $propId = $sub['appendPropId'] ?? '';
                        $val    = (float) ($sub['statValue'] ?? 0);
                        $mappedKey = $this->subStatPropMap[$propId] ?? null;

                        if ($mappedKey && $val > 0) {
                            $subStats[] = [
                                'key'   => $mappedKey,
                                'value' => $val,
                            ];
                        }
                    }

                    $invArtifact = InventoryArtifact::create(
                        [
                            'game_account_id'       => $account->id,
                            'equipped_character_id' => $character->id,
                            'slot_key'              => $slotKey,
                            'artifact_set_id'       => $artSet->id,
                            'rarity'                => $rarity,
                            'level'                 => $level,
                            'main_stat_key'         => $mainStatKey,
                            'main_stat_value'       => $mainStatValue,
                            'sub_stats'             => $subStats,
                            'scanned_at'            => now(),
                        ]
                    );

                    // Auto-score artefak
                    try {
                        $rule = ArtifactScoringRule::where('character_id', $character->id)->first();
                        $this->scorer->scoreAndSave($invArtifact, $character, $rule);
                    } catch (\Throwable $scoreEx) {
                        // ignore scoring failure if rule not found
                    }

                    $syncedArtifactsCount++;
                }
            }
        }

        $account->update([
            'last_synced_at' => now(),
            'nickname'       => $playerInfo['nickname'] ?? $account->nickname,
        ]);

        return [
            'success'           => true,
            'message'           => "Berhasil mensinkronkan {$syncedCharsCount} karakter, {$syncedWeaponsCount} senjata, dan {$syncedArtifactsCount} artifact lengkap dari Enka.Network!",
            'synced_characters' => $syncedCharsCount,
            'synced_weapons'    => $syncedWeaponsCount,
            'synced_artifacts'  => $syncedArtifactsCount,
            'characters'        => $syncedCharNames,
            'player_nickname'   => $playerInfo['nickname'] ?? $account->nickname,
        ];
    }


    /**
     * Router sinkronisasi seluruh master database karakter (Project Amber atau Enka Network)
     *
     * @param string $source 'amber' | 'enka'
     */
    public function syncMasterCharacters(string $source = 'amber'): array
    {
        if ($source === 'enka') {
            return $this->syncMasterCharactersFromEnka();
        }

        return $this->syncMasterCharactersFromProjectAmber();
    }

    /**
     * Sinkronisasi master database karakter dari Project Amber (gi.yatta.moe)
     */
    public function syncMasterCharactersFromProjectAmber(string $lang = 'en'): array
    {
        $response = Http::timeout(20)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->get("https://gi.yatta.moe/api/v2/{$lang}/avatar");

        if (!$response->successful()) {
            throw new \RuntimeException('Gagal menghubungi API Project Amber (gi.yatta.moe). Kode HTTP: ' . $response->status());
        }

        $json = $response->json();
        $items = $json['data']['items'] ?? [];

        if (empty($items)) {
            throw new \RuntimeException('Data katalog karakter dari Project Amber kosong.');
        }

        $elementMap = [
            'Ice'      => 'Cryo',
            'Fire'     => 'Pyro',
            'Water'    => 'Hydro',
            'Wind'     => 'Anemo',
            'Electric' => 'Electro',
            'Grass'    => 'Dendro',
            'Rock'     => 'Geo',
        ];

        $weaponMap = [
            'WEAPON_SWORD_ONE_HAND' => 'Sword',
            'WEAPON_CLAYMORE'       => 'Claymore',
            'WEAPON_POLE'           => 'Polearm',
            'WEAPON_BOW'            => 'Bow',
            'WEAPON_CATALYST'       => 'Catalyst',
        ];

        $regionMap = [
            'MONDSTADT' => 'Mondstadt',
            'LIYUE'     => 'Liyue',
            'INAZUMA'   => 'Inazuma',
            'SUMERU'    => 'Sumeru',
            'FONTAINE'  => 'Fontaine',
            'NATLAN'    => 'Natlan',
            'SNEZHNAYA' => 'Snezhnaya',
        ];

        $created = 0;
        $updated = 0;
        $processedTraveler = false;

        foreach ($items as $id => $info) {
            $name = trim($info['name'] ?? '');

            if (!$name || str_starts_with($name, '{') || str_contains($name, '(Trial)') || str_contains($name, '(Test)') || str_starts_with($name, 'Trial ') || str_starts_with($name, 'Test ')) {
                continue;
            }

            // Normalisasi Traveler agar tidak terduplikasi per-elemen
            $idStr = (string)$id;
            if ($name === 'Traveler' || $name === 'Pengembara' || str_starts_with($idStr, '10000005') || str_starts_with($idStr, '10000007')) {
                if ($processedTraveler) {
                    continue;
                }
                $name = 'Traveler';
                $processedTraveler = true;
            }

            $elementRaw = $info['element'] ?? '';
            $element    = $elementMap[$elementRaw] ?? 'Pyro';

            $weaponRaw  = $info['weaponType'] ?? '';
            $weaponType = $weaponMap[$weaponRaw] ?? 'Sword';

            $rarity     = (int) ($info['rank'] ?? 4);

            $regionRaw  = strtoupper($info['region'] ?? '');
            $region     = $regionMap[$regionRaw] ?? ($regionRaw ? ucwords(strtolower($regionRaw)) : null);

            $iconName   = $info['icon'] ?? '';
            $iconUrl    = $iconName ? "https://gi.yatta.moe/assets/UI/{$iconName}.png" : null;

            $slug = Str::slug($name);

            $character = Character::where('avatar_id', (int) $id)
                ->orWhere('slug', $slug)
                ->orWhere('name', $name)
                ->first();

            $dataToSave = [
                'avatar_id'   => (int) $id,
                'element'     => $element,
                'weapon_type' => $weaponType,
                'rarity'      => $rarity,
                'icon_url'    => $iconUrl ?: ($character?->icon_url),
            ];

            if ($region) {
                $dataToSave['region'] = $region;
            }

            if ($character) {
                $character->update($dataToSave);
                $updated++;
            } else {
                $dataToSave['name'] = $name;
                $dataToSave['slug'] = $slug;
                Character::create($dataToSave);
                $created++;
            }
        }

        return [
            'success' => true,
            'source'  => 'Project Amber (gi.yatta.moe)',
            'message' => "Master karakter berhasil disinkronkan dari Project Amber! ({$created} baru ditambahkan, {$updated} diperbarui).",
            'created' => $created,
            'updated' => $updated,
            'total'   => Character::count(),
        ];
    }

    /**
     * Sinkronisasi seluruh master database karakter dari Enka / Genshin data katalog
     */
    public function syncMasterCharactersFromEnka(): array
    {
        $charsJson = Http::timeout(15)->get('https://raw.githubusercontent.com/EnkaNetwork/API-docs/master/store/characters.json')->json();
        $locJson   = Http::timeout(15)->get('https://raw.githubusercontent.com/EnkaNetwork/API-docs/master/store/loc.json')->json()['en'] ?? [];

        if (empty($charsJson)) {
            throw new \RuntimeException('Gagal mengambil data katalog karakter dari Enka Network.');
        }

        $elementMap = [
            'Ice'      => 'Cryo',
            'Fire'     => 'Pyro',
            'Water'    => 'Hydro',
            'Wind'     => 'Anemo',
            'Electric' => 'Electro',
            'Grass'    => 'Dendro',
            'Rock'     => 'Geo',
        ];

        $weaponMap = [
            'WEAPON_SWORD_ONE_HAND' => 'Sword',
            'WEAPON_CLAYMORE'       => 'Claymore',
            'WEAPON_POLE'           => 'Polearm',
            'WEAPON_BOW'            => 'Bow',
            'WEAPON_CATALYST'       => 'Catalyst',
        ];

        $created = 0;
        $updated = 0;

        foreach ($charsJson as $avatarId => $info) {
            $nameHash = $info['NameTextMapHash'] ?? null;
            $name = $nameHash ? ($locJson[$nameHash] ?? null) : null;
            if (!$name || str_starts_with($name, '{') || $name === 'PlayerBoy' || $name === 'PlayerGirl' || str_contains($name, '(Trial)') || str_contains($name, '(Test)') || str_starts_with($name, 'Trial ') || str_starts_with($name, 'Test ')) {
                continue;
            }

            $element    = $elementMap[$info['Element'] ?? ''] ?? 'Pyro';
            $weaponType = $weaponMap[$info['WeaponType'] ?? ''] ?? 'Sword';
            $rarity     = ($info['QualityType'] ?? '') === 'QUALITY_ORANGE' ? 5 : 4;
            $iconName   = $info['SideIconName'] ?? '';
            $iconUrl    = $iconName ? "https://enka.network/ui/{$iconName}.png" : null;

            $slug = Str::slug($name);

            $character = Character::where('avatar_id', (int) $avatarId)
                ->orWhere('slug', $slug)
                ->orWhere('name', $name)
                ->first();

            if ($character) {
                $character->update([
                    'avatar_id'   => (int) $avatarId,
                    'element'     => $element,
                    'weapon_type' => $weaponType,
                    'rarity'      => $rarity,
                    'icon_url'    => $iconUrl ?: $character->icon_url,
                ]);
                $updated++;
            } else {
                Character::create([
                    'avatar_id'   => (int) $avatarId,
                    'name'        => $name,
                    'slug'        => $slug,
                    'element'     => $element,
                    'weapon_type' => $weaponType,
                    'rarity'      => $rarity,
                    'icon_url'    => $iconUrl,
                ]);
                $created++;
            }
        }

        return [
            'success' => true,
            'source'  => 'Enka.Network (API-docs Store)',
            'message' => "Master karakter berhasil disinkronkan dari Enka.Network! ({$created} baru ditambahkan, {$updated} diperbarui).",
            'created' => $created,
            'updated' => $updated,
            'total'   => Character::count(),
        ];
    }

    /**
     * Sinkronisasi seluruh master database senjata dari Enka / Genshin data katalog
     */
    public function syncMasterWeapons(): array
    {
        $weaponsJson = Http::timeout(15)->get('https://raw.githubusercontent.com/EnkaNetwork/API-docs/master/store/gi/weapons.json')->json();
        $locJson     = Http::timeout(15)->get('https://raw.githubusercontent.com/EnkaNetwork/API-docs/master/store/loc.json')->json()['en'] ?? [];
        $giLocJson   = Http::timeout(15)->get('https://raw.githubusercontent.com/EnkaNetwork/API-docs/master/store/gi/locs.json')->json()['en'] ?? [];

        if (empty($weaponsJson)) {
            throw new \RuntimeException('Gagal mengambil data katalog senjata dari Enka Network.');
        }

        $created = 0;
        $updated = 0;

        foreach ($weaponsJson as $weaponId => $info) {
            $hash = (string)($info['NameTextMapHash'] ?? '');
            $name = $locJson[$hash] ?? ($giLocJson[$hash] ?? null);

            if (!$name || str_starts_with($name, '{') || str_contains(strtolower($name), 'test') || str_contains(strtolower($name), 'trial')) {
                continue;
            }

            $icon = $info['Icon'] ?? '';
            $iconName = ltrim($icon, '/');
            $iconUrl = $iconName ? "https://enka.network/{$iconName}" : null;

            // Identifikasi tipe senjata berdasarkan icon atau tipe numerik
            $type = 'Sword';
            if (str_contains($icon, '_Sword_')) {
                $type = 'Sword';
            } elseif (str_contains($icon, '_Claymore_')) {
                $type = 'Claymore';
            } elseif (str_contains($icon, '_Pole_')) {
                $type = 'Polearm';
            } elseif (str_contains($icon, '_Bow_')) {
                $type = 'Bow';
            } elseif (str_contains($icon, '_Catalyst_')) {
                $type = 'Catalyst';
            } else {
                $rawType = $info['WeaponType'] ?? 0;
                $type = match ((int)$rawType) {
                    1 => 'Sword',
                    2 => 'Claymore',
                    3 => 'Bow',
                    4 => 'Polearm',
                    5 => 'Catalyst',
                    default => 'Sword',
                };
            }

            $rarity = (int)($info['Rarity'] ?? 1);

            // Hitung base ATK level 1
            $baseAtk = isset($info['BaseProps']['4']) ? (int)round($info['BaseProps']['4']) : 0;
            if ($baseAtk <= 0) {
                $baseAtk = match ($rarity) {
                    5 => 46,
                    4 => 42,
                    3 => 39,
                    2 => 33,
                    default => 23,
                };
            }

            $slug = Str::slug($name);

            $weapon = Weapon::where('game_id', (int)$weaponId)
                ->orWhere('slug', $slug)
                ->orWhere('name', $name)
                ->first();

            if ($weapon) {
                $weapon->update([
                    'game_id'  => (int)$weaponId,
                    'type'     => $type,
                    'rarity'   => $rarity,
                    'base_atk' => $weapon->base_atk ?: $baseAtk,
                    'icon_url' => $iconUrl ?: $weapon->icon_url,
                ]);
                $updated++;
            } else {
                Weapon::create([
                    'game_id'  => (int)$weaponId,
                    'name'     => $name,
                    'slug'     => $slug,
                    'type'     => $type,
                    'rarity'   => $rarity,
                    'base_atk' => $baseAtk,
                    'icon_url' => $iconUrl,
                ]);
                $created++;
            }
        }

        return [
            'success' => true,
            'message' => "Master senjata berhasil disinkronkan! ({$created} baru ditambahkan, {$updated} diperbarui).",
            'created' => $created,
            'updated' => $updated,
            'total'   => Weapon::count(),
        ];
    }

    /**
     * Sinkronisasi master database artifact sets (Project Amber atau Enka)
     */
    public function syncMasterArtifactSets(string $source = 'amber'): array
    {
        if ($source === 'enka') {
            return $this->syncMasterArtifactSetsFromEnka();
        }

        return $this->syncMasterArtifactSetsFromProjectAmber();
    }

    /**
     * Sinkronisasi master artifact set dari Project Amber (gi.yatta.moe)
     */
    public function syncMasterArtifactSetsFromProjectAmber(): array
    {
        $response = Http::timeout(20)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->get("https://gi.yatta.moe/api/v2/en/reliquary");

        if (!$response->successful()) {
            throw new \RuntimeException('Gagal menghubungi API Project Amber (gi.yatta.moe). Kode HTTP: ' . $response->status());
        }

        $json = $response->json();
        $items = $json['data']['items'] ?? [];

        if (empty($items)) {
            throw new \RuntimeException('Data katalog artifact sets dari Project Amber kosong.');
        }

        $created = 0;
        $updated = 0;

        foreach ($items as $setId => $info) {
            $setId   = (int) ($info['id'] ?? $setId);
            $name    = trim($info['name'] ?? '');

            if (!$name || str_starts_with($name, '{') || str_contains(strtolower($name), 'test') || str_contains(strtolower($name), 'trial')) {
                continue;
            }

            $slug       = Str::slug($name);
            $maxRarity  = !empty($info['levelList']) ? (int) max($info['levelList']) : 5;
            $affixes    = array_values($info['affixList'] ?? []);
            $twoBonus   = $affixes[0] ?? null;
            $fourBonus  = $affixes[1] ?? null;
            $icon       = $info['icon'] ?? '';
            $iconUrl    = $icon ? "https://enka.network/ui/{$icon}.png" : null;

            $set = ArtifactSet::where('set_id', $setId)
                ->orWhere('slug', $slug)
                ->orWhere('name', $name)
                ->first();

            $dataToSave = [
                'set_id'           => $setId,
                'max_rarity'       => $maxRarity,
                'two_piece_bonus'  => $twoBonus ?: ($set?->two_piece_bonus),
                'four_piece_bonus' => $fourBonus ?: ($set?->four_piece_bonus),
                'icon_url'         => $iconUrl ?: ($set?->icon_url),
            ];

            // Lepas set_id pada record lain jika ada bentrok ID historis
            ArtifactSet::where('set_id', $setId)
                ->where('id', '!=', $set ? $set->id : 0)
                ->update(['set_id' => null]);

            if ($set) {
                $set->update($dataToSave);
                $updated++;
            } else {
                $dataToSave['name'] = $name;
                $dataToSave['slug'] = $slug;
                ArtifactSet::create($dataToSave);
                $created++;
            }
        }

        return [
            'success' => true,
            'source'  => 'Project Amber (gi.yatta.moe)',
            'message' => "Master artifact set berhasil disinkronkan dari Project Amber! ({$created} baru ditambahkan, {$updated} diperbarui).",
            'created' => $created,
            'updated' => $updated,
            'total'   => ArtifactSet::count(),
        ];
    }

    /**
     * Sinkronisasi master artifact set dari Enka Network
     */
    public function syncMasterArtifactSetsFromEnka(): array
    {
        $created = 0;
        $updated = 0;

        foreach ($this->setMap as $setId => $name) {
            $slug = Str::slug($name);

            $set = ArtifactSet::where('set_id', $setId)
                ->orWhere('slug', $slug)
                ->orWhere('name', $name)
                ->first();

            if ($set) {
                if (!$set->set_id) {
                    $set->update(['set_id' => $setId]);
                    $updated++;
                }
            } else {
                ArtifactSet::create([
                    'set_id'     => $setId,
                    'name'       => $name,
                    'slug'       => $slug,
                    'max_rarity' => 5,
                ]);
                $created++;
            }
        }

        return [
            'success' => true,
            'source'  => 'Enka.Network (Set Map)',
            'message' => "Master artifact set berhasil disinkronkan dari Enka Network! ({$created} baru ditambahkan, {$updated} diperbarui).",
            'created' => $created,
            'updated' => $updated,
            'total'   => ArtifactSet::count(),
        ];
    }
}

