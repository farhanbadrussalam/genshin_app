<?php

namespace App\Http\Controllers;

use App\Models\task;
use App\Models\material;
use App\Models\subTask;
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
        $data['dataMaterial'] = material::orderby('familie_id', 'ASC')->orderby('name', 'ASC')->get();
        $data['dataTask'] = task::with('sub_task')
                            ->orderby('prioritas', 'ASC')
                            ->where('status', 'start')
                            ->orderby('created_at', 'ASC')
                            ->get();
        
        foreach ($data['dataTask'] as $keyTask => $task) {
            # looping subtask
            $statusUpgrade = true;
            foreach ($task->sub_task as $keysubtask => $subtask) {
                $material = $subtask->material;
                
                $source = json_decode($material->source);
                if(in_array("Didapatkan melalui Craft", $source)){
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
                
                $dimiliki = $material->amount;
                $dibutuhkan = $subtask->amount;
                isset($material['hasilCraft']) ? $dimiliki += $material['hasilCraft'] : null;

                if($dimiliki >= $dibutuhkan) {
                    $statusUpgrade = $statusUpgrade == false ? false : true;
                }else{
                    $statusUpgrade = false;
                }
            }
            $task['statusUpgrade'] = $statusUpgrade;
        }
        $data['title'] = 'Task Upgraded';
        return Response(view('task.index', $data));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = array(
            'nama_task' => $request->nameTask,
            'images' => $request->urlImage,
            'jenis' => $request->jenis_task,
            'status' => 'start', //complete,finish
            'prioritas' => $request->prioritas
        );

        $task = task::create($data);
        if($task){
            foreach ($request->namaMaterial as $key => $value) {
                $dibutuhkan = $request->amount[$key];
                $material = array(
                    'material_id' => $value,
                    'task_id' => $task->id,
                    'amount' => $dibutuhkan
                );

                subTask::create($material);
            }
        }
        
        return redirect()->route('task.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(task $task): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(task $task): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, task $task): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(task $task): RedirectResponse
    {
        subTask::where('task_id', $task->id)->delete();

        $task->delete();
        
        return redirect()->route('task.index');
    }

    public function craftingBuild(Request $request)
    {
        $idMaterial = $request->formidmaterial;
        $crafting = $request->formRangeCraft;

        $getMaterial = material::find($idMaterial);

        $getMaterial->amount = $getMaterial->amount - ($crafting * 3);
        $getMaterial->save();

        $getCraftMaterial = material::where('familie_id', $getMaterial->familie_id)
                            ->where('rarity', ($getMaterial->rarity + 1))
                            ->first();
        $getCraftMaterial->update([
            'amount' => $getCraftMaterial->amount + $crafting
        ]);
        
        return redirect()->route('task.index');
    }

    public function editMaterial(Request $request)
    {
        $idMaterial = $request->formidUpdatematerial;
        $amount = $request->formAmountMaterial;

        $getMaterial = material::find($idMaterial);
        $getMaterial->amount = $getMaterial->amount + $amount;
        $getMaterial->save();

        return redirect()->route('task.index');
    }

    public function upgradeTaskComplete($id)
    {
        $getTask = task::with('sub_task')->find($id);
        foreach ($getTask->sub_task as $key => $subtask) {
            $updateMaterial = material::find($subtask->material_id);

            $updateMaterial->update([
                'amount' => $updateMaterial->amount - $subtask->amount
            ]);
        }
        $getTask->update([
            'status' => 'complete'
        ]);

        return redirect()->route('task.index');
    }
}
