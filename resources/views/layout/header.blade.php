@php
    $activeGame = session('active_game');
    $isGenshinRoute = request()->routeIs(
        'inventory.*', 'character.*', 'weapon.*', 'artifact.*', 'artifact-scoring.*',
        'enemy.*', 'material.*', 'family.*', 'task.*', 'calculator.*', 'party.*',
        'daily-resin.*', 'farming-planner.*', 'build-planner.*'
    );
    if ($isGenshinRoute && !$activeGame) {
        session(['active_game' => 'genshin_impact']);
        $activeGame = 'genshin_impact';
    }
    $isGenshinActive = ($activeGame === 'genshin_impact');
@endphp

<nav class="navbar navbar-expand-lg genshin-navbar">
  <div class="container-fluid">

    {{-- Brand / Logo --}}
    @if($isGenshinActive)
      <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('inventory.dashboard') }}" title="Genshin Impact Tracker">
        <span class="brand-icon">✦</span>
        <span class="fw-bold" style="letter-spacing: 0.04em;">Genshin Impact</span>
      </a>
    @else
      <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('welcome') }}" title="Multiverse Game Portal">
        <span class="brand-icon">✦</span>
        <span class="fw-bold" style="letter-spacing: 0.04em;">Game Hub</span>
      </a>
    @endif

    {{-- Page Title (mobile center) --}}
    @isset($title)
      <span class="page-title d-lg-none">{{ $title }}</span>
    @endisset

    {{-- Hamburger Toggler --}}
    <button class="navbar-toggler border-0 ms-auto" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list text-gold fs-5"></i>
    </button>

    {{-- Nav Links --}}
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1 py-2 py-lg-0">
        
        {{-- Home Portal Link --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}" href="{{ route('welcome') }}" title="Halaman Utama Game Hub">
            <i class="bi bi-grid-fill me-1 text-gold"></i>Portal Game
          </a>
        </li>

        {{-- KHUSUS JIKA GENSHIN IMPACT DIPILIH / AKTIF --}}
        @if($isGenshinActive)
          
          {{-- Dashboard --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" href="{{ route('inventory.dashboard') }}">
              <i class="bi bi-speedometer2 me-1 text-gold"></i>Dashboard
            </a>
          </li>

          {{-- Dropdown Inventori --}}
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('inventory.*', 'artifact-scoring.*') && !request()->routeIs('inventory.dashboard') ? 'active' : '' }}" 
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-backpack-fill me-1 text-gold"></i>Inventori
            </a>
            <ul class="dropdown-menu dropdown-menu-dark border-secondary shadow-lg">
              <li>
                <a class="dropdown-item {{ request()->routeIs('inventory.characters.*') ? 'active' : '' }}" href="{{ route('inventory.characters.index') }}">
                  <i class="bi bi-person-badge-fill me-2 text-gold"></i>Inventori Karakter
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('inventory.weapons.*') ? 'active' : '' }}" href="{{ route('inventory.weapons.index') }}">
                  <i class="bi bi-shield-fill-check me-2 text-gold"></i>Inventori Senjata
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('inventory.artifacts.*') ? 'active' : '' }}" href="{{ route('inventory.artifacts.index') }}">
                  <i class="bi bi-flower1 me-2 text-gold"></i>Inventori Artifact
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('artifact-scoring.*') ? 'active' : '' }}" href="{{ route('artifact-scoring.index') }}">
                  <i class="bi bi-trophy-fill me-2 text-warning"></i>Artifact Scoring
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('inventory.compare') ? 'active' : '' }}" href="{{ route('inventory.compare') }}">
                  <i class="bi bi-arrow-left-right me-2 text-warning"></i>Komparasi Build 2 Akun
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('inventory.materials.*') ? 'active' : '' }}" href="{{ route('inventory.materials.index') }}">
                  <i class="bi bi-gem me-2 text-gold"></i>Inventori Material
                </a>
              </li>
              <li><hr class="dropdown-divider border-secondary opacity-25"></li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('inventory.good.*') ? 'active' : '' }}" href="{{ route('inventory.good.index') }}">
                  <i class="bi bi-arrow-left-right me-2 text-info"></i>Export / Import GOOD
                </a>
              </li>
            </ul>
          </li>

          {{-- Dropdown Planner & Tools --}}
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('task.*', 'build-planner.*', 'farming-planner.*', 'calculator.*', 'party.*') ? 'active' : '' }}" 
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-kanban-fill me-1 text-gold"></i>Planner & Tools
            </a>
            <ul class="dropdown-menu dropdown-menu-dark border-secondary shadow-lg">
              <li>
                <a class="dropdown-item {{ request()->routeIs('task.*') ? 'active' : '' }}" href="{{ route('task.index') }}">
                  <i class="bi bi-list-task me-2 text-gold"></i>Task Tracker
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('build-planner.*') ? 'active' : '' }}" href="{{ route('build-planner.index') }}">
                  <i class="bi bi-person-gear me-2 text-gold"></i>Build Planner
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('farming-planner.*') ? 'active' : '' }}" href="{{ route('farming-planner.index') }}">
                  <i class="bi bi-map-fill me-2 text-success"></i>Farming Planner
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('calculator.*') ? 'active' : '' }}" href="{{ route('calculator.index') }}">
                  <i class="bi bi-calculator-fill me-2 text-info"></i>Kalkulator Upgrade
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('party.*') ? 'active' : '' }}" href="{{ route('party.index') }}">
                  <i class="bi bi-shield-shaded me-2 text-warning"></i>Party Analyzer
                </a>
              </li>
            </ul>
          </li>

          {{-- Dropdown Master Database --}}
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('character.*', 'weapon.*', 'artifact.*', 'enemy.*', 'material.*', 'family.*') ? 'active' : '' }}" 
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-database-fill me-1 text-gold"></i>Master Data
            </a>
            <ul class="dropdown-menu dropdown-menu-dark border-secondary shadow-lg">
              <li>
                <a class="dropdown-item {{ request()->routeIs('character.*') ? 'active' : '' }}" href="{{ route('character.index') }}">
                  <i class="bi bi-people-fill me-2 text-gold"></i>Master Karakter
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('weapon.*') ? 'active' : '' }}" href="{{ route('weapon.index') }}">
                  <i class="bi bi-shield-shaded me-2 text-gold"></i>Master Senjata
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('artifact.*') ? 'active' : '' }}" href="{{ route('artifact.index') }}">
                  <i class="bi bi-flower1 me-2 text-gold"></i>Master Artifact Set
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('enemy.*') ? 'active' : '' }}" href="{{ route('enemy.index') }}">
                  <i class="bi bi-crosshair me-2 text-gold"></i>Master Musuh
                </a>
              </li>
              <li><hr class="dropdown-divider border-secondary opacity-25"></li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('material.*') ? 'active' : '' }}" href="{{ route('material.index') }}">
                  <i class="bi bi-gem me-2 text-gold"></i>Master Material
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ request()->routeIs('family.*') ? 'active' : '' }}" href="{{ route('family.index') }}">
                  <i class="bi bi-collection-fill me-2 text-gold"></i>Family Material
                </a>
              </li>
            </ul>
          </li>

          {{-- Daily & Resin --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('daily-resin.*') ? 'active' : '' }}" href="{{ route('daily-resin.index') }}">
              <i class="bi bi-moon-stars-fill me-1 text-info"></i>Daily & Resin
            </a>
          </li>

        @endif

        {{-- Akun Game --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('game-accounts.*') ? 'active' : '' }}" href="{{ route('game-accounts.index') }}">
            <i class="bi bi-controller me-1 text-gold"></i>Akun Game
          </a>
        </li>

        {{-- Active Game Switcher Pill --}}
        @if($isGenshinActive)
          <li class="nav-item ms-lg-2">
            <div class="dropdown">
              <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                      style="background: rgba(229, 160, 41, 0.15); border: 1px solid rgba(229, 160, 41, 0.4); color: #ffd700; border-radius: 20px; font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                <span style="color: #ffd700;">✦</span>
                <span class="fw-semibold">Genshin</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary shadow-lg">
                <li><h6 class="dropdown-header text-gold font-display" style="font-size: 0.72rem;">Game Aktif: Genshin Impact</h6></li>
                <li>
                  <a class="dropdown-item small" href="{{ route('reset-game') }}">
                    <i class="bi bi-arrow-repeat me-2 text-warning"></i>Ganti Game (Multiverse Portal)
                  </a>
                </li>
              </ul>
            </div>
          </li>
        @endif

        {{-- User Authentication Profile / Login Menu --}}
        @if(auth()->check())
          <li class="nav-item dropdown ms-lg-2">
            <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    style="background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(229, 160, 41, 0.45); color: #f8fafc; border-radius: 20px; font-size: 0.78rem; padding: 0.28rem 0.75rem;">
              <i class="bi bi-person-circle text-gold"></i>
              <span class="fw-semibold text-truncate" style="max-width: 110px;">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary shadow-lg py-2" style="min-width: 200px;">
              <li class="px-3 py-1 mb-1 border-bottom border-secondary border-opacity-25">
                <div class="fw-bold text-gold small text-truncate">{{ auth()->user()->name }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">{{ auth()->user()->email }}</div>
              </li>
              <li>
                <a class="dropdown-item small" href="{{ route('game-accounts.index') }}">
                  <i class="bi bi-controller me-2 text-gold"></i>Kelola Akun Game
                </a>
              </li>
              <li>
                <a class="dropdown-item small" href="{{ route('inventory.dashboard') }}">
                  <i class="bi bi-speedometer2 me-2 text-gold"></i>Dashboard
                </a>
              </li>
              <li><hr class="dropdown-divider border-secondary opacity-25"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}" id="logoutNavForm" class="m-0">
                  @csrf
                  <button type="submit" class="dropdown-item small text-danger d-flex align-items-center">
                    <i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)
                  </button>
                </form>
              </li>
            </ul>
          </li>
        @else
          <li class="nav-item ms-lg-2 d-flex align-items-center gap-1">
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-warning text-gold py-1 px-2.5 rounded-pill" style="font-size: 0.75rem; border-color: rgba(229,160,41,0.5);">
              <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
            </a>
            <a href="{{ route('register') }}" class="btn btn-sm btn-genshin py-1 px-2.5 rounded-pill text-dark fw-bold" style="font-size: 0.75rem;">
              Daftar
            </a>
          </li>
        @endif

      </ul>
    </div>

  </div>
</nav>
