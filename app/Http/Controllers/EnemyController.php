<?php

namespace App\Http\Controllers;

use App\Models\Enemy;
use App\Models\EnemyDrop;
use App\Models\material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EnemyController extends Controller
{
    /**
     * Tampilkan daftar master data musuh
     */
    public function index(Request $request): View
    {
        $query = Enemy::with('drops');

        // Filter pencarian nama / deskripsi / famili
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('family', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter Kategori
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        // Filter Region
        if ($request->filled('region') && $request->input('region') !== 'all') {
            $query->where('region', $request->input('region'));
        }

        // Filter Elemen (JSON search)
        if ($request->filled('element') && $request->input('element') !== 'all') {
            $element = $request->input('element');
            $query->whereJsonContains('elements', $element);
        }

        $enemies = $query
            ->orderByRaw("FIELD(category, 'Weekly Bosses', 'Normal Bosses', 'Elite Enemies', 'Common Enemies')")
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $stats = [
            'total' => Enemy::count(),
            'weekly' => Enemy::where('category', 'Weekly Bosses')->count(),
            'boss' => Enemy::where('category', 'Normal Bosses')->count(),
            'elite' => Enemy::where('category', 'Elite Enemies')->count(),
            'common' => Enemy::where('category', 'Common Enemies')->count(),
        ];

        $categories = ['Common Enemies', 'Elite Enemies', 'Normal Bosses', 'Weekly Bosses'];
        $elements = ['Pyro', 'Hydro', 'Anemo', 'Electro', 'Dendro', 'Cryo', 'Geo'];
        $regions = ['Mondstadt', 'Liyue', 'Inazuma', 'Sumeru', 'Fontaine', 'Natlan', 'Global'];

        return view('enemy.index', [
            'enemies' => $enemies,
            'stats' => $stats,
            'categories' => $categories,
            'elements' => $elements,
            'regions' => $regions,
            'filters' => $request->only(['search', 'category', 'element', 'region']),
        ]);
    }

    /**
     * Detail musuh dalam format JSON untuk modal preview / AJAX
     */
    public function show($id): JsonResponse
    {
        $enemy = Enemy::with('drops.material')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $enemy,
        ]);
    }

    /**
     * Simpan data musuh baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:enemies,slug',
            'category' => 'required|string|max:100',
            'family' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'elements' => 'nullable|array',
            'description' => 'nullable|string',
            'icon_url' => 'nullable|url|max:500',
            'mora_gained' => 'nullable|integer|min:0',
            'tips_strategy' => 'nullable|string',
            'immunities' => 'nullable|array',
            'weakness_elements' => 'nullable|array',
            'recommended_mechanics' => 'nullable|array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $enemy = Enemy::create($validated);

        return redirect()->route('enemy.index')->with('success', "Musuh {$enemy->name} berhasil ditambahkan!");
    }

    /**
     * Update data musuh
     */
    public function update(Request $request, Enemy $enemy): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:enemies,slug,' . $enemy->id,
            'category' => 'required|string|max:100',
            'family' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'elements' => 'nullable|array',
            'description' => 'nullable|string',
            'icon_url' => 'nullable|url|max:500',
            'mora_gained' => 'nullable|integer|min:0',
            'tips_strategy' => 'nullable|string',
            'immunities' => 'nullable|array',
            'weakness_elements' => 'nullable|array',
            'recommended_mechanics' => 'nullable|array',
        ]);

        $enemy->update($validated);

        return redirect()->route('enemy.index')->with('success', "Data musuh {$enemy->name} berhasil diperbarui!");
    }

    /**
     * Hapus data musuh
     */
    public function destroy(Enemy $enemy): RedirectResponse
    {
        $name = $enemy->name;
        $enemy->delete();

        return redirect()->route('enemy.index')->with('success', "Musuh {$name} berhasil dihapus.");
    }

    /**
     * Sinkronisasi data musuh dan drops dari Genshin API secara cepat menggunakan concurrent requests
     */
    public function syncAll(Request $request)
    {
        try {
            $syncedCount = 0;

            // 1. Ambil daftar regular enemies
            $enemiesListRes = Http::timeout(10)->get('https://genshin.jmp.blue/enemies');
            $enemyIds = $enemiesListRes->successful() ? $enemiesListRes->json() : [];

            // 2. Ambil daftar weekly bosses
            $bossesListRes = Http::timeout(10)->get('https://genshin.jmp.blue/boss/weekly-boss');
            $bossIds = $bossesListRes->successful() ? $bossesListRes->json() : [];

            // 3. Tarik data regular enemies secara concurrent pool dalam chunk kecil
            $chunks = array_chunk($enemyIds, 15);
            foreach ($chunks as $chunk) {
                $responses = Http::pool(function ($pool) use ($chunk) {
                    foreach ($chunk as $id) {
                        $pool->as($id)->timeout(12)->get("https://genshin.jmp.blue/enemies/{$id}");
                    }
                });

                foreach ($responses as $id => $res) {
                    if ($res instanceof \Illuminate\Http\Client\Response && $res->successful()) {
                        try {
                            $this->saveSyncedEnemy($res->json(), (string)$id, 'enemies');
                            $syncedCount++;
                        } catch (\Throwable $err) {
                            // Abaikan error individu agar tidak menggugurkan proses sync
                        }
                    }
                }
            }

            // 4. Tarik data weekly boss secara concurrent
            if (!empty($bossIds)) {
                $bossResponses = Http::pool(function ($pool) use ($bossIds) {
                    foreach ($bossIds as $id) {
                        $pool->as($id)->timeout(12)->get("https://genshin.jmp.blue/boss/weekly-boss/{$id}");
                    }
                });

                foreach ($bossResponses as $id => $res) {
                    if ($res instanceof \Illuminate\Http\Client\Response && $res->successful()) {
                        try {
                            $this->saveSyncedEnemy($res->json(), (string)$id, 'weekly-boss');
                            $syncedCount++;
                        } catch (\Throwable $err) {
                            // Abaikan error individu
                        }
                    }
                }
            }

            $message = "Berhasil mensinkronisasi {$syncedCount} master musuh beserta item drop-nya dari API!";

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'count' => $syncedCount]);
            }

            return redirect()->route('enemy.index')->with('success', $message);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal sinkronisasi data musuh: ' . $e->getMessage());
        }
    }

    /**
     * Helper menyimpan musuh hasil sinkronisasi API
     */
    protected function saveSyncedEnemy(array $d, string $id, string $typeEndpoint): void
    {
        $name = $d['name'] ?? Str::title(str_replace('-', ' ', $id));
        $slug = $id;

        $category = 'Common Enemies';
        if ($typeEndpoint === 'weekly-boss') {
            $category = 'Weekly Bosses';
        } elseif (!empty($d['type'])) {
            $category = match (strtolower($d['type'])) {
                'elite enemies' => 'Elite Enemies',
                'bosses', 'normal bosses' => 'Normal Bosses',
                'weekly bosses' => 'Weekly Bosses',
                default => 'Common Enemies',
            };
        }

        $iconUrl = $typeEndpoint === 'weekly-boss'
            ? "https://genshin.jmp.blue/boss/weekly-boss/{$id}/icon"
            : "https://genshin.jmp.blue/enemies/{$id}/icon";

        // Bersihkan dan ratakan array elements
        $rawElements = $d['elements'] ?? [];
        if (empty($rawElements) && !empty($d['element'])) {
            $rawElements = [$d['element']];
        }

        $elements = [];
        if (!empty($rawElements)) {
            array_walk_recursive($rawElements, function ($item) use (&$elements) {
                if (is_string($item) && trim($item) !== '') {
                    $elements[] = trim($item);
                }
            });
            $elements = array_values(array_unique($elements));
        }

        // Tentukan rekomendasi kelemahan elemental dasar secara otomatis
        $weakness = [];
        $mechanics = [];
        if (in_array('Hydro', $elements)) {
            $weakness = array_unique(array_merge($weakness, ['Cryo', 'Dendro', 'Electro']));
            $mechanics[] = 'freeze';
        }
        if (in_array('Pyro', $elements)) {
            $weakness = array_unique(array_merge($weakness, ['Hydro']));
            $mechanics[] = 'vaporize';
        }
        if (in_array('Cryo', $elements)) {
            $weakness = array_unique(array_merge($weakness, ['Pyro']));
            $mechanics[] = 'melt';
        }
        if (in_array('Electro', $elements)) {
            $weakness = array_unique(array_merge($weakness, ['Cryo', 'Pyro', 'Dendro']));
        }
        if ($category === 'Weekly Bosses' || $category === 'Normal Bosses') {
            $mechanics[] = 'shielder';
            $mechanics[] = 'healer';
        }

        $enemy = Enemy::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'category' => $category,
                'family' => $d['family'] ?? $d['faction'] ?? null,
                'region' => $d['region'] ?? 'Global',
                'elements' => $elements,
                'description' => $d['description'] ?? null,
                'icon_url' => $iconUrl,
                'mora_gained' => (int)($d['mora-gained'] ?? 0),
                'weakness_elements' => $weakness,
                'recommended_mechanics' => array_values(array_unique($mechanics)),
            ]
        );

        // Sync Drops
        if (!empty($d['drops']) && is_array($d['drops'])) {
            $enemy->drops()->delete();
            foreach ($d['drops'] as $dropData) {
                if (empty($dropData['name'])) continue;

                // Cari relasi ke material jika ada
                $mat = material::where('name', 'like', '%' . $dropData['name'] . '%')->first();

                EnemyDrop::create([
                    'enemy_id' => $enemy->id,
                    'material_id' => $mat?->id,
                    'name' => $dropData['name'],
                    'drop_type' => 'material',
                    'rarity' => $dropData['rarity'] ?? $mat?->rarity ?? 1,
                    'minimum_level' => $dropData['minimum-level'] ?? null,
                    'source_note' => $dropData['source'] ?? null,
                    'icon_url' => $mat?->images ?? null,
                ]);
            }
        }
    }

    /**
     * Endpoint API JSON untuk integrasi lain
     */
    public function apiList(): JsonResponse
    {
        $enemies = Enemy::with('drops')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $enemies,
        ]);
    }
}