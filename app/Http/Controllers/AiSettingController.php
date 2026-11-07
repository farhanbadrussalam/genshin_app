<?php

namespace App\Http\Controllers;

use App\Services\AiSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSettingController extends Controller
{
    public function __construct(
        protected AiSettingService $aiSettingService
    ) {}

    /**
     * Halaman Pengaturan AI & Monitoring Kuota Real-time
     */
    public function index(): View
    {
        $stats = $this->aiSettingService->getQuotaUsageStats();

        return view('settings.ai', [
            'title'        => 'Pengaturan AI & Kuota Limit',
            'stats'        => $stats,
            'config'       => $stats['config'],
            'models'       => $stats['available_models'],
            'recentLogs'   => $stats['recent_logs'],
        ]);
    }

    /**
     * Simpan pembaruan konfigurasi model AI
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'active_model'         => 'required|string|in:' . implode(',', array_keys(AiSettingService::MODEL_LIMITS)),
            'fallback_model'       => 'required|string|in:' . implode(',', array_keys(AiSettingService::MODEL_LIMITS)),
            'enable_auto_fallback' => 'nullable|boolean',
            'rate_limit_tier'      => 'required|string|in:free,pay_as_you_go',
            'temperature'          => 'required|numeric|min:0.0|max:1.0',
            'max_tokens'           => 'required|integer|min:1024|max:16384',
        ]);

        $validated['enable_auto_fallback'] = $request->boolean('enable_auto_fallback');

        $updated = $this->aiSettingService->saveConfig($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi AI berhasil diperbarui!',
                'config'  => $updated,
            ]);
        }

        return redirect()->route('settings.ai')->with('success', 'Konfigurasi Model AI & Kuota berhasil disimpan!');
    }

    /**
     * Endpoint API JSON untuk pemantauan kuota real-time (Polling / Refresh)
     */
    public function quotaAjax(): JsonResponse
    {
        $stats = $this->aiSettingService->getQuotaUsageStats();

        return response()->json([
            'success' => true,
            'stats'   => $stats,
        ]);
    }

    /**
     * Tes koneksi ping ke model Gemini
     */
    public function testConnection(Request $request): JsonResponse
    {
        $model = $request->input('model', $this->aiSettingService->getActiveModel());
        $result = $this->aiSettingService->testModelConnection($model);

        return response()->json($result);
    }
}

