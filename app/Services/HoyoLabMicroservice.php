<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * HoyoLabMicroservice
 *
 * Service class yang bertugas sebagai jembatan antara Laravel dan
 * Python FastAPI microservice (genshin.py). Semua komunikasi HTTP ke
 * microservice dilakukan di sini menggunakan Illuminate\Http\Client.
 */
class HoyoLabMicroservice
{
    /**
     * Base URL microservice Python.
     */
    private string $baseUrl;

    /**
     * Timeout request dalam detik.
     */
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.hoyolab_microservice.url', 'http://localhost:8001'),
            '/'
        );

        $this->timeout = (int) config('services.hoyolab_microservice.timeout', 30);
    }

    // ??? Private Helpers ??????????????????????????????????????????????????????

    /**
     * Membuat HTTP client yang sudah dikonfigurasi dengan base URL dan timeout.
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Service-Source' => 'laravel-genshin-app',
            ]);
    }

    // ??? Public API ???????????????????????????????????????????????????????????

    /**
     * Mengambil daftar karakter Genshin Impact milik pemain dari HoYoLAB
     */
    public function getCharacters(string $ltuid_v2, string $ltoken_v2, int $uid): array
    {
        Log::info("[HoyoLabMicroservice] Mengirim request karakter untuk UID: {$uid}");

        try {
            $response = $this->client()->post('/api/genshin/characters', [
                'ltuid_v2'  => $ltuid_v2,
                'ltoken_v2' => $ltoken_v2,
                'uid'       => $uid,
            ]);
        } catch (ConnectionException $e) {
            Log::error("[HoyoLabMicroservice] Gagal terhubung ke microservice: {$e->getMessage()}");
            throw new RuntimeException(
                "Tidak dapat terhubung ke HoYoLAB microservice di [{$this->baseUrl}]. " .
                "Pastikan container Docker sudah berjalan.",
                previous: $e
            );
        }

        if ($response->failed()) {
            $errorBody = $response->json('detail', []);
            $errorCode = is_array($errorBody) ? ($errorBody['error'] ?? 'UNKNOWN') : 'UNKNOWN';
            $errorMsg  = is_array($errorBody) ? ($errorBody['message'] ?? $response->body()) : $response->body();

            Log::warning("[HoyoLabMicroservice] API error [{$response->status()} - {$errorCode}]: {$errorMsg}");

            throw new RuntimeException($errorMsg, $response->status());
        }

        $data = $response->json();
        Log::info("[HoyoLabMicroservice] Berhasil ambil {$data['total_characters']} karakter untuk UID {$uid}.");

        return $data;
    }

    /**
     * Mengambil Real-Time Notes (Resin, Realm Currency, Expedisi, Komisi)
     */
    public function getRealtimeNotes(string $ltuid_v2, string $ltoken_v2, int $uid): array
    {
        Log::info("[HoyoLabMicroservice] Mengambil Real-Time Notes untuk UID: {$uid}");

        try {
            $response = $this->client()->post('/api/genshin/notes', [
                'ltuid_v2'  => $ltuid_v2,
                'ltoken_v2' => $ltoken_v2,
                'uid'       => $uid,
            ]);
        } catch (ConnectionException $e) {
            Log::error("[HoyoLabMicroservice] Gagal terhubung ke microservice: {$e->getMessage()}");
            throw new RuntimeException(
                "Tidak dapat terhubung ke HoYoLAB microservice di [{$this->baseUrl}].",
                previous: $e
            );
        }

        if ($response->failed()) {
            $errorBody = $response->json('detail', []);
            $errorMsg  = is_array($errorBody) ? ($errorBody['message'] ?? $response->body()) : $response->body();
            Log::warning("[HoyoLabMicroservice] Error ambil notes UID {$uid}: {$errorMsg}");
            throw new RuntimeException($errorMsg, $response->status());
        }

        return $response->json();
    }

    /**
     * Menjalankan klaim Daily Check-in HoYoLAB
     */
    public function claimDailyCheckin(string $ltuid_v2, string $ltoken_v2, int $uid): array
    {
        Log::info("[HoyoLabMicroservice] Menjalankan Daily Check-in untuk UID: {$uid}");

        try {
            $response = $this->client()->post('/api/genshin/daily-checkin', [
                'ltuid_v2'  => $ltuid_v2,
                'ltoken_v2' => $ltoken_v2,
                'uid'       => $uid,
            ]);
        } catch (ConnectionException $e) {
            Log::error("[HoyoLabMicroservice] Gagal terhubung ke microservice: {$e->getMessage()}");
            throw new RuntimeException(
                "Tidak dapat terhubung ke HoYoLAB microservice di [{$this->baseUrl}].",
                previous: $e
            );
        }

        if ($response->failed()) {
            $errorBody = $response->json('detail', []);
            $errorMsg  = is_array($errorBody) ? ($errorBody['message'] ?? $response->body()) : $response->body();
            Log::warning("[HoyoLabMicroservice] Error daily checkin UID {$uid}: {$errorMsg}");
            throw new RuntimeException($errorMsg, $response->status());
        }

        return $response->json();
    }

    /**
     * Mengambil status check-in dan riwayat klaim hadiah terakhir
     */
    public function getDailyCheckinStatus(string $ltuid_v2, string $ltoken_v2, int $uid): array
    {
        Log::info("[HoyoLabMicroservice] Mengambil status checkin untuk UID: {$uid}");

        try {
            $response = $this->client()->post('/api/genshin/daily-checkin/status', [
                'ltuid_v2'  => $ltuid_v2,
                'ltoken_v2' => $ltoken_v2,
                'uid'       => $uid,
            ]);
        } catch (ConnectionException $e) {
            Log::error("[HoyoLabMicroservice] Gagal terhubung ke microservice: {$e->getMessage()}");
            throw new RuntimeException(
                "Tidak dapat terhubung ke HoYoLAB microservice di [{$this->baseUrl}].",
                previous: $e
            );
        }

        if ($response->failed()) {
            $errorBody = $response->json('detail', []);
            $errorMsg  = is_array($errorBody) ? ($errorBody['message'] ?? $response->body()) : $response->body();
            Log::warning("[HoyoLabMicroservice] Error status checkin UID {$uid}: {$errorMsg}");
            throw new RuntimeException($errorMsg, $response->status());
        }

        return $response->json();
    }

    /**
     * Cek status / health dari microservice Python.
     */
    public function ping(): bool
    {
        try {
            $response = $this->client()->get('/health');
            return $response->successful() && $response->json('status') === 'ok';
        } catch (ConnectionException) {
            return false;
        }
    }

    /**
     * Sync all inventory from HoYoLAB
     */
    public function syncAll(array $cookies, int $uid, string $server): array
    {
        $ltuid = $cookies['ltuid_v2'] ?? '';
        $ltoken = $cookies['ltoken_v2'] ?? '';

        $battleChronicle = $this->getCharacters($ltuid, $ltoken, $uid);
        $characters = $battleChronicle['characters'] ?? [];

        $syncedChars = 0;
        $syncedWeapons = 0;
        $syncedArts = 0;

        $account = \App\Models\GameAccount::where('uid', $uid)->first();
        if (!$account) {
            throw new \RuntimeException("Akun dengan UID {$uid} tidak ditemukan di database.");
        }

        foreach ($characters as $charData) {
            $charName = $charData['name'];
            $slug = \Illuminate\Support\Str::slug($charName);

            $character = \App\Models\Character::where('slug', $slug)->orWhere('name', $charName)->first();
            if (!$character) {
                $character = \App\Models\Character::create([
                    'name'        => $charName,
                    'slug'        => $slug,
                    'element'     => ucfirst($charData['element'] ?? 'Pyro'),
                    'weapon_type' => isset($charData['weapon']['type']) ? $charData['weapon']['type'] : 'Sword',
                    'rarity'      => $charData['rarity'] ?? 4,
                    'icon_url'    => $charData['image'] ?? null,
                ]);
            }

            $level = (int) ($charData['level'] ?? 1);
            $ascension = match (true) {
                $level > 80 => 6,
                $level > 70 => 5,
                $level > 60 => 4,
                $level > 50 => 3,
                $level > 40 => 2,
                $level > 20 => 1,
                default     => 0,
            };

            \App\Models\InventoryCharacter::updateOrCreate(
                ['game_account_id' => $account->id, 'character_id' => $character->id],
                [
                    'level'         => $level,
                    'ascension'     => $ascension,
                    'constellation' => (int) ($charData['constellation'] ?? 0),
                    'scanned_at'    => now(),
                ]
            );
            $syncedChars++;

            // Weapons
            if (!empty($charData['weapon']) && !empty($charData['weapon']['name'])) {
                $wData = $charData['weapon'];
                $wName = $wData['name'];
                $wSlug = \Illuminate\Support\Str::slug($wName);

                $weapon = \App\Models\Weapon::where('slug', $wSlug)->orWhere('name', $wName)->first();
                $validType = in_array($wData['type'] ?? '', ['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'])
                    ? $wData['type']
                    : ($character->weapon_type ?? 'Sword');

                if (!$weapon) {
                    $weapon = \App\Models\Weapon::create([
                        'name'     => $wName,
                        'slug'     => $wSlug,
                        'type'     => $validType,
                        'rarity'   => $wData['rarity'] ?? 4,
                        'icon_url' => !empty($wData['icon']) ? $wData['icon'] : null,
                    ]);
                } else {
                    $updates = [];
                    if (empty($weapon->icon_url) && !empty($wData['icon'])) $updates['icon_url'] = $wData['icon'];
                    if (!in_array($weapon->type, ['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'])) $updates['type'] = $validType;
                    if (!empty($updates)) $weapon->update($updates);
                }

                $wLevel = (int) ($wData['level'] ?? 1);
                $wAscension = match (true) {
                    $wLevel > 80 => 6, $wLevel > 70 => 5, $wLevel > 60 => 4, $wLevel > 50 => 3, $wLevel > 40 => 2, $wLevel > 20 => 1, default => 0,
                };

                \App\Models\InventoryWeapon::updateOrCreate(
                    ['game_account_id' => $account->id, 'equipped_character_id' => $character->id],
                    [
                        'weapon_id'  => $weapon->id,
                        'level'      => $wLevel,
                        'ascension'  => $wAscension,
                        'refinement' => (int) ($wData['refinement'] ?? 1),
                        'scanned_at' => now(),
                    ]
                );
                $syncedWeapons++;
            }

            // Artifacts
            if (!empty($charData['artifacts']) && is_array($charData['artifacts'])) {
                foreach ($charData['artifacts'] as $artData) {
                    $setName = $artData['set_name'] ?? 'Unknown Set';
                    $setSlug = \Illuminate\Support\Str::slug($setName);
                    
                    $artifactSet = \App\Models\ArtifactSet::where('slug', $setSlug)->orWhere('name', $setName)->first();
                    if (!$artifactSet) {
                        $artifactSet = \App\Models\ArtifactSet::create([
                            'name'       => $setName,
                            'slug'       => $setSlug,
                            'max_rarity' => $artData['rarity'] ?? 5,
                            'icon_url'   => null,
                        ]);
                    }

                    $slotKey = match((int)($artData['pos'] ?? 1)) {
                        1 => 'flower',
                        2 => 'plume',
                        3 => 'sands',
                        4 => 'goblet',
                        5 => 'circlet',
                        default => 'flower',
                    };

                    $mainStatName = $artData['main_stat_name'] ?? 'HP';
                    $mainStatValue = $artData['main_stat_value'] ?? '0';
                    $mainStatKey = \Illuminate\Support\Str::slug(str_replace('%', '_percent', $mainStatName), '_');

                    $invArt = \App\Models\InventoryArtifact::where('game_account_id', $account->id)
                        ->where('equipped_character_id', $character->id)
                        ->where('slot_key', $slotKey)
                        ->first();

                    if ($invArt) {
                        $invArt->update([
                            'artifact_set_id' => $artifactSet->id,
                            'rarity' => $artData['rarity'] ?? 5,
                            'level' => $artData['level'] ?? 0,
                            'main_stat_key' => $mainStatKey,
                            'main_stat_value' => $mainStatValue,
                            'scanned_at' => now(),
                        ]);
                    } else {
                        \App\Models\InventoryArtifact::create([
                            'game_account_id' => $account->id,
                            'artifact_set_id' => $artifactSet->id,
                            'slot_key' => $slotKey,
                            'rarity' => $artData['rarity'] ?? 5,
                            'level' => $artData['level'] ?? 0,
                            'main_stat_key' => $mainStatKey,
                            'main_stat_value' => $mainStatValue,
                            'equipped_character_id' => $character->id,
                            'scanned_at' => now(),
                        ]);
                    }
                    $syncedArts++;
                }
            }
        }

        return [
            'characters' => array_fill(0, $syncedChars, 1),
            'weapons' => array_fill(0, $syncedWeapons, 1),
            'artifacts' => array_fill(0, $syncedArts, 1),
        ];
    }
}
