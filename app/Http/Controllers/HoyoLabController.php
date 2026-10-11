<?php

namespace App\Http\Controllers;

use App\Models\task;
use App\Models\GameAccount;
use App\Services\HoyoLabMicroservice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * HoyoLabController
 *
 * Controller yang mengorkestrasi alur:
 * 1. Validasi request masuk dari user (cookie + UID)
 * 2. Panggil HoyoLabMicroservice untuk ambil data dari Python API
 * 3. Simpan karakter-karakter sebagai draft Task (jenis: stat) ke database
 */
class HoyoLabController extends Controller
{
    public function __construct(
        private readonly HoyoLabMicroservice $hoyolab
    ) {}

    // ─── Public Endpoints ────────────────────────────────────────────────────

    /**
     * POST /hoyolab/import-characters
     *
     * Mengimpor daftar karakter dari HoYoLAB dan menyimpannya sebagai
     * draft Task (jenis 'stat') di database. Skips karakter yang
     * task-nya sudah ada sebelumnya.
     *
     * Body JSON:
     * {
     *   "ltuid_v2": "123456789",
     *   "ltoken_v2": "v2_xxxxxxxxxxxx",
     *   "uid": 812345678
     * }
     */
    public function importCharactersAsTasks(Request $request): JsonResponse
    {
        // ─── 1. Validasi Request ─────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'ltuid_v2'  => ['required', 'string', 'min:5'],
            'ltoken_v2' => ['required', 'string', 'min:10'],
            'uid'       => ['required', 'integer', 'min:100000000'],
        ], [
            'ltuid_v2.required'  => 'Cookie ltuid_v2 wajib diisi.',
            'ltoken_v2.required' => 'Cookie ltoken_v2 wajib diisi.',
            'uid.required'       => 'UID akun Genshin Impact wajib diisi.',
            'uid.integer'        => 'UID harus berupa angka.',
            'uid.min'            => 'UID tidak valid (minimal 9 digit).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // ─── 2. Panggil Microservice Python ──────────────────────────────
        try {
            $battleChronicle = $this->hoyolab->getCharacters(
                ltuid_v2:  $validated['ltuid_v2'],
                ltoken_v2: $validated['ltoken_v2'],
                uid:       (int) $validated['uid'],
            );
        } catch (RuntimeException $e) {
            Log::error("[HoyoLabController] Gagal ambil karakter: {$e->getMessage()}");

            $httpCode = $e->getCode() >= 400 ? $e->getCode() : 502;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $httpCode);
        }

        // ─── 3. Simpan ke Database sebagai Draft Task ─────────────────────
        $characters   = $battleChronicle['characters'] ?? [];
        $savedTasks   = [];
        $skippedTasks = [];

        DB::beginTransaction();
        try {
            foreach ($characters as $char) {
                $taskName = "Upgrade: {$char['name']}";

                $gameAccount = GameAccount::where('uid', $validated['uid'])->first();

                // Cek apakah task untuk karakter ini sudah ada pada akun terkait
                $existsQuery = task::where('nama_task', $taskName)->where('jenis', 'stat');
                if ($gameAccount) {
                    $existsQuery->where('game_account_id', $gameAccount->id);
                }
                $exists = $existsQuery->exists();

                if ($exists) {
                    $skippedTasks[] = $char['name'];
                    Log::info("[HoyoLabController] Task '{$taskName}' sudah ada, dilewati.");
                    continue;
                }

                // Buat draft Task baru
                $newTask = task::create([
                    'game_account_id' => $gameAccount?->id,
                    'nama_task'       => $taskName,
                    'images'          => $char['image'],
                    'jenis'           => 'stat',              // Enum: stat | weapon | talent
                    'status'          => 'start',              // Status awal: mulai direncanakan
                    'prioritas'       => $this->mapLevelToPriority($char['level']),
                ]);

                $savedTasks[] = [
                    'task_id'       => $newTask->id,
                    'character'     => $char['name'],
                    'element'       => $char['element'],
                    'level'         => $char['level'],
                    'constellation' => $char['constellation'],
                    'rarity'        => $char['rarity'],
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[HoyoLabController] Gagal menyimpan tasks ke database: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Terjadi error saat menyimpan data ke database.',
            ], 500);
        }

        // ─── 4. Return Response Ringkasan ─────────────────────────────────
        return response()->json([
            'success' => true,
            'message' => "Import selesai. {$this->countLabel(count($savedTasks), 'task')} berhasil dibuat.",
            'summary' => [
                'uid'             => $battleChronicle['uid'],
                'total_fetched'   => $battleChronicle['total_characters'],
                'total_saved'     => count($savedTasks),
                'total_skipped'   => count($skippedTasks),
            ],
            'saved_tasks'    => $savedTasks,
            'skipped_characters' => $skippedTasks,
        ], 201);
    }

    /**
     * GET /hoyolab/ping
     *
     * Mengecek apakah Python microservice aktif dan bisa dijangkau.
     */
    public function pingMicroservice(): JsonResponse
    {
        $isAlive = $this->hoyolab->ping();

        return response()->json([
            'success'    => $isAlive,
            'microservice_url' => config('services.hoyolab_microservice.url'),
            'status'     => $isAlive ? 'online' : 'offline',
            'message'    => $isAlive
                ? 'Microservice aktif dan siap digunakan.'
                : 'Microservice tidak dapat dijangkau. Pastikan container Docker sudah berjalan.',
        ], $isAlive ? 200 : 503);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Memetakan level karakter ke angka prioritas task.
     * Karakter level lebih rendah → prioritas lebih tinggi (angka kecil).
     *
     * Level 1–40   → Prioritas 1 (butuh banyak upgrade)
     * Level 41–60  → Prioritas 2
     * Level 61–79  → Prioritas 3
     * Level 80–90  → Prioritas 4 (sudah cukup tinggi)
     */
    private function mapLevelToPriority(int $level): int
    {
        return match (true) {
            $level <= 40 => 1,
            $level <= 60 => 2,
            $level <= 79 => 3,
            default      => 4,
        };
    }

    /**
     * Helper untuk membuat label hitungan yang rapi.
     */
    private function countLabel(int $count, string $label): string
    {
        return "{$count} {$label}" . ($count !== 1 ? 's' : '');
    }
}
