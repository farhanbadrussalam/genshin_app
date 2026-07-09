<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Family;
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
        $data['dataFamily'] = Family::with('material')->orderBy('name', 'ASC')->get();
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

        $data = array(
            'name' => $material_->name,
            'familie_id' => $family_id,
            'rarity' => isset($material_->rarity) ? $material_->rarity : 0,
            'category' => $material_->category,
            'materialtype' => $material_->materialtype,
            'dropdomain' => isset($material_->dropdomain) ? $material_->dropdomain : null,
            'amount' => $amount,
            'description' => $material_->description,
            'images' => isset($material_->images->fandom) ? $material_->images->fandom : $material_->images->redirect,
            'daysofweek' => isset($material_->daysofweek) ? json_encode($material_->daysofweek) : '[]',
            'source' => isset($material_->source) ? json_encode($material_->source) : '[]',
        );

        // Cek data
        $cekdata = Material::where('name', $material_->name)->first();

        if(isset($cekdata)){
            $updateMount = $cekdata->update([
                'amount' => (int) $cekdata->amount + $amount
            ]);
        }else{
            $created = Material::create($data);
        }

        return redirect()->route('material.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material): RedirectResponse
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
    public function destroy(Material $material): RedirectResponse
    {
        //
    }
}
