<?php

namespace App\Console\Commands;

use App\Models\GameAccount;
use App\Models\ResinAlert;
use App\Services\HoyoLabMicroservice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckResinAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hoyolab:check-resin-alerts 
                            {--account= : ID spesifik akun game yang ingin dicek}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa jumlah Original Resin dan mencatat alert jika mencapai atau melebihi threshold.';

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
        $this->info("=== [HoYoLAB Resin Alert Checker] Memeriksa status Resin ===");

        $query = GameAccount::query();

        if ($accountId = $this->option('account')) {
            $query->where('id', $accountId);
        } else {
            $query->where('resin_alert_enabled', true);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn("Tidak ada akun dengan Resin Alert yang aktif.");
            return Command::SUCCESS;
        }

        $alertsTriggered = 0;

        foreach ($accounts as $account) {
            $this->line("------------------------------------------------------------");
            $this->info("Memeriksa: [{$account->nickname}] (UID: {$account->uid})");

            if (!$account->hasHoyoLabCookies()) {
                $this->warn("Akun {$account->nickname} tidak memiliki cookie HoYoLAB. Dilewati.");
                continue;
            }

            try {
                $notes = $this->hoyolab->getRealtimeNotes(
                    $account->ltuid_v2,
                    $account->ltoken_v2,
                    (int) $account->uid
                );

                $currentResin = (int) ($notes['current_resin'] ?? 0);
                $maxResin     = (int) ($notes['max_resin'] ?? 200);
                $threshold    = (int) ($account->resin_alert_threshold ?? 160);

                // Update data resin terakhir di model GameAccount
                $account->last_known_resin     = $currentResin;
                $account->last_known_resin_max = $maxResin;
                $account->last_resin_synced_at = now();

                $this->line("Resin saat ini: <comment>{$currentResin} / {$maxResin}</comment> (Batas Alert: {$threshold})");

                // Cek kondisi alert
                if ($currentResin >= $threshold) {
                    // Cek apakah baru saja diberi alert (cooldown 60 menit kecuali resin sudah penuh 200)
                    $canAlert = true;
                    if ($account->last_resin_alert_at) {
                        $diffInMinutes = $account->last_resin_alert_at->diffInMinutes(now());
                        if ($diffInMinutes < 60 && $currentResin < $maxResin) {
                            $canAlert = false;
                            $this->line("<comment>Alert sedang dalam cooldown (baru dikirim {$diffInMinutes} menit lalu).</comment>");
                        }
                    }

                    if ($canAlert) {
                        $isCapped = $currentResin >= $maxResin;
                        $alertType = $isCapped ? 'resin_capped' : 'threshold_reached';
                        $message = $isCapped
                            ? "Original Resin akun [{$account->nickname}] SUDAH PENUH ({$currentResin}/{$maxResin})! Segera gunakan agar tidak terbuang!"
                            : "Original Resin akun [{$account->nickname}] telah mencapai {$currentResin}/{$maxResin} (melebihi batas {$threshold}).";

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

                        $account->last_resin_alert_at = now();
                        $alertsTriggered++;

                        $this->warn("?? ALERT DIBUAT: {$message}");
                        Log::warning("[ResinAlert] {$message}");
                    }
                } else {
                    $this->info("? Resin masih di bawah ambang batas alert. Aman.");
                }

                $account->save();

            } catch (\Exception $e) {
                $this->error("Gagal memeriksa resin UID {$account->uid}: {$e->getMessage()}");
                Log::error("[ResinAlert] Error UID {$account->uid}: {$e->getMessage()}");
            }
        }

        $this->line("============================================================");
        $this->info("Pengecekan selesai. Alert baru terpicu: {$alertsTriggered}");

        return Command::SUCCESS;
    }
}
