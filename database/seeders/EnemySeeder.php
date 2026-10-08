<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enemy;
use App\Models\EnemyDrop;

class EnemySeeder extends Seeder
{
    public function run(): void
    {
        $enemiesData = [
            // ─── ELITE ENEMIES ──────────────────────────────────────────────────────────
            [
                'name' => 'Ruin Guard',
                'slug' => 'ruin-guard',
                'category' => 'Elite Enemies',
                'family' => 'Automatons',
                'region' => 'Global',
                'elements' => ['Physical'],
                'description' => 'Ancient humanoid war machine from a lost civilization. Highly resistant to physical attacks but vulnerable at its optical core.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/ruin-guard/icon',
                'mora_gained' => 200,
                'immunities' => [],
                'elemental_res' => ['physical' => 70, 'pyro' => 10, 'hydro' => 10, 'cryo' => 10, 'electro' => 10, 'anemo' => 10, 'geo' => 10, 'dendro' => 10],
                'weakness_elements' => ['Pyro', 'Hydro', 'Cryo', 'Electro', 'Dendro'],
                'recommended_mechanics' => ['bow', 'elemental_dps', 'shielder'],
                'tips_strategy' => 'Tembak bagian mata depan atau belakang menggunakan karakter Panah (Bow) dua kali untuk melumpuhkannya sementara. Hindari serangan Physical karena resistensinya 70%.',
                'drops' => [
                    ['name' => 'Chaos Device', 'rarity' => 2, 'minimum_level' => 1, 'icon_url' => 'https://genshin.jmp.blue/materials/common-ascension/chaos-device'],
                    ['name' => 'Chaos Circuit', 'rarity' => 3, 'minimum_level' => 40, 'icon_url' => 'https://genshin.jmp.blue/materials/common-ascension/chaos-circuit'],
                    ['name' => 'Chaos Core', 'rarity' => 4, 'minimum_level' => 60, 'icon_url' => 'https://genshin.jmp.blue/materials/common-ascension/chaos-core'],
                ]
            ],
            [
                'name' => 'Abyss Herald: Wicked Torrents',
                'slug' => 'abyss-herald-wicked-torrents',
                'category' => 'Elite Enemies',
                'family' => 'The Abyss',
                'region' => 'Global',
                'elements' => ['Hydro'],
                'description' => 'A warrior of the Abyss Order wielding twin water blades. In critical condition, creates an impenetrable Hydro shield.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/abyss-herald/icon',
                'mora_gained' => 600,
                'immunities' => ['Hydro'],
                'elemental_res' => ['hydro' => 50, 'physical' => 10, 'cryo' => 10, 'dendro' => 10, 'electro' => 10],
                'weakness_elements' => ['Cryo', 'Dendro', 'Electro'],
                'recommended_mechanics' => ['freeze', 'bloom', 'shield_breaker', 'healer'],
                'tips_strategy' => 'Ketika HP tersisa sedikit, ia mengaktifkan shield Hydro tebal. Gunakan karakter Cryo untuk membekukannya (Freeze) atau Dendro (Bloom) untuk menghancurkan shieldnya dengan cepat.',
                'drops' => [
                    ['name' => 'Gloomy Statuette', 'rarity' => 2, 'minimum_level' => 1, 'icon_url' => null],
                    ['name' => 'Dark Statuette', 'rarity' => 3, 'minimum_level' => 40, 'icon_url' => null],
                    ['name' => 'Deathly Statuette', 'rarity' => 4, 'minimum_level' => 60, 'icon_url' => null],
                ]
            ],
            [
                'name' => 'Abyss Lector: Fathomless Flames',
                'slug' => 'abyss-lector-fathomless-flames',
                'category' => 'Elite Enemies',
                'family' => 'The Abyss',
                'region' => 'Global',
                'elements' => ['Pyro'],
                'description' => 'An Abyss scholar manipulating dark flames that burn and deplete the active character party energy.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/abyss-lector/icon',
                'mora_gained' => 600,
                'immunities' => ['Pyro'],
                'elemental_res' => ['pyro' => 50, 'hydro' => 10, 'cryo' => 10, 'electro' => 10],
                'weakness_elements' => ['Hydro'],
                'recommended_mechanics' => ['vaporize', 'shield_breaker', 'shielder'],
                'tips_strategy' => 'Perisai Pyro-nya sangat cepat hancur oleh aplikasi Hydro bertubi-tubi (Hydro Catalyst/Sword seperti Xingqiu, Yelan, Ayato, Neuvillette). Hancurkan orb api di sekitarnya menggunakan Hydro.',
                'drops' => [
                    ['name' => 'Gloomy Statuette', 'rarity' => 2, 'minimum_level' => 1, 'icon_url' => null],
                    ['name' => 'Dark Statuette', 'rarity' => 3, 'minimum_level' => 40, 'icon_url' => null],
                    ['name' => 'Deathly Statuette', 'rarity' => 4, 'minimum_level' => 60, 'icon_url' => null],
                ]
            ],
            [
                'name' => 'Fatui Skirmisher - Electrohammer Vanguard',
                'slug' => 'fatui-skirmisher-electrohammer',
                'category' => 'Elite Enemies',
                'family' => 'Fatui',
                'region' => 'Global',
                'elements' => ['Electro'],
                'description' => 'A brute soldier armed with an electric warhammer. Possesses high defense when armored with Electro shield.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/fatui-skirmisher/icon',
                'mora_gained' => 200,
                'immunities' => [],
                'elemental_res' => ['electro' => 100, 'cryo' => 10, 'physical' => -20],
                'weakness_elements' => ['Cryo'],
                'recommended_mechanics' => ['superconduct', 'shield_breaker', 'cryo_application'],
                'tips_strategy' => 'WAJIB membawa karakter Cryo. Tanpa Cryo, perisai Electrohammer sangat tebal dan sulit ditembus. Superconduct juga mereduksi physical resistance-nya.',
                'drops' => [
                    ['name' => 'Recruit\'s Insignia', 'rarity' => 1, 'minimum_level' => 1, 'icon_url' => null],
                    ['name' => 'Sergeant\'s Insignia', 'rarity' => 2, 'minimum_level' => 40, 'icon_url' => null],
                    ['name' => 'Lieutenant\'s Insignia', 'rarity' => 3, 'minimum_level' => 60, 'icon_url' => null],
                ]
            ],
            [
                'name' => 'Mirror Maiden',
                'slug' => 'mirror-maiden',
                'category' => 'Elite Enemies',
                'family' => 'Fatui',
                'region' => 'Inazuma',
                'elements' => ['Hydro'],
                'description' => 'Fatui operative manipulating Hydro mirrors to trap opponents in spatial refraction cages.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/mirror-maiden/icon',
                'mora_gained' => 200,
                'immunities' => [],
                'elemental_res' => ['hydro' => 50, 'physical' => -20, 'cryo' => 10, 'pyro' => 10],
                'weakness_elements' => ['Cryo', 'Pyro', 'Dendro'],
                'recommended_mechanics' => ['freeze', 'dps_burst', 'shielder'],
                'tips_strategy' => 'Bekukan dengan Cryo agar tidak bisa berteleportasi dan menjebak karakter. Sangat rentan terhadap Physical damage saat tidak dalam status Polar Glass.',
                'drops' => [
                    ['name' => 'Dismal Prism', 'rarity' => 2, 'minimum_level' => 1, 'icon_url' => null],
                    ['name' => 'Crystal Prism', 'rarity' => 3, 'minimum_level' => 40, 'icon_url' => null],
                    ['name' => 'Polarizing Prism', 'rarity' => 4, 'minimum_level' => 60, 'icon_url' => null],
                ]
            ],

            // ─── NORMAL BOSSES ──────────────────────────────────────────────────────────
            [
                'name' => 'Pyro Hypostasis',
                'slug' => 'pyro-hypostasis',
                'category' => 'Normal Bosses',
                'family' => 'Elemental Lifeforms',
                'region' => 'Inazuma',
                'elements' => ['Pyro'],
                'description' => 'Elemental cube encased in pure blazing Pyro. Immune to all Pyro damage and protected by a Pyro barrier.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/pyro-hypostasis/icon',
                'mora_gained' => 6000,
                'immunities' => ['Pyro'],
                'elemental_res' => ['pyro' => 999, 'hydro' => 10, 'cryo' => 10, 'electro' => 10],
                'weakness_elements' => ['Hydro'],
                'recommended_mechanics' => ['hydro_enabler', 'shield_breaker', 'burst_dps'],
                'tips_strategy' => 'KEBAL terhadap semua serangan Pyro! Karakter Hydro (seperti Xingqiu, Yelan, Kokomi, Mona, Barbara) mutlak dibutuhkan untuk memadamkan perisai api dan menghancurkan tinder saat fase revival.',
                'drops' => [
                    ['name' => 'Smoldering Pearl', 'rarity' => 4, 'minimum_level' => 30, 'icon_url' => null],
                    ['name' => 'Agnidus Agate Gemstone', 'rarity' => 5, 'minimum_level' => 75, 'icon_url' => null],
                    ['name' => 'Agnidus Agate Chunk', 'rarity' => 4, 'minimum_level' => 60, 'icon_url' => null],
                    ['name' => 'Agnidus Agate Fragment', 'rarity' => 3, 'minimum_level' => 40, 'icon_url' => null],
                ]
            ],
            [
                'name' => 'Primo Geovishap',
                'slug' => 'primo-geovishap',
                'category' => 'Normal Bosses',
                'family' => 'Mystical Beasts',
                'region' => 'Liyue',
                'elements' => ['Geo', 'Pyro', 'Hydro', 'Cryo', 'Electro'],
                'description' => 'An ancient dragon behemoth that can absorb elemental energy and unleash catastrophic Primordial Showers.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/primo-geovishap/icon',
                'mora_gained' => 6000,
                'immunities' => [],
                'elemental_res' => ['geo' => 50, 'physical' => 30, 'pyro' => 10, 'hydro' => 10],
                'weakness_elements' => ['Geo'],
                'recommended_mechanics' => ['shielder', 'shield_counter', 'claymore'],
                'tips_strategy' => 'WAJIB membawa karakter Shielder (Zhongli, Noelle, Layla, Diona, Thoma). Saat boss mengeluarkan serangan Primordial Shower, pantulkan kembali serangannya dengan perisai untuk memberikan damage masif pada dirinya sendiri.',
                'drops' => [
                    ['name' => 'Juvenile Jade', 'rarity' => 4, 'minimum_level' => 30, 'icon_url' => null],
                    ['name' => 'Prithiva Topaz Gemstone', 'rarity' => 5, 'minimum_level' => 75, 'icon_url' => null],
                ]
            ],

            // ─── WEEKLY BOSSES ──────────────────────────────────────────────────────────
            [
                'name' => 'Childe (Tartaglia)',
                'slug' => 'childe',
                'category' => 'Weekly Bosses',
                'family' => 'Fatui',
                'region' => 'Liyue',
                'elements' => ['Hydro', 'Electro'],
                'description' => 'Eleventh of the Fatui Harbingers. Possesses Hydro Vision and Electro Delusion across three intense battle phases.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/childe/icon',
                'mora_gained' => 7500,
                'immunities' => [],
                'elemental_res' => ['hydro' => 50, 'electro' => 50, 'pyro' => 0, 'cryo' => 0, 'physical' => 0],
                'weakness_elements' => ['Pyro', 'Cryo', 'Dendro'],
                'recommended_mechanics' => ['shielder', 'burst_dps', 'dodge'],
                'tips_strategy' => 'Hindari serangan bertubi-tubi agar tidak terkena Riptide mark. Sentuh dinding arena bertirai Pyro/Cryo untuk menghapus mark. Bawa Shielder untuk menahan serangan one-shot panah paus.',
                'drops' => [
                    ['name' => 'Tusk of Monoceros Caeli', 'rarity' => 5, 'minimum_level' => 70, 'icon_url' => null],
                    ['name' => 'Shard of a Foul Legacy', 'rarity' => 5, 'minimum_level' => 70, 'icon_url' => null],
                    ['name' => 'Shadow of the Warrior', 'rarity' => 5, 'minimum_level' => 70, 'icon_url' => null],
                ]
            ],
            [
                'name' => 'Azhdaha',
                'slug' => 'azhdaha',
                'category' => 'Weekly Bosses',
                'family' => 'Mystical Beasts',
                'region' => 'Liyue',
                'elements' => ['Geo', 'Pyro', 'Hydro', 'Cryo', 'Electro'],
                'description' => 'An enormous Earth Dragon. Infuses itself with varying dual elements and inflicts severe damage over time without shields.',
                'icon_url' => 'https://genshin.jmp.blue/boss/weekly-boss/azhdaha/icon',
                'mora_gained' => 7500,
                'immunities' => [],
                'elemental_res' => ['geo' => 70, 'physical' => 40],
                'weakness_elements' => ['Dendro', 'Pyro', 'Cryo', 'Hydro'],
                'recommended_mechanics' => ['shielder', 'healer', 'ranged_dps'],
                'tips_strategy' => 'Membawa Shielder tebal (seperti Zhongli) adalah keharusan. Jika terkena serangannya tanpa perisai, karakter akan terkena status DoT (Damage over Time) yang sangat mematikan.',
                'drops' => [
                    ['name' => 'Bloodjade Branch', 'rarity' => 5, 'minimum_level' => 70, 'icon_url' => null],
                    ['name' => 'Dragon Lord\'s Crown', 'rarity' => 5, 'minimum_level' => 70, 'icon_url' => null],
                    ['name' => 'Gilded Scale', 'rarity' => 5, 'minimum_level' => 70, 'icon_url' => null],
                ]
            ],

            // ─── COMMON ENEMIES ─────────────────────────────────────────────────────────
            [
                'name' => 'Hydro Slime',
                'slug' => 'hydro-slime',
                'category' => 'Common Enemies',
                'family' => 'Elemental Lifeforms',
                'region' => 'Global',
                'elements' => ['Hydro'],
                'description' => 'A small monster created by the sedimentation of Hydro elements.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/slime/icon',
                'mora_gained' => 15,
                'immunities' => ['Hydro'],
                'elemental_res' => ['hydro' => 999, 'pyro' => 10, 'cryo' => 10, 'electro' => 10, 'dendro' => 10],
                'weakness_elements' => ['Cryo', 'Pyro', 'Dendro', 'Electro'],
                'recommended_mechanics' => ['freeze', 'hyperbloom', 'vaporize'],
                'tips_strategy' => 'Kebal terhadap Hydro. Serangan Cryo akan langsung membekukannya permanen, sedangkan Dendro memicu Bloom tanpa perlu aplikasi Hydro dari luar.',
                'drops' => [
                    ['name' => 'Slime Condensate', 'rarity' => 1, 'minimum_level' => 1, 'icon_url' => null],
                    ['name' => 'Slime Secretions', 'rarity' => 2, 'minimum_level' => 40, 'icon_url' => null],
                    ['name' => 'Slime Concentrate', 'rarity' => 3, 'minimum_level' => 60, 'icon_url' => null],
                ]
            ],
            [
                'name' => 'Specter',
                'slug' => 'specter',
                'category' => 'Common Enemies',
                'family' => 'Elemental Lifeforms',
                'region' => 'Inazuma',
                'elements' => ['Anemo', 'Geo', 'Hydro', 'Pyro', 'Electro', 'Cryo'],
                'description' => 'A high-density elemental creature that floats in the air and explodes violently with Fury upon death.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/specter/icon',
                'mora_gained' => 45,
                'immunities' => [],
                'elemental_res' => ['anemo' => 20, 'geo' => 20],
                'weakness_elements' => ['Pyro', 'Hydro', 'Electro', 'Dendro'],
                'recommended_mechanics' => ['bow', 'catalyst', 'ranged_dps', 'single_target'],
                'tips_strategy' => 'Musuh melayang yang menyebalkan bagi karakter jarak dekat (melee). Karakter Catalyst atau Bow dengan auto-target (seperti Yoimiya, Yelan, Tighnari, Yanfei) sangat efektif.',
                'drops' => [
                    ['name' => 'Spectral Husk', 'rarity' => 1, 'minimum_level' => 1, 'icon_url' => null],
                    ['name' => 'Spectral Heart', 'rarity' => 2, 'minimum_level' => 40, 'icon_url' => null],
                    ['name' => 'Spectral Nucleus', 'rarity' => 3, 'minimum_level' => 60, 'icon_url' => null],
                ]
            ],
            [
                'name' => 'Kairagi: Dancing Thunder',
                'slug' => 'kairagi-dancing-thunder',
                'category' => 'Elite Enemies',
                'family' => 'Other Human Factions',
                'region' => 'Inazuma',
                'elements' => ['Electro'],
                'description' => 'Wandering samurai who infuses his katana with lightning talismans. Enters an enraged healing state if his partner falls first.',
                'icon_url' => 'https://genshin.jmp.blue/enemies/kairagi/icon',
                'mora_gained' => 200,
                'immunities' => [],
                'elemental_res' => ['electro' => 20, 'physical' => 10],
                'weakness_elements' => ['Pyro', 'Cryo', 'Dendro'],
                'recommended_mechanics' => ['freeze', 'overload', 'burst_aoe'],
                'tips_strategy' => 'Jika bertarung bersama Kairagi Fiery Might, usahakan kurangi HP kedua samurai secara seimbang dan kalahkan bersamaan agar tidak memicu pemulihan HP & status kekebalan CC.',
                'drops' => [
                    ['name' => 'Old Handguard', 'rarity' => 1, 'minimum_level' => 1, 'icon_url' => null],
                    ['name' => 'Kageuchi Handguard', 'rarity' => 2, 'minimum_level' => 40, 'icon_url' => null],
                    ['name' => 'Famed Handguard', 'rarity' => 3, 'minimum_level' => 60, 'icon_url' => null],
                ]
            ]
        ];

        foreach ($enemiesData as $data) {
            $drops = $data['drops'] ?? [];
            unset($data['drops']);

            $enemy = Enemy::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Bersihkan drops lama lalu buat baru
            $enemy->drops()->delete();
            foreach ($drops as $drop) {
                EnemyDrop::create(array_merge($drop, [
                    'enemy_id' => $enemy->id,
                ]));
            }
        }
    }
}