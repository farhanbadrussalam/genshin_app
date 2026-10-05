<?php

namespace Database\Seeders;

use App\Models\ArtifactSet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArtifactSetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sets = [
            [
                'name' => 'Emblem of Severed Fate',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Energy Recharge +20%',
                'four_piece_bonus' => 'Meningkatkan DMG Elemental Burst sebesar 25% dari Energy Recharge. Efek ini dapat meningkatkan DMG Burst hingga maksimum 75%.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15020_4.png',
            ],
            [
                'name' => 'Deepwood Memories',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Dendro DMG Bonus +15%',
                'four_piece_bonus' => 'Setelah Elemental Skill atau Burst mengenai musuh, Dendro RES target akan berkurang 30% selama 8 detik. Efek ini dapat terpicu bahkan saat karakter tidak berada di medan pertempuran.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15025_4.png',
            ],
            [
                'name' => 'Gilded Dreams',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Elemental Mastery +80',
                'four_piece_bonus' => 'Dalam 8 detik setelah memicu Reaksi Elemental, karakter mendapatkan buff sesuai elemen anggota party lainnya: ATK +14% untuk setiap anggota berelemen sama, dan Elemental Mastery +50 untuk setiap anggota berelemen berbeda.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15026_4.png',
            ],
            [
                'name' => 'Marechaussee Hunter',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Normal dan Charged Attack DMG +15%',
                'four_piece_bonus' => 'Saat HP saat ini bertambah atau berkurang, CRIT Rate meningkat 12% selama 5 detik. Dapat ditumpuk hingga 3 lapis (maksimum 36% CRIT Rate).',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15031_4.png',
            ],
            [
                'name' => 'Golden Troupe',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Meningkatkan Elemental Skill DMG sebesar 20%',
                'four_piece_bonus' => 'Meningkatkan Elemental Skill DMG sebesar 25%. Selain itu, saat karakter tidak berada di medan tempur, Elemental Skill DMG meningkat lagi sebesar 25%.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15032_4.png',
            ],
            [
                'name' => 'Viridescent Venerer',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Anemo DMG Bonus +15%',
                'four_piece_bonus' => 'Meningkatkan Swirl DMG sebesar 60%. Mengurangi Elemental RES musuh terhadap elemen yang terinfusi Swirl sebesar 40% selama 10 detik.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15002_4.png',
            ],
            [
                'name' => 'Noblesse Oblige',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Elemental Burst DMG +20%',
                'four_piece_bonus' => 'Setelah melancarkan Elemental Burst, meningkatkan ATK seluruh anggota party sebesar 20% selama 12 detik. Efek ini tidak dapat ditumpuk.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15007_4.png',
            ],
            [
                'name' => 'Crimson Witch of Flames',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Pyro DMG Bonus +15%',
                'four_piece_bonus' => 'Meningkatkan Overloaded, Burning, dan Burgeon DMG sebesar 40%. Meningkatkan Vaporize dan Melt DMG sebesar 15%. Menggunakan Elemental Skill meningkatkan efek 2-piece sebesar 50% selama 10 detik (maksimal 3 tumpukan).',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15006_4.png',
            ],
            [
                'name' => 'Blizzard Strayer',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Cryo DMG Bonus +15%',
                'four_piece_bonus' => 'Saat karakter menyerang musuh yang terkena Cryo, CRIT Rate meningkat 20%. Jika musuh dalam kondisi Frozen, CRIT Rate meningkat tambahan 20%.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_14001_4.png',
            ],
            [
                'name' => "Gladiator's Finale",
                'max_rarity' => 5,
                'two_piece_bonus' => 'ATK +18%',
                'four_piece_bonus' => 'Jika pengguna menggunakan Sword, Claymore, atau Polearm, meningkatkan Normal Attack DMG sebesar 35%.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15001_4.png',
            ],
            [
                'name' => "Shimenawa's Reminiscence",
                'max_rarity' => 5,
                'two_piece_bonus' => 'ATK +18%',
                'four_piece_bonus' => 'Saat melancarkan Elemental Skill, jika karakter memiliki 15 Energy atau lebih, karakter akan kehilangan 15 Energy dan Normal/Charged/Plunging Attack DMG meningkat 50% selama 10 detik.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15019_4.png',
            ],
            [
                'name' => 'Husk of Opulent Dreams',
                'max_rarity' => 5,
                'two_piece_bonus' => 'DEF +30%',
                'four_piece_bonus' => 'Karakter memperoleh tumpukan Curiosity: saat aktif di medan pertempuran mengenai musuh dengan Geo (1 lapis tiap 0.3s); saat di luar medan pertempuran (1 lapis tiap 3s). Setiap lapis memberi DEF +6% dan Geo DMG +6% (maksimal 4 lapis).',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15021_4.png',
            ],
            [
                'name' => 'Ocean-Hued Clam',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Healing Bonus +15%',
                'four_piece_bonus' => 'Memunculkan Sea-Dyed Foam saat memulihkan HP anggota party. Busa mengumpulkan jumlah pemulihan HP hingga 30.000 HP selama 3 detik, lalu meledak memberikan Physical DMG sebesar 90% dari total pemulihan.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15022_4.png',
            ],
            [
                'name' => 'Obsidian Codex',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Saat karakter berada dalam Nightsoul\'s Blessing dan di medan pertempuran, DMG meningkat 15%',
                'four_piece_bonus' => 'Setelah karakter mengonsumsi 1 poin Nightsoul di medan pertempuran, CRIT Rate meningkat 40% selama 6 detik. Efek ini dapat terpicu sekali setiap 1 detik.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15038_4.png',
            ],
            [
                'name' => 'Scroll of the Hero of Cinder City',
                'max_rarity' => 5,
                'two_piece_bonus' => 'Saat anggota party di sekitar memicu Nightsoul Burst, karakter memulihkan 6 Elemental Energy.',
                'four_piece_bonus' => 'Setelah karakter memicu reaksi elemental terkait elemen mereka, seluruh anggota party memperoleh 12% Elemental DMG Bonus untuk elemen terkait selama 15 detik. Jika dalam Nightsoul\'s Blessing, bonus bertambah 28%.',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15037_4.png',
            ],
            [
                'name' => 'Fragment of Harmonic Whimsy',
                'max_rarity' => 5,
                'two_piece_bonus' => 'ATK +18%',
                'four_piece_bonus' => 'Saat nilai Bond of Life bertambah atau berkurang, karakter menghasilkan 18% DMG lebih banyak selama 6 detik (maksimal 3 tumpukan / 54% DMG).',
                'icon_url' => 'https://enka.network/ui/UI_RelicIcon_15035_4.png',
            ],
        ];

        foreach ($sets as $set) {
            ArtifactSet::updateOrCreate(
                ['name' => $set['name']],
                [
                    'slug'             => Str::slug($set['name']),
                    'max_rarity'       => $set['max_rarity'],
                    'two_piece_bonus'  => $set['two_piece_bonus'],
                    'four_piece_bonus' => $set['four_piece_bonus'],
                    'icon_url'         => $set['icon_url'],
                ]
            );
        }
    }
}
