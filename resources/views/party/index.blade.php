@extends('layout.main')

@section('content')
@php $title = 'Party Analyzer'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 4rem;">

  {{-- Flash Message --}}
  @if(session('success'))
    <div class="alert-toast success" id="alertToast">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert-toast error" id="alertToast">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    </div>
  @endif

  {{-- Page Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.45rem; letter-spacing: 0.08em;">
        <i class="bi bi-shield-fill-check me-2"></i>Party Matchup & Combat Analyzer
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0;">
        Analisis kecocokan komposisi tim dan rekomendasi karakter terbaik melawan musuh Teyvat
      </p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn-genshin btn-genshin-sm" id="btnAutoGenerateTop" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.25), rgba(249, 115, 22, 0.25)); border-color: rgba(234, 179, 8, 0.8);">
        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Auto-Generate Tim Terbaik
      </button>
      <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalSaveParty" style="border-radius: 8px;">
        <i class="bi bi-bookmark-plus me-1"></i>Simpan Party
      </button>
    </div>
  </div>

  {{-- Target & Account Selector Bar --}}
  <div class="filter-panel mb-4 animate-fade-in-up">
    <form method="GET" action="{{ route('party.index') }}" id="partyFilterForm">
      <div class="row g-3 align-items-center">
        {{-- Pilih Musuh --}}
        <div class="col-12 col-md-6 col-lg-5">
          <label class="form-label small text-gold fw-bold mb-1"><i class="bi bi-crosshair me-1"></i>Target Musuh yang Akan Dilawan:</label>
          <select name="enemy_id" class="form-select genshin-select" onchange="this.form.submit()">
            @foreach($enemies as $enemy)
              <option value="{{ $enemy->id }}" {{ $selectedEnemy && $selectedEnemy->id == $enemy->id ? 'selected' : '' }}>
                [{{ $enemy->category }}] {{ $enemy->name }} {{ !empty($enemy->elements) ? '('.implode('/', $enemy->elements).')' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Pilih Akun Game --}}
        <div class="col-12 col-md-4 col-lg-4">
          <label class="form-label small text-gold fw-bold mb-1"><i class="bi bi-person-badge me-1"></i>Sumber Karakter (Akun):</label>
          <select name="game_account_id" class="form-select genshin-select" onchange="this.form.submit()">
            <option value="all" {{ empty($selectedAccountId) ? 'selected' : '' }}>Semua Karakter (Master Data Teyvat)</option>
            @foreach($gameAccounts as $acc)
              <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                {{ $acc->account_name }} (UID: {{ $acc->uid }}) — {{ $acc->inventory_characters_count }} Karakter
              </option>
            @endforeach
          </select>
        </div>

        {{-- Quick Refresh Button --}}
        <div class="col-12 col-md-2 col-lg-3 d-flex align-items-end">
          <button type="submit" class="btn btn-sm btn-outline-secondary w-100" style="border-radius: 8px; padding: 0.45rem;">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh Data
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- Target Enemy Dossier Card --}}
  @if($selectedEnemy)
    @php
      $elementColor = $selectedEnemy->element_color;
    @endphp
    <div class="enemy-dossier-card mb-4 animate-fade-in-up">
      <div class="row g-3 align-items-center">
        <div class="col-auto">
          <div class="enemy-avatar-ring" style="width: 80px; height: 80px; --element-glow: {{ $elementColor }};">
            @if($selectedEnemy->icon_url)
              <img src="{{ $selectedEnemy->icon_url }}" alt="{{ $selectedEnemy->name }}" class="enemy-avatar" referrerpolicy="no-referrer">
            @else
              <i class="bi bi-crosshair text-gold fs-2"></i>
            @endif
          </div>
        </div>

        <div class="col">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h3 class="font-display text-gold mb-0" style="font-size: 1.25rem;">{{ $selectedEnemy->name }}</h3>
            <span class="badge {{ $selectedEnemy->category_badge_class }}">{{ $selectedEnemy->category }}</span>
            @if($selectedEnemy->region)
              <span class="badge bg-dark border border-secondary text-info"><i class="bi bi-geo-alt-fill me-1"></i>{{ $selectedEnemy->region }}</span>
            @endif
            @if($selectedEnemy->family)
              <span class="badge bg-dark border border-secondary text-light">{{ $selectedEnemy->family }}</span>
            @endif
          </div>

          <p class="text-secondary small mb-2" style="max-height: 48px; overflow: hidden; line-height: 1.4;">
            {{ $selectedEnemy->description ?: 'Musuh penjelajah dunia Teyvat.' }}
          </p>

          {{-- Attributes & Counters Strip --}}
          <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Elemen Musuh --}}
            <div class="d-flex align-items-center gap-1">
              <span class="dossier-mini-label text-muted">Elemen:</span>
              @if(!empty($selectedEnemy->elements))
                @foreach($selectedEnemy->elements as $elm)
                  <span class="badge-mini-element" data-element="{{ strtolower($elm) }}">{{ $elm }}</span>
                @endforeach
              @else
                <span class="badge-mini-element text-muted">Physical</span>
              @endif
            </div>

            {{-- Kelemahan Elemen --}}
            @if(!empty($selectedEnemy->weakness_elements))
              <div class="d-flex align-items-center gap-1">
                <span class="dossier-mini-label text-success fw-bold">Lemah:</span>
                @foreach($selectedEnemy->weakness_elements as $w)
                  <span class="badge bg-success bg-opacity-25 border border-success text-success-emphasis" style="font-size: 0.68rem;">{{ $w }}</span>
                @endforeach
              </div>
            @endif

            {{-- Imunitas --}}
            @if(!empty($selectedEnemy->immunities))
              <div class="d-flex align-items-center gap-1">
                <span class="dossier-mini-label text-danger fw-bold">Kebal:</span>
                @foreach($selectedEnemy->immunities as $im)
                  <span class="badge bg-danger bg-opacity-25 border border-danger text-danger-emphasis" style="font-size: 0.68rem;">{{ $im }}</span>
                @endforeach
              </div>
            @endif

            {{-- Mekanik Wajib --}}
            @if(!empty($selectedEnemy->recommended_mechanics))
              <div class="d-flex align-items-center gap-1">
                <span class="dossier-mini-label text-info fw-bold">Mekanik Disarankan:</span>
                @foreach($selectedEnemy->recommended_mechanics as $mc)
                  <span class="badge bg-info bg-opacity-25 border border-info text-info" style="font-size: 0.68rem;">{{ ucwords(str_replace('_', ' ', $mc)) }}</span>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        {{-- Strategy Tips Box --}}
        @if($selectedEnemy->tips_strategy)
          <div class="col-12 col-lg-4 border-start border-secondary border-opacity-25 ps-lg-3">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-lightbulb-fill text-warning fs-5 flex-shrink-0 mt-1"></i>
              <div>
                <span class="fw-bold text-gold small d-block">Tips Strategi:</span>
                <small class="text-secondary" style="font-size: 0.78rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ $selectedEnemy->tips_strategy }}
                </small>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  @endif
  {{-- Interactive Party Slots Grid (4 Characters) --}}
  <div class="row g-3 mb-4 animate-fade-in-up">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="font-display text-gold mb-0" style="font-size: 1.1rem;">
          <i class="bi bi-people-fill me-2"></i>Komposisi Party (Maks. 4 Karakter)
        </h4>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearParty" style="border-radius: 6px; font-size: 0.78rem;">
            <i class="bi bi-trash me-1"></i>Reset Party
          </button>
        </div>
      </div>
    </div>

    @for($slot = 0; $slot < 4; $slot++)
      @php
        $member = $analysis['members'][$slot] ?? null;
        $char = $member['character'] ?? null;
        $inv = $member['inventory'] ?? null;
        $roleLabel = match($slot) {
            0 => 'Main DPS',
            1 => 'Sub-DPS / Enabler',
            2 => 'Support / Buffer',
            default => 'Sustainer (Shield/Heal)',
        };
      @endphp
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="party-slot-card {{ $char ? 'filled' : 'empty' }}" data-slot="{{ $slot }}" id="partySlot_{{ $slot }}">
          <div class="slot-role-tag">{{ $roleLabel }}</div>

          @if($char)
            @php
              $elColor = $char->element_color;
              $isFiveStar = $char->rarity === 5;
            @endphp
            <div class="slot-card-inner">
              <button type="button" class="btn-remove-slot" data-slot="{{ $slot }}" title="Keluarkan Karakter">
                <i class="bi bi-x"></i>
              </button>

              <div class="slot-avatar-wrap" style="--element-glow: {{ $elColor }};">
                @if($char->icon_url)
                  <img src="{{ $char->icon_url }}" alt="{{ $char->name }}" class="slot-avatar" referrerpolicy="no-referrer">
                @else
                  <span class="fs-3 fw-bold" style="color: {{ $elColor }};">{{ substr($char->name, 0, 1) }}</span>
                @endif
              </div>

              <div class="slot-info text-center mt-2">
                <span class="badge-mini-element mb-1 d-inline-block" data-element="{{ strtolower($char->element) }}">
                  {{ $char->element }}
                </span>
                <h5 class="slot-char-name mb-1" title="{{ $char->name }}">{{ $char->name }}</h5>

                @if($inv)
                  <div class="slot-acc-stats mb-1">
                    <span class="badge bg-dark border border-secondary text-warning" style="font-size: 0.68rem;">Lv. {{ $inv->level }}</span>
                    @if($inv->constellation > 0)
                      <span class="badge bg-dark border border-secondary text-info" style="font-size: 0.68rem;">C{{ $inv->constellation }}</span>
                    @endif
                  </div>
                @endif

                {{-- Individual Matchup Score --}}
                <div class="slot-matchup-score-badge tier-{{ strtolower($member['tier'] ?? 'b') }}">
                  <span class="score-num">{{ $member['score'] ?? 50 }}</span>
                  <span class="score-tier">Tier {{ $member['tier'] ?? 'B' }}</span>
                </div>

                {{-- Status Tags / Warnings --}}
                <div class="d-flex flex-wrap justify-content-center gap-1 mt-2">
                  @if(!empty($member['is_immune']))
                    <span class="badge bg-danger text-light" style="font-size: 0.65rem;">⚠️ KEBAL</span>
                  @endif
                  @foreach(array_slice($member['tags'] ?? [], 0, 2) as $tg)
                    @if($tg !== 'KEBAL!')
                      <span class="badge bg-dark border border-secondary text-light" style="font-size: 0.65rem;">{{ $tg }}</span>
                    @endif
                  @endforeach
                </div>

                <button type="button" class="btn-swap-slot btn btn-sm btn-outline-secondary w-100 mt-2" data-slot="{{ $slot }}" style="border-radius: 6px; font-size: 0.72rem;">
                  <i class="bi bi-arrow-left-right me-1"></i>Ganti Karakter
                </button>
              </div>
            </div>
          @else
            <div class="empty-slot-content text-center py-4" data-slot="{{ $slot }}">
              <div class="empty-plus-icon mb-2">
                <i class="bi bi-plus-lg"></i>
              </div>
              <p class="text-muted small mb-2">Slot Kosong</p>
              <button type="button" class="btn btn-genshin btn-genshin-sm btn-select-slot" data-slot="{{ $slot }}">
                + Pilih Karakter
              </button>
            </div>
          @endif
        </div>
      </div>
    @endfor
  </div>

  {{-- Real-time Combat Analysis & Team Synergy Panel --}}
  <div class="row g-3 mb-4 animate-fade-in-up">
    {{-- Left: Team Synergy Score Card --}}
    <div class="col-12 col-lg-4">
      <div class="synergy-score-card h-100 text-center p-4">
        <span class="text-gold small fw-bold text-uppercase d-block mb-1" style="letter-spacing: 0.1em;">
          Skor Sinergi Tim vs Target
        </span>

        @php
          $teamScore = $analysis['team_score'] ?? 0;
          $teamTier = $analysis['team_tier'] ?? 'C';
          $tierColor = match($teamTier) {
              'S' => '#22c55e',
              'A' => '#3b82f6',
              'B' => '#eab308',
              default => '#ef4444',
          };
          $tierLabel = match($teamTier) {
              'S' => 'S-Tier (Optimal Counter)',
              'A' => 'A-Tier (Sangat Efektif)',
              'B' => 'B-Tier (Layak Tempur)',
              default => 'C-Tier (Berisiko / Tidak Disarankan)',
          };
        @endphp

        <div class="synergy-dial my-3" style="--tier-color: {{ $tierColor }};">
          <div class="synergy-dial-val">{{ $teamScore }}</div>
          <div class="synergy-dial-max">/ 100</div>
        </div>

        <h5 class="fw-bold mb-2" style="color: {{ $tierColor }};">{{ $tierLabel }}</h5>
        <p class="text-secondary small mb-0" style="font-size: 0.8rem; line-height: 1.4;">
          Nilai ini mengukur efektivitas perontokan perisai musuh, ketersediaan mekanik wajib, dan sinergi reaksi tim.
        </p>

        {{-- Elemental Resonances List --}}
        @if(!empty($analysis['resonances']))
          <hr class="border-secondary opacity-25 my-3">
          <span class="text-gold small fw-bold d-block text-start mb-2"><i class="bi bi-stars me-1"></i>Resonansi Elemental Aktif:</span>
          <div class="d-flex flex-column gap-2 text-start">
            @foreach($analysis['resonances'] as $res)
              <div class="resonance-pill p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="fw-bold text-light" style="font-size: 0.82rem;">{{ $res['name'] }}</span>
                  <span class="badge-mini-element" data-element="{{ strtolower($res['element']) }}">{{ $res['element'] }}</span>
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">{{ $res['desc'] }}</small>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    {{-- Right: Tactical Advantages & Warnings Panel --}}
    <div class="col-12 col-lg-8">
      <div class="tactical-analysis-card h-100 p-4">
        <h5 class="font-display text-gold mb-3" style="font-size: 1.1rem;">
          <i class="bi bi-clipboard2-check-fill me-2"></i>Evaluasi Taktis & Keuntungan Tempur
        </h5>

        {{-- Keuntungan (Advantages) --}}
        <div class="mb-3">
          <h6 class="text-success small fw-bold mb-2"><i class="bi bi-check-circle-fill me-1"></i>Keunggulan Komposisi Tim:</h6>
          @if(!empty($analysis['advantages']))
            <ul class="advantage-list mb-0">
              @foreach($analysis['advantages'] as $adv)
                <li>{{ $adv }}</li>
              @endforeach
            </ul>
          @else
            <p class="text-muted small mb-0">Belum ada sinergi keunggulan khusus terdeteksi.</p>
          @endif
        </div>

        <hr class="border-secondary opacity-25 my-3">

        {{-- Peringatan Bahaya (Warnings) --}}
        <div>
          <h6 class="text-danger small fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>Peringatan & Potensi Risiko:</h6>
          @if(!empty($analysis['warnings']))
            <ul class="warning-list mb-0">
              @foreach($analysis['warnings'] as $warn)
                <li>{{ $warn }}</li>
              @endforeach
            </ul>
          @else
            <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 py-2 px-3 mb-0" style="font-size: 0.8rem; color: #86efac;">
              <i class="bi bi-shield-check me-1"></i>Tidak ada kelemahan fatal yang terdeteksi. Komposisi ini aman melawan {{ $selectedEnemy->name ?? 'musuh' }}!
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  {{-- Top Recommended Characters Grid (Top Counter Picks) --}}
  <div class="mb-4 animate-fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="font-display text-gold mb-1" style="font-size: 1.15rem;">
          <i class="bi bi-star-fill text-warning me-2"></i>Rekomendasi Karakter Teratas (Top Counter Picks)
        </h4>
        <p class="text-secondary small mb-0">
          Karakter dengan rating kecocokan tertinggi melawan {{ $selectedEnemy->name ?? 'musuh ini' }}
        </p>
      </div>
    </div>

    <div class="row g-2">
      @forelse($availableCharacters->take(8) as $item)
        @php
          $cand = $item['character'];
          $candInv = $item['inventory'];
          $isImmune = $item['is_immune'];
          $score = $item['score'];
          $tier = $item['tier'];
        @endphp
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="counter-pick-card p-2 rounded d-flex align-items-center gap-2">
            <div class="enemy-avatar-ring flex-shrink-0" style="width: 48px; height: 48px; --element-glow: {{ $cand->element_color }};">
              <img src="{{ $cand->icon_url }}" alt="{{ $cand->name }}" class="enemy-avatar" referrerpolicy="no-referrer">
            </div>

            <div class="flex-grow-1 min-w-0">
              <div class="d-flex align-items-center justify-content-between">
                <span class="fw-bold text-light text-truncate d-block" style="font-size: 0.82rem;" title="{{ $cand->name }}">
                  {{ $cand->name }}
                </span>
                <span class="badge {{ $score >= 70 ? 'bg-success' : ($score >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}" style="font-size: 0.65rem;">
                  {{ $score }}
                </span>
              </div>

              <div class="d-flex align-items-center gap-1 my-1">
                <span class="badge-mini-element" data-element="{{ strtolower($cand->element) }}">{{ $cand->element }}</span>
                @if($candInv)
                  <span class="badge bg-dark border border-secondary text-warning" style="font-size: 0.62rem;">Lv.{{ $candInv->level }}</span>
                @endif
              </div>

              @if(!empty($item['tags']))
                <small class="text-secondary text-truncate d-block" style="font-size: 0.68rem;">
                  {{ implode(', ', array_slice($item['tags'], 0, 2)) }}
                </small>
              @endif
            </div>

            <button type="button" class="btn btn-sm btn-outline-warning btn-quick-add p-1 flex-shrink-0" 
                    data-id="{{ $cand->id }}" 
                    title="Pasang ke Slot Kosong">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>
        </div>
      @empty
        <div class="col-12 text-center py-3 text-muted">Belum ada data karakter yang dapat dianalisis.</div>
      @endforelse
    </div>
  </div>

  {{-- Saved Parties Section --}}
  @if($savedParties->isNotEmpty())
    <div class="saved-parties-box p-3 rounded mb-4 animate-fade-in-up" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);">
      <h5 class="font-display text-gold mb-3" style="font-size: 1rem;">
        <i class="bi bi-bookmark-star-fill me-2"></i>Komposisi Party Tersimpan untuk {{ $selectedEnemy->name ?? 'Musuh Ini' }}
      </h5>
      <div class="row g-2">
        @foreach($savedParties as $sp)
          <div class="col-12 col-md-6 col-lg-4">
            <div class="p-2 rounded border border-secondary border-opacity-25 d-flex align-items-center justify-content-between" style="background: rgba(15,23,42,0.6);">
              <div>
                <span class="fw-bold text-light d-block" style="font-size: 0.85rem;">{{ $sp->name }}</span>
                <div class="d-flex gap-1 my-1">
                  @foreach($sp->characters as $spChar)
                    <img src="{{ $spChar->icon_url }}" alt="{{ $spChar->name }}" class="rounded-circle border" style="width: 26px; height: 26px; border-color: {{ $spChar->element_color }};" title="{{ $spChar->name }}">
                  @endforeach
                </div>
                <small class="text-muted" style="font-size: 0.7rem;">Skor Sinergi: <strong class="text-gold">{{ $sp->synergy_score }} (Tier {{ $sp->synergy_tier }})</strong></small>
              </div>

              <div class="d-flex gap-1">
                <a href="{{ route('party.index', ['enemy_id' => $sp->enemy_id, 'game_account_id' => $sp->game_account_id, 'characters' => $sp->character_ids]) }}" class="btn btn-sm btn-outline-warning p-1" title="Muat Party Ini">
                  <i class="bi bi-box-arrow-in-down"></i>
                </a>
                <form action="{{ route('party.delete-saved', $sp) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus party tersimpan ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>
{{-- ─── MODAL PILIH KARAKTER UNTUK SLOT ──────────────────────────────────────── --}}
<div class="modal fade" id="modalSelectCharacter" tabindex="-1" aria-labelledby="modalSelectCharacterLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content genshin-modal">
      <div class="modal-header border-secondary">
        <h5 class="modal-title font-display text-gold" id="modalSelectCharacterLabel">
          <i class="bi bi-person-plus-fill me-2"></i>Pilih Karakter untuk Slot <span id="targetSlotDisplay">1</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3">
        {{-- Search & Element Filters in Modal --}}
        <div class="row g-2 mb-3">
          <div class="col-12 col-md-6">
            <div class="input-group input-group-sm">
              <span class="input-group-text genshin-input-group-text"><i class="bi bi-search text-gold"></i></span>
              <input type="text" id="modalSearchChar" class="form-control genshin-input" placeholder="Cari nama karakter...">
            </div>
          </div>
          <div class="col-12 col-md-6">
            <select id="modalFilterElement" class="form-select form-select-sm genshin-select">
              <option value="all">Semua Elemen</option>
              @foreach(['Pyro', 'Hydro', 'Anemo', 'Electro', 'Dendro', 'Cryo', 'Geo'] as $el)
                <option value="{{ $el }}">{{ $el }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Character Grid in Modal --}}
        <div class="row g-2" id="modalCharGrid">
          @foreach($availableCharacters as $item)
            @php
              $c = $item['character'];
              $inv = $item['inventory'];
              $score = $item['score'];
              $isImmune = $item['is_immune'];
            @endphp
            <div class="col-6 col-sm-4 col-md-3 modal-char-item" 
                 data-id="{{ $c->id }}" 
                 data-name="{{ strtolower($c->name) }}" 
                 data-element="{{ $c->element }}">
              <div class="modal-char-card p-2 rounded text-center {{ $isImmune ? 'border-danger' : '' }}" 
                   data-id="{{ $c->id }}">
                <div class="enemy-avatar-ring mx-auto mb-1" style="width: 54px; height: 54px; --element-glow: {{ $c->element_color }};">
                  <img src="{{ $c->icon_url }}" alt="{{ $c->name }}" class="enemy-avatar" referrerpolicy="no-referrer">
                </div>
                <h6 class="modal-char-name text-truncate mb-0" title="{{ $c->name }}">{{ $c->name }}</h6>
                <div class="d-flex justify-content-center align-items-center gap-1 my-1">
                  <span class="badge-mini-element" data-element="{{ strtolower($c->element) }}" style="font-size: 0.6rem;">{{ $c->element }}</span>
                  @if($inv)
                    <span class="badge bg-dark border border-secondary text-warning" style="font-size: 0.6rem;">Lv.{{ $inv->level }}</span>
                  @endif
                </div>

                <div class="d-flex justify-content-center align-items-center gap-1">
                  <span class="badge {{ $score >= 70 ? 'bg-success' : ($score >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}" style="font-size: 0.65rem;">
                    Skor: {{ $score }}
                  </span>
                  @if($isImmune)
                    <span class="badge bg-danger" style="font-size: 0.6rem;">KEBAL!</span>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

{{-- ─── MODAL SIMPAN PARTY ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalSaveParty" tabindex="-1" aria-labelledby="modalSavePartyLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal">
      <div class="modal-header border-secondary">
        <h5 class="modal-title font-display text-gold" id="modalSavePartyLabel">
          <i class="bi bi-bookmark-plus-fill me-2"></i>Simpan Komposisi Party
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('party.save') }}" method="POST" id="formSaveParty">
        @csrf
        <input type="hidden" name="enemy_id" value="{{ $selectedEnemy->id ?? '' }}">
        <input type="hidden" name="game_account_id" value="{{ $selectedAccountId ?? '' }}">
        <div id="hiddenCharInputs">
          {{-- Diisi dinamis via JS sesuai slot aktif --}}
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small text-gold">Nama Party <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control genshin-input" required placeholder="Contoh: Anti-Ruin Guard Hypercarry" value="Tim Counter {{ $selectedEnemy->name ?? 'Target' }}">
          </div>
          <div class="mb-3">
            <label class="form-label small text-gold">Catatan Taktis (Opsional)</label>
            <textarea name="notes" class="form-control genshin-input" rows="2" placeholder="Catatan rotasi skill atau kombinasi..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">Simpan Party</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
/* ─── Styles Scoped Party Matchup & Analyzer ────────────────────────── */
.enemy-avatar-ring {
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.3) 100%);
  border: 2px solid var(--element-glow, #eab308);
  box-shadow: 0 0 10px var(--element-glow, #eab308) 44;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 2px;
}

.enemy-avatar {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.enemy-dossier-card {
  background: var(--bg-card, #161926);
  border: 1px solid rgba(234, 179, 8, 0.3);
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.dossier-mini-label {
  font-size: 0.72rem;
  font-weight: 600;
}

.party-slot-card {
  background: var(--bg-card, #161926);
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  padding: 1rem;
  min-height: 270px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: all 0.2s ease;
}

.party-slot-card.filled {
  border-style: solid;
  border-color: rgba(234, 179, 8, 0.4);
  background: linear-gradient(180deg, rgba(22, 25, 38, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
}

.slot-role-tag {
  position: absolute;
  top: 8px;
  left: 8px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--accent-gold, #eab308);
  letter-spacing: 0.05em;
  background: rgba(0,0,0,0.4);
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
}

.btn-remove-slot {
  position: absolute;
  top: 6px;
  right: 6px;
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.5);
  color: #fca5a5;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-remove-slot:hover {
  background: #ef4444;
  color: #fff;
}

.slot-avatar-wrap {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  margin: 1rem auto 0 auto;
  padding: 3px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.4) 100%);
  border: 2px solid var(--element-glow, #eab308);
  box-shadow: 0 0 12px var(--element-glow, #eab308) 44;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.slot-avatar {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.slot-char-name {
  font-family: var(--font-display, inherit);
  font-size: 0.92rem;
  color: var(--text-primary, #f3f4f6);
  font-weight: 700;
}

.slot-matchup-score-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  margin-top: 0.2rem;
}

.slot-matchup-score-badge.tier-s { background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; }
.slot-matchup-score-badge.tier-a { background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.5); color: #93c5fd; }
.slot-matchup-score-badge.tier-b { background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.5); color: #fde047; }
.slot-matchup-score-badge.tier-c { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; }

.empty-plus-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  color: var(--accent-gold, #eab308);
  font-size: 1.25rem;
}

.synergy-score-card, .tactical-analysis-card {
  background: var(--bg-card, #161926);
  border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
  border-radius: 12px;
}

.synergy-dial {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 104px;
  height: 104px;
  border-radius: 50%;
  border: 4px solid var(--tier-color, #eab308);
  background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, rgba(0, 0, 0, 0.3) 100%);
  box-shadow: 0 0 20px var(--tier-color, #eab308) 33;
}

.synergy-dial-val {
  font-family: var(--font-display, inherit);
  font-size: 2rem;
  font-weight: 800;
  line-height: 1;
  color: #fff;
}

.synergy-dial-max {
  font-size: 0.72rem;
  color: var(--text-muted, #94a3b8);
}

.advantage-list, .warning-list {
  padding-left: 1.2rem;
  font-size: 0.82rem;
  line-height: 1.6;
}

.advantage-list li { color: #86efac; }
.warning-list li { color: #fca5a5; }

.counter-pick-card {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  transition: all 0.2s ease;
}

.counter-pick-card:hover {
  border-color: rgba(234, 179, 8, 0.5);
  background: rgba(15, 23, 42, 0.9);
}

.modal-char-card {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  cursor: pointer;
  transition: all 0.15s ease;
}

.modal-char-card:hover {
  border-color: var(--accent-gold, #eab308);
  background: rgba(234, 179, 8, 0.08);
  transform: translateY(-2px);
}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  let partyCharacterIds = @json($partyCharacterIds ?? []);
  const enemyId = "{{ $selectedEnemy->id ?? '' }}";
  const accountId = "{{ $selectedAccountId ?? '' }}";
  let activeSlot = 0;

  // ─── Modal Buka Pemilih Karakter ──────────────────────────────────────────
  const modalSelectChar = new bootstrap.Modal(document.getElementById('modalSelectCharacter'));

  document.querySelectorAll('.btn-select-slot, .btn-swap-slot').forEach(btn => {
    btn.addEventListener('click', function () {
      activeSlot = parseInt(this.getAttribute('data-slot'));
      document.getElementById('targetSlotDisplay').textContent = (activeSlot + 1);
      modalSelectChar.show();
    });
  });

  // ─── Memilih Karakter dari Modal ──────────────────────────────────────────
  document.querySelectorAll('.modal-char-card').forEach(card => {
    card.addEventListener('click', function () {
      const charId = parseInt(this.getAttribute('data-id'));

      // Cegah duplikasi karakter yang sama dalam 1 party
      const existingIndex = partyCharacterIds.indexOf(charId);
      if (existingIndex !== -1 && existingIndex !== activeSlot) {
        Swal.fire({
          icon: 'info',
          title: 'Karakter Sudah Ada di Party',
          text: 'Karakter ini sudah berada di slot ' + (existingIndex + 1) + '. Karakter akan dipindahkan ke slot ' + (activeSlot + 1) + '.',
          timer: 1500,
          showConfirmButton: false
        });
        partyCharacterIds.splice(existingIndex, 1);
      }

      partyCharacterIds[activeSlot] = charId;
      modalSelectChar.hide();
      reloadWithParty();
    });
  });

  // ─── Quick Add dari Rekomendasi Teratas ────────────────────────────────────
  document.querySelectorAll('.btn-quick-add').forEach(btn => {
    btn.addEventListener('click', function () {
      const charId = parseInt(this.getAttribute('data-id'));

      if (partyCharacterIds.includes(charId)) {
        Swal.fire({ icon: 'info', text: 'Karakter ini sudah berada di dalam party!' });
        return;
      }

      // Cari slot kosong pertama (0 - 3)
      let targetSlot = -1;
      for (let s = 0; s < 4; s++) {
        if (!partyCharacterIds[s]) {
          targetSlot = s;
          break;
        }
      }

      if (targetSlot === -1) {
        targetSlot = 3; // ganti slot terakhir jika penuh
      }

      partyCharacterIds[targetSlot] = charId;
      reloadWithParty();
    });
  });

  // ─── Keluarkan Karakter dari Slot ─────────────────────────────────────────
  document.querySelectorAll('.btn-remove-slot').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      const slot = parseInt(this.getAttribute('data-slot'));
      partyCharacterIds.splice(slot, 1);
      reloadWithParty();
    });
  });

  // ─── Reset Party ──────────────────────────────────────────────────────────
  const btnClear = document.getElementById('btnClearParty');
  if (btnClear) {
    btnClear.addEventListener('click', function () {
      partyCharacterIds = [];
      reloadWithParty();
    });
  }

  // ─── Auto-Generate Rekomendasi Satu Klik ───────────────────────────────────
  const btnAuto = document.getElementById('btnAutoGenerateTop');
  if (btnAuto) {
    btnAuto.addEventListener('click', function () {
      btnAuto.disabled = true;
      btnAuto.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menganalisis...';

      fetch('{{ route("party.auto-generate") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          enemy_id: enemyId,
          game_account_id: accountId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && data.character_ids) {
          partyCharacterIds = data.character_ids;
          reloadWithParty();
        } else {
          Swal.fire({ icon: 'error', text: 'Gagal menghasilkan rekomendasi otomatis.' });
          btnAuto.disabled = false;
          btnAuto.innerHTML = '<i class="bi bi-lightning-charge-fill text-warning me-1"></i>Auto-Generate Tim Terbaik';
        }
      })
      .catch(() => {
        btnAuto.disabled = false;
        btnAuto.innerHTML = '<i class="bi bi-lightning-charge-fill text-warning me-1"></i>Auto-Generate Tim Terbaik';
      });
    });
  }

  // ─── Filter Pencarian Karakter di Modal ────────────────────────────────────
  const searchInput = document.getElementById('modalSearchChar');
  const elementSelect = document.getElementById('modalFilterElement');

  function filterModalCards() {
    const q = (searchInput.value || '').toLowerCase();
    const el = elementSelect.value;

    document.querySelectorAll('.modal-char-item').forEach(item => {
      const name = item.getAttribute('data-name');
      const itemEl = item.getAttribute('data-element');

      const matchesSearch = !q || name.includes(q);
      const matchesElement = el === 'all' || itemEl === el;

      item.style.display = (matchesSearch && matchesElement) ? 'block' : 'none';
    });
  }

  if (searchInput) searchInput.addEventListener('input', filterModalCards);
  if (elementSelect) elementSelect.addEventListener('change', filterModalCards);

  // ─── Menyiapkan Form Simpan Party ──────────────────────────────────────────
  const formSaveParty = document.getElementById('formSaveParty');
  if (formSaveParty) {
    formSaveParty.addEventListener('submit', function (e) {
      const hiddenContainer = document.getElementById('hiddenCharInputs');
      hiddenContainer.innerHTML = '';

      const validIds = partyCharacterIds.filter(Boolean);
      if (validIds.length === 0) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', text: 'Pilih minimal 1 karakter dalam party sebelum menyimpan!' });
        return;
      }

      validIds.forEach(id => {
        hiddenContainer.innerHTML += `<input type="hidden" name="character_ids[]" value="${id}">`;
      });
    });
  }

  // ─── Helper Reload dengan Parameter Party ─────────────────────────────────
  function reloadWithParty() {
    const params = new URLSearchParams();
    if (enemyId) params.set('enemy_id', enemyId);
    if (accountId) params.set('game_account_id', accountId);

    partyCharacterIds.filter(Boolean).forEach(id => {
      params.append('characters[]', id);
    });

    window.location.href = `{{ route('party.index') }}?${params.toString()}`;
  }
});
</script>
@endpush