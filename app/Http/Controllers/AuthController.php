<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GameAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('inventory.dashboard');
        }

        return view('auth.login', [
            'title' => 'Masuk - Genshin Impact Tracker',
        ]);
    }

    /**
     * Proses login user
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Rate limiting: cegah brute-force login (maksimal 5 percobaan per menit)
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik."]);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();

            // Auto-assign akun game lama yang belum memiliki owner ke user pertama
            if (User::count() === 1 && GameAccount::withoutGlobalScopes()->whereNull('user_id')->exists()) {
                GameAccount::withoutGlobalScopes()->whereNull('user_id')->update(['user_id' => $user->id]);
            }

            return redirect()->intended(route('inventory.dashboard'))
                ->with('success', "Selamat datang kembali, {$user->name}!");
        }

        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
    }

    /**
     * Tampilkan form registrasi
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('inventory.dashboard');
        }

        return view('auth.register', [
            'title' => 'Daftar Akun Baru - Genshin Tracker',
        ]);
    }

    /**
     * Proses registrasi user baru
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:50'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah terdaftar. Silakan gunakan email lain atau masuk.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal harus 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => trim($validated['name']),
            'email'    => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Jika ini user pertama, otomatis hubungkan akun game yang belum ada owner-nya
        if (User::count() === 1 && GameAccount::withoutGlobalScopes()->whereNull('user_id')->exists()) {
            GameAccount::withoutGlobalScopes()->whereNull('user_id')->update(['user_id' => $user->id]);
        }

        return redirect()->route('inventory.dashboard')
            ->with('success', "Selamat datang di Genshin Impact Tracker, {$user->name}! Akun Anda berhasil dibuat.");
    }

    /**
     * Proses logout user
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')
            ->with('info', 'Anda telah berhasil keluar.');
    }
}