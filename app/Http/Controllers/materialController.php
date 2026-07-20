<?php

namespace App\Http\Controllers;

use App\Models\material;
use App\Models\family;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class materialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data['dataFamily'] = family::with('material')->orderBy('name', 'ASC')->get();
        $data['title'] = 'Material';

        return Response(view('material.index', $data));
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
        $material_ = json_decode($request->dataMaterial);
        $family_id = $request->family_id;
        $amount = $request->amount;

        $imgUrl = '';
        if (isset($material_->images)) {
            if (isset($material_->images->fandom)) {
                $imgUrl = $material_->images->fandom;
            } elseif (isset($material_->images->redirect)) {
                $imgUrl = $material_->images->redirect;
            } elseif (isset($material_->images->filename_icon)) {
                $imgUrl = 'https://upload-os-bbs.mihoyo.com/game_record/genshin/equip/' . $material_->images->filename_icon . '.png';
            }
        }

        $data = array(
            'name' => $material_->name,
            'familie_id' => $family_id,
            'rarity' => isset($material_->rarity) ? $material_->rarity : 0,
            'category' => isset($material_->category) ? $material_->category : null,
            'materialtype' => isset($material_->materialtype) ? $material_->materialtype : (isset($material_->typeText) ? $material_->typeText : null),
            'dropdomain' => isset($material_->dropdomain) ? $material_->dropdomain : null,
            'amount' => $amount,
            'description' => isset($material_->description) ? $material_->description : null,
            'images' => $imgUrl,
            'daysofweek' => isset($material_->daysofweek) ? json_encode($material_->daysofweek) : (isset($material_->daysOfWeek) ? json_encode($material_->daysOfWeek) : '[]'),
            'source' => isset($material_->source) ? json_encode($material_->source) : (isset($material_->sources) ? json_encode($material_->sources) : '[]'),
        );

        // Cek data
        $cekdata = material::where('name', $material_->name)->first();

        if(isset($cekdata)){
            $updateMount = $cekdata->update([
                'amount' => (int) $cekdata->amount + $amount
            ]);
        }else{
            $created = material::create($data);
        }

        return redirect()->route('material.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(material $material): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(material $material): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, material $material): RedirectResponse
    {
        $material->update([
            'amount' => $request->amount,
            'familie_id' => $request->family_id
        ]);

        return redirect()->route('material.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(material $material): RedirectResponse
    {
        //
    }
}
