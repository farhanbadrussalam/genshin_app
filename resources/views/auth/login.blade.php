@extends('layout.main')

@section('content')
@php $title = 'Masuk - Genshin Impact Tracker'; @endphp
@include('layout.header')

<div class="container py-5 my-auto" style="min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center;">
  <div class="row justify-content-center w-100">
    <div class="col-12 col-md-8 col-lg-5 col-xl-4">
      
      {{-- Card Login Glassmorphism --}}
      <div class="auth-card p-4 p-sm-5 animate-fade-in-up" 
           style="background: rgba(17, 24, 39, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(229, 160, 41, 0.3); border-radius: 20px; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.7), 0 0 25px rgba(229,160,41,0.15);">
        
        {{-- Header & Logo --}}
        <div class="text-center mb-4">
          <div class="auth-icon-badge d-inline-flex align-items-center justify-content-center mb-3" 
               style="width: 58px; height: 58px; border-radius: 16px; background: rgba(229, 160, 41, 0.15); border: 1px solid rgba(229, 160, 41, 0.4); color: #ffd700; font-size: 1.8rem; text-shadow: 0 0 15px rgba(229,160,41,0.6);">
            ✦
          </div>
          <h3 class="font-display text-gold mb-1" style="font-size: 1.55rem; letter-spacing: 0.05em;">
            Selamat Datang
          </h3>
          <p class="text-muted small mb-0" style="font-size: 0.84rem;">
            Masuk ke akun Genshin Tracker Anda
          </p>
        </div>

        {{-- Flash Messages --}}
        @if(session('info'))
          <div class="alert alert-info alert-dismissible fade show border-0 rounded-3 mb-3 py-2 px-3 small" role="alert" style="background: rgba(14, 165, 233, 0.15); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.3) !important;">
            <i class="bi bi-info-circle-fill me-1.5"></i>{{ session('info') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
          </div>
        @endif

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-3 py-2 px-3 small" role="alert" style="background: rgba(34, 197, 94, 0.15); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.3) !important;">
            <i class="bi bi-check-circle-fill me-1.5"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-3 py-2 px-3 small" role="alert" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <i class="bi bi-exclamation-triangle-fill me-1.5"></i>{{ $errors->first() }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
          </div>
        @endif

        {{-- Form Login --}}
        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
          @csrf

          {{-- Email --}}
          <div class="mb-3">
            <label for="email" class="form-label text-light fw-medium small mb-1.5">
              <i class="bi bi-envelope-fill text-gold me-1"></i>Email
            </label>
            <div class="input-group">
              <span class="input-group-text border-secondary bg-dark text-muted" style="border-color: rgba(255,255,255,0.15) !important;">
                <i class="bi bi-person"></i>
              </span>
              <input type="email" 
                     name="email" 
                     id="email" 
                     class="form-control text-light @error('email') is-invalid @enderror" 
                     style="background: rgba(15, 23, 42, 0.7); border-color: rgba(255,255,255,0.15); font-size: 0.9rem;"
                     placeholder="contoh@domain.com" 
                     value="{{ old('email') }}" 
                     required 
                     autofocus>
            </div>
          </div>

          {{-- Password --}}
          <div class="mb-3">
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
                     placeholder="••••••••" 
                     required>
              <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePasswordBtn" style="border-color: rgba(255,255,255,0.15) !important;">
                <i class="bi bi-eye-slash text-muted" id="togglePasswordIcon"></i>
              </button>
            </div>
          </div>

          {{-- Remember Me --}}
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="background-color: rgba(15, 23, 42, 0.7); border-color: rgba(229,160,41,0.4);">
              <label class="form-check-label text-muted small" for="remember">
                Ingat saya
              </label>
            </div>
          </div>

          {{-- Submit Button --}}
          <button type="submit" class="btn btn-genshin w-100 py-2.5 mb-3 fw-bold shadow" id="btnSubmitLogin" style="font-size: 0.95rem; letter-spacing: 0.05em;">
            <i class="bi bi-box-arrow-in-right me-1.5"></i>Masuk ke Tracker
          </button>
        </form>

        {{-- Divider --}}
        <div class="position-relative text-center my-3">
          <hr class="border-secondary opacity-25">
          <span class="position-absolute top-50 start-50 translate-middle px-3 text-muted" style="background: rgba(17, 24, 39, 0.95); font-size: 0.75rem;">
            ATAU
          </span>
        </div>

        {{-- Register Link --}}
        <div class="text-center pt-2">
          <span class="text-muted small">Belum punya akun?</span>
          <a href="{{ route('register') }}" class="text-gold fw-semibold text-decoration-none small ms-1" style="transition: color 0.2s ease;">
            Daftar Sekarang <i class="bi bi-arrow-right-short"></i>
          </a>
        </div>

      </div>

    </div>
  </div>
</div>

@push('scripts')
<script>
  document.getElementById('togglePasswordBtn').addEventListener('click', function() {
    const passInput = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (passInput.type === 'password') {
      passInput.type = 'text';
      icon.classList.remove('bi-eye-slash');
      icon.classList.add('bi-eye');
    } else {
      passInput.type = 'password';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
    }
  });
</script>
@endpush
@endsection