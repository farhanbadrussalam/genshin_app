<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\family;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $families = [
            // Common Enemy Drops
            'Slime',
            'Hilichurl Mask',
            'Arrowhead',
            'Samachurl Scroll',
            'Treasure Hoarder Insignia',
            'Fatui Insignia',
            'Whopperflower Nectar',
            'Hilichurl Horn',
            'Ley Line',
            'Chaos Parts',
            'Mist Grass',
            'Sacrificial Knife',
            'Bone Shard',
            'Handguard',
            'Specter Nucleus',
            'Ruin Sentinel Gear',
            'Mirror Maiden Prism',
            'Fungal Spore',
            'Eremite Redbrocade',
            'Ruin Drake Chaos',
            'Primal Obelisk Prism',
            'Transoceanic Pearl / Fontemer',
            'Clockwork Meka Gear',
            'Tainted Water Drop',
            'Operative Pocket Watch',
            'Saurian Fang',

            // Talent Books
            'Freedom (Kebebasan)',
            'Resistance (Perlawanan)',
            'Ballad (Puisi)',
            'Prosperity (Kemakmuran)',
            'Diligence (Kerajinan)',
            'Gold (Emas)',
            'Transience (Fana)',
            'Elegance (Elegan)',
            'Light (Cahaya)',
            'Admonition (Nasihat)',
            'Ingenuity (Keselarasan)',
            'Praxis (Praksis)',
            'Equity (Keadilan)',
            'Justice (Kehakiman)',
            'Order (Ketertiban)',
            'Contention (Perselisihan)',
            'Kindling (Kobaran Api)',
            'Conflict (Konflik)',

            // Elemental Gems
            'Agnidus Agate (Pyro)',
            'Varunada Lazurite (Hydro)',
            'Nagadus Emerald (Dendro)',
            'Vajrada Amethyst (Electro)',
            'Vayuda Turquoise (Anemo)',
            'Shivada Jade (Cryo)',
            'Prithiva Topaz (Geo)',

            // Weapon Ascension Materials
            'Decarabian',
            'Boreal Wolf',
            'Dandelion Gladiator',
            'Guyun',
            'Mist Veiled',
            'Aerosiderite',
            'Distant Sea',
            'Narukami',
            'Mask of Kijin',
            'Forest Dew',
            'Oasis Garden',
            'Scorching Sun',
            'Sacred Dewdrop',
            'Pure Pristine Sea',
            'Ancient Chord',
            'Deliberation',

            // Lainnya
            'Other'
        ];

        foreach ($families as $name) {
            family::firstOrCreate(['name' => $name]);
        }

        $this->call([
            MaterialSeeder::class,
            CharacterSeeder::class,
            WeaponSeeder::class,
            ArtifactSetSeeder::class,
            EnemySeeder::class,
        ]);
    }
}
