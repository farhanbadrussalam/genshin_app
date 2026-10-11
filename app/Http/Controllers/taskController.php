<?php

namespace App\Http\Controllers;

use App\Models\task;
use App\Models\material;
use App\Models\subTask;
use App\Models\Character;
use App\Models\Weapon;
use App\Models\GameAccount;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class taskController extends Controller
{
    /**
     * Tampilkan daftar task tracker untuk akun yang sedang dipilih
     */
    public function index(Request $request): Response
    {
        $accounts = GameAccount::where('game', 'genshin_impact')
            ->orderBy('nickname')
            ->get();

        if ($accounts->isEmpty()) {
            $accounts = GameAccount::orderBy('nickname')->get();
        }

        $selectedAccountId = $request->input('account_id', session('active_game_account_id', $accounts->first()?->id));
        $activeAccount = $accounts->firstWhere('id', (int) $selectedAccountId) ?? $accounts->first();

        if ($activeAccount) {
            session(['active_game_account_id' => $activeAccount->id]);
        }

        $currentStatus = $request->input('status', 'start'); // 'start' (aktif) atau 'complete' (selesai) atau 'all'

        $data['accounts'] = $accounts;
        $data['activeAccount'] = $activeAccount;
        $data['characters'] = Character::where('is_active', true)->orderBy('name')->get(['id', 'name', 'element', 'weapon_type', 'icon_url', 'rarity']);
        $data['weapons'] = Weapon::orderBy('name')->get(['id', 'name', 'type', 'rarity', 'icon_url']);
        $data['dataMaterial'] = material::orderby('familie_id', 'ASC')->orderby('name', 'ASC')->get();
        $data['currentStatus'] = $currentStatus;

        $todayName = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->locale('id')->isoFormat('dddd');
        $data['todayName'] = $todayName;

        // Query dasar terhubung akun yang dipilih
        $baseQuery = task::with('sub_task.material');
        if ($activeAccount) {
            $baseQuery->where('game_account_id', $activeAccount->id);
        }

        // Statistik task
        $data['activeCount'] = (clone $baseQuery)->where('status', 'start')->count();
        $data['completedCount'] = (clone $baseQuery)->where('status', 'complete')->count();
        $data['highPriorityCount'] = (clone $baseQuery)->where('status', 'start')->where('prioritas', '<=', 3)->count();

        // Filter status
        $taskQuery = clone $baseQuery;
        if ($currentStatus === 'complete') {
            $taskQuery->where('status', 'complete')->orderby('updated_at', 'DESC');
        } elseif ($currentStatus === 'all') {
            $taskQuery->orderby('status', 'ASC')->orderby('prioritas', 'ASC')->orderby('created_at', 'ASC');
        } else {
            $taskQuery->where('status', 'start')->orderby('prioritas', 'ASC')->orderby('created_at', 'ASC');
        }

        $tasks = $taskQuery->get();

        foreach ($tasks as $t) {
            $totalSub = $t->sub_task->count();
            $doneSub = $t->sub_task->where('is_completed', true)->count();
            $t->total_subtasks = $totalSub;
            $t->completed_subtasks = $doneSub;
            $t->progress_pct = $totalSub > 0 ? (int) round(($doneSub / $totalSub) * 100) : ($t->status === 'complete' ? 100 : 0);

            // Cek jadwal farming hari ini
            $hasFarmingToday = false;
            foreach ($t->sub_task as $st) {
                if ($st->is_completed) continue;
                $m = $st->material;
                if ($m) {
                    $days = json_decode($m->daysofweek ?? '[]', true) ?: [];
                    $isToday = in_array($todayName, $days) || in_array('Minggu', $days);
                    $m->is_available_today = $isToday;
                    if ($isToday) {
                        $hasFarmingToday = true;
                    }
                }
            }
            $t->has_farming_today = $hasFarmingToday;
        }

        $data['dataTask'] = $tasks;
        $data['title'] = 'Task Tracker';

        return Response(view('task.index', $data));
    }

    /**
     * Toggle status task (Tandai Selesai / Buka Kembali)
     */
    public function toggleStatus(Request $request, $id): RedirectResponse
    {
        $task = task::findOrFail($id);
        $accId = $task->game_account_id ?: session('active_game_account_id');

        if ($task->status === 'complete') {
            // Reopen task
            $task->update(['status' => 'start']);
            $msg = "Task \"{$task->nama_task}\" dikembalikan ke daftar aktif.";
            $targetStatus = 'start';
        } else {
            // Tandai selesai
            $task->update(['status' => 'complete']);
            $task->sub_task()->update(['is_completed' => true]);
            $msg = "Task \"{$task->nama_task}\" berhasil ditandai selesai!";
            $targetStatus = 'complete';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'new_status' => $task->status,
                'message' => $msg
            ]);
        }

        return redirect()->route('task.index', ['account_id' => $accId, 'status' => $request->input('redirect_status', $targetStatus)])
            ->with('success', $msg);
    }

    /**
     * Toggle status checklist per item material (SubTask)
     */
    public function toggleSubTask(Request $request, $id): JsonResponse|RedirectResponse
    {
        $sub = subTask::with('task')->findOrFail($id);
        $sub->is_completed = !$sub->is_completed;
        $sub->save();

        $parentTask = $sub->task;
        $allSubs = subTask::where('task_id', $parentTask->id)->get();
        $allDone = $allSubs->every(fn($item) => $item->is_completed);

        if ($allDone && $parentTask->status !== 'complete') {
            $parentTask->update(['status' => 'complete']);
        } elseif (!$allDone && $parentTask->status === 'complete') {
            $parentTask->update(['status' => 'start']);
        }

        $totalSub = $allSubs->count();
        $doneSub = $allSubs->where('is_completed', true)->count();
        $pct = $totalSub > 0 ? (int) round(($doneSub / $totalSub) * 100) : 0;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'sub_task_id'    => $sub->id,
                'is_completed'   => $sub->is_completed,
                'parent_status'  => $parentTask->status,
                'progress_pct'   => $pct,
                'done_subtasks'  => $doneSub,
                'total_subtasks' => $totalSub,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Backward compatibility untuk taskComplete route
     */
    public function upgradeTaskComplete($id, Request $request): RedirectResponse
    {
        return $this->toggleStatus($request, $id);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        return $this->index($request);
    }

    /**
     * Simpan task baru
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nameTask' => 'required|string|max:255',
            'prioritas' => 'required|integer|min:1',
        ]);

        $accountId = $request->input('game_account_id', session('active_game_account_id'));
        if (!$accountId) {
            $firstAcc = GameAccount::where('game', 'genshin_impact')->first();
            $accountId = $firstAcc?->id;
        }

        $imageUrl = $request->urlImage;
        if (empty($imageUrl)) {
            if ($request->has('target_character_id') && !empty($request->target_character_id)) {
                $char = Character::find($request->target_character_id);
                $imageUrl = $char?->icon_url;
            } elseif ($request->has('target_weapon_id') && !empty($request->target_weapon_id)) {
                $weap = Weapon::find($request->target_weapon_id);
                $imageUrl = $weap?->icon_url;
            }
        }

        $task = task::create([
            'game_account_id' => $accountId,
            'nama_task'       => $request->nameTask,
            'images'          => $imageUrl ?: 'https://gi.yatta.moe/assets/UI/UI_AvatarIcon_0.png',
            'jenis'           => $request->jenis_task ?: 'stat',
            'status'          => 'start',
            'prioritas'       => (int) $request->prioritas,
        ]);

        if ($task && $request->has('namaMaterial') && is_array($request->namaMaterial)) {
            foreach ($request->namaMaterial as $key => $materialId) {
                if (empty($materialId)) continue;
                $dibutuhkan = max(1, (int) ($request->amount[$key] ?? 1));
                subTask::create([
                    'task_id'      => $task->id,
                    'material_id'  => $materialId,
                    'amount'       => $dibutuhkan,
                    'is_completed' => false,
                ]);
            }
        }
        
        return redirect()->route('task.index', ['account_id' => $accountId])
            ->with('success', 'Task "' . $task->nama_task . '" berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(task $task): Response
    {
        return $this->index(request());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(task $task): Response
    {
        return $this->index(request());
    }

    /**
     * Update task yang sudah ada
     */
    public function update(Request $request, task $task): RedirectResponse
    {
        $request->validate([
            'nameTaskEdit' => 'required|string|max:255',
            'prioritasEdit' => 'required|integer|min:1',
        ]);

        $taskData = [
            'nama_task' => $request->nameTaskEdit,
            'jenis'     => $request->jenis_taskEdit ?: $task->jenis,
            'prioritas' => (int) $request->prioritasEdit,
            'images'    => $request->urlImageEdit ?: $task->images,
        ];

        if ($request->filled('game_account_id')) {
            $taskData['game_account_id'] = $request->input('game_account_id');
        }

        $task->update($taskData);

        if ($request->has('namaMaterial') && is_array($request->namaMaterial)) {
            // Ambil status is_completed lama jika ada
            $existingDone = subTask::where('task_id', $task->id)->where('is_completed', true)->pluck('material_id')->toArray();
            subTask::where('task_id', $task->id)->delete();

            foreach ($request->namaMaterial as $key => $materialId) {
                if (empty($materialId)) continue;
                $amount = max(1, (int) ($request->amount[$key] ?? 1));
                subTask::create([
                    'task_id'      => $task->id,
                    'material_id'  => $materialId,
                    'amount'       => $amount,
                    'is_completed' => in_array($materialId, $existingDone),
                ]);
            }
        }

        return redirect()->route('task.index', ['account_id' => $task->game_account_id])
            ->with('success', 'Perubahan task berhasil disimpan!');
    }

    /**
     * Hapus task
     */
    public function destroy(task $task): RedirectResponse
    {
        $accId = $task->game_account_id;
        subTask::where('task_id', $task->id)->delete();
        $task->delete();
        
        return redirect()->route('task.index', ['account_id' => $accId])
            ->with('success', 'Task berhasil dihapus.');
    }

    public function craftingBuild(Request $request): RedirectResponse
    {
        return redirect()->route('task.index')->with('info', 'Material craft telah disederhanakan ke sistem checklist langsung.');
    }

    public function editMaterial(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function getCharacterTalentMaterials(Request $request, $characterId)
    {
        $currentLevel = (int) $request->input('current_level', 1);
        $targetLevel  = (int) $request->input('target_level', 8);
        $talentCount  = (int) $request->input('talent_count', 1);
        $accountId    = $request->input('account_id', session('active_game_account_id'));

        $result = \App\Services\TalentMaterialService::calculateRequirements(
            (int) $characterId,
            $currentLevel,
            $targetLevel,
            $talentCount,
            $accountId
        );

        return response()->json($result);
    }

    public function getTalentPresets($characterId)
    {
        $presets = \App\Services\TalentMaterialService::getPresetsForCharacter((int) $characterId);
        return response()->json([
            'success' => true,
            'presets' => $presets
        ]);
    }
}