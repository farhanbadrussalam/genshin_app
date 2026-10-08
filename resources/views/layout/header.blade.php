<nav class="navbar navbar-expand-md genshin-navbar">
  <div class="container-fluid">

    {{-- Brand / Logo --}}
    <a class="navbar-brand" href="{{ url('/') }}">
      <span class="brand-icon">?</span>
      Genshin
    </a>

    {{-- Page Title (center, visible on mobile) --}}
    @isset($title)
    <span class="page-title d-md-none">{{ $title }}</span>
    @endisset

    {{-- Hamburger --}}
    <button class="navbar-toggler border-0 ms-auto" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list text-gold fs-5"></i>
    </button>

    {{-- Nav Links --}}
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto gap-1 py-2 py-md-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}"
            href="{{ url('/') }}">
            <i class="bi bi-house-fill me-1"></i>Home
          </a>
        </li>

        {{-- Auto Daily & Resin Alert --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('daily-resin.*') ? 'active' : '' }}"
            href="{{ route('daily-resin.index') }}">
            <i class="bi bi-moon-stars-fill me-1 text-info"></i>Daily & Resin
          </a>
        </li>

        {{-- Dropdown Master Data --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('character.*', 'weapon.*', 'artifact.*', 'enemy.*', 'material.*', 'family.*') ? 'active' : '' }}" 
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-database-fill me-1"></i>Master Data
          </a>
          <ul class="dropdown-menu dropdown-menu-dark border-secondary">
            <li><a class="dropdown-item {{ request()->routeIs('character.*') ? 'active' : '' }}" href="{{ route('character.index') }}"><i class="bi bi-people-fill me-2 text-gold"></i>Master Karakter</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('weapon.*') ? 'active' : '' }}" href="{{ route('weapon.index') }}"><i class="bi bi-shield-shaded me-2 text-gold"></i>Master Senjata</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('artifact.*') ? 'active' : '' }}" href="{{ route('artifact.index') }}"><i class="bi bi-flower1 me-2 text-gold"></i>Master Artifact Set</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('enemy.*') ? 'active' : '' }}" href="{{ route('enemy.index') }}"><i class="bi bi-crosshair me-2 text-gold"></i>Master Musuh</a></li>
            <li><hr class="dropdown-divider border-secondary opacity-25"></li>
            <li><a class="dropdown-item {{ request()->routeIs('material.*') ? 'active' : '' }}" href="{{ route('material.index') }}"><i class="bi bi-gem me-2 text-gold"></i>Material</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('family.*') ? 'active' : '' }}" href="{{ route('family.index') }}"><i class="bi bi-collection-fill me-2 text-gold"></i>Family Material</a></li>
          </ul>
        </li>

        {{-- Dropdown Inventori --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('inventory.*') ? 'active' : '' }}" 
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-backpack-fill me-1"></i>Inventori
          </a>
          <ul class="dropdown-menu dropdown-menu-dark border-secondary">
            <li><a class="dropdown-item {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" href="{{ route('inventory.dashboard') }}"><i class="bi bi-speedometer2 me-2 text-gold"></i>Ringkasan Inventori</a></li>
            <li><a class="dropdown-item text-warning fw-bold" href="{{ route('inventory.dashboard') }}"><i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>? Sync Semua Inventori</a></li>
            <li><a class="dropdown-item text-info fw-bold" href="{{ route('daily-resin.index') }}"><i class="bi bi-moon-stars-fill me-2 text-info"></i>?? Daily & Resin Alert</a></li>
            <li><hr class="dropdown-divider border-secondary opacity-25"></li>
            <li><a class="dropdown-item {{ request()->routeIs('inventory.characters.*') ? 'active' : '' }}" href="{{ route('inventory.characters.index') }}"><i class="bi bi-person-badge-fill me-2 text-gold"></i>Inventori Karakter</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('inventory.weapons.*') ? 'active' : '' }}" href="{{ route('inventory.weapons.index') }}"><i class="bi bi-shield-fill-check me-2 text-gold"></i>Inventori Senjata</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('inventory.artifacts.*') ? 'active' : '' }}" href="{{ route('inventory.artifacts.index') }}"><i class="bi bi-flower1 me-2 text-gold"></i>Inventori Artifact</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('artifact-scoring.*') ? 'active' : '' }}" href="{{ route('artifact-scoring.index') }}"><i class="bi bi-trophy-fill me-2 text-gold"></i>Artifact Scoring</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('inventory.materials.*') ? 'active' : '' }}" href="{{ route('inventory.materials.index') }}"><i class="bi bi-gem me-2 text-gold"></i>Inventori Material</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('party.*') ? 'active' : '' }}"
            href="{{ route('party.index') }}">
            <i class="bi bi-shield-fill-check me-1 text-gold"></i>Party Analyzer
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('calculator.*') ? 'active' : '' }}"
            href="{{ route('calculator.index') }}">
            <i class="bi bi-calculator-fill me-1"></i>Kalkulator
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('task.*') ? 'active' : '' }}"
            href="{{ route('task.index') }}">
            <i class="bi bi-list-task me-1"></i>Task
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('game-accounts.*') ? 'active' : '' }}"
            href="{{ route('game-accounts.index') }}">
            <i class="bi bi-controller me-1"></i>Akun Game
          </a>
        </li>
      </ul>
    </div>

  </div>
</nav>
