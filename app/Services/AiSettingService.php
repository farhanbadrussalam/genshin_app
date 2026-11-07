<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\AppSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSettingService
{
    public const SETTING_KEY = 'gemini_ai_config';

    /**
     * Spesifikasi resmi Google AI Studio (Free Tier Rate Limits)
     */
    public const MODEL_LIMITS = [
        'gemini-2.5-flash' => [
            'name'        => 'Gemini 2.5 Flash',
            'badge'       => '⚡ Cepat & Seimbang',
            'rpm'         => 15,
            'rpd'         => 1500,
            'tpm'         => 1000000,
            'description' => 'Model rekomendasi utama untuk komputasi cepat, multimodal, dan kuota sangat lega (1.500 RPD).',
            'tier'        => 'free_generous',
        ],
        'gemini-2.5-pro' => [
            'name'        => 'Gemini 2.5 Pro',
            'badge'       => '🧠 Deep Reasoning & Komparasi',
            'rpm'         => 2,
            'rpd'         => 50,
            'tpm'         => 32000,
            'description' => 'Penalaran dan evaluasi teori mendalam, cocok untuk komparasi benchmark namun kuota Free Tier ketat (50 RPD & 2 RPM).',
            'tier'        => 'free_strict',
        ],
        'gemini-2.5-flash-lite' => [
            'name'        => 'Gemini 2.5 Flash-Lite',
            'badge'       => '🪶 Sangat Ringan & Hemat',
            'rpm'         => 15,
            'rpd'         => 1500,
            'tpm'         => 1000000,
            'description' => 'Versi paling hemat latensi dan sumber daya, cocok sebagai fallback darurat saat server sibuk.',
            'tier'        => 'free_generous',
        ],
        'gemini-3.8-flash' => [
            'name'        => 'Gemini 3.8 Flash',
            'badge'       => '🚀 Generasi Terbaru',
            'rpm'         => 15,
            'rpd'         => 1500,
            'tpm'         => 1000000,
            'description' => 'Model terbaru generasi 3.8 dengan kemampuan penalaran dan format JSON mutakhir.',
            'tier'        => 'free_generous',
        ],
    ];

    /**
     * Konfigurasi default sistem
     */
    public static function getDefaultConfig(): array
    {
        return [
            'active_model'         => 'gemini-2.5-flash',
            'fallback_model'       => 'gemini-2.5-flash-lite',
            'enable_auto_fallback' => true,
            'rate_limit_tier'      => 'free', // 'free' atau 'pay_as_you_go'
            'temperature'          => 0.25,
            'max_tokens'           => 8192,
        ];
    }

    /**
     * Dapatkan konfigurasi AI aktif saat ini dari database
     */
    public function getConfig(): array
    {
        $saved = AppSetting::get(self::SETTING_KEY, []);
        $defaults = self::getDefaultConfig();

        if (!is_array($saved)) {
            $saved = [];
        }

        return array_merge($defaults, $saved);
    }

    /**
     * Simpan konfigurasi AI baru ke database
     */
    public function saveConfig(array $data): array
    {
        $current = $this->getConfig();

        $allowedModels = array_keys(self::MODEL_LIMITS);
        $activeModel = in_array($data['active_model'] ?? '', $allowedModels)
            ? $data['active_model']
            : $current['active_model'];

        $fallbackModel = in_array($data['fallback_model'] ?? '', $allowedModels)
            ? $data['fallback_model']
            : $current['fallback_model'];

        $updated = [
            'active_model'         => $activeModel,
            'fallback_model'       => $fallbackModel,
            'enable_auto_fallback' => isset($data['enable_auto_fallback'])
                ? filter_var($data['enable_auto_fallback'], FILTER_VALIDATE_BOOLEAN)
                : $current['enable_auto_fallback'],
            'rate_limit_tier'      => ($data['rate_limit_tier'] ?? 'free') === 'pay_as_you_go' ? 'pay_as_you_go' : 'free',
            'temperature'          => isset($data['temperature']) ? (float)$data['temperature'] : $current['temperature'],
            'max_tokens'           => isset($data['max_tokens']) ? (int)$data['max_tokens'] : $current['max_tokens'],
        ];

        AppSetting::set(self::SETTING_KEY, $updated, 'Konfigurasi Model & Kuota Gemini AI');

        return $updated;
    }

    /**
     * Ambil model utama yang aktif untuk eksekusi fitur
     */
    public function getActiveModel(): string
    {
        $cfg = $this->getConfig();
        return $cfg['active_model'] ?? 'gemini-2.5-flash';
    }

    /**
     * Ambil daftar model kandidat (utama + cadangan) sesuai konfigurasi
     */
    public function getCandidateModels(?string $preferredType = null): array
    {
        $cfg = $this->getConfig();
        $active = $cfg['active_model'];
        $fallback = $cfg['fallback_model'];
        $allowFallback = $cfg['enable_auto_fallback'];

        if ($preferredType === 'pro') {
            $list = ['gemini-2.5-pro'];
            if ($allowFallback) {
                if ($active !== 'gemini-2.5-pro') $list[] = $active;
                if ($fallback !== 'gemini-2.5-pro' && !in_array($fallback, $list)) $list[] = $fallback;
            }
            return array_values(array_unique($list));
        }

        if ($preferredType === 'flash') {
            $list = [str_contains($active, 'pro') ? 'gemini-2.5-flash' : $active];
            if ($allowFallback) {
                if (!in_array($fallback, $list) && !str_contains($fallback, 'pro')) $list[] = $fallback;
                if (!in_array('gemini-2.5-flash-lite', $list)) $list[] = 'gemini-2.5-flash-lite';
            }
            return array_values(array_unique($list));
        }

        // Default sesuai settingan
        $list = [$active];
        if ($allowFallback && $fallback && $fallback !== $active) {
            $list[] = $fallback;
        }
        if ($allowFallback && !in_array('gemini-2.5-flash-lite', $list)) {
            $list[] = 'gemini-2.5-flash-lite';
        }

        return array_values(array_unique($list));
    }

    /**
     * Catat log pemakaian API ke tabel ai_usage_logs
     */
    public function recordUsage(
        string $feature,
        string $modelUsed,
        int $promptTokens = 0,
        int $candidatesTokens = 0,
        string $status = 'success',
        ?string $errorMessage = null,
        int $durationMs = 0
    ): AiUsageLog {
        try {
            return AiUsageLog::create([
                'user_id'           => auth()->id(),
                'feature'           => $feature,
                'model_used'        => $modelUsed,
                'prompt_tokens'     => $promptTokens,
                'candidates_tokens' => $candidatesTokens,
                'total_tokens'      => ($promptTokens + $candidatesTokens),
                'status'            => $status,
                'error_message'     => $errorMessage ? substr($errorMessage, 0, 1000) : null,
                'duration_ms'       => $durationMs,
                'created_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Gagal mencatat ai_usage_log: " . $e->getMessage());
            return new AiUsageLog();
        }
    }

    /**
     * Hitung statistik kuota dan limit saat ini (Hari ini dan Menit ini)
     */
    public function getQuotaUsageStats(): array
    {
        $cfg = $this->getConfig();
        $activeModel = $cfg['active_model'];
        $modelMeta = self::MODEL_LIMITS[$activeModel] ?? self::MODEL_LIMITS['gemini-2.5-flash'];

        // Google AI Studio reset jam 00:00 UTC (jam 07:00 WIB)
        // Hitung start of day berdasarkan zona UTC
        $startOfDayUtc = Carbon::now('UTC')->startOfDay();

        // 1. Permintaan Hari Ini
        $todayQuery = AiUsageLog::where('created_at', '>=', $startOfDayUtc);
        $totalRequestsToday = (clone $todayQuery)->count();
        $totalTokensToday   = (clone $todayQuery)->sum('total_tokens');
        $errorCountToday    = (clone $todayQuery)->where('status', 'error')->count();

        // Filter per model aktif hari ini
        $activeModelRequestsToday = (clone $todayQuery)->where('model_used', $activeModel)->count();
        $activeModelTokensToday   = (clone $todayQuery)->where('model_used', $activeModel)->sum('total_tokens');

        // 2. Permintaan Menit Ini (1 menit terakhir untuk RPM)
        $oneMinuteAgo = Carbon::now()->subSeconds(60);
        $requestsLastMinute = AiUsageLog::where('created_at', '>=', $oneMinuteAgo)->count();
        $activeModelRequestsLastMinute = AiUsageLog::where('created_at', '>=', $oneMinuteAgo)
            ->where('model_used', $activeModel)
            ->count();

        // Limit harian dan menit sesuai tier
        $isPayAsYouGo = ($cfg['rate_limit_tier'] === 'pay_as_you_go');
        $rpdLimit = $isPayAsYouGo ? 100000 : $modelMeta['rpd'];
        $rpmLimit = $isPayAsYouGo ? 1000 : $modelMeta['rpm'];
        $tpmLimit = $isPayAsYouGo ? 5000000 : $modelMeta['tpm'];

        $rpdPercent = $rpdLimit > 0 ? min(100, round(($activeModelRequestsToday / $rpdLimit) * 100, 1)) : 0;
        $rpmPercent = $rpmLimit > 0 ? min(100, round(($activeModelRequestsLastMinute / $rpmLimit) * 100, 1)) : 0;

        // Status keamanan kuota
        $statusHealth = 'safe';
        if ($rpdPercent >= 90 || $rpmPercent >= 90) {
            $statusHealth = 'critical';
        } elseif ($rpdPercent >= 70 || $rpmPercent >= 70) {
            $statusHealth = 'warning';
        }

        // 3. Distribusi Pemakaian per Fitur Hari Ini
        $featureDistribution = (clone $todayQuery)
            ->selectRaw('feature, COUNT(*) as count, SUM(total_tokens) as tokens')
            ->groupBy('feature')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->feature => [
                    'count'  => (int)$row->count,
                    'tokens' => (int)$row->tokens,
                ]];
            })
            ->toArray();

        // 4. Log 10 Pemakaian Terakhir
        $recentLogs = AiUsageLog::latest('id')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id'           => $log->id,
                    'feature'      => $this->formatFeatureName($log->feature),
                    'model'        => $log->model_used,
                    'total_tokens' => $log->total_tokens,
                    'status'       => $log->status,
                    'duration_sec' => round($log->duration_ms / 1000, 1),
                    'time_ago'     => $log->created_at ? $log->created_at->diffForHumans() : '-',
                ];
            });

        // Waktu reset kuota Google AI Studio berikutnya (00:00 UTC esok hari)
        $nextResetUtc = Carbon::now('UTC')->endOfDay()->addSecond();
        $secondsUntilReset = (int) Carbon::now('UTC')->diffInSeconds($nextResetUtc);
        $hoursUntilReset = (int) floor($secondsUntilReset / 3600);
        $minutesUntilReset = (int) floor(($secondsUntilReset % 3600) / 60);

        return [
            'config'                 => $cfg,
            'active_model_meta'      => $modelMeta,
            'is_pay_as_you_go'       => $isPayAsYouGo,
            'status_health'          => $statusHealth,
            'next_reset_in'          => "{$hoursUntilReset} jam {$minutesUntilReset} menit",
            'rpd'                    => [
                'used'    => $activeModelRequestsToday,
                'limit'   => $rpdLimit,
                'percent' => $rpdPercent,
                'all_models_used' => $totalRequestsToday,
            ],
            'rpm'                    => [
                'used'    => $activeModelRequestsLastMinute,
                'limit'   => $rpmLimit,
                'percent' => $rpmPercent,
                'all_models_used' => $requestsLastMinute,
            ],
            'tokens'                 => [
                'used_today'      => $activeModelTokensToday,
                'all_today'       => $totalTokensToday,
                'limit_per_min'   => $tpmLimit,
            ],
            'error_count_today'      => $errorCountToday,
            'feature_distribution'   => $featureDistribution,
            'recent_logs'            => $recentLogs,
            'available_models'       => self::MODEL_LIMITS,
        ];
    }

    /**
     * Label representasi fitur dalam Bahasa Indonesia
     */
    public function formatFeatureName(string $feature): string
    {
        return match ($feature) {
            'smart_team_builder' => 'Smart Team Builder',
            'chat'               => 'Theorycrafter AI Chat',
            'compare'            => 'Benchmark A/B Testing',
            'vision_extract'     => 'DPS Screenshot Scanner',
            'good_export'        => 'GOOD / Gemini Exporter',
            default              => ucfirst(str_replace('_', ' ', $feature)),
        };
    }

    /**
     * Tes ping koneksi ke Gemini API dengan model tertentu
     */
    public function testModelConnection(string $modelName): array
    {
        $apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'GEMINI_API_KEY belum dikonfigurasi di file .env',
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";
        $t0 = microtime(true);

        try {
            $response = Http::timeout(20)->post($url, [
                'contents' => [
                    [
                        'role'  => 'user',
                        'parts' => [['text' => 'Ping. Jawab 1 kata: "Aktif".']],
                    ],
                ],
            ]);

            $durationMs = (int)round((microtime(true) - $t0) * 1000);

            if ($response->successful()) {
                $reply = trim($response->json('candidates.0.content.parts.0.text') ?? 'OK');
                $tokens = (int)($response->json('usageMetadata.totalTokenCount') ?? 5);

                $this->recordUsage(
                    feature: 'test_connection',
                    modelUsed: $modelName,
                    promptTokens: (int)($response->json('usageMetadata.promptTokenCount') ?? 5),
                    candidatesTokens: (int)($response->json('usageMetadata.candidatesTokenCount') ?? 2),
                    status: 'success',
                    durationMs: $durationMs
                );

                return [
                    'success'     => true,
                    'model'       => $modelName,
                    'latency_ms'  => $durationMs,
                    'reply'       => $reply,
                    'message'     => "Koneksi ke {$modelName} berhasil! Latensi: {$durationMs}ms.",
                ];
            }

            $status = $response->status();
            $errMsg = $response->json('error.message') ?? "HTTP {$status}";

            $this->recordUsage(
                feature: 'test_connection',
                modelUsed: $modelName,
                status: 'error',
                errorMessage: $errMsg,
                durationMs: $durationMs
            );

            return [
                'success' => false,
                'model'   => $modelName,
                'status'  => $status,
                'message' => "Gagal menghubungi {$modelName} ({$status}): {$errMsg}",
            ];
        } catch (\Throwable $e) {
            $durationMs = (int)round((microtime(true) - $t0) * 1000);
            return [
                'success' => false,
                'model'   => $modelName,
                'message' => "Exception ({$modelName}): " . $e->getMessage(),
            ];
        }
    }
}

