@extends('layout.main')

@section('content')
@php $title = 'Artifact Scoring'; @endphp
@include('layout.header')

<div class="scoring-page-container">

  {{-- Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div class="d-flex align-items-center gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <h1 class="font-display text-gold mb-1" style="font-size: 1.55rem; letter-spacing: 0.08em;">
            <i class="bi bi-trophy-fill me-2"></i>Artifact Scoring System
          </h1>
          <button type="button" class="btn btn-link text-gold p-0 ms-1 btn-help-icon" data-bs-toggle="modal" data-bs-target="#modalHelpScoring" title="Buka Panduan Bantuan" style="text-decoration: none;">
            <i class="bi bi-question-circle fs-5"></i>
          </button>
        </div>
        <p style="color: var(--text-secondary); font-size: 0.86rem; margin-bottom: 0;">
          Nilai kualitas artifact berdasarkan sub-stat yang relevan untuk tiap karakter
        </p>
      </div>
    </div>

    {{-- Account Switcher --}}
    @if($accounts->isNotEmpty())
    <form method="GET" action="{{ route('artifact-scoring.index') }}" id="accountForm">
      <div class="input-group input-group-sm">
        <span class="input-group-text genshin-input-group-text">
          <i class="bi bi-controller text-gold"></i>
        </span>
        <select name="account_id" class="form-select genshin-select" onchange="document.getElementById('accountForm').submit()" style="min-width: 220px;">
          @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" {{ ($activeAccount?->id === $acc->id) ? 'selected' : '' }}>
              {{ $acc->nickname }} (UID: {{ $acc->uid }})
            </option>
          @endforeach
        </select>
      </div>
    </form>
    @endif
  </div>

  @if(!$activeAccount)
    <div class="empty-state text-center py-5">
      <div style="font-size: 3rem; opacity: 0.4; margin-bottom: 1rem;">🏆</div>
      <h5 class="text-gold">Belum Ada Akun Game</h5>
      <a href="{{ route('game-accounts.index') }}" class="btn-genshin btn-genshin-sm mt-3">Kelola Akun Game</a>
    </div>
  @else

    {{-- Quick Actions Bar --}}
    <div class="scoring-actions-bar mb-4 animate-fade-in-up">
      <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex gap-2 flex-wrap">
          {{-- Score All --}}
          <button class="btn-genshin" id="btnScoreAll" data-account="{{ $activeAccount->id }}">
            <i class="bi bi-lightning-charge-fill me-2"></i>Hitung Semua Skor
          </button>
          <a href="{{ route('artifact-scoring.rules', ['account_id' => $activeAccount->id]) }}" class="btn-genshin btn-genshin-outline">
            <i class="bi bi-gear-fill me-2"></i>Aturan Scoring
          </a>
          {{-- Generate Mock Substats Button --}}
          <button type="button" class="btn-genshin btn-genshin-outline" id="btnGenerateMockSubstats" data-account="{{ $activeAccount->id }}" title="Isi sub-stats realistis untuk pengujian scoring">
            <i class="bi bi-dice-5 me-2"></i>Isi Sub-Stat Simulasi
          </button>
          {{-- Sync Enka (UID) Button --}}
          <button type="button" class="btn-genshin btn-genshin-outline" data-bs-toggle="modal" data-bs-target="#modalSyncEnka" title="Sinkronisasi Artifact Showcase langsung via UID dengan Enka.Network">
            <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Enka (UID)
          </button>
          {{-- Help Button --}}
          <button type="button" class="btn-genshin btn-genshin-outline" data-bs-toggle="modal" data-bs-target="#modalHelpScoring">
            <i class="bi bi-question-circle-fill me-2"></i>Bantuan
          </button>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('artifact-scoring.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
          <input type="hidden" name="account_id" value="{{ $activeAccount->id }}">

          {{-- Filter Karakter Pemakai --}}
          <select name="equipped_character" class="form-select form-select-sm genshin-select" style="min-width: 165px;" title="Filter Karakter Pemakai">
            <option value="all" {{ ($filters['equipped_character'] ?? 'all') === 'all' ? 'selected' : '' }}>
              👤 Semua Karakter
            </option>
            <option value="equipped" {{ ($filters['equipped_character'] ?? '') === 'equipped' ? 'selected' : '' }}>
              ⚡ Sedang Dipakai (Semua)
            </option>
            <option value="unequipped" {{ ($filters['equipped_character'] ?? '') === 'unequipped' ? 'selected' : '' }}>
              📦 Belum Dipakai (Kosong)
            </option>
            @if($characters->isNotEmpty())
              <optgroup label="── Karakter Pemakai ──">
                @foreach($characters as $char)
                  <option value="{{ $char->id }}" {{ ($filters['equipped_character'] ?? '') == $char->id ? 'selected' : '' }}>
                    {{ $char->name }}
                  </option>
                @endforeach
              </optgroup>
            @endif
          </select>

          {{-- Filter Slot --}}
          <select name="slot" class="form-select form-select-sm genshin-select" style="width: 130px;">
            <option value="all" {{ ($filters['slot'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Slot</option>
            <option value="flower" {{ ($filters['slot'] ?? '') === 'flower' ? 'selected' : '' }}>🌸 Flower</option>
            <option value="plume"  {{ ($filters['slot'] ?? '') === 'plume'  ? 'selected' : '' }}>🪶 Plume</option>
            <option value="sands"  {{ ($filters['slot'] ?? '') === 'sands'  ? 'selected' : '' }}>⏳ Sands</option>
            <option value="goblet" {{ ($filters['slot'] ?? '') === 'goblet' ? 'selected' : '' }}>🏆 Goblet</option>
            <option value="circlet"{{ ($filters['slot'] ?? '') === 'circlet'? 'selected' : '' }}>👑 Circlet</option>
          </select>

          {{-- Filter Rating --}}
          <select name="rating" class="form-select form-select-sm genshin-select" style="width: 125px;">
            <option value="all" {{ ($filters['rating'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Rating</option>
            @foreach(['SS','S','A','B','C','D'] as $r)
              <option value="{{ $r }}" {{ ($filters['rating'] ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
            @endforeach
          </select>

          {{-- Sorting --}}
          <select name="sort" class="form-select form-select-sm genshin-select" style="width: 145px;">
            <option value="score_desc" {{ ($filters['sort'] ?? 'score_desc') === 'score_desc' ? 'selected' : '' }}>Skor Tertinggi</option>
            <option value="score_asc"  {{ ($filters['sort'] ?? '') === 'score_asc'  ? 'selected' : '' }}>Skor Terendah</option>
            <option value="level_desc" {{ ($filters['sort'] ?? '') === 'level_desc' ? 'selected' : '' }}>Level +20 Dulu</option>
          </select>

          <div class="form-check d-flex align-items-center gap-2 mb-0">
            <input class="form-check-input" type="checkbox" name="scored_only" value="1" id="chkScored"
              {{ request()->boolean('scored_only') ? 'checked' : '' }}>
            <label class="form-check-label" for="chkScored" style="font-size: 0.82rem; color: var(--text-secondary); white-space: nowrap;">
              Sudah di-score
            </label>
          </div>

          <div class="d-flex gap-1">
            <button class="btn-genshin btn-genshin-sm" type="submit">
              <i class="bi bi-funnel-fill me-1"></i>Filter
            </button>
            @if(
              ($filters['slot'] ?? 'all') !== 'all' ||
              ($filters['rating'] ?? 'all') !== 'all' ||
              ($filters['equipped_character'] ?? 'all') !== 'all' ||
              request()->boolean('scored_only') ||
              ($filters['sort'] ?? 'score_desc') !== 'score_desc'
            )
              <a href="{{ route('artifact-scoring.index', ['account_id' => $activeAccount->id]) }}" class="btn-genshin btn-genshin-sm btn-genshin-outline" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>

    {{-- Score Progress Bar (batch) --}}
    <div id="scoreProgressBar" class="scoring-batch-progress d-none mb-3">
      <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem; color: var(--text-secondary);">
        <span>Menghitung skor artifact...</span>
        <span id="scoreProgressText">0%</span>
      </div>
      <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
        <div class="progress-bar bg-warning" id="scoreProgressFill" style="width: 0%; transition: width 0.3s ease;"></div>
      </div>
    </div>

    {{-- Artifact Cards Grid --}}
    <div class="row g-3 animate-fade-in-up" id="artifactGrid">
      @forelse($artifacts as $art)
        @php
          $ratingColors = [
            'SS' => '#ffd700', 'S' => '#e5a029', 'A' => '#a855f7',
            'B'  => '#3b82f6', 'C' => '#10b981', 'D' => '#64748b',
          ];
          $rating = $art->score_rating;
          $rColor = $ratingColors[$rating] ?? '#64748b';
          $slotIcons = ['flower'=>'🌸','plume'=>'🪶','sands'=>'⏳','goblet'=>'🏆','circlet'=>'👑'];
          $slotIcon  = $slotIcons[$art->slot_key] ?? '💎';
        @endphp
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
          <div class="artifact-score-card" data-artifact-id="{{ $art->id }}">

            {{-- Rating Badge + Slot --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex align-items-center gap-2">
                <span style="font-size: 1.3rem;">{{ $slotIcon }}</span>
                <div>
                  <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-primary);">
                    {{ $art->artifactSet?->name ?? 'Unknown Set' }}
                  </div>
                  <div style="font-size: 0.72rem; color: var(--text-muted);">
                    Lv.{{ $art->level }} • {{ str_repeat('★', $art->rarity) }}
                  </div>
                </div>
              </div>
              @if($rating)
                <div class="rating-badge-big" style="background: {{ $rColor }}22; border-color: {{ $rColor }}66; color: {{ $rColor }};">
                  {{ $rating }}
                </div>
              @else
                <div class="rating-badge-big unscored">?</div>
              @endif
            </div>

            {{-- Score Display --}}
            <div class="score-display-bar mb-2">
              @if($art->score !== null)
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span style="font-size: 0.76rem; color: var(--text-muted);">Skor</span>
                  <span style="font-size: 1.1rem; font-weight: 800; color: {{ $rColor }};">{{ number_format($art->score, 1) }}</span>
                </div>
                <div class="score-progress-track">
                  <div class="score-progress-fill" style="width: {{ min($art->score, 100) }}%; background: {{ $rColor }};"></div>
                </div>
              @else
                <div class="text-center py-1" style="font-size: 0.76rem; color: var(--text-muted);">
                  <i class="bi bi-calculator me-1"></i>Belum dihitung
                </div>
              @endif
            </div>

            {{-- Main Stat --}}
            <div class="main-stat-chip mb-2">
              <span class="text-gold fw-bold" style="font-size: 0.78rem;">{{ strtoupper($art->main_stat_key) }}</span>
              <span style="font-size: 0.78rem; color: var(--text-muted);">{{ $art->main_stat_value }}</span>
            </div>

            {{-- Sub-Stats Section --}}
            <div class="sub-stats-container mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span style="font-size: 0.72rem; color: #cbd5e1; font-weight: 700; letter-spacing: 0.05em;">SUB-STATS</span>
                <button type="button" class="btn btn-link p-0 text-gold btn-edit-substats" 
                        data-artifact-id="{{ $art->id }}"
                        data-artifact-name="{{ $art->artifactSet?->name ?? 'Artifact' }} (+{{ $art->level }})"
                        data-substats="{{ json_encode($art->sub_stats ?? []) }}"
                        data-char-id="{{ $art->scored_for_character_id ?? $art->equipped_character_id ?? '' }}"
                        title="Edit / Input Sub-Stat" style="text-decoration: none; font-size: 0.72rem;">
                  <i class="bi bi-pencil-square me-1"></i>{{ empty($art->sub_stats) ? 'Input' : 'Edit' }}
                </button>
              </div>

              @if($art->sub_stats && count($art->sub_stats) > 0)
                <div class="sub-stats-mini">
                  @foreach(array_slice($art->sub_stats, 0, 4) as $sub)
                    @php
                      $sKey = $sub['key'] ?? $sub['stat'] ?? '';
                      $sLabel = $statOptions[$sKey] ?? ucwords(str_replace('_', ' ', $sKey));
                      $isCrit = str_contains(strtolower($sKey), 'crit');
                    @endphp
                    <div class="sub-stat-row">
                      <span style="color: {{ $isCrit ? '#fef08a' : '#cbd5e1' }}; font-size: 0.73rem;">
                        {{ $sLabel }}
                      </span>
                      <span style="color: #fff; font-size: 0.73rem; font-weight: 700;">
                        +{{ $sub['value'] ?? '—' }}{{ in_array($sKey, ['crit_rate','crit_dmg','atk_pct','hp_pct','def_pct','er']) && !str_contains($sub['value'] ?? '', '%') ? '%' : '' }}
                      </span>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="substats-empty-box p-2 text-center rounded">
                  <div style="font-size: 0.72rem; color: #fca5a5; line-height: 1.3;">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>Sub-stat kosong (HoYoLAB)
                  </div>
                  <button type="button" class="btn-genshin btn-genshin-sm btn-genshin-outline w-100 mt-1 py-0 btn-edit-substats"
                          data-artifact-id="{{ $art->id }}"
                          data-artifact-name="{{ $art->artifactSet?->name ?? 'Artifact' }} (+{{ $art->level }})"
                          data-substats="{{ json_encode($art->sub_stats ?? []) }}"
                          data-char-id="{{ $art->scored_for_character_id ?? $art->equipped_character_id ?? '' }}"
                          style="font-size: 0.7rem; height: 24px;">
                    <i class="bi bi-plus-circle me-1"></i>Input Sub-Stat
                  </button>
                </div>
              @endif
            </div>

            {{-- Equipped Character --}}
            @if($art->equippedCharacter)
              <div class="equipped-badge mb-2">
                <i class="bi bi-person-check-fill text-gold me-1"></i>
                <span style="font-size: 0.72rem;">{{ $art->equippedCharacter->name }}</span>
              </div>
            @endif

            {{-- Action: Score with Character --}}
            <div class="d-flex gap-1 align-items-center">
              <select class="form-select form-select-sm genshin-select score-char-select" style="font-size: 0.72rem;">
                <option value="">— Pilih Karakter —</option>
                @foreach($characters as $char)
                  <option value="{{ $char->id }}" {{ $art->scored_for_character_id == $char->id ? 'selected' : '' }}>
                    {{ $char->name }}
                  </option>
                @endforeach
              </select>
              <button class="btn-score-now" title="Hitung Skor" data-artifact="{{ $art->id }}">
                <i class="bi bi-calculator-fill"></i>
              </button>
            </div>

          </div>
        </div>
      @empty
        <div class="col-12 text-center py-5">
          <div style="font-size: 3rem; opacity: 0.3;">🏆</div>
          <p class="text-white mt-2 mb-1" style="color: #ffffff !important; font-size: 0.95rem;">Tidak ada artifact yang sesuai dengan filter.</p>
          <a href="{{ route('artifact-scoring.index', ['account_id' => $activeAccount->id]) }}" class="btn-genshin btn-genshin-sm btn-genshin-outline mt-2">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
          </a>
        </div>
      @endforelse
    </div>

    {{-- Pagination --}}
    @if($artifacts instanceof \Illuminate\Pagination\LengthAwarePaginator)
      <div class="scoring-pagination-wrapper">
        {{ $artifacts->links('pagination::bootstrap-5') }}
      </div>
    @endif

  @endif

</div>

{{-- Toast Notification --}}
<div id="scoreToast" class="score-toast d-none">
  <div id="scoreToastContent"></div>
</div>

{{-- MODAL HELP / PANDUAN ARTIFACT SCORING --}}
<div class="modal fade" id="modalHelpScoring" tabindex="-1" aria-labelledby="modalHelpScoringLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content genshin-modal-content">
      
      {{-- Modal Header --}}
      <div class="modal-header genshin-modal-header py-3 px-4">
        <div class="d-flex align-items-center gap-2">
          <div class="help-header-icon">
            <i class="bi bi-journal-bookmark-fill text-gold"></i>
          </div>
          <div>
            <h5 class="modal-title font-display text-gold mb-0" id="modalHelpScoringLabel">
              Panduan & Cara Penggunaan Artifact Scoring
            </h5>
            <small style="color: #ffffff !important; font-size: 0.8rem; font-weight: 500;">
              Panduan lengkap kalkulasi nilai, sistem rating, dan konfigurasi bobot
            </small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Modal Navigation Tabs --}}
      <div class="help-nav-container px-4 pt-3">
        <ul class="nav nav-pills help-nav-pills gap-1" id="helpTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-langkah-btn" data-bs-toggle="pill" data-bs-target="#tab-langkah" type="button" role="tab">
              <i class="bi bi-play-circle me-1"></i>Cara Pakai
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-rating-btn" data-bs-toggle="pill" data-bs-target="#tab-rating" type="button" role="tab">
              <i class="bi bi-award me-1"></i>Skala Rating
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-rumus-btn" data-bs-toggle="pill" data-bs-target="#tab-rumus" type="button" role="tab">
              <i class="bi bi-calculator me-1"></i>Rumus & Roll
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-archetype-btn" data-bs-toggle="pill" data-bs-target="#tab-archetype" type="button" role="tab">
              <i class="bi bi-diagram-3 me-1"></i>Archetype
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-faq-btn" data-bs-toggle="pill" data-bs-target="#tab-faq" type="button" role="tab">
              <i class="bi bi-patch-question me-1"></i>FAQ
            </button>
          </li>
        </ul>
      </div>

      {{-- Modal Body --}}
      <div class="modal-body p-4">
        <div class="tab-content" id="helpTabContent">

          {{-- TAB 1: CARA PENGGUNAAN --}}
          <div class="tab-pane fade show active" id="tab-langkah" role="tabpanel">
            <div class="help-step-list d-flex flex-column gap-3">
              
              <div class="help-step-card">
                <div class="help-step-num">1</div>
                <div class="help-step-body">
                  <h6 class="text-gold mb-1 font-display">Sinkronkan Data dari HoYoLAB</h6>
                  <p class="mb-0 text-white" style="font-size: 0.86rem; color: #ffffff !important; line-height: 1.55;">
                    Buka menu <strong class="text-white">Inventori > Artifact</strong> atau <strong class="text-white">Senjata</strong>, lalu klik tombol <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Sync HoYoLAB</span> untuk memperbarui daftar artifact aktif beserta sub-statnya langsung dari game.
                  </p>
                </div>
              </div>

              <div class="help-step-card">
                <div class="help-step-num">2</div>
                <div class="help-step-body">
                  <h6 class="text-gold mb-1 font-display">Hitung Skor Massal (Batch Score All)</h6>
                  <p class="mb-0 text-white" style="font-size: 0.86rem; color: #ffffff !important; line-height: 1.55;">
                    Klik tombol <strong class="text-gold">"⚡ Hitung Semua Skor"</strong> di atas. Sistem akan otomatis menilai semua artifact yang Anda miliki. Artifact yang terpasang pada karakter akan dihitung sesuai profil build karakter tersebut.
                  </p>
                </div>
              </div>

              <div class="help-step-card">
                <div class="help-step-num">3</div>
                <div class="help-step-body">
                  <h6 class="text-gold mb-1 font-display">Simulasi / Skor Per-Artifact Interaktif</h6>
                  <p class="mb-0 text-white" style="font-size: 0.86rem; color: #ffffff !important; line-height: 1.55;">
                    Ingin tahu apakah suatu artifact cocok untuk karakter lain? Pada kartu artifact, pilih karakter di dropdown <em class="text-white">"Pilih Karakter"</em> lalu klik ikon kalkulator <i class="bi bi-calculator-fill text-gold"></i>. Skor & rating akan terhitung secara instan.
                  </p>
                </div>
              </div>

              <div class="help-step-card">
                <div class="help-step-num">4</div>
                <div class="help-step-body">
                  <h6 class="text-gold mb-1 font-display">Kustomisasi Bobot Karakter</h6>
                  <p class="mb-0 text-white" style="font-size: 0.86rem; color: #ffffff !important; line-height: 1.55;">
                    Klik tombol <strong class="text-white">"Aturan Scoring per Karakter"</strong> untuk mengubah preferensi stat karakter (misal: memprioritaskan EM untuk Xiangling atau HP% untuk Hu Tao).
                  </p>
                </div>
              </div>

              <div class="help-step-card">
                <div class="help-step-num">5</div>
                <div class="help-step-body">
                  <h6 class="text-gold mb-1 font-display">Tarik Sub-Stat Asli Otomatis via UID (Enka.Network)</h6>
                  <p class="mb-0 text-white" style="font-size: 0.86rem; color: #ffffff !important; line-height: 1.55;">
                    Ingin seluruh sub-stat asli in-game tertarik otomatis tanpa perlu cookie/password? Klik tombol <strong class="text-gold"><i class="bi bi-cloud-arrow-down-fill me-1"></i>Sync Enka (UID)</strong>. Sistem akan mengambil artifact yang sedang dipajang di <em>Character Showcase</em> profil in-game Anda beserta semua roll sub-stat aslinya.
                  </p>
                </div>
              </div>

            </div>
          </div>

          {{-- TAB 2: SKALA RATING --}}
          <div class="tab-pane fade" id="tab-rating" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-dark table-hover align-middle help-table mb-2">
                <thead>
                  <tr style="border-bottom: 1px solid rgba(229,160,41,0.25);">
                    <th style="width: 80px;" class="text-gold">Rating</th>
                    <th style="width: 110px;" class="text-gold">Rentang Skor</th>
                    <th class="text-gold">Predikat Kualitas</th>
                    <th class="text-gold">Rekomendasi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><span class="badge-rating-pill" style="background:#ffd70022; border-color:#ffd700; color:#ffd700;">SS</span></td>
                    <td class="fw-bold text-gold">≥ 55.0</td>
                    <td class="text-white font-weight-bold">👑 God Tier</td>
                    <td class="text-white" style="color: #ffffff !important; font-size: 0.85rem;">Kualitas sempurna! Kunci dan simpan untuk karakter utama.</td>
                  </tr>
                  <tr>
                    <td><span class="badge-rating-pill" style="background:#e5a02922; border-color:#e5a029; color:#e5a029;">S</span></td>
                    <td class="fw-bold" style="color:#e5a029;">45.0 – 54.9</td>
                    <td class="text-white font-weight-bold">⭐ Outstanding</td>
                    <td class="text-white" style="color: #ffffff !important; font-size: 0.85rem;">Sangat bagus, ideal untuk Spiral Abyss Floor 12.</td>
                  </tr>
                  <tr>
                    <td><span class="badge-rating-pill" style="background:#a855f722; border-color:#a855f7; color:#a855f7;">A</span></td>
                    <td class="fw-bold" style="color:#a855f7;">35.0 – 44.9</td>
                    <td class="text-white font-weight-bold">✨ Great</td>
                    <td class="text-white" style="color: #ffffff !important; font-size: 0.85rem;">Sangat layak pakai dan efisien untuk endgame.</td>
                  </tr>
                  <tr>
                    <td><span class="badge-rating-pill" style="background:#3b82f622; border-color:#3b82f6; color:#3b82f6;">B</span></td>
                    <td class="fw-bold" style="color:#3b82f6;">25.0 – 34.9</td>
                    <td class="text-white font-weight-bold">🔹 Decent</td>
                    <td class="text-white" style="color: #ffffff !important; font-size: 0.85rem;">Cukup bagus, cocok untuk placeholder atau karakter support.</td>
                  </tr>
                  <tr>
                    <td><span class="badge-rating-pill" style="background:#10b98122; border-color:#10b981; color:#10b981;">C</span></td>
                    <td class="fw-bold" style="color:#10b981;">15.0 – 24.9</td>
                    <td class="text-white font-weight-bold">🔸 Mediocre</td>
                    <td class="text-white" style="color: #ffffff !important; font-size: 0.85rem;">Biasa saja. Prioritaskan diganti saat ada opsi lebih baik.</td>
                  </tr>
                  <tr>
                    <td><span class="badge-rating-pill" style="background:#64748b22; border-color:#64748b; color:#94a3b8;">D</span></td>
                    <td class="fw-bold text-white">&lt; 15.0</td>
                    <td class="text-white font-weight-bold">❌ Fodder</td>
                    <td class="text-white" style="color: #ffffff !important; font-size: 0.85rem;">Sub-stat tidak mendukung. Cocok dijadikan bahan upgrade / Mystic Offering.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          {{-- TAB 3: RUMUS & ROLL QUALITY --}}
          <div class="tab-pane fade" id="tab-rumus" role="tabpanel">
            <div class="p-3 mb-3" style="background: rgba(229,160,41,0.1); border: 1px solid rgba(229,160,41,0.3); border-radius: 10px;">
              <h6 class="text-gold mb-1 font-display"><i class="bi bi-lightbulb-fill me-1"></i>Konsep Perhitungan</h6>
              <p class="mb-0 text-white" style="color: #ffffff !important; font-size: 0.86rem; line-height: 1.55;">
                Setiap sub-stat dinormalisasi terhadap <strong class="text-gold">nilai rata-rata 1 kali roll</strong> (bintang 5), dikalikan dengan <strong class="text-gold">bobot (0.0 – 1.0)</strong> sesuai karakter yang dipilih, kemudian dijumlahkan.
              </p>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">CRIT Rate</span>
                  <span class="stat-val text-gold">3.30%</span>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">CRIT DMG</span>
                  <span class="stat-val text-gold">6.60%</span>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">ATK% / HP%</span>
                  <span class="stat-val text-gold">4.95%</span>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">DEF%</span>
                  <span class="stat-val text-gold">6.20%</span>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">Elem. Mastery</span>
                  <span class="stat-val text-gold">19.75</span>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">Energy Recharge</span>
                  <span class="stat-val text-gold">5.50%</span>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">Flat ATK</span>
                  <span class="stat-val text-white" style="color: #ffffff !important;">16.50</span>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="roll-ref-card">
                  <span class="stat-name text-white" style="color: #ffffff !important;">Flat HP / DEF</span>
                  <span class="stat-val text-white" style="color: #ffffff !important;">253 / 19.4</span>
                </div>
              </div>
            </div>

            <div class="p-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px;">
              <span class="text-gold fw-bold" style="font-size: 0.85rem;">Formula Matematis:</span>
              <code class="d-block mt-2 p-2 rounded text-white" style="background: #090b10; color: #ffd700; font-size: 0.85rem; border: 1px solid rgba(255,255,255,0.1);">
                Rolls = Nilai SubStat / Rata-rata 1 Roll<br>
                Kontribusi = Rolls × Bobot (0.0 - 1.0) × 10<br>
                Total Skor = Σ Kontribusi (Maksimal 100)
              </code>
            </div>
          </div>

          {{-- TAB 4: ARCHETYPE --}}
          <div class="tab-pane fade" id="tab-archetype" role="tabpanel">
            <p class="text-white mb-3" style="color: #ffffff !important; font-size: 0.86rem;">
              Anda dapat memilih template archetype berikut saat mengatur preferensi karakter di halaman <strong class="text-gold">Aturan Scoring</strong>:
            </p>
            <div class="d-flex flex-column gap-2">
              <div class="archetype-badge-item">
                <div class="fw-bold text-gold" style="font-size: 0.88rem;"><i class="bi bi-crosshair me-2"></i>DPS — CRIT Build</div>
                <div class="text-white mt-1" style="color: #ffffff !important; font-size: 0.84rem; line-height: 1.5;">Fokus utama pada CRIT Rate (1.0), CRIT DMG (1.0), dan ATK% (0.75). Standar untuk sebagian besar DPS.</div>
              </div>
              <div class="archetype-badge-item">
                <div class="fw-bold text-gold" style="font-size: 0.88rem;"><i class="bi bi-fire me-2"></i>DPS — Elemental Mastery Build</div>
                <div class="text-white mt-1" style="color: #ffffff !important; font-size: 0.84rem; line-height: 1.5;">Fokus tinggi pada Elemental Mastery (1.0) dan CRIT (0.5). Ideal untuk tim reaksi elemental (Vaporize/Melt/Aggravate).</div>
              </div>
              <div class="archetype-badge-item">
                <div class="fw-bold text-gold" style="font-size: 0.88rem;"><i class="bi bi-heart-pulse-fill me-2"></i>DPS — HP Scaling</div>
                <div class="text-white mt-1" style="color: #ffffff !important; font-size: 0.84rem; line-height: 1.5;">Untuk karakter seperti Hu Tao, Yelan, Neuvillette (HP% 1.0, CRIT 0.75, ATK% 0.0).</div>
              </div>
              <div class="archetype-badge-item">
                <div class="fw-bold text-gold" style="font-size: 0.88rem;"><i class="bi bi-shield-shaded me-2"></i>Support (Buffer/Debuffer)</div>
                <div class="text-white mt-1" style="color: #ffffff !important; font-size: 0.84rem; line-height: 1.5;">Fokus pada kelancaran rotasi: Energy Recharge (1.0), HP%/DEF%, dan EM (0.5).</div>
              </div>
              <div class="archetype-badge-item">
                <div class="fw-bold text-gold" style="font-size: 0.88rem;"><i class="bi bi-bandaid-fill me-2"></i>Healer / Tank</div>
                <div class="text-white mt-1" style="color: #ffffff !important; font-size: 0.84rem; line-height: 1.5;">Fokus penuh pada HP% (1.0), Energy Recharge (1.0), dan DEF% (0.25).</div>
              </div>
            </div>
          </div>

          {{-- TAB 5: FAQ --}}
          <div class="tab-pane fade" id="tab-faq" role="tabpanel">
            <div class="accordion help-accordion" id="helpAccordion">
              
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                    Mengapa artifact +20 saya hanya mendapatkan rating C atau D?
                  </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                  <div class="accordion-body text-white" style="color: #ffffff !important; font-size: 0.85rem; line-height: 1.55;">
                    Hal ini biasanya terjadi karena roll peningkatan level masuk ke sub-stat yang bernilai bobot rendah untuk karakter tersebut (misal banyak roll masuk ke Flat DEF/Flat HP pada karakter DPS), atau aturan bobot karakter belum disesuaikan dengan kebutuhan build-nya.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    Bagaimana jika artifact belum dipasang ke karakter manapun?
                  </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                  <div class="accordion-body text-white" style="color: #ffffff !important; font-size: 0.85rem; line-height: 1.55;">
                    Artifact yang belum terpasang akan dinilai menggunakan formula standar DPS CRIT (fokus CRIT & ATK%). Anda juga dapat memilih karakter secara manual melalui dropdown kartu untuk melihat simulasi skor pada karakter tertentu.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                    Apakah penilaian ini mengubah data di game Genshin Impact saya?
                  </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                  <div class="accordion-body text-white" style="color: #ffffff !important; font-size: 0.85rem; line-height: 1.55;">
                    Tidak sama sekali. Sistem ini hanya membaca data inventori melalui HoYoLAB API dan menghitung skor di database lokal aplikasi untuk membantu analisis Anda.
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>

      {{-- Modal Footer --}}
      <div class="modal-footer genshin-modal-footer d-flex justify-content-between py-2 px-4">
        <small class="text-white" style="color: #ffffff !important; font-size: 0.82rem;">
          <i class="bi bi-file-earmark-text text-gold me-1"></i>Dokumentasi lengkap: <span class="badge" style="background: rgba(229,160,41,0.25); color: #ffd700; border: 1px solid rgba(229,160,41,0.5);">PANDUAN_ARTIFACT_SCORING.md</span>
        </small>
        <button type="button" class="btn-genshin btn-genshin-sm" data-bs-dismiss="modal">
          Tutup Panduan
        </button>
      </div>

    </div>
  </div>
</div>

{{-- MODAL INPUT/EDIT SUB-STATS --}}
<div class="modal fade" id="modalEditSubstats" tabindex="-1" aria-labelledby="modalEditSubstatsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header py-3 px-4">
        <h5 class="modal-title font-display text-gold mb-0" id="modalEditSubstatsLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Sub-Stat Artifact
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="p-2 mb-3 rounded" style="background: rgba(229,160,41,0.08); border: 1px solid rgba(229,160,41,0.25);">
          <span id="modalSubstatArtName" class="fw-bold text-gold" style="font-size: 0.88rem;"></span>
          <p class="mb-0 text-white mt-1" style="font-size: 0.78rem; line-height: 1.4;">
            Masukkan 4 sub-stat artifact ini. Skor kualitas akan otomatis dihitung setelah Anda menyimpan.
          </p>
        </div>

        <form id="formEditSubstats">
          <input type="hidden" id="editSubstatArtifactId" value="">
          
          <div class="d-flex flex-column gap-2 mb-3">
            @for($i = 0; $i < 4; $i++)
              <div class="row g-2 align-items-center">
                <div class="col-7">
                  <label class="form-label mb-1 text-white" style="font-size: 0.74rem;">Sub-Stat #{{ $i + 1 }}</label>
                  <select class="form-select form-select-sm genshin-select sub-stat-key-input" data-index="{{ $i }}">
                    <option value="">-- Pilih Jenis Stat --</option>
                    @foreach($statOptions as $sKey => $sLabel)
                      <option value="{{ $sKey }}">{{ $sLabel }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-5">
                  <label class="form-label mb-1 text-white" style="font-size: 0.74rem;">Nilai Angka</label>
                  <input type="number" step="0.1" min="0" class="form-control form-control-sm genshin-input sub-stat-val-input" data-index="{{ $i }}" placeholder="contoh: 3.9">
                </div>
              </div>
            @endfor
          </div>

          <div class="mb-3">
            <label class="form-label text-white" style="font-size: 0.8rem;">Hitung Skor Untuk Karakter:</label>
            <select id="modalSubstatCharSelect" class="form-select form-select-sm genshin-select">
              <option value="">-- Gunakan Karakter Terpasang / Default CV --</option>
              @foreach($characters as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer genshin-modal-footer d-flex justify-content-between py-2 px-4">
        <button type="button" class="btn btn-sm btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn-genshin btn-genshin-sm" id="btnSaveSubstats">
          <i class="bi bi-check-lg me-1"></i>Simpan & Hitung Skor
        </button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL SYNC ENKA.NETWORK VIA UID --}}
<div class="modal fade" id="modalSyncEnka" tabindex="-1" aria-labelledby="modalSyncEnkaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header py-3 px-4">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-cloud-arrow-down-fill text-gold fs-5"></i>
          <h5 class="modal-title font-display text-gold mb-0" id="modalSyncEnkaLabel">
            Sinkronisasi Showcase via Enka.Network (UID)
          </h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="p-3 mb-3 rounded" style="background: rgba(229,160,41,0.08); border: 1px solid rgba(229,160,41,0.25);">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge" style="background: #22c55e; color: #fff; font-size: 0.72rem;">✨ Sub-Stat Asli 100%</span>
            <span class="badge" style="background: #3b82f6; color: #fff; font-size: 0.72rem;">🔒 Tanpa Cookie / Password</span>
          </div>
          <p class="mb-0 text-white" style="font-size: 0.84rem; line-height: 1.55;">
            Fitur ini menarik karakter dan artifact yang sedang dipajang di <strong>Character Showcase</strong> in-game profil Genshin Impact Anda. Seluruh <strong>sub-stat asli</strong> langsung tersimpan ke inventori dan skor kualitasnya langsung dihitung!
          </p>
        </div>

        @if($activeAccount)
        <form id="formSyncEnka">
          <input type="hidden" id="syncEnkaAccountId" value="{{ $activeAccount->id }}">

          <div class="mb-3">
            <label class="form-label text-white" style="font-size: 0.8rem; font-weight: 600;">Akun Game Terpilih:</label>
            <div class="p-2 rounded d-flex align-items-center justify-content-between" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
              <span class="text-gold fw-bold" style="font-size: 0.88rem;">{{ $activeAccount->nickname }}</span>
              <span class="text-white-50" style="font-size: 0.8rem;">Server: {{ ucfirst($activeAccount->server ?? 'Asia') }}</span>
            </div>
          </div>

          <div class="mb-3">
            <label for="syncEnkaUidInput" class="form-label text-white" style="font-size: 0.8rem; font-weight: 600;">UID Akun Genshin Impact:</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); color: #ffd700;">
                <i class="bi bi-person-badge"></i>
              </span>
              <input type="text" id="syncEnkaUidInput" class="form-control form-control-sm genshin-input"
                     value="{{ $activeAccount->uid }}" placeholder="contoh: 809404073" required>
            </div>
            <div class="form-text text-white-50 mt-1" style="font-size: 0.75rem;">
              <i class="bi bi-info-circle me-1 text-gold"></i>Pastikan opsi <em>"Tampilkan Detail Karakter"</em> / <em>"Show Character Details"</em> sudah dalam posisi <strong>AKTIF</strong> di profil in-game.
            </div>
          </div>
        </form>
        @endif

        <div id="syncEnkaAlert" class="alert d-none mt-3 py-2 px-3 mb-0" style="font-size: 0.82rem;"></div>
      </div>
      <div class="modal-footer genshin-modal-footer d-flex justify-content-between py-2 px-4">
        <button type="button" class="btn btn-sm btn-outline-secondary text-white" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn-genshin btn-genshin-sm" id="btnSubmitSyncEnka">
          <i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai Sinkronkan (UID)
        </button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/artifact-scoring.css') }}">
@endpush

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
  || '{{ csrf_token() }}';

// ─── Score Single Artifact ────────────────────────────────────
document.querySelectorAll('.btn-score-now').forEach(btn => {
  btn.addEventListener('click', async () => {
    const card       = btn.closest('.artifact-score-card');
    const artifactId = btn.dataset.artifact;
    const charSelect = card.querySelector('.score-char-select');
    const charId     = charSelect?.value || '';

    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    btn.disabled  = true;

    try {
      const res  = await fetch(`/artifact-scoring/score-one/${artifactId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ character_id: charId || null }),
      });
      const data = await res.json();

      if (data.success) {
        updateCardScore(card, data);
        showToast(`✅ Skor: <strong>${data.score}</strong> (${data.rating})${data.character ? ' — ' + data.character : ''}`);
      }
    } catch (e) {
      showToast('❌ Gagal menghitung skor', '#ef4444');
    } finally {
      btn.innerHTML = '<i class="bi bi-calculator-fill"></i>';
      btn.disabled  = false;
    }
  });
});

// ─── Score All Button ─────────────────────────────────────────
document.getElementById('btnScoreAll')?.addEventListener('click', async () => {
  const accountId = document.getElementById('btnScoreAll').dataset.account;
  const progress  = document.getElementById('scoreProgressBar');
  const fill      = document.getElementById('scoreProgressFill');
  const txt       = document.getElementById('scoreProgressText');

  progress.classList.remove('d-none');
  fill.style.width = '0%';

  // Animasi loading palsu sementara menunggu respons
  let pct = 0;
  const ticker = setInterval(() => {
    if (pct < 85) { pct += 5; fill.style.width = pct + '%'; txt.textContent = pct + '%'; }
  }, 200);

  try {
    const res  = await fetch('/artifact-scoring/score-all', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ account_id: accountId }),
    });
    const data = await res.json();

    clearInterval(ticker);
    fill.style.width = '100%'; txt.textContent = '100%';

    if (data.success) {
      showToast(`✅ ${data.message}`, '#22c55e');
      setTimeout(() => window.location.reload(), 1800);
    }
  } catch (e) {
    clearInterval(ticker);
    showToast('❌ Gagal batch scoring', '#ef4444');
  } finally {
    setTimeout(() => progress.classList.add('d-none'), 2000);
  }
});

// ─── Update Card UI setelah di-score ─────────────────────────
function updateCardScore(card, data) {
  const ratingColors = {
    SS: '#ffd700', S: '#e5a029', A: '#a855f7',
    B: '#3b82f6',  C: '#10b981',D: '#64748b',
  };
  const color = ratingColors[data.rating] || '#64748b';
  const score = parseFloat(data.score).toFixed(1);

  // Update rating badge
  const badge = card.querySelector('.rating-badge-big');
  if (badge) {
    badge.textContent = data.rating;
    badge.style.background = color + '22';
    badge.style.borderColor = color + '66';
    badge.style.color = color;
    badge.classList.remove('unscored');
  }

  // Update score bar
  const barDiv = card.querySelector('.score-display-bar');
  if (barDiv) {
    barDiv.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span style="font-size:0.76rem;color:var(--text-muted);">Skor</span>
        <span style="font-size:1.1rem;font-weight:800;color:${color};">${score}</span>
      </div>
      <div class="score-progress-track">
        <div class="score-progress-fill" style="width:${Math.min(data.score, 100)}%;background:${color};"></div>
      </div>`;
  }
}

// ─── Toast Helper ─────────────────────────────────────────────
function showToast(msg, borderColor = 'rgba(229,160,41,0.4)') {
  const toast = document.getElementById('scoreToast');
  const content = document.getElementById('scoreToastContent');
  content.innerHTML = msg;
  toast.style.borderColor = borderColor;
  toast.classList.remove('d-none');
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.add('d-none'), 3500);
}

// ─── Modal Edit Sub-Stats Logic ───────────────────────────────
const modalSubstatsEl = document.getElementById('modalEditSubstats');
const bsModalSubstats = modalSubstatsEl ? new bootstrap.Modal(modalSubstatsEl) : null;

document.querySelectorAll('.btn-edit-substats').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const artId = btn.dataset.artifactId;
    const artName = btn.dataset.artifactName || 'Artifact';
    const charId = btn.dataset.charId || '';
    let substats = [];
    try {
      substats = JSON.parse(btn.dataset.substats || '[]');
    } catch (err) {
      substats = [];
    }

    document.getElementById('editSubstatArtifactId').value = artId;
    document.getElementById('modalSubstatArtName').textContent = artName;
    const charSelect = document.getElementById('modalSubstatCharSelect');
    if (charSelect) charSelect.value = charId;

    // Reset inputs
    document.querySelectorAll('.sub-stat-key-input').forEach((sel, idx) => {
      sel.value = substats[idx]?.key || substats[idx]?.stat || '';
    });
    document.querySelectorAll('.sub-stat-val-input').forEach((inp, idx) => {
      inp.value = substats[idx]?.value || substats[idx]?.val || '';
    });

    bsModalSubstats?.show();
  });
});

// Simpan Sub-stats via AJAX
document.getElementById('btnSaveSubstats')?.addEventListener('click', async () => {
  const artId = document.getElementById('editSubstatArtifactId').value;
  const charId = document.getElementById('modalSubstatCharSelect')?.value || '';
  const keyInputs = document.querySelectorAll('.sub-stat-key-input');
  const valInputs = document.querySelectorAll('.sub-stat-val-input');

  const subStats = [];
  keyInputs.forEach((sel, idx) => {
    const k = sel.value.trim();
    const v = parseFloat(valInputs[idx]?.value || 0);
    if (k && v > 0) {
      subStats.push({ key: k, value: v });
    }
  });

  if (subStats.length === 0) {
    alert('Mohon isi minimal satu sub-stat dengan nilai lebih dari 0.');
    return;
  }

  const saveBtn = document.getElementById('btnSaveSubstats');
  saveBtn.disabled = true;
  saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Menyimpan...';

  try {
    const res = await fetch(`/artifact-scoring/update-substats/${artId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ sub_stats: subStats, character_id: charId || null }),
    });
    const data = await res.json();

    if (data.success) {
      bsModalSubstats?.hide();
      showToast(`✅ ${data.message}`, '#22c55e');

      // Update kartu yang bersangkutan di halaman
      const card = document.querySelector(`.artifact-score-card[data-artifact-id="${artId}"]`);
      if (card) {
        updateCardScore(card, data);
        // Refresh halaman setelah jeda sebentar agar tampilan sub-stat terupdate lengkap
        setTimeout(() => window.location.reload(), 1200);
      } else {
        setTimeout(() => window.location.reload(), 1200);
      }
    } else {
      showToast('❌ Gagal menyimpan sub-stat', '#ef4444');
    }
  } catch (err) {
    showToast('❌ Terjadi kesalahan saat menyimpan', '#ef4444');
  } finally {
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan & Hitung Skor';
  }
});

// ─── Generate Mock Sub-stats ──────────────────────────────────
document.getElementById('btnGenerateMockSubstats')?.addEventListener('click', async () => {
  if (!confirm('Apakah Anda ingin menghasilkan roll sub-stats acak realistis untuk seluruh artifact di akun ini?')) {
    return;
  }

  const accountId = document.getElementById('btnGenerateMockSubstats').dataset.account;
  const progress = document.getElementById('scoreProgressBar');
  const fill = document.getElementById('scoreProgressFill');
  const txt = document.getElementById('scoreProgressText');

  progress?.classList.remove('d-none');
  if (fill) fill.style.width = '50%';
  if (txt) txt.textContent = 'Membuat roll sub-stats simulasi...';

  try {
    const res = await fetch('/artifact-scoring/generate-mock-substats', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ account_id: accountId }),
    });
    const data = await res.json();

    if (data.success) {
      if (fill) fill.style.width = '100%';
      showToast(`🎲 ${data.message}`, '#22c55e');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showToast('❌ Gagal menghasilkan sub-stats', '#ef4444');
    }
  } catch (err) {
    showToast('❌ Terjadi kesalahan saat generate sub-stats', '#ef4444');
  } finally {
    setTimeout(() => progress?.classList.add('d-none'), 2000);
  }
});

// ─── Sync via Enka.Network UID ────────────────────────────────
const modalSyncEnkaEl = document.getElementById('modalSyncEnka');
const bsModalSyncEnka = modalSyncEnkaEl ? new bootstrap.Modal(modalSyncEnkaEl) : null;

document.getElementById('btnSubmitSyncEnka')?.addEventListener('click', async () => {
  const accountId = document.getElementById('syncEnkaAccountId')?.value;
  const uid = document.getElementById('syncEnkaUidInput')?.value.trim();
  const alertBox = document.getElementById('syncEnkaAlert');
  const btn = document.getElementById('btnSubmitSyncEnka');

  if (!uid || !/^\d{9,10}$/.test(uid)) {
    if (alertBox) {
      alertBox.className = 'alert alert-danger mt-3 py-2 px-3 mb-0';
      alertBox.textContent = 'Mohon masukkan UID yang valid (9-10 digit angka).';
      alertBox.classList.remove('d-none');
    }
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghubungi Enka.Network...';
  if (alertBox) {
    alertBox.className = 'alert alert-info mt-3 py-2 px-3 mb-0';
    alertBox.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sedang mengambil data showcase & menghitung skor kualitas artifact...';
    alertBox.classList.remove('d-none');
  }

  try {
    const res = await fetch('/artifact-scoring/sync-enka', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ account_id: accountId, uid: uid }),
    });
    const data = await res.json();

    if (data.success) {
      alertBox.className = 'alert alert-success mt-3 py-2 px-3 mb-0';
      alertBox.innerHTML = `✅ ${data.message}`;
      showToast(`✨ ${data.message}`, '#22c55e');
      setTimeout(() => {
        bsModalSyncEnka?.hide();
        window.location.reload();
      }, 1500);
    } else {
      alertBox.className = 'alert alert-warning mt-3 py-2 px-3 mb-0';
      alertBox.innerHTML = `⚠️ ${data.message}`;
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-cloud-arrow-down-fill me-1"></i>Coba Lagi';
    }
  } catch (err) {
    alertBox.className = 'alert alert-danger mt-3 py-2 px-3 mb-0';
    alertBox.textContent = 'Terjadi kesalahan jaringan saat sinkronisasi.';
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-cloud-arrow-down-fill me-1"></i>Coba Lagi';
  }
});
</script>
@endpush

@endsection
