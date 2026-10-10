@extends('layout.main')

@section('content')
@php $title = 'Game Hub & Portal'; @endphp
@include('layout.header')

<div class="page-container">

  {{-- Hero Gaming Hub Section --}}
  <div class="game-portal-hero animate-fade-in-up">
    <div style="font-size: 2.8rem; line-height: 1; margin-bottom: 0.5rem; text-shadow: 0 0 20px rgba(229,160,41,0.5);">✦</div>
    <h1 class="font-display text-gold mb-2" style="font-size: 1.85rem; letter-spacing: 0.08em;">
      MULTIVERSE GAME HUB & INVENTORY TRACKER
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem; max-width: 680px; margin: 0 auto;">
      Platform terpadu untuk pelacakan inventori karakter, kalkulator ascension material, dan scoring artefak dari berbagai game favoritmu.
    </p>
  </div>

  {{-- SECTION 1: DAFTAR GAME (FEATURED & DALAM PENGEMBANGAN) --}}
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="font-display text-white mb-0" style="font-size: 1.25rem;">
          <i class="bi bi-controller text-gold me-2"></i>Pilih Game
        </h4>
        <span style="font-size: 0.8rem; color: var(--text-secondary);">
          Klik pada salah satu game di bawah untuk masuk ke dashboard overview & inventori
        </span>
      </div>
      <span class="badge bg-dark border border-secondary text-light px-3 py-2">
        1 Aktif &bull; 3 Dalam Rencana
      </span>
    </div>

    <div class="row g-4">
      
      {{-- 1. GENSHIN IMPACT (ACTIVE) --}}
      <div class="col-12 col-md-6 col-xl-3 animate-fade-in-up" style="animation-delay: 0.05s;">
        <a href="{{ route('select-game', 'genshin_impact') }}" class="game-hub-card game-active" title="Masuk ke Dashboard Genshin Impact">
          <div class="game-card-banner">
            <div class="game-card-banner-bg" style="background-image: radial-gradient(circle, #e5a029 0%, #0d0f1a 100%);"></div>
            <div class="game-card-icon-wrap" style="color: #ffd700; border-color: rgba(229, 160, 41, 0.4);">
              ✦
            </div>
          </div>
          <div class="game-card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="game-status-badge status-active">
                <span class="status-dot"></span> Siap Digunakan
              </span>
              <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.68rem;">v5.4 Ready</span>
            </div>
            <h3 class="game-card-title text-gold">
              Genshin Impact
            </h3>
            <div class="game-card-meta">
              <i class="bi bi-tag-fill me-1"></i>Action RPG &bull; HoYoverse
            </div>
            <p class="game-card-desc">
              Sinkronisasi Enka.Network & HoYoLAB, inventori karakter & senjata, kalkulator ascension material, dan scoring artefak otomatis.
            </p>
            <div class="mb-3">
              <span class="game-tag-pill">Enka Sync</span>
              <span class="game-tag-pill">Artifact Scoring</span>
              <span class="game-tag-pill">Ascension Calc</span>
              <span class="game-tag-pill">Task Planner</span>
            </div>
            <button type="button" class="btn-game-enter btn-active-game">
              <span>Masuk Dashboard</span>
              <i class="bi bi-arrow-right-circle-fill fs-6"></i>
            </button>
          </div>
        </a>
      </div>

      {{-- 2. HONKAI: STAR RAIL (IN DEVELOPMENT) --}}
      <div class="col-12 col-md-6 col-xl-3 animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="game-hub-card game-hsr">
          <div class="game-card-banner">
            <div class="game-card-banner-bg" style="background-image: radial-gradient(circle, #0284c7 0%, #0d0f1a 100%);"></div>
            <div class="game-card-icon-wrap" style="color: #38bdf8; border-color: rgba(56, 189, 248, 0.4);">
              <i class="bi bi-stars"></i>
            </div>
          </div>
          <div class="game-card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="game-status-badge status-indev">
                <span class="status-dot"></span> Sedang Dikembangkan
              </span>
              <span class="badge bg-info text-dark fw-bold" style="font-size: 0.68rem;">Tahap Perancangan</span>
            </div>
            <h3 class="game-card-title" style="color: #e0f2fe;">
              Honkai: Star Rail
            </h3>
            <div class="game-card-meta">
              <i class="bi bi-tag-fill me-1"></i>Space Fantasy RPG &bull; HoYoverse
            </div>
            <p class="game-card-desc">
              Pelacak Karakter Trailblazer, Light Cones, Relic & Planar Ornament scoring, serta kalkulasi Trace material.
            </p>
            <div class="mb-3">
              <span class="game-tag-pill">Relic Scoring</span>
              <span class="game-tag-pill">Light Cones</span>
              <span class="game-tag-pill">Mihomo / Enka</span>
            </div>
            <button type="button" class="btn-game-enter btn-coming-soon" disabled>
              <i class="bi bi-lock-fill me-1"></i>Segera Hadir
            </button>
          </div>
        </div>
      </div>

      {{-- 3. ZENLESS ZONE ZERO (PLANNED) --}}
      <div class="col-12 col-md-6 col-xl-3 animate-fade-in-up" style="animation-delay: 0.15s;">
        <div class="game-hub-card game-zzz">
          <div class="game-card-banner">
            <div class="game-card-banner-bg" style="background-image: radial-gradient(circle, #e11d48 0%, #0d0f1a 100%);"></div>
            <div class="game-card-icon-wrap" style="color: #f43f5e; border-color: rgba(244, 63, 94, 0.4);">
              <i class="bi bi-lightning-charge-fill"></i>
            </div>
          </div>
          <div class="game-card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="game-status-badge status-planned">
                <span class="status-dot"></span> Rencana Rilis
              </span>
              <span class="badge bg-secondary text-light" style="font-size: 0.68rem;">Coming Soon</span>
            </div>
            <h3 class="game-card-title" style="color: #ffe4e6;">
              Zenless Zone Zero
            </h3>
            <div class="game-card-meta">
              <i class="bi bi-tag-fill me-1"></i>Urban Action RPG &bull; HoYoverse
            </div>
            <p class="game-card-desc">
              Manajemen Agen Proxy, W-Engine, Drive Disc tuner & rating substat, serta pelacak material Battery Charge.
            </p>
            <div class="mb-3">
              <span class="game-tag-pill">Agents</span>
              <span class="game-tag-pill">Drive Discs</span>
              <span class="game-tag-pill">W-Engines</span>
            </div>
            <button type="button" class="btn-game-enter btn-coming-soon" disabled>
              <i class="bi bi-lock-fill me-1"></i>Segera Hadir
            </button>
          </div>
        </div>
      </div>

      {{-- 4. WUTHERING WAVES (PLANNED) --}}
      <div class="col-12 col-md-6 col-xl-3 animate-fade-in-up" style="animation-delay: 0.2s;">
        <div class="game-hub-card game-wuwa">
          <div class="game-card-banner">
            <div class="game-card-banner-bg" style="background-image: radial-gradient(circle, #7e22ce 0%, #0d0f1a 100%);"></div>
            <div class="game-card-icon-wrap" style="color: #c084fc; border-color: rgba(168, 85, 247, 0.4);">
              <i class="bi bi-tsunami"></i>
            </div>
          </div>
          <div class="game-card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="game-status-badge status-planned">
                <span class="status-dot"></span> Rencana Rilis
              </span>
              <span class="badge bg-secondary text-light" style="font-size: 0.68rem;">Coming Soon</span>
            </div>
            <h3 class="game-card-title" style="color: #f3e8ff;">
              Wuthering Waves
            </h3>
            <div class="game-card-meta">
              <i class="bi bi-tag-fill me-1"></i>Open-world ARPG &bull; Kuro Games
            </div>
            <p class="game-card-desc">
              Pelacak Resonator, Weapon ascension, Echo set Sonata effect build analyzer, dan kalkulator Waveplate.
            </p>
            <div class="mb-3">
              <span class="game-tag-pill">Echo Analyzer</span>
              <span class="game-tag-pill">Resonators</span>
              <span class="game-tag-pill">Sonata Sets</span>
            </div>
            <button type="button" class="btn-game-enter btn-coming-soon" disabled>
              <i class="bi bi-lock-fill me-1"></i>Segera Hadir
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
