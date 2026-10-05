@extends('layout.main')

@section('content')
@php $title = 'Ringkasan Inventori'; @endphp
@include('layout.header')

<div class="dashboard-page-container">

  {{-- Page Header & Account Switcher --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.55rem; letter-spacing: 0.08em;">
        <i class="bi bi-speedometer2 me-2"></i>Ringkasan Inventori Akun
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.86rem; margin-bottom: 0;">
        Pusat statistik, koleksi karakter, senjata, artefak, dan material pada akun game kamu
      </p>
    </div>

    {{-- Account Switcher --}}
    @if($accounts->isNotEmpty())
      <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('inventory.dashboard') }}" id="accountSelectForm">
          <div class="input-group input-group-sm">
            <span class="input-group-text genshin-input-group-text">
              <i class="bi bi-controller text-gold"></i>
            </span>
            <select name="account_id" class="form-select genshin-select" onchange="this.form.submit()" style="min-width: 220px;">
              @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ ($activeAccount?->id === $acc->id) ? 'selected' : '' }}>
                  {{ $acc->nickname }} (UID: {{ $acc->uid }})
                </option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
    @endif
  </div>

  @if(!$activeAccount)
    {{-- Empty State --}}
    <div class="empty-state animate-fade-in-up">
      <div style="font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.4;">🎮</div>
      <h4 class="font-display text-gold">Belum Ada Akun Game Terdaftar</h4>
      <p style="color: var(--text-secondary); font-size: 0.88rem; max-width: 480px; margin: 0 auto 1.5rem;">
        Tambahkan akun game Genshin Impact terlebih dahulu untuk melihat dashboard overview inventori.
      </p>
      <a href="{{ route('game-accounts.index') }}" class="btn-genshin btn-genshin-sm">
        <i class="bi bi-plus-lg me-1"></i>Kelola Akun Game
      </a>
    </div>
  @else

    {{-- Account Hero Banner --}}
    <div class="account-hero-banner mb-4 animate-fade-in-up">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="account-avatar-circle">
            <i class="bi bi-person-fill text-gold fs-2"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <h3 class="font-display mb-0 text-white" style="font-size: 1.35rem; letter-spacing: 0.04em;">
                {{ $activeAccount->nickname }}
              </h3>
              <span class="badge badge-server-tag">
                {{ strtoupper($activeAccount->server) }}
              </span>
              <span class="badge badge-uid-tag">
                UID: {{ $activeAccount->uid }}
              </span>
            </div>
            <div class="text-muted" style="font-size: 0.78rem;">
              <i class="bi bi-arrow-repeat text-gold me-1"></i>
              Sinkronisasi terakhir: 
              <span class="text-light fw-medium">
                {{ $activeAccount->last_synced_at ? $activeAccount->last_synced_at->diffForHumans() : 'Belum pernah sync' }}
              </span>
            </div>
          </div>
        </div>

        {{-- Actions: 1-Click Sync All & Quick Nav Links --}}
        <div class="d-flex flex-wrap align-items-center gap-2">
          <button type="button" class="btn btn-warning fw-bold d-inline-flex align-items-center gap-1 shadow px-3 py-2 text-dark" data-bs-toggle="modal" data-bs-target="#modalSyncAllInventory">
            <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
            <span>⚡ Sync Semua (Karakter, Senjata, Artefak)</span>
          </button>

          <a href="{{ route('inventory.characters.index', ['account_id' => $activeAccount->id]) }}" class="nav-chip-link">
            <i class="bi bi-person-badge-fill text-gold me-1"></i>Karakter
          </a>
          <a href="{{ route('inventory.weapons.index', ['account_id' => $activeAccount->id]) }}" class="nav-chip-link">
            <i class="bi bi-shield-shaded text-gold me-1"></i>Senjata
          </a>
          <a href="{{ route('inventory.artifacts.index', ['account_id' => $activeAccount->id]) }}" class="nav-chip-link">
            <i class="bi bi-gem text-gold me-1"></i>Artifact
          </a>
          <a href="{{ route('inventory.materials.index', ['account_id' => $activeAccount->id]) }}" class="nav-chip-link">
            <i class="bi bi-backpack text-gold me-1"></i>Material
          </a>
          <a href="{{ route('calculator.index') }}" class="nav-chip-link highlight">
            <i class="bi bi-calculator-fill me-1"></i>Kalkulator
          </a>
        </div>
      </div>
    </div>

    {{-- 4 Primary KPI Summary Cards --}}
    <div class="row g-3 mb-4 animate-fade-in-up">
      {{-- Card 1: Karakter --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-box h-100">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="kpi-icon-pill gold">
                <i class="bi bi-people-fill"></i>
              </div>
              <span class="kpi-card-heading">Koleksi Karakter</span>
            </div>
            <span class="badge kpi-pill-badge gold">★ 5 & 4</span>
          </div>

          <div class="kpi-hero-val-box mb-3">
            <span class="kpi-big-num">{{ $stats['total_characters'] }}</span>
            <span class="kpi-unit-sub">Karakter Dimiliki</span>
          </div>

          <div class="kpi-metric-list mb-3">
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot gold"></span>
                <span class="kpi-row-label">Bintang 5</span>
              </span>
              <span class="text-gold fw-bold">{{ $stats['char_5_stars'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot purple"></span>
                <span class="kpi-row-label">Bintang 4</span>
              </span>
              <span class="text-purple fw-bold">{{ $stats['char_4_stars'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot cyan"></span>
                <span class="kpi-row-label">Level 90 (Max)</span>
              </span>
              <span class="text-cyan fw-bold">{{ $stats['char_max_level'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot green"></span>
                <span class="kpi-row-label">Konstelasi C6</span>
              </span>
              <span class="text-success fw-bold">{{ $stats['char_c6'] }}</span>
            </div>
          </div>

          <a href="{{ route('inventory.characters.index', ['account_id' => $activeAccount->id]) }}" class="kpi-card-footer">
            <span>Buka Inventori Karakter</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      {{-- Card 2: Senjata --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-box h-100">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="kpi-icon-pill blue">
                <i class="bi bi-shield-shaded"></i>
              </div>
              <span class="kpi-card-heading">Gudang Senjata</span>
            </div>
            <span class="badge kpi-pill-badge blue">Weaponry</span>
          </div>

          <div class="kpi-hero-val-box mb-3">
            <span class="kpi-big-num">{{ $stats['total_weapons'] }}</span>
            <span class="kpi-unit-sub">Senjata Dimiliki</span>
          </div>

          <div class="kpi-metric-list mb-3">
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot gold"></span>
                <span class="kpi-row-label">Bintang 5</span>
              </span>
              <span class="text-gold fw-bold">{{ $stats['weapon_5_stars'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot purple"></span>
                <span class="kpi-row-label">Bintang 4</span>
              </span>
              <span class="text-purple fw-bold">{{ $stats['weapon_4_stars'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot cyan"></span>
                <span class="kpi-row-label">Level 90 (Max)</span>
              </span>
              <span class="text-cyan fw-bold">{{ $stats['weapon_max_level'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot orange"></span>
                <span class="kpi-row-label">Refinement R5</span>
              </span>
              <span class="text-warning fw-bold">{{ $stats['weapon_r5'] }}</span>
            </div>
          </div>

          <a href="{{ route('inventory.weapons.index', ['account_id' => $activeAccount->id]) }}" class="kpi-card-footer">
            <span>Buka Inventori Senjata</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      {{-- Card 3: Artifact --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-box h-100">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="kpi-icon-pill purple">
                <i class="bi bi-gem"></i>
              </div>
              <span class="kpi-card-heading">Koleksi Artifact</span>
            </div>
            <span class="badge kpi-pill-badge purple">Artifacts</span>
          </div>

          <div class="kpi-hero-val-box mb-3">
            <span class="kpi-big-num">{{ $stats['total_artifacts'] }}</span>
            <span class="kpi-unit-sub">Pieces Dimiliki</span>
          </div>

          <div class="kpi-metric-list mb-3">
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot cyan"></span>
                <span class="kpi-row-label">Level 20 (Max)</span>
              </span>
              <span class="text-cyan fw-bold">{{ $stats['art_max_level'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot gold"></span>
                <span class="kpi-row-label">Bintang 5</span>
              </span>
              <span class="text-gold fw-bold">{{ $stats['art_5_stars'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot green"></span>
                <span class="kpi-row-label">Terpasang ke Hero</span>
              </span>
              <span class="text-success fw-bold">{{ $stats['art_equipped'] }}</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot gray"></span>
                <span class="kpi-row-label">Tersimpan di Tas</span>
              </span>
              <span class="text-muted fw-bold">{{ max(0, $stats['total_artifacts'] - $stats['art_equipped']) }}</span>
            </div>
          </div>

          <a href="{{ route('inventory.artifacts.index', ['account_id' => $activeAccount->id]) }}" class="kpi-card-footer">
            <span>Buka Inventori Artifact</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      {{-- Card 4: Material & Task --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-box h-100">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="kpi-icon-pill green">
                <i class="bi bi-backpack"></i>
              </div>
              <span class="kpi-card-heading">Material & Task</span>
            </div>
            <span class="badge kpi-pill-badge green">Resources</span>
          </div>

          <div class="kpi-hero-val-box mb-3">
            <span class="kpi-big-num">{{ number_format($stats['total_material_qty']) }}</span>
            <span class="kpi-unit-sub">Total Stok Bahan</span>
          </div>

          <div class="kpi-metric-list mb-3">
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot blue"></span>
                <span class="kpi-row-label">Varian Material</span>
              </span>
              <span class="text-info fw-bold">{{ $stats['total_material_types'] }} jenis</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot orange"></span>
                <span class="kpi-row-label">Task Aktif</span>
              </span>
              <span class="text-warning fw-bold">{{ $stats['active_tasks_count'] }} Task</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot green"></span>
                <span class="kpi-row-label">Kalkulator Upgrade</span>
              </span>
              <span class="text-success fw-bold">Siap</span>
            </div>
            <div class="kpi-metric-row">
              <span class="d-flex align-items-center gap-2">
                <span class="dot gold"></span>
                <span class="kpi-row-label">Sync HoYoLAB</span>
              </span>
              <span class="text-gold fw-bold">Terkoneksi</span>
            </div>
          </div>

          <a href="{{ route('inventory.materials.index', ['account_id' => $activeAccount->id]) }}" class="kpi-card-footer">
            <span>Buka Inventori Material</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    {{-- Visual Breakdown: 3 Balanced Columns (Elemen, Tipe Senjata, Slot Artefak) --}}
    <div class="row g-3 mb-4 animate-fade-in-up">
      {{-- Col 1: Distribusi Elemen Karakter --}}
      <div class="col-12 col-lg-4">
        <div class="dashboard-panel-box h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="panel-header-title">
              <i class="bi bi-palette-fill text-gold me-2"></i>Distribusi Elemen
            </span>
            <span class="badge text-muted" style="font-size: 0.72rem;">{{ $stats['total_characters'] }} Karakter</span>
          </div>

          @php
            $elemConfig = [
              'Pyro'    => ['color' => '#ef4444', 'icon' => '🔥'],
              'Hydro'   => ['color' => '#3b82f6', 'icon' => '💧'],
              'Anemo'   => ['color' => '#10b981', 'icon' => '🍃'],
              'Electro' => ['color' => '#a855f7', 'icon' => '⚡'],
              'Dendro'  => ['color' => '#22c55e', 'icon' => '🌿'],
              'Cryo'    => ['color' => '#06b6d4', 'icon' => '❄️'],
              'Geo'     => ['color' => '#eab308', 'icon' => '🪨'],
            ];
            $totalChars = max(1, $stats['total_characters']);
          @endphp

          <div class="d-flex flex-column gap-2">
            @foreach($elemConfig as $elName => $elData)
              @php
                $cnt = $stats['element_counts'][$elName] ?? 0;
                $pct = round(($cnt / $totalChars) * 100);
              @endphp
              <div class="elem-bar-row">
                <div class="elem-name-col">
                  <span class="me-1">{{ $elData['icon'] }}</span>
                  <span style="color: {{ $elData['color'] }}; font-weight: 600;">{{ $elName }}</span>
                </div>
                <div class="elem-track">
                  <div class="elem-fill" style="width: {{ $pct }}%; background-color: {{ $elData['color'] }};"></div>
                </div>
                <div class="elem-count-col">
                  <span class="text-white fw-bold">{{ $cnt }}</span>
                  <span class="text-muted" style="font-size: 0.7rem;">({{ $pct }}%)</span>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Col 2: Tipe Senjata --}}
      <div class="col-12 col-lg-4">
        <div class="dashboard-panel-box h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="panel-header-title">
              <i class="bi bi-shield-shaded text-gold me-2"></i>Tipe Senjata
            </span>
            <span class="badge text-muted" style="font-size: 0.72rem;">{{ $stats['total_weapons'] }} Senjata</span>
          </div>

          @php
            $wepMeta = [
              'Sword'    => ['icon' => 'bi-slash-lg', 'label' => 'Sword'],
              'Claymore' => ['icon' => 'bi-hammer', 'label' => 'Claymore'],
              'Polearm'  => ['icon' => 'bi-pin-angle-fill', 'label' => 'Polearm'],
              'Bow'      => ['icon' => 'bi-bullseye', 'label' => 'Bow'],
              'Catalyst' => ['icon' => 'bi-book-fill', 'label' => 'Catalyst'],
            ];
            $wTotal = max(1, $stats['total_weapons']);
          @endphp

          <div class="d-flex flex-column gap-2 mb-3">
            @foreach($wepMeta as $wKey => $meta)
              @php
                $wCount = $stats['weapon_type_counts'][$wKey] ?? 0;
                $wPct = round(($wCount / $wTotal) * 100);
              @endphp
              <div class="wep-stat-row">
                <div class="wep-label-col">
                  <i class="bi {{ $meta['icon'] }} text-gold me-2"></i>
                  <span>{{ $meta['label'] }}</span>
                </div>
                <div class="wep-track">
                  <div class="wep-fill" style="width: {{ $wPct }}%;"></div>
                </div>
                <div class="wep-count-col">
                  <span class="text-white fw-bold">{{ $wCount }}</span>
                  <span class="text-muted" style="font-size: 0.7rem;">({{ $wPct }}%)</span>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Quick Stat Footer --}}
          <div class="p-2 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); font-size: 0.76rem;">
            <div class="d-flex justify-content-between text-secondary">
              <span><i class="bi bi-check2-circle text-success me-1"></i>Terpasang ke Hero:</span>
              <span class="text-white fw-bold">{{ $stats['weapon_equipped'] }} senjata</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Col 3: Rincian Slot Artefak & Top 5 Sets --}}
      <div class="col-12 col-lg-4">
        <div class="dashboard-panel-box h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="panel-header-title">
              <i class="bi bi-flower1 text-gold me-2"></i>Koleksi Artifact Set
            </span>
            <span class="badge text-muted" style="font-size: 0.72rem;">{{ $stats['total_artifacts'] }} Pieces</span>
          </div>

          {{-- 5 Slot Mini Grid --}}
          <div class="slot-tiles-container mb-3">
            <div class="slot-tile-card">
              <span class="slot-tile-icon">🌸</span>
              <span class="slot-tile-number">{{ $stats['slot_counts']['flower'] ?? 0 }}</span>
              <span class="slot-tile-name">Flower</span>
            </div>
            <div class="slot-tile-card">
              <span class="slot-tile-icon">🪶</span>
              <span class="slot-tile-number">{{ $stats['slot_counts']['plume'] ?? 0 }}</span>
              <span class="slot-tile-name">Plume</span>
            </div>
            <div class="slot-tile-card">
              <span class="slot-tile-icon">⏳</span>
              <span class="slot-tile-number">{{ $stats['slot_counts']['sands'] ?? 0 }}</span>
              <span class="slot-tile-name">Sands</span>
            </div>
            <div class="slot-tile-card">
              <span class="slot-tile-icon">🏆</span>
              <span class="slot-tile-number">{{ $stats['slot_counts']['goblet'] ?? 0 }}</span>
              <span class="slot-tile-name">Goblet</span>
            </div>
            <div class="slot-tile-card">
              <span class="slot-tile-icon">👑</span>
              <span class="slot-tile-number">{{ $stats['slot_counts']['circlet'] ?? 0 }}</span>
              <span class="slot-tile-name">Circlet</span>
            </div>
          </div>

          {{-- Top 5 Sets --}}
          <div class="top-sets-header mb-2">5 Set Paling Banyak Dikoleksi:</div>
          <div class="d-flex flex-column gap-1">
            @forelse($stats['top_sets'] as $item)
              <div class="top-set-row">
                <span class="top-set-label text-truncate" title="{{ $item['set']->name }}">
                  <i class="bi bi-gem text-gold me-1" style="font-size: 0.7rem;"></i>{{ $item['set']->name }}
                </span>
                <span class="badge badge-set-count">{{ $item['count'] }} pcs</span>
              </div>
            @empty
              <div class="text-muted text-center py-2" style="font-size: 0.76rem;">Belum ada artefak</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    {{-- Top Character Builds Showcase --}}
    <div class="dashboard-panel-box mb-4 animate-fade-in-up">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
          <h5 class="panel-header-title mb-1">
            <i class="bi bi-star-fill text-gold me-2"></i>Karakter Unggulan & Build Terpasang
          </h5>
          <span style="font-size: 0.78rem; color: var(--text-secondary);">
            Karakter level tertinggi beserta senjata dan kelengkapan artefaknya
          </span>
        </div>
        <a href="{{ route('inventory.characters.index', ['account_id' => $activeAccount->id]) }}" class="btn-genshin btn-genshin-sm">
          Lihat Semua Karakter ({{ $stats['total_characters'] }})
        </a>
      </div>

      <div class="row g-3">
        @forelse($topCharactersShowcase as $item)
          @php
            $char = $item['character'];
            $invChar = $item['inventory_character'];
            $wep = $item['equipped_weapon'];
            $artCount = $item['artifacts_count'];
            $isFiveStar = ($char?->rarity === 5);
          @endphp
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="char-showcase-box {{ $isFiveStar ? 'five-star' : 'four-star' }}">
              
              {{-- Top Row: Avatar & Identity --}}
              <div class="d-flex align-items-center gap-3 mb-2">
                <div class="char-avatar-frame">
                  @if($char?->icon_url)
                    <img src="{{ $char->icon_url }}" alt="{{ $char->name }}" class="char-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                    <div class="char-avatar-fallback d-none"><i class="bi bi-person-fill"></i></div>
                  @else
                    <div class="char-avatar-fallback"><i class="bi bi-person-fill"></i></div>
                  @endif
                  <span class="char-lvl-tag">Lv.{{ $invChar->level }}</span>
                </div>

                <div class="flex-grow-1 min-w-0">
                  <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                    <span class="char-full-name text-truncate" title="{{ $char?->name }}">
                      {{ $char?->name ?? 'Karakter' }}
                    </span>
                    <span class="badge badge-c-level">
                      C{{ $invChar->constellation }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                    <span style="color: {{ $char?->element_color ?? '#e5a029' }}; font-weight: 700;">
                      {{ $char?->element ?? 'Pyro' }}
                    </span>
                    <span class="text-gold ms-1">
                      {{ str_repeat('★', $char?->rarity ?? 4) }}
                    </span>
                  </div>
                </div>
              </div>

              {{-- Gear Status Box --}}
              <div class="char-gear-card">
                {{-- Weapon Row --}}
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                  <div class="d-flex align-items-center gap-2 min-w-0">
                    @if($wep && $wep->weapon?->icon_url)
                      <img src="{{ $wep->weapon->icon_url }}" class="gear-icon-thumb" alt="{{ $wep->weapon->name }}" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                      <i class="bi bi-shield text-gold d-none" style="font-size: 0.85rem;"></i>
                    @else
                      <i class="bi bi-shield text-gold" style="font-size: 0.85rem;"></i>
                    @endif
                    <span class="gear-name-text text-truncate" title="{{ $wep ? $wep->weapon->name : 'Tanpa Senjata' }}">
                      {{ $wep ? $wep->weapon->name : 'Tanpa Senjata' }}
                    </span>
                  </div>
                  @if($wep)
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                      <span class="badge badge-r-level">R{{ $wep->refinement }}</span>
                      <span class="gear-lvl-text">Lv.{{ $wep->level }}</span>
                    </div>
                  @endif
                </div>

                {{-- Artifact Row --}}
                <div class="d-flex align-items-center justify-content-between">
                  <span class="gear-name-text" style="color: var(--text-muted);">
                    <i class="bi bi-gem text-gold me-1"></i>Artifact Set
                  </span>
                  <div class="d-flex align-items-center gap-1">
                    <div class="art-dots-bar">
                      @for($i = 1; $i <= 5; $i++)
                        <span class="art-dot-pill {{ $i <= $artCount ? 'filled' : '' }}"></span>
                      @endfor
                    </div>
                    <span class="gear-lvl-text ms-1">{{ $artCount }}/5</span>
                  </div>
                </div>
              </div>

            </div>
          </div>
        @empty
          <div class="col-12 text-center py-4 text-muted">
            Belum ada karakter yang tersinkronkan pada akun ini.
          </div>
        @endforelse
      </div>
    </div>

    {{-- 5-Star Weapons Showcase --}}
    @if($fiveStarWeapons->isNotEmpty())
      <div class="dashboard-panel-box animate-fade-in-up">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
          <div>
            <h5 class="panel-header-title mb-1 text-gold">
              <i class="bi bi-award-fill me-2"></i>Koleksi Senjata Bintang 5 (★ 5)
            </h5>
            <span style="font-size: 0.78rem; color: var(--text-secondary);">
              Daftar seluruh senjata bintang lima beserta karakter pemakainya
            </span>
          </div>
          <a href="{{ route('inventory.weapons.index', ['account_id' => $activeAccount->id, 'rarity' => 5]) }}" class="btn-genshin btn-genshin-sm">
            Lihat Semua Senjata
          </a>
        </div>

        <div class="row g-2">
          @foreach($fiveStarWeapons as $wItem)
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
              <div class="wep-five-star-card">
                <div class="wep-art-box">
                  @if($wItem->weapon?->icon_url)
                    <img src="{{ $wItem->weapon->icon_url }}" alt="{{ $wItem->weapon->name }}" class="wep-art-img" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                    <div class="d-none w-100 h-100 align-items-center justify-content-center">
                      <i class="bi bi-shield-shaded text-gold fs-1"></i>
                    </div>
                  @else
                    <i class="bi bi-shield-fill text-gold fs-1"></i>
                  @endif
                  <span class="wep-badge-refine">R{{ $wItem->refinement }}</span>
                  <span class="wep-badge-level">Lv.{{ $wItem->level }}</span>
                </div>
                <div class="wep-detail-box">
                  <div class="wep-item-name text-truncate" title="{{ $wItem->weapon?->name }}">
                    {{ $wItem->weapon?->name ?? 'Senjata' }}
                  </div>
                  <div class="wep-user-label text-truncate">
                    @if($wItem->equippedCharacter)
                      <i class="bi bi-person-check-fill text-gold me-1"></i>{{ $wItem->equippedCharacter->name }}
                    @else
                      <span class="text-muted"><i class="bi bi-box-seam me-1"></i>Di Tas</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

  @endif

  {{-- Modal 1-Tombol Sync Semua Inventori --}}
  @if($activeAccount)
  <div class="modal fade" id="modalSyncAllInventory" tabindex="-1" aria-labelledby="modalSyncAllInventoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content genshin-modal-content">
        <form id="formSyncAllInventory" action="{{ route('inventory.sync-all') }}" method="POST">
          @csrf
          <input type="hidden" name="account_id" value="{{ $activeAccount->id }}">

          <div class="modal-header genshin-modal-header">
            <h5 class="modal-title font-display text-gold d-flex align-items-center gap-2" id="modalSyncAllInventoryLabel">
              <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
              Sync Semua Inventori Sekaligus
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="p-3 mb-3 rounded" style="background: rgba(229, 160, 41, 0.1); border: 1px solid rgba(229, 160, 41, 0.3);">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-person-fill text-gold fs-5"></i>
                <strong class="text-white">{{ $activeAccount->nickname }}</strong>
                <span class="badge bg-secondary">UID: {{ $activeAccount->uid }}</span>
              </div>
              <small class="text-light" style="opacity: 0.95; line-height: 1.5; display: block;">
                1 klik ini akan otomatis mensinkronkan <strong>Karakter Showcase</strong>, <strong>Senjata yang Terpasang</strong>, dan <strong>Artefak (beserta Substat & Auto-Score)</strong> sekaligus!
              </small>
            </div>

            <div class="mb-3">
              <label class="form-label genshin-label">Pilih Sumber Sinkronisasi</label>
              <div class="d-flex flex-column gap-2">
                <label class="p-2 rounded d-flex align-items-center gap-2 border border-secondary border-opacity-25" style="background: rgba(15, 18, 30, 0.6); cursor: pointer;">
                  <input type="radio" name="source" value="enka" checked class="form-check-input mt-0">
                  <div>
                    <strong class="text-gold d-block" style="font-size: 0.88rem;">Enka.Network (via UID Game) &mdash; Direkomendasikan</strong>
                    <small class="text-light" style="opacity: 0.85;">Langsung sinkronisasi dari Showcase UID publik di dalam game tanpa login cookie.</small>
                  </div>
                </label>
                <label class="p-2 rounded d-flex align-items-center gap-2 border border-secondary border-opacity-25" style="background: rgba(15, 18, 30, 0.6); cursor: pointer;">
                  <input type="radio" name="source" value="hoyolab" class="form-check-input mt-0">
                  <div>
                    <strong class="text-white d-block" style="font-size: 0.88rem;">HoYoLAB Battle Chronicle (via Cookies)</strong>
                    <small class="text-light" style="opacity: 0.85;">Mengambil data seluruh koleksi dari akun HoYoLAB yang terhubung (perlu cookie aktif).</small>
                  </div>
                </label>
              </div>
            </div>

            <div class="mb-3" id="overrideUidGroup">
              <label for="override_uid" class="form-label genshin-label">Override UID (Opsional)</label>
              <input type="text" class="form-control" id="override_uid" name="override_uid" value="{{ $activeAccount->uid }}" placeholder="Masukkan UID jika ingin ganti">
              <small class="text-light" style="font-size: 0.75rem; opacity: 0.85;">Default menggunakan UID akun terpilih ({{ $activeAccount->uid }}).</small>
            </div>

            <div class="alert alert-dark border border-secondary text-light mb-0" style="font-size: 0.8rem; background: rgba(13, 15, 26, 0.8);">
              <i class="bi bi-info-circle-fill text-gold me-1"></i>
              <strong>Catatan Enka:</strong> Pastikan di dalam game Genshin Impact, opsi <em>"Tampilkan Detail Karakter"</em> pada profil Anda dalam keadaan <strong>AKTIF</strong>.
            </div>
          </div>

          <div class="modal-footer genshin-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" id="btnSubmitSyncAll" class="btn btn-warning fw-bold btn-sm text-dark px-3">
              <i class="bi bi-lightning-charge-fill me-1"></i>Mulai Sync Semua
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@push('scripts')
<script>
  $(function () {
    const $form = $('#formSyncAllInventory');
    const $btn = $('#btnSubmitSyncAll');

    $form.on('submit', function (e) {
      e.preventDefault();

      const sourceVal = $('input[name="source"]:checked').val();
      const sourceLabel = sourceVal === 'enka' ? 'Enka.Network (UID)' : 'HoYoLAB';

      Swal.fire({
        title: 'Sedang Mensinkronkan...',
        html: `Menghubungi <b>${sourceLabel}</b> untuk mengambil Karakter, Senjata, dan Artefak sekaligus.<br><small class="text-muted">Mohon tunggu beberapa detik...</small>`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        success: function (res) {
          $('#modalSyncAllInventory').modal('hide');
          Swal.fire({
            icon: 'success',
            title: 'Sinkronisasi Berhasil!',
            html: `
              <div class="text-start p-2 mb-2 rounded bg-dark border border-secondary" style="font-size: 0.9rem;">
                <div><i class="bi bi-check-circle-fill text-success me-1"></i> Karakter: <strong>${res.synced_characters ?? 0}</strong></div>
                <div><i class="bi bi-check-circle-fill text-success me-1"></i> Senjata: <strong>${res.synced_weapons ?? 0}</strong></div>
                <div><i class="bi bi-check-circle-fill text-success me-1"></i> Artifact: <strong>${res.synced_artifacts ?? 0}</strong></div>
              </div>
              <p class="mb-0 text-muted" style="font-size: 0.85rem;">${res.message || 'Semua data inventori berhasil diperbarui.'}</p>
            `,
            confirmButtonText: 'Segarkan Halaman',
            confirmButtonColor: '#e5a029'
          }).then(() => {
            window.location.reload();
          });
        },
        error: function (xhr) {
          let errMsg = 'Terjadi kesalahan saat sinkronisasi.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errMsg = xhr.responseJSON.message;
          }
          Swal.fire({
            icon: 'error',
            title: 'Gagal Sinkronisasi',
            text: errMsg,
            confirmButtonColor: '#ef4444'
          });
        },
        complete: function () {
          $btn.prop('disabled', false).html('<i class="bi bi-lightning-charge-fill me-1"></i>Mulai Sync Semua');
        }
      });
    });
  });
</script>
@endpush
@endsection

