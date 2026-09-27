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
 * microservice dilakukan di sini menggunakan Illuminate\Http\Client (Guzzle-wrapper).
 */
class HoyoLabMicroservice
{
    /**
     * Base URL microservice Python.
     * Bisa diatur melalui env HOYOLAB_MICROSERVICE_URL di .env Laravel.
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

    // ─── Private Helpers ──────────────────────────────────────────────────────

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

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Mengambil daftar karakter Genshin Impact milik pemain dari HoYoLAB
     * melalui Python FastAPI microservice.
     *
     * @param  string  $ltuid_v2   Cookie ltuid_v2 dari HoYoLAB
     * @param  string  $ltoken_v2  Cookie ltoken_v2 dari HoYoLAB
     * @param  int     $uid        UID akun Genshin Impact in-game
     * @return array{
     *     uid: int,
     *     total_characters: int,
     *     characters: array<int, array{
     *         id: int,
     *         name: string,
     *         element: string,
     *         rarity: int,
     *         level: int,
     *         friendship: int,
     *         constellation: int,
     *         image: string,
     *         weapon: array|null
     *     }>
     * }
     *
     * @throws \RuntimeException Jika terjadi error koneksi atau response tidak valid
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

        // ─── Tangani HTTP Error dari Microservice ─────────────────────────
        if ($response->failed()) {
            $errorBody = $response->json('detail', []);
            $errorCode = $errorBody['error'] ?? 'UNKNOWN';
            $errorMsg  = $errorBody['message'] ?? $response->body();

            Log::warning(
                "[HoyoLabMicroservice] Microservice mengembalikan error.",
                [
                    'http_status' => $response->status(),
                    'error_code'  => $errorCode,
                    'message'     => $errorMsg,
                    'uid'         => $uid,
                ]
            );

            // Re-throw dengan pesan yang ramah pengguna
            throw new RuntimeException("[{$errorCode}] {$errorMsg}", $response->status());
        }

        $data = $response->json();

        Log::info(
            "[HoyoLabMicroservice] Berhasil ambil {$data['total_characters']} karakter untuk UID {$uid}."
        );

        return $data;
    }

    /**
     * Cek status / health dari microservice Python.
     *
     * @return bool True jika microservice aktif dan sehat.
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
}
