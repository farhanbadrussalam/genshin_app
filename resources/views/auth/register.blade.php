@extends('layout.main')

@section('content')
@php $title = 'Daftar Akun Baru - Genshin Tracker'; @endphp
@include('layout.header')

<div class="container py-5 my-auto" style="min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center;">
  <div class="row justify-content-center w-100">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
      
      {{-- Card Register Glassmorphism --}}
      <div class="auth-card p-4 p-sm-5 animate-fade-in-up" 
           style="background: rgba(17, 24, 39, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(229, 160, 41, 0.3); border-radius: 20px; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.7), 0 0 25px rgba(229,160,41,0.15);">
        
        {{-- Header & Logo --}}
        <div class="text-center mb-4">
          <div class="auth-icon-badge d-inline-flex align-items-center justify-content-center mb-3" 
               style="width: 58px; height: 58px; border-radius: 16px; background: rgba(229, 160, 41, 0.15); border: 1px solid rgba(229, 160, 41, 0.4); color: #ffd700; font-size: 1.8rem; text-shadow: 0 0 15px rgba(229,160,41,0.6);">
            ✦
          </div>
          <h3 class="font-display text-gold mb-1" style="font-size: 1.55rem; letter-spacing: 0.05em;">
            Buat Akun Baru
          </h3>
          <p class="text-muted small mb-0" style="font-size: 0.84rem;">
            Mulai kelola inventori karakter dan artefak Genshin Impact Anda
          </p>
        </div>

        @if($errors->any())
          <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-3 py-2 px-3 small" role="alert" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <i class="bi bi-exclamation-triangle-fill me-1.5"></i>{{ $errors->first() }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
          </div>
        @endif

        {{-- Form Register --}}
        <form method="POST" action="{{ route('register.post') }}" id="registerForm">
          @csrf

          {{-- Nama Lengkap --}}
          <div class="mb-3">
            <label for="name" class="form-label text-light fw-medium small mb-1.5">
              <i class="bi bi-person-badge-fill text-gold me-1"></i>Nama Lengkap / Traveler
            </label>
            <div class="input-group">
              <span class="input-group-text border-secondary bg-dark text-muted" style="border-color: rgba(255,255,255,0.15) !important;">
                <i class="bi bi-person"></i>
              </span>
              <input type="text" 
                     name="name" 
                     id="name" 
                     class="form-control text-light @error('name') is-invalid @enderror" 
                     style="background: rgba(15, 23, 42, 0.7); border-color: rgba(255,255,255,0.15); font-size: 0.9rem;"
                     placeholder="contoh: Aether / Lumine" 
                     value="{{ old('name') }}" 
                     required 
                     autofocus>
            </div>
          </div>

          {{-- Email --}}
          <div class="mb-3">
            <label for="email" class="form-label text-light fw-medium small mb-1.5">
              <i class="bi bi-envelope-fill text-gold me-1"></i>Alamat Email
            </label>
            <div class="input-group">
              <span class="input-group-text border-secondary bg-dark text-muted" style="border-color: rgba(255,255,255,0.15) !important;">
                <i class="bi bi-envelope"></i>
              </span>
              <input type="email" 
                     name="email" 
                     id="email" 
                     class="form-control text-light @error('email') is-invalid @enderror" 
                     style="background: rgba(15, 23, 42, 0.7); border-color: rgba(255,255,255,0.15); font-size: 0.9rem;"
                     placeholder="contoh@domain.com" 
                     value="{{ old('email') }}" 
                     required>
            </div>
          </div>

          {{-- Password --}}
          <div class="row g-2 mb-4">
            <div class="col-12 col-sm-6">
              <label for="password" class="form-label text-light fw-medium small mb-1.5">
                <i class="bi bi-key-fill text-gold me-1"></i>Kata Sandi
              </label>
              <div class="input-group">
                <span class="input-group-text border-secondary bg-dark text-muted" style="border-color: rgba(255,255,255,0.15) !important;">
                  <i class="bi bi-lock"></i>
                </span>
                <input type="password" 
                       name="password" 
                       id="password" 
                       class="form-control text-light @error('password') is-invalid @enderror" 
                       style="background: rgba(15, 23, 42, 0.7); border-color: rgba(255,255,255,0.15); font-size: 0.9rem;"
                       placeholder="Min. 6 karakter" 
                       required>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <label for="password_confirmation" class="form-label text-light fw-medium small mb-1.5">
                <i class="bi bi-shield-check text-gold me-1"></i>Ulangi Sandi
              </label>
              <div class="input-group">
                <span class="input-group-text border-secondary bg-dark text-muted" style="border-color: rgba(255,255,255,0.15) !important;">
                  <i class="bi bi-lock-fill"></i>
                </span>
                <input type="password" 
                       name="password_confirmation" 
                       id="password_confirmation" 
                       class="form-control text-light" 
                       style="background: rgba(15, 23, 42, 0.7); border-color: rgba(255,255,255,0.15); font-size: 0.9rem;"
                       placeholder="Ulangi sandi" 
                       required>
              </div>
            </div>
          </div>

          {{-- Submit Button --}}
          <button type="submit" class="btn btn-genshin w-100 py-2.5 mb-3 fw-bold shadow" id="btnSubmitRegister" style="font-size: 0.95rem; letter-spacing: 0.05em;">
            <i class="bi bi-person-plus-fill me-1.5"></i>Daftar Sekarang
          </button>
        </form>

        {{-- Divider --}}
        <div class="position-relative text-center my-3">
          <hr class="border-secondary opacity-25">
          <span class="position-absolute top-50 start-50 translate-middle px-3 text-muted" style="background: rgba(17, 24, 39, 0.95); font-size: 0.75rem;">
            SUDAH PUNYA AKUN?
          </span>
        </div>

        {{-- Login Link --}}
        <div class="text-center pt-2">
          <a href="{{ route('login') }}" class="text-gold fw-semibold text-decoration-none small" style="transition: color 0.2s ease;">
            <i class="bi bi-arrow-left-short"></i> Masuk ke Akun yang Ada
          </a>
        </div>

      </div>

    </div>
  </div>
</div>
@endsection