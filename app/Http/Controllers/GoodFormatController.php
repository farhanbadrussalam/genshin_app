<?php

namespace App\Http\Controllers;

use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Models\InventoryCharacter;
use App\Models\InventoryMaterial;
use App\Models\InventoryWeapon;
use App\Services\GoodFormatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GoodFormatController extends Controller
{
    public function __construct(
        protected GoodFormatService $goodService
    ) {}

    /**
     * Tampilkan halaman utama Ekspor & Impor format GOOD
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::where('game', 'genshin_impact')
            ->orderBy('nickname')
            ->get();

        if ($accounts->isEmpty()) {
            $accounts = GameAccount::orderBy('nickname')->get();
        }

        $selectedAccountId = $request->input('account_id', $accounts->first()?->id);
        $activeAccount = $accounts->firstWhere('id', $selectedAccountId) ?? $accounts->first();

        // Statistik akun aktif untuk ringkasan di panel export
        $accountStats = [
            'characters' => 0,
            'weapons'    => 0,
            'artifacts'  => 0,
            'materials'  => 0,
        ];

        if ($activeAccount) {
            $accountStats['characters'] = InventoryCharacter::where('game_account_id', $activeAccount->id)->count();
            $accountStats['weapons']    = InventoryWeapon::where('game_account_id', $activeAccount->id)->count();
            $accountStats['artifacts']  = InventoryArtifact::where('game_account_id', $activeAccount->id)->count();
            $accountStats['materials']  = InventoryMaterial::where('game_account_id', $activeAccount->id)->count();
        }

        return view('inventory.good', [
            'title'         => 'Export / Import Format GOOD',
            'accounts'      => $accounts,
            'activeAccount' => $activeAccount,
            'accountStats'  => $accountStats,
        ]);
    }

    /**
     * Ekspor data inventori sebagai berkas download JSON format GOOD
     */
    public function export(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'game_account_id'    => 'required|exists:game_accounts,id',
            'include_characters' => 'nullable|boolean',
            'include_weapons'    => 'nullable|boolean',
            'include_artifacts'  => 'nullable|boolean',
            'include_materials'  => 'nullable|boolean',
        ]);

        $account = GameAccount::findOrFail($validated['game_account_id']);

        $options = [
            'include_characters' => $request->boolean('include_characters', true),
            'include_weapons'    => $request->boolean('include_weapons', true),
            'include_artifacts'  => $request->boolean('include_artifacts', true),
            'include_materials'  => $request->boolean('include_materials', true),
        ];

        $goodData = $this->goodService->export($account, $options);
        $jsonString = json_encode($goodData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $safeNickname = Str::slug($account->nickname ?: 'account');
        $filename = sprintf('GOOD_%s_%s_%s.json', $safeNickname, $account->uid ?: $account->id, date('Ymd_His'));

        return response($jsonString, 200, [
            'Content-Type'        => 'application/json; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, private',
        ]);
    }

    /**
     * Mengembalikan data ekspor dalam format JSON mentah untuk fitur preview / copy ke clipboard
     */
    public function exportJson(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_account_id'    => 'required|exists:game_accounts,id',
            'include_characters' => 'nullable|boolean',
            'include_weapons'    => 'nullable|boolean',
            'include_artifacts'  => 'nullable|boolean',
            'include_materials'  => 'nullable|boolean',
        ]);

        $account = GameAccount::findOrFail($validated['game_account_id']);

        $options = [
            'include_characters' => $request->boolean('include_characters', true),
            'include_weapons'    => $request->boolean('include_weapons', true),
            'include_artifacts'  => $request->boolean('include_artifacts', true),
            'include_materials'  => $request->boolean('include_materials', true),
        ];

        $goodData = $this->goodService->export($account, $options);

        return response()->json([
            'success' => true,
            'data'    => $goodData,
            'summary' => [
                'characters' => count($goodData['characters'] ?? []),
                'weapons'    => count($goodData['weapons'] ?? []),
                'artifacts'  => count($goodData['artifacts'] ?? []),
                'materials'  => count($goodData['materials'] ?? []),
            ],
        ]);
    }

    /**
     * Impor berkas GOOD JSON (via upload file atau paste string JSON)
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'game_account_id'   => 'required|exists:game_accounts,id',
            'mode'              => 'required|in:merge,replace',
            'good_file'         => 'nullable|file|max:15360', // Max 15MB
            'good_json_raw'     => 'nullable|string',
            'import_characters' => 'nullable|boolean',
            'import_weapons'    => 'nullable|boolean',
            'import_artifacts'  => 'nullable|boolean',
            'import_materials'  => 'nullable|boolean',
        ]);

        $account = GameAccount::findOrFail($validated['game_account_id']);

        // Ambil konten JSON dari file upload atau teks paste
        $jsonContent = null;
        if ($request->hasFile('good_file') && $request->file('good_file')->isValid()) {
            $jsonContent = file_get_contents($request->file('good_file')->getRealPath());
        } elseif (!empty($validated['good_json_raw'])) {
            $jsonContent = trim($validated['good_json_raw']);
        }

        if (empty($jsonContent)) {
            return redirect()->route('inventory.good.index', ['account_id' => $account->id])
                ->with('error', 'Silakan pilih berkas file .json atau tempel (paste) kode JSON format GOOD.');
        }

        // Decode JSON
        $goodData = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($goodData)) {
            return redirect()->route('inventory.good.index', ['account_id' => $account->id])
                ->with('error', 'Format JSON tidak valid atau berkas rusak: ' . json_last_error_msg());
        }

        // Validasi struktur GOOD
        $hasGoodFormat = isset($goodData['format']) && strtoupper((string)$goodData['format']) === 'GOOD';
        $hasValidKeys  = isset($goodData['characters']) || isset($goodData['weapons']) || isset($goodData['artifacts']);

        if (!$hasGoodFormat && !$hasValidKeys) {
            return redirect()->route('inventory.good.index', ['account_id' => $account->id])
                ->with('error', 'Format berkas tidak dikenali sebagai format GOOD (Genshin Open Object Description). Pastikan berkas memiliki key format "GOOD" atau data characters/weapons/artifacts.');
        }

        $options = [
            'mode'              => $validated['mode'],
            'import_characters' => $request->boolean('import_characters', true),
            'import_weapons'    => $request->boolean('import_weapons', true),
            'import_artifacts'  => $request->boolean('import_artifacts', true),
            'import_materials'  => $request->boolean('import_materials', true),
        ];

        $result = $this->goodService->import($account, $goodData, $options);

        if (!$result['success']) {
            return redirect()->route('inventory.good.index', ['account_id' => $account->id])
                ->with('error', $result['message'] ?? 'Gagal memproses impor berkas GOOD.');
        }

        return redirect()->route('inventory.good.index', ['account_id' => $account->id])
            ->with('good_import_result', $result)
            ->with('success', 'Impor format GOOD berhasil diselesaikan!');
    }
}
