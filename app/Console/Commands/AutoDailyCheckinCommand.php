<?php

namespace App\Console\Commands;

use App\Models\DailyCheckinLog;
use App\Models\GameAccount;
use App\Services\HoyoLabMicroservice;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoDailyCheckinCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hoyolab:auto-checkin 
                            {--account= : ID spesifik akun game yang ingin di-check-in}
                            {--force : Paksa check-in meskipun status lokal mencatat sudah hari ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan klaim otomatis Daily Check-in HoYoLAB untuk akun-akun aktif.';

    public function __construct(
        private readonly HoyoLabMicroservice $hoyolab
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=== [HoYoLAB Auto Daily Check-in] Memulai proses check-in ===");

        $query = GameAccount::query();

        if ($accountId = $this->option('account')) {
            $query->where('id', $accountId);
        } else {
            $query->where('auto_checkin_enabled', true);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn("Tidak ada akun yang memenuhi kriteria untuk auto check-in.");
            return Command::SUCCESS;
        }

        $successCount = 0;
        $alreadyClaimedCount = 0;
        $failedCount = 0;

        foreach ($accounts as $account) {
            $this->line("------------------------------------------------------------");
            $this->info("Memproses akun: [{$account->nickname}] (UID: {$account->uid})");

            if (!$account->hasHoyoLabCookies()) {
                $this->warn("Akun {$account->nickname} tidak memiliki cookie HoYoLAB (ltuid_v2/ltoken_v2). Dilewati.");
                continue;
            }

            // Cek apakah sudah check-in hari ini (jika tidak force)
            if (!$this->option('force') && $account->last_checkin_at && $account->last_checkin_at->isToday() && $account->last_checkin_status !== 'failed') {
                $this->line("<comment>Akun {$account->nickname} sudah pernah melakukan check-in hari ini pada: {$account->last_checkin_at->format('H:i:s')}. Dilewati.</comment>");
                $alreadyClaimedCount++;
                continue;
            }

            try {
                $result = $this->hoyolab->claimDailyCheckin(
                    $account->ltuid_v2,
                    $account->ltoken_v2,
                    (int) $account->uid
                );

                $status = ($result['already_claimed'] ?? false) ? 'already_claimed' : 'success';
                $rewardName   = $result['reward']['name'] ?? null;
                $rewardAmount = $result['reward']['amount'] ?? 1;
                $rewardIcon   = $result['reward']['icon'] ?? null;
                $message      = $result['message'] ?? 'Check-in selesai.';

                // Simpan log riwayat check-in
                DailyCheckinLog::create([
                    'game_account_id' => $account->id,
                    'status'          => $status,
                    'reward_name'     => $rewardName,
                    'reward_amount'   => $rewardAmount,
                    'reward_icon'     => $rewardIcon,
                    'message'         => $message,
                    'is_auto'         => true,
                    'checked_at'      => now(),
                ]);

                // Update akun game
                $account->update([
                    'last_checkin_at'      => now(),
                    'last_checkin_status'  => $status,
                    'last_checkin_message' => $message,
                ]);

                if ($status === 'success') {
                    $this->info("? Check-in BERHASIL! Hadiah: {$rewardName} x{$rewardAmount}");
                    $successCount++;
                } else {
                    $this->comment("? {$message}");
                    $alreadyClaimedCount++;
                }

                Log::info("[AutoDailyCheckin] Akun {$account->nickname} (UID {$account->uid}): {$message}");

            } catch (\Exception $e) {
                $failedCount++;
                $errorMsg = $e->getMessage();

                DailyCheckinLog::create([
                    'game_account_id' => $account->id,
                    'status'          => 'failed',
                    'message'         => $errorMsg,
                    'is_auto'         => true,
                    'checked_at'      => now(),
                ]);

                $account->update([
                    'last_checkin_at'      => now(),
                    'last_checkin_status'  => 'failed',
                    'last_checkin_message' => $errorMsg,
                ]);

                $this->error("? Check-in GAGAL: {$errorMsg}");
                Log::error("[AutoDailyCheckin] Error UID {$account->uid}: {$errorMsg}");
            }
        }

        $this->line("============================================================");
        $this->info("Ringkasan: Berhasil baru: {$successCount} | Sudah diklaim: {$alreadyClaimedCount} | Gagal: {$failedCount}");

        return Command::SUCCESS;
    }
}
