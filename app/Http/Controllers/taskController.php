<?php

namespace App\Http\Controllers;

use App\Models\task;
use App\Models\material;
use App\Models\subTask;
use App\Models\Character;
use App\Models\Weapon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class taskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data['characters'] = Character::where('is_active', true)->orderBy('name')->get(['id', 'name', 'element', 'weapon_type', 'icon_url', 'rarity']);
        $data['weapons'] = Weapon::orderBy('name')->get(['id', 'name', 'type', 'rarity', 'icon_url']);
        $data['dataMaterial'] = material::orderby('familie_id', 'ASC')->orderby('name', 'ASC')->get();
        $data['dataTask'] = task::with('sub_task.material')
                            ->orderby('prioritas', 'ASC')
                            ->where('status', 'start')
                            ->orderby('created_at', 'ASC')
                            ->get();
        
        foreach ($data['dataTask'] as $keyTask => $task) {
            # looping subtask
            $statusUpgrade = true;
            foreach ($task->sub_task as $keysubtask => $subtask) {
                $material = $subtask->material;
                if (!$material) continue;
                
                $source = json_decode($material->source ?? '[]');
                if(is_array($source) && in_array("Didapatkan melalui Craft", $source)){
                    $hasilCraft = 0;
                    $jumlahCraft = 0;
                    $arrSumberCraft = array();
                    for ($i=1; $i < $material->rarity; $i++) { 
                        $sumberCraft = material::where('familie_id', $material->familie_id)->where('rarity', $i)->first();
                        
                        if($sumberCraft){
                            $hasilCraft = (int) (($sumberCraft->amount + $hasilCraft) / 3);
                            $jumlahCraft = $hasilCraft;
                            array_push($arrSumberCraft, $sumberCraft);
                        }
                    }
                    $material['hasilCraft'] = $jumlahCraft;
                    $material['sumberCraft'] = $arrSumberCraft;
                }
                
                $dimiliki = $material->amount ?? 0;
                $dibutuhkan = $subtask->amount ?? 0;
                if (isset($material['hasilCraft'])) {
                    $dimiliki += $material['hasilCraft'];
                }

                if($dimiliki >= $dibutuhkan) {
                    $statusUpgrade = $statusUpgrade == false ? false : true;
                } else {
                    $statusUpgrade = false;
                }
            }
            $task['statusUpgrade'] = $statusUpgrade;
        }
        $data['title'] = 'Task Tracker';
        return Response(view('task.index', $data));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return $this->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nameTask' => 'required|string|max:255',
            'prioritas' => 'required|integer|min:1',
        ]);

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
            'nama_task' => $request->nameTask,
            'images'    => $imageUrl,
            'jenis'     => $request->jenis_task ?: 'stat',
            'status'    => 'start',
            'prioritas' => (int) $request->prioritas,
        ]);

        if ($task && $request->has('namaMaterial') && is_array($request->namaMaterial)) {
            foreach ($request->namaMaterial as $key => $materialId) {
                if (empty($materialId)) continue;
                $dibutuhkan = max(1, (int) ($request->amount[$key] ?? 1));
                subTask::create([
                    'task_id'     => $task->id,
                    'material_id' => $materialId,
                    'amount'      => $dibutuhkan,
                ]);
            }
        }
        
        return redirect()->route('task.index')->with('success', 'Task "' . $task->nama_task . '" berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(task $task): Response
    {
        return $this->index();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(task $task): Response
    {
        return $this->index();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, task $task): RedirectResponse
    {
        $request->validate([
            'nameTaskEdit' => 'required|string|max:255',
            'prioritasEdit' => 'required|integer|min:1',
        ]);

        $task->update([
            'nama_task' => $request->nameTaskEdit,
            'jenis'     => $request->jenis_taskEdit ?: $task->jenis,
            'prioritas' => (int) $request->prioritasEdit,
            'images'    => $request->urlImageEdit ?: $task->images,
        ]);

        if ($request->has('namaMaterial') && is_array($request->namaMaterial)) {
            subTask::where('task_id', $task->id)->delete();

            foreach ($request->namaMaterial as $key => $materialId) {
                if (empty($materialId)) continue;
                $amount = max(1, (int) ($request->amount[$key] ?? 1));
                subTask::create([
                    'task_id'     => $task->id,
                    'material_id' => $materialId,
                    'amount'      => $amount,
                ]);
            }
        }

        return redirect()->route('task.index')->with('success', 'Perubahan task berhasil disimpan!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(task $task): RedirectResponse
    {
        subTask::where('task_id', $task->id)->delete();
        $task->delete();
        
        return redirect()->route('task.index')->with('success', 'Task berhasil dihapus.');
    }

    public function craftingBuild(Request $request)
    {
        $idMaterial = $request->formidmaterial;
        $crafting = (int) $request->formRangeCraft;

        $getMaterial = material::find($idMaterial);
        if ($getMaterial && $crafting > 0) {
            $getMaterial->amount = max(0, $getMaterial->amount - ($crafting * 3));
            $getMaterial->save();

            $getCraftMaterial = material::where('familie_id', $getMaterial->familie_id)
                                ->where('rarity', ($getMaterial->rarity + 1))
                                ->first();
            if ($getCraftMaterial) {
                $getCraftMaterial->update([
                    'amount' => $getCraftMaterial->amount + $crafting
                ]);
            }
        }
        
        return redirect()->route('task.index')->with('success', 'Crafting material berhasil!');
    }

    public function editMaterial(Request $request)
    {
        $idMaterial = $request->formidUpdatematerial;
        $amount = (int) $request->formAmountMaterial;

        $getMaterial = material::find($idMaterial);
        if ($getMaterial) {
            $newAmount = max(0, $getMaterial->amount + $amount);
            $getMaterial->amount = $newAmount;
            $getMaterial->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $getMaterial->id ?? null,
                'new_amount' => $getMaterial->amount ?? 0
            ]);
        }

        return redirect()->route('task.index');
    }

    public function upgradeTaskComplete($id)
    {
        $getTask = task::with('sub_task')->find($id);
        if ($getTask) {
            foreach ($getTask->sub_task as $subtask) {
                $updateMaterial = material::find($subtask->material_id);
                if ($updateMaterial) {
                    $newAmount = max(0, $updateMaterial->amount - $subtask->amount);
                    $updateMaterial->update(['amount' => $newAmount]);
                }
            }
            $getTask->update(['status' => 'complete']);
            return redirect()->route('task.index')->with('success', "Task \"{$getTask->nama_task}\" berhasil di-upgrade!");
        }

        return redirect()->route('task.index');
    }

    public function getCharacterTalentMaterials(Request $request, $characterId)
    {
        $currentLevel = (int) $request->input('current_level', 1);
        $targetLevel  = (int) $request->input('target_level', 8);
        $talentCount  = (int) $request->input('talent_count', 1);

        $result = \App\Services\TalentMaterialService::calculateRequirements(
            (int) $characterId,
            $currentLevel,
            $targetLevel,
            $talentCount
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
