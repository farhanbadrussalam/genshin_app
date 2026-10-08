<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckinLog;
use App\Models\GameAccount;
use App\Models\ResinAlert;
use App\Services\HoyoLabMicroservice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class DailyResinController extends Controller
{
    public function __construct(
        private readonly HoyoLabMicroservice $hoyolab
    ) {}

    /**
     * Tampilkan halaman utama Daily Check-in & Resin Alert Tracker
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::latest()->get();

        $selectedAccountId = $request->query('account_id');
        $selectedAccount = null;

        if ($selectedAccountId) {
            $selectedAccount = $accounts->firstWhere('id', (int) $selectedAccountId);
        }

        if (!$selectedAccount) {
            // Utamakan akun yang sudah memiliki cookie HoYoLAB
            $selectedAccount = $accounts->first(fn($a) => $a->hasHoyoLabCookies()) ?? $accounts->first();
        }

        $notes = null;
        $checkinStatus = null;
        $fetchError = null;

        // Ambil data real-time jika akun memiliki cookie
        if ($selectedAccount && $selectedAccount->hasHoyoLabCookies()) {
            try {
                $notes = $this->hoyolab->getRealtimeNotes(
                    $selectedAccount->ltuid_v2,
                    $selectedAccount->ltoken_v2,
                    (int) $selectedAccount->uid
                );

                // Update cache lokal
                $selectedAccount->update([
                    'last_known_resin'     => (int) ($notes['current_resin'] ?? 0),
                    'last_known_resin_max' => (int) ($notes['max_resin'] ?? 200),
                    'last_resin_synced_at' => now(),
                ]);

                // Cek threshold alert
                $this->evaluateResinAlert($selectedAccount, (int) ($notes['current_resin'] ?? 0), (int) ($notes['max_resin'] ?? 200));

            } catch (\Exception $e) {
                Log::warning("[DailyResinController] Gagal fetch real-time notes: {$e->getMessage()}");
                $fetchError = "Catatan: Tidak dapat memperbarui data Real-Time dari HoYoLAB: " . $e->getMessage();
            }

            try {
                $checkinStatus = $this->hoyolab->getDailyCheckinStatus(
                    $selectedAccount->ltuid_v2,
                    $selectedAccount->ltoken_v2,
                    (int) $selectedAccount->uid
                );
            } catch (\Exception $e) {
                Log::warning("[DailyResinController] Gagal fetch checkin status: {$e->getMessage()}");
            }
        }

        // Ambil riwayat log check-in dan alert
        $checkinLogs = $selectedAccount ? $selectedAccount->dailyCheckinLogs()->take(10)->get() : collect();
        $resinAlerts = $selectedAccount ? $selectedAccount->resinAlerts()->take(10)->get() : collect();
        $unreadAlertsCount = $selectedAccount ? $selectedAccount->unreadResinAlerts()->count() : 0;

        return view('daily_resin.index', [
            'title'             => 'Daily Check-in & Resin Alert',
            'accounts'          => $accounts,
            'selectedAccount'   => $selectedAccount,
            'notes'             => $notes,
            'checkinStatus'     => $checkinStatus,
            'checkinLogs'       => $checkinLogs,
            'resinAlerts'       => $resinAlerts,
            'unreadAlertsCount' => $unreadAlertsCount,
            'fetchError'        => $fetchError,
        ]);
    }

    /**
     * Endpoint AJAX untuk mengambil data real-time terbaru tanpa reload halaman
     */
    public function ajaxData(GameAccount $gameAccount): JsonResponse
    {
        if (!$gameAccount->hasHoyoLabCookies()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini belum memiliki cookie HoYoLAB (ltuid_v2 & ltoken_v2).',
            ], 422);
        }

        try {
            $notes = $this->hoyolab->getRealtimeNotes(
                $gameAccount->ltuid_v2,
                $gameAccount->ltoken_v2,
                (int) $gameAccount->uid
            );

            $gameAccount->update([
                'last_known_resin'     => (int) ($notes['current_resin'] ?? 0),
                'last_known_resin_max' => (int) ($notes['max_resin'] ?? 200),
                'last_resin_synced_at' => now(),
            ]);

            $alertTriggered = $this->evaluateResinAlert(
                $gameAccount,
                (int) ($notes['current_resin'] ?? 0),
                (int) ($notes['max_resin'] ?? 200)
            );

            $checkinStatus = null;
            try {
                $checkinStatus = $this->hoyolab->getDailyCheckinStatus(
                    $gameAccount->ltuid_v2,
                    $gameAccount->ltoken_v2,
                    (int) $gameAccount->uid
                );
            } catch (\Exception $e) {
                Log::warning("[DailyResinController] Ajax status error: {$e->getMessage()}");
            }

            $recentAlerts = $gameAccount->resinAlerts()->take(10)->get();
            $unreadCount  = $gameAccount->unreadResinAlerts()->count();

            return response()->json([
                'success'         => true,
                'notes'           => $notes,
                'checkin_status'  => $checkinStatus,
                'alert_triggered' => $alertTriggered,
                'unread_alerts'   => $unreadCount,
                'recent_alerts'   => $recentAlerts,
                'synced_at'       => now()->format('H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data HoYoLAB: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint manual untuk mengklaim Daily Check-in via tombol di UI
     */
    public function claimCheckin(GameAccount $gameAccount): JsonResponse
    {
        if (!$gameAccount->hasHoyoLabCookies()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum memiliki cookie HoYoLAB.',
            ], 422);
        }

        try {
            $result = $this->hoyolab->claimDailyCheckin(
                $gameAccount->ltuid_v2,
                $gameAccount->ltoken_v2,
                (int) $gameAccount->uid
            );

            $isAlready = $result['already_claimed'] ?? false;
            $status = $isAlready ? 'already_claimed' : 'success';
            $reward = $result['reward'] ?? null;
            $msg = $result['message'] ?? 'Check-in selesai.';

            DailyCheckinLog::create([
                'game_account_id' => $gameAccount->id,
                'status'          => $status,
                'reward_name'     => $reward['name'] ?? null,
                'reward_amount'   => $reward['amount'] ?? 1,
                'reward_icon'     => $reward['icon'] ?? null,
                'message'         => $msg,
                'is_auto'         => false,
                'checked_at'      => now(),
            ]);

            $gameAccount->update([
                'last_checkin_at'      => now(),
                'last_checkin_status'  => $status,
                'last_checkin_message' => $msg,
            ]);

            return response()->json([
                'success'         => true,
                'already_claimed' => $isAlready,
                'message'         => $msg,
                'reward'          => $reward,
                'claimed_count'   => $result['claimed_rewards_count'] ?? null,
            ]);

        } catch (\Exception $e) {
            $errMsg = $e->getMessage();

            DailyCheckinLog::create([
                'game_account_id' => $gameAccount->id,
                'status'          => 'failed',
                'message'         => $errMsg,
                'is_auto'         => false,
                'checked_at'      => now(),
            ]);

            $gameAccount->update([
                'last_checkin_at'      => now(),
                'last_checkin_status'  => 'failed',
                'last_checkin_message' => $errMsg,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Check-in gagal: ' . $errMsg,
            ], 500);
        }
    }

    /**
     * Update preferensi Auto Check-in & Resin Alert
     */
    public function updateSettings(Request $request, GameAccount $gameAccount): JsonResponse
    {
        $validated = $request->validate([
            'auto_checkin_enabled'  => 'required|boolean',
            'resin_alert_enabled'   => 'required|boolean',
            'resin_alert_threshold' => 'required|integer|min:40|max:200',
        ]);

        $gameAccount->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan!',
            'account' => $gameAccount,
        ]);
    }

    /**
     * Tandai semua notifikasi alert sebagai sudah dibaca
     */
    public function markAlertsAsRead(GameAccount $gameAccount): JsonResponse
    {
        $gameAccount->resinAlerts()->where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua alert ditandai telah dibaca.',
        ]);
    }

    /**
     * Trigger alert pengujian untuk mengecek Web Notification & Audio Chime
     */
    public function testResinAlert(GameAccount $gameAccount): JsonResponse
    {
        $currentResin = (int) ($gameAccount->last_known_resin ?? 160);
        $maxResin     = (int) ($gameAccount->last_known_resin_max ?? 200);

        $alert = ResinAlert::create([
            'game_account_id' => $gameAccount->id,
            'resin_amount'    => $currentResin,
            'max_resin'       => $maxResin,
            'threshold'       => $gameAccount->resin_alert_threshold ?? 160,
            'alert_type'      => 'threshold_reached',
            'message'         => "Uji Coba Alert: Original Resin akun [{$gameAccount->nickname}] telah mencapai {$currentResin}/{$maxResin}!",
            'is_read'         => false,
            'notified_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alert simulasi berhasil dikirim!',
            'alert'   => $alert,
        ]);
    }

    // ??? Private Helper ???????????????????????????????????????????????????????

    /**
     * Evaluasi apakah kondisi Resin saat ini memenuhi kriteria alert
     */
    private function evaluateResinAlert(GameAccount $account, int $currentResin, int $maxResin): bool
    {
        if (!$account->resin_alert_enabled) {
            return false;
        }

        $threshold = (int) ($account->resin_alert_threshold ?? 160);

        if ($currentResin >= $threshold) {
            // Cek cooldown 60 menit
            if ($account->last_resin_alert_at) {
                $diff = $account->last_resin_alert_at->diffInMinutes(now());
                if ($diff < 60 && $currentResin < $maxResin) {
                    return false;
                }
            }

            $isCapped = $currentResin >= $maxResin;
            $alertType = $isCapped ? 'resin_capped' : 'threshold_reached';
            $message = $isCapped
                ? "Original Resin akun [{$account->nickname}] SUDAH PENUH ({$currentResin}/{$maxResin})! Segera gunakan agar tidak terbuang!"
                : "Original Resin akun [{$account->nickname}] telah mencapai {$currentResin}/{$maxResin} (batas: {$threshold}).";

            ResinAlert::create([
                'game_account_id' => $account->id,
                'resin_amount'    => $currentResin,
                'max_resin'       => $maxResin,
                'threshold'       => $threshold,
                'alert_type'      => $alertType,
                'message'         => $message,
                'is_read'         => false,
                'notified_at'     => now(),
            ]);

            $account->update(['last_resin_alert_at' => now()]);
            return true;
        }

        return false;
    }
}
