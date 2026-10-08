<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Enemy;
use App\Models\GameAccount;
use App\Models\InventoryCharacter;
use App\Models\SavedParty;
use App\Services\PartyAnalyzerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartyAnalyzerController extends Controller
{
    protected PartyAnalyzerService $analyzer;

    public function __construct(PartyAnalyzerService $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    /**
     * Tampilan utama Party Matchup & Analyzer
     */
    public function index(Request $request): View
    {
        $enemies = Enemy::orderByRaw("FIELD(category, 'Weekly Bosses', 'Normal Bosses', 'Elite Enemies', 'Common Enemies')")
            ->orderBy('name')
            ->get();

        $gameAccounts = GameAccount::withCount('inventoryCharacters')->get();

        // Tentukan musuh yang dipilih
        $selectedEnemyId = $request->input('enemy_id');
        $selectedEnemy = $selectedEnemyId ? Enemy::with('drops')->find($selectedEnemyId) : $enemies->first();
        if (!$selectedEnemy && $enemies->isNotEmpty()) {
            $selectedEnemy = $enemies->first();
        }

        // Tentukan akun game yang dipilih (default akun pertama jika ada)
        $selectedAccountId = $request->input('game_account_id');
        if ($selectedAccountId === null && $gameAccounts->isNotEmpty()) {
            $selectedAccountId = $gameAccounts->first()->id;
        } elseif ($selectedAccountId === 'all') {
            $selectedAccountId = null;
        } else {
            $selectedAccountId = (int)$selectedAccountId;
        }

        // Ambil daftar karakter yang dapat dipilih
        $availableCharacters = $this->getAvailableCharacters($selectedAccountId, $selectedEnemy);

        // Tentukan karakter yang sedang dipilih dalam party
        $partyCharacterIds = $request->input('characters');
        if (empty($partyCharacterIds) || !is_array($partyCharacterIds)) {
            // Auto-generate rekomendasi awal jika belum ada party yang ditentukan
            $partyCharacterIds = $selectedEnemy ? $this->analyzer->autoGenerateParty($selectedEnemy, $selectedAccountId) : [];
        }

        // Jalankan analisis tim
        $analysis = $selectedEnemy 
            ? $this->analyzer->analyzePartyVsEnemy($partyCharacterIds, $selectedEnemy, $selectedAccountId)
            : null;

        // Ambil saved parties untuk musuh ini
        $savedParties = $selectedEnemy 
            ? SavedParty::where('enemy_id', $selectedEnemy->id)
                ->when($selectedAccountId, fn ($q) => $q->where('game_account_id', $selectedAccountId))
                ->latest()
                ->get()
            : collect();

        return view('party.index', [
            'enemies'             => $enemies,
            'gameAccounts'        => $gameAccounts,
            'selectedEnemy'       => $selectedEnemy,
            'selectedAccountId'   => $selectedAccountId,
            'availableCharacters' => $availableCharacters,
            'partyCharacterIds'   => $partyCharacterIds,
            'analysis'            => $analysis,
            'savedParties'        => $savedParties,
        ]);
    }

    /**
     * Endpoint API JSON untuk kalkulasi analisis secara real-time via JavaScript
     */
    public function analyzeAjax(Request $request): JsonResponse
    {
        $enemyId = (int)$request->input('enemy_id');
        $accountId = $request->input('game_account_id');
        $accountId = ($accountId === 'all' || empty($accountId)) ? null : (int)$accountId;
        $characterIds = (array)$request->input('character_ids', []);

        $enemy = Enemy::find($enemyId);
        if (!$enemy) {
            return response()->json(['success' => false, 'message' => 'Musuh tidak ditemukan.'], 404);
        }

        $analysis = $this->analyzer->analyzePartyVsEnemy($characterIds, $enemy, $accountId);

        return response()->json([
            'success'  => true,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Endpoint API JSON untuk generate rekomendasi otomatis satu klik
     */
    public function autoGenerateAjax(Request $request): JsonResponse
    {
        $enemyId = (int)$request->input('enemy_id');
        $accountId = $request->input('game_account_id');
        $accountId = ($accountId === 'all' || empty($accountId)) ? null : (int)$accountId;

        $enemy = Enemy::find($enemyId);
        if (!$enemy) {
            return response()->json(['success' => false, 'message' => 'Musuh tidak ditemukan.'], 404);
        }

        $recommendedIds = $this->analyzer->autoGenerateParty($enemy, $accountId);
        $analysis = $this->analyzer->analyzePartyVsEnemy($recommendedIds, $enemy, $accountId);

        return response()->json([
            'success'       => true,
            'character_ids' => $recommendedIds,
            'analysis'      => $analysis,
        ]);
    }

    /**
     * Simpan komposisi party untuk musuh ini
     */
    public function saveParty(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enemy_id'        => 'required|exists:enemies,id',
            'game_account_id' => 'nullable|exists:game_accounts,id',
            'name'            => 'required|string|max:100',
            'character_ids'   => 'required|array|min:1|max:4',
            'notes'           => 'nullable|string|max:500',
        ]);

        $enemy = Enemy::findOrFail($validated['enemy_id']);
        $analysis = $this->analyzer->analyzePartyVsEnemy(
            $validated['character_ids'], 
            $enemy, 
            $validated['game_account_id'] ? (int)$validated['game_account_id'] : null
        );

        SavedParty::create([
            'enemy_id'        => $validated['enemy_id'],
            'game_account_id' => $validated['game_account_id'] ?: null,
            'name'            => $validated['name'],
            'character_ids'   => $validated['character_ids'],
            'synergy_score'   => $analysis['team_score'] ?? 0,
            'synergy_tier'    => $analysis['team_tier'] ?? 'B',
            'notes'           => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Komposisi party berhasil disimpan!');
    }

    /**
     * Hapus party yang disimpan
     */
    public function deleteParty(SavedParty $savedParty): RedirectResponse
    {
        $savedParty->delete();
        return redirect()->back()->with('success', 'Party tersimpan berhasil dihapus.');
    }

    /**
     * Helper mengambil daftar karakter berserta skor kecocokan
     */
    protected function getAvailableCharacters(?int $accountId, ?Enemy $enemy)
    {
        if ($accountId) {
            $invChars = InventoryCharacter::with('character')
                ->where('game_account_id', $accountId)
                ->get();

            return $invChars->map(function ($inv) use ($enemy) {
                $scoreData = $enemy 
                    ? $this->analyzer->scoreCharacterVsEnemy($inv->character, $enemy, $inv)
                    : ['score' => 50, 'tier' => 'B', 'is_immune' => false, 'tags' => []];

                return array_merge($scoreData, [
                    'inventory' => $inv,
                    'character' => $inv->character,
                ]);
            })->sortByDesc('score')->values();
        }

        $chars = Character::where('is_active', true)->get();
        return $chars->map(function ($c) use ($enemy) {
            $scoreData = $enemy 
                ? $this->analyzer->scoreCharacterVsEnemy($c, $enemy, null)
                : ['score' => 50, 'tier' => 'B', 'is_immune' => false, 'tags' => []];

            return array_merge($scoreData, [
                'inventory' => null,
                'character' => $c,
            ]);
        })->sortByDesc('score')->values();
    }
}