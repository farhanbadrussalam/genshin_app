<?php

namespace App\Http\Controllers;

use App\Models\family;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class familyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data['dataFamily'] = family::all();
        $data['title'] = 'Family Material';
        return Response(view('family.index', $data));
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
        $validation = $request->validate([
            'nameFamily' => 'required'
        ]);

        $data = array(
            'name' => $request->nameFamily
        );

        family::create($data);

        return redirect()->route('family.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(family $family): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(family $family): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, family $family): RedirectResponse
    {
        $family->update([
            'name' => $request->nameFamily
        ]);

        return redirect()->route('family.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(family $family): RedirectResponse
    {
        $family->delete();

        return redirect()->route('family.index');
    }
}
