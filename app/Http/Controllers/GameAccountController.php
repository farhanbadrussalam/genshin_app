<?php

namespace App\Http\Controllers;

use App\Models\GameAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class GameAccountController extends Controller
{
    /**
     * Tampilkan daftar semua akun game
     */
    public function index(): Response
    {
        $data['accounts'] = GameAccount::latest()->get();
        $data['title']    = 'Game Accounts';
        $data['games']    = GameAccount::supportedGames();
        $data['servers']  = GameAccount::supportedServers();

        return Response(view('inventory.accounts', $data));
    }

    /**
     * Simpan akun game baru
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'game'     => 'required|string',
            'uid'      => 'required|string|max:20|unique:game_accounts,uid',
            'nickname' => 'required|string|max:100',
            'server'   => 'required|string|max:10',
            'notes'    => 'nullable|string',
        ], [
            'uid.unique' => 'UID ini sudah terdaftar.',
        ]);

        GameAccount::create([
            'game'     => $request->input('game'),
            'uid'      => $request->input('uid'),
            'nickname' => $request->input('nickname'),
            'server'   => $request->input('server'),
            'notes'    => $request->input('notes'),
        ]);

        return redirect()->route('game-accounts.index')
            ->with('success', 'Akun game berhasil ditambahkan!');
    }

    /**
     * Update akun game
     */
    public function update(Request $request, GameAccount $gameAccount): RedirectResponse
    {
        $request->validate([
            'nickname' => 'required|string|max:100',
            'server'   => 'required|string|max:10',
            'notes'    => 'nullable|string',
        ]);

        $gameAccount->update([
            'nickname' => $request->input('nickname'),
            'server'   => $request->input('server'),
            'notes'    => $request->input('notes'),
        ]);

        return redirect()->route('game-accounts.index')
            ->with('success', 'Akun game berhasil diperbarui!');
    }

    /**
     * Hapus akun game
     */
    public function destroy(GameAccount $gameAccount): RedirectResponse
    {
        $gameAccount->delete();

        return redirect()->route('game-accounts.index')
            ->with('success', 'Akun game berhasil dihapus.');
    }
}
