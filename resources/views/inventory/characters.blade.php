@extends('layout.main')

@section('content')
@php $title = 'Inventori Karakter'; @endphp
@include('layout.header')

<style>
  .bi-feather {
    display: inline-block;
    width: 1em;
    height: 1em;
    vertical-align: -0.125em;
    background-color: currentColor;
    -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z'/%3E%3Cline x1='16' y1='8' x2='2' y2='22'/%3E%3Cline x1='17.5' y1='15' x2='9' y2='15'/%3E%3C/svg%3E") no-repeat center / contain;
    mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z'/%3E%3Cline x1='16' y1='8' x2='2' y2='22'/%3E%3Cline x1='17.5' y1='15' x2='9' y2='15'/%3E%3C/svg%3E") no-repeat center / contain;
  }
  .inv-gear-box {
    background: rgba(10, 14, 24, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 0.5rem;
    margin-top: 0.5rem;
  }
  .weapon-icon-sm {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
  }
  .weapon-star-5 {
    border: 1px solid #eab308;
    background: rgba(234, 179, 8, 0.15);
  }
  .weapon-star-4 {
    border: 1px solid #a855f7;
    background: rgba(168, 85, 247, 0.15);
  }
  .artifact-slot-pill {
    flex: 1;
    text-align: center;
    padding: 2px 2px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(228, 196, 133, 0.2);
    font-size: 0.65rem;
    cursor: default;
    transition: all 0.2s;
  }
  .artifact-slot-pill:hover {
    background: rgba(228, 196, 133, 0.2);
    border-color: rgba(228, 196, 133, 0.5);
  }
  .artifact-slot-empty {
    flex: 1;
    text-align: center;
    padding: 2px 2px;
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.2);
    border: 1px dashed rgba(255, 255, 255, 0.15);
    color: #64748b;
    font-size: 0.65rem;
    opacity: 0.4;
  }
  .btn-action.view-gear {
    background: rgba(56, 189, 248, 0.15);
    border-color: rgba(56, 189, 248, 0.4);
    color: #7dd3fc;
  }
  .btn-action.view-gear:hover {
    background: rgba(56, 189, 248, 0.3);
    border-color: #38bdf8;
    color: #fff;
  }
</style>

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

  {{-- Page Header & Account Selector --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.35rem; letter-spacing: 0.08em;">
        <i class="bi bi-person-badge-fill me-2"></i>Inventori Karakter
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.84rem; margin-bottom: 0;">
        Pantau level, konstelasi, dan talent seluruh karakter pada akun game kamu
      </p>
    </div>

    {{-- Account Switcher --}}
    <div class="d-flex align-items-center gap-2">
      @if($accounts->isNotEmpty())
        <div class="account-selector-box">
          <form method="GET" action="{{ route('inventory.characters.index') }}" id="accountSelectForm">
            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
            <input type="hidden" name="element" value="{{ $filters['element'] ?? '' }}">
            <input type="hidden" name="rarity" value="{{ $filters['rarity'] ?? '' }}">
            <div class="input-group input-group-sm">
              <span class="input-group-text genshin-input-group-text"><i class="bi bi-controller text-gold"></i></span>
              <select name="account_id" class="form-select genshin-select" onchange="this.form.submit()" style="min-width: 180px;">
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

      @if($activeAccount)
        <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5); color: #f3f4f6;" data-bs-toggle="modal" data-bs-target="#modalSyncEnkaChar">
          <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Enka (UID)
        </button>
        <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(147, 51, 234, 0.2)); border-color: rgba(96, 165, 250, 0.5); color: #f3f4f6;" data-bs-toggle="modal" data-bs-target="#modalSyncHoyoLab">
          <i class="bi bi-arrow-repeat me-1"></i>Sync HoYoLAB
        </button>
        <a href="{{ route('inventory.compare', ['account1_id' => $activeAccount->id]) }}" class="btn-genshin btn-genshin-sm" style="background: rgba(245, 158, 11, 0.2); border-color: rgba(245, 158, 11, 0.5); color: #fef08a;">
          <i class="bi bi-arrow-left-right me-1"></i>Bandingkan 2 Akun
        </a>
        <a href="{{ route('inventory.good.index', ['account_id' => $activeAccount->id]) }}" class="btn-genshin btn-genshin-sm" style="background: rgba(228, 196, 133, 0.15); border-color: rgba(228, 196, 133, 0.5); color: var(--genshin-gold);">
          <i class="bi bi-arrow-left-right me-1"></i>GOOD (Export/Import)
        </a>
        <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal" data-bs-target="#modalAddInventoryChar">
          <i class="bi bi-plus-lg me-1"></i>Tambah Manual
        </button>
      @endif
    </div>
  </div>

  @if($accounts->isEmpty())
    {{-- Empty State: Belum ada akun game --}}
    <div class="empty-state animate-fade-in-up">
      <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">🎮</div>
      <h4 class="font-display text-gold">Belum Ada Akun Game</h4>
      <p style="color: var(--text-secondary); font-size: 0.88rem; max-width: 450px; margin: 0 auto 1.5rem;">
        Tambahkan akun game Genshin Impact terlebih dahulu untuk mulai mendata inventori karaktermu.
      </p>
      <a href="{{ route('game-accounts.index') }}" class="btn-genshin btn-genshin-sm">
        <i class="bi bi-plus-lg me-1"></i>Kelola Akun Game
      </a>
    </div>
  @else

    {{-- Stats Bar --}}
    <div class="row g-2 mb-4 animate-fade-in-up">
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon"><i class="bi bi-people-fill text-gold"></i></div>
          <div class="stat-content">
            <span class="stat-value">{{ $stats['total'] }}</span>
            <span class="stat-label">Total Dimiliki</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon text-info"><i class="bi bi-award-fill"></i></div>
          <div class="stat-content">
            <span class="stat-value text-info">{{ $stats['max_level'] }}</span>
            <span class="stat-label">Level 90 (Max)</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon text-warning"><i class="bi bi-stars"></i></div>
          <div class="stat-content">
            <span class="stat-value text-warning">{{ $stats['c6'] }}</span>
            <span class="stat-label">Konstelasi C6</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon text-danger"><i class="bi bi-shield-fill-check"></i></div>
          <div class="stat-content">
            <span class="stat-value text-danger">{{ $stats['crowns'] }}</span>
            <span class="stat-label">Pernah Di-Crown (T10)</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Filter & Search Panel --}}
    <div class="filter-panel mb-4 animate-fade-in-up">
      <form method="GET" action="{{ route('inventory.characters.index') }}" id="invFilterForm">
        <input type="hidden" name="account_id" value="{{ $activeAccount?->id }}">
        <div class="row g-2 align-items-center">
          {{-- Search --}}
          <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
              <span class="input-group-text genshin-input-group-text"><i class="bi bi-search text-gold"></i></span>
              <input type="text" name="search" class="form-control genshin-input" 
                     placeholder="Cari karakter dimiliki..." 
                     value="{{ $filters['search'] ?? '' }}"
                     onchange="this.form.submit()">
            </div>
          </div>

          {{-- Elemen --}}
          <div class="col-6 col-sm-4 col-md-2">
            <select name="element" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
              <option value="all">Semua Elemen</option>
              @foreach(['Pyro', 'Hydro', 'Anemo', 'Electro', 'Dendro', 'Cryo', 'Geo'] as $el)
                <option value="{{ $el }}" {{ ($filters['element'] ?? '') === $el ? 'selected' : '' }}>
                  {{ $el }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Rarity --}}
          <div class="col-6 col-sm-4 col-md-2">
            <select name="rarity" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
              <option value="all">Semua Rarity</option>
              <option value="5" {{ ($filters['rarity'] ?? '') === '5' ? 'selected' : '' }}>★★★★★ (Bintang 5)</option>
              <option value="4" {{ ($filters['rarity'] ?? '') === '4' ? 'selected' : '' }}>★★★★ (Bintang 4)</option>
            </select>
          </div>

          {{-- Sorting --}}
          <div class="col-6 col-sm-4 col-md-2">
            <select name="sort" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
              <option value="level_desc" {{ ($filters['sort'] ?? '') === 'level_desc' ? 'selected' : '' }}>Level Tertinggi</option>
              <option value="const_desc" {{ ($filters['sort'] ?? '') === 'const_desc' ? 'selected' : '' }}>Konstelasi Tertinggi</option>
              <option value="name_asc" {{ ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
              <option value="level_asc" {{ ($filters['sort'] ?? '') === 'level_asc' ? 'selected' : '' }}>Level Terendah</option>
            </select>
          </div>

          {{-- Reset --}}
          <div class="col-6 col-md-2">
            @if(!empty(array_filter($filters ?? [])))
              <a href="{{ route('inventory.characters.index', ['account_id' => $activeAccount?->id]) }}" 
                 class="btn btn-sm btn-outline-secondary w-100" style="border-radius: 8px; font-size: 0.8rem;">
                <i class="bi bi-x-circle me-1"></i>Reset
              </a>
            @endif
          </div>
        </div>
      </form>
    </div>

    {{-- Inventory Grid --}}
    @if($inventoryCharacters->isEmpty())
      <div class="empty-state animate-fade-in-up">
        <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;">✦</div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
          @if(!empty($filters['search']) || !empty($filters['element']) || !empty($filters['rarity']))
            Tidak ada karakter yang cocok dengan kriteria filter.
          @else
            Akun <strong>{{ $activeAccount?->nickname }}</strong> belum memiliki data karakter inventori.
          @endif
        </p>
        <div class="d-flex justify-content-center gap-2 mt-3">
          <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(147, 51, 234, 0.25)); border-color: rgba(96, 165, 250, 0.6); color: #f3f4f6;" data-bs-toggle="modal" data-bs-target="#modalSyncHoyoLab">
            <i class="bi bi-arrow-repeat me-1"></i>Sync Otomatis via HoYoLAB
          </button>
          <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal" data-bs-target="#modalAddInventoryChar">
            <i class="bi bi-plus-lg me-1"></i>Tambah Manual
          </button>
        </div>
      </div>
    @else
      <div class="row g-3">
        @foreach($inventoryCharacters as $inv)
          @php
            $char = $inv->character;
            $elementColor = $char?->element_color ?? '#94a3b8';
            $isFiveStar = ($char?->rarity ?? 4) === 5;
          @endphp
          <div class="col-12 col-sm-6 col-md-4 col-lg-3 animate-fade-in-up" style="animation-delay: {{ ($loop->index % 12) * 0.04 }}s;">
            <div class="inv-card {{ $isFiveStar ? 'card-star-5' : 'card-star-4' }}">
              
              {{-- Header Card: Element & Constellation --}}
              <div class="inv-card-header">
                <span class="inv-element-tag" style="background-color: {{ $elementColor }};">
                  {{ $char?->element }}
                </span>
                <span class="inv-const-tag {{ $inv->constellation === 6 ? 'c6-glow' : '' }}">
                  C{{ $inv->constellation }}
                </span>
              </div>

              {{-- Avatar & Level Overlay --}}
              <div class="inv-avatar-box">
                @if($char?->icon_url)
                  <img src="{{ $char->icon_url }}" 
                       alt="{{ $char->name }}" 
                       class="inv-avatar"
                       loading="lazy"
                       referrerpolicy="no-referrer"
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class="inv-avatar-placeholder" style="display: none; background: {{ $elementColor }}20;">
                    <span style="color: {{ $elementColor }}; font-weight: bold; font-size: 2rem;">
                      {{ substr($char->name ?? '?', 0, 1) }}
                    </span>
                  </div>
                @else
                  <div class="inv-avatar-placeholder" style="background: {{ $elementColor }}20;">
                    <span style="color: {{ $elementColor }}; font-weight: bold; font-size: 2rem;">
                      {{ substr($char->name ?? '?', 0, 1) }}
                    </span>
                  </div>
                @endif

                {{-- Level Badge Overlay --}}
                <div class="inv-level-badge">
                  Lv. {{ $inv->level }}<span class="cap">/{{ $inv->max_level }}</span>
                </div>
              </div>

              {{-- Character Name & Stars --}}
              <div class="inv-body">
                <h3 class="inv-name" title="{{ $char?->name }}">
                  {{ $char?->name }}
                </h3>
                <div class="inv-stars mb-2">
                  @for($i = 0; $i < ($char?->rarity ?? 4); $i++)
                    <i class="bi bi-star-fill text-gold"></i>
                  @endfor
                </div>

                {{-- Talent Bar --}}
                <div class="talent-display mb-2">
                  <div class="talent-item {{ $inv->talent_attack >= 10 ? 'crowned' : '' }}" title="Normal Attack: Lv. {{ $inv->talent_attack }}">
                    <span class="talent-type">NA</span>
                    <span class="talent-lvl">{{ $inv->talent_attack }}</span>
                  </div>
                  <div class="talent-item {{ $inv->talent_skill >= 10 ? 'crowned' : '' }}" title="Elemental Skill: Lv. {{ $inv->talent_skill }}">
                    <span class="talent-type">ES</span>
                    <span class="talent-lvl">{{ $inv->talent_skill }}</span>
                  </div>
                  <div class="talent-item {{ $inv->talent_burst >= 10 ? 'crowned' : '' }}" title="Elemental Burst: Lv. {{ $inv->talent_burst }}">
                    <span class="talent-type">EB</span>
                    <span class="talent-lvl">{{ $inv->talent_burst }}</span>
                  </div>
                </div>

                @if($inv->notes)
                  <p class="inv-notes" title="{{ $inv->notes }}">{{ $inv->notes }}</p>
                @endif

                {{-- Gear & Build Overview (Senjata & Artefak) --}}
                <div class="inv-gear-box">
                  {{-- Senjata Terpasang --}}
                  <div class="d-flex align-items-center justify-content-between gap-2 pb-2 mb-2 border-bottom border-secondary border-opacity-25">
                    @if($inv->equipped_weapon && $inv->equipped_weapon->weapon)
                      @php
                        $eqW = $inv->equipped_weapon;
                        $wModel = $eqW->weapon;
                        $isW5 = ($wModel->rarity ?? 4) === 5;
                      @endphp
                      <div class="d-flex align-items-center gap-2 overflow-hidden w-100">
                        @if($wModel->icon_url)
                          <img src="{{ $wModel->icon_url }}" alt="{{ $wModel->name }}" class="weapon-icon-sm {{ $isW5 ? 'weapon-star-5' : 'weapon-star-4' }}">
                        @else
                          <div class="weapon-icon-sm d-flex align-items-center justify-content-center {{ $isW5 ? 'weapon-star-5 text-warning' : 'weapon-star-4 text-purple' }}">
                            <i class="bi bi-shield-shaded"></i>
                          </div>
                        @endif
                        <div class="text-truncate flex-grow-1" style="line-height: 1.2;">
                          <div class="small fw-bold text-truncate {{ $isW5 ? 'text-gold' : 'text-purple' }}" title="{{ $wModel->name }}" style="font-size: 0.76rem;">
                            {{ $wModel->name }}
                          </div>
                          <div class="text-white-50" style="font-size: 0.68rem;">
                            Lv. {{ $eqW->level }} <span class="text-warning">R{{ $eqW->refinement }}</span>
                            @if($wModel->sub_stat_type)
                              <span class="text-info ms-1">• {{ $wModel->sub_stat_type }}</span>
                            @endif
                          </div>
                        </div>
                      </div>
                    @else
                      <div class="small text-muted py-1 d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                        <i class="bi bi-shield-x text-secondary"></i>Belum ada senjata
                      </div>
                    @endif
                  </div>

                  {{-- Artefak Terpasang & CV --}}
                  <div>
                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.72rem;">
                      <span class="text-white-50">
                        <i class="bi bi-gem text-gold me-1"></i>Artefak ({{ $inv->equipped_artifacts->count() }}/5)
                      </span>
                      @if($inv->total_artifact_cv > 0)
                        <span class="badge {{ $inv->total_artifact_cv >= 140 ? 'bg-warning text-dark' : 'bg-dark text-info border border-info border-opacity-50' }}" style="font-size: 0.65rem;" title="Total Crit Value dari Artefak">
                          {{ $inv->total_artifact_cv }} CV
                        </span>
                      @endif
                    </div>

                    {{-- Set Bonus Badge --}}
                    @if(!empty($inv->active_set_bonuses))
                      <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach($inv->active_set_bonuses as $setB)
                          <span class="badge" style="background: rgba(228, 196, 133, 0.15); color: var(--genshin-gold); border: 1px solid rgba(228, 196, 133, 0.35); font-size: 0.65rem; font-weight: 500;">
                            {{ $setB }}
                          </span>
                        @endforeach
                      </div>
                    @elseif($inv->equipped_artifacts->isNotEmpty())
                      <div class="mb-2">
                        <span class="badge bg-secondary bg-opacity-25 text-white-50 border border-secondary border-opacity-25" style="font-size: 0.65rem;">
                          Campuran (Rainbow Set)
                        </span>
                      </div>
                    @else
                      <div class="mb-2 text-muted" style="font-size: 0.7rem;">
                        Belum ada artefak terpasang
                      </div>
                    @endif

                    {{-- Mini 5 Slot Indicator --}}
                    @if($inv->equipped_artifacts->isNotEmpty())
                      <div class="d-flex gap-1 justify-content-between pt-1 border-top border-secondary border-opacity-10">
                        @php
                          $slotIcons = [
                            'flower'  => ['icon' => 'bi-flower1', 'label' => 'Flower'],
                            'plume'   => ['icon' => 'bi-feather', 'label' => 'Plume'],
                            'sands'   => ['icon' => 'bi-hourglass-split', 'label' => 'Sands'],
                            'goblet'  => ['icon' => 'bi-cup-straw', 'label' => 'Goblet'],
                            'circlet' => ['icon' => 'bi-gem', 'label' => 'Circlet'],
                          ];
                          $artBySlot = $inv->equipped_artifacts->keyBy('slot_key');
                        @endphp
                        @foreach($slotIcons as $sKey => $sInfo)
                          @php $artSlot = $artBySlot->get($sKey); @endphp
                          @if($artSlot)
                            <div class="artifact-slot-pill" title="{{ ucfirst($sKey) }}: {{ $artSlot->artifactSet?->name }} (+{{ $artSlot->level }}) | Main: {{ $artSlot->main_stat_key }} ({{ $artSlot->main_stat_value }})">
                              @if($sKey === 'plume')
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-gold" style="display:inline-block; vertical-align:-1px;">
                                  <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                                  <line x1="16" y1="8" x2="2" y2="22"></line>
                                  <line x1="17.5" y1="15" x2="9" y2="15"></line>
                                </svg>
                              @else
                                <i class="bi {{ $sInfo['icon'] }} text-gold" style="font-size: 0.72rem;"></i>
                              @endif
                              <div style="font-size: 0.58rem; color: #cbd5e1; line-height: 1;">+{{ $artSlot->level }}</div>
                            </div>
                          @else
                            <div class="artifact-slot-empty" title="{{ ucfirst($sKey) }}: Kosong">
                              @if($sKey === 'plume')
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:-1px; opacity:0.6;">
                                  <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                                  <line x1="16" y1="8" x2="2" y2="22"></line>
                                  <line x1="17.5" y1="15" x2="9" y2="15"></line>
                                </svg>
                              @else
                                <i class="bi {{ $sInfo['icon'] }}" style="font-size: 0.72rem;"></i>
                              @endif
                              <div style="font-size: 0.58rem; line-height: 1;">-</div>
                            </div>
                          @endif
                        @endforeach
                      </div>
                    @endif
                  </div>
                </div>

              </div>

              {{-- Footer Action Buttons --}}
              <div class="inv-footer">
                @php
                  $gearData = [
                    'char_name'      => $char?->name,
                    'element'        => $char?->element,
                    'element_color'  => $elementColor,
                    'icon_url'       => $char?->icon_url,
                    'level'          => $inv->level,
                    'max_level'      => $inv->max_level,
                    'constellation'  => $inv->constellation,
                    'talents'        => [
                      'na' => $inv->talent_attack,
                      'es' => $inv->talent_skill,
                      'eb' => $inv->talent_burst,
                    ],
                    'notes'          => $inv->notes,
                    'weapon'         => ($inv->equipped_weapon && $inv->equipped_weapon->weapon) ? [
                      'name'           => $inv->equipped_weapon->weapon->name,
                      'type'           => $inv->equipped_weapon->weapon->type,
                      'rarity'         => $inv->equipped_weapon->weapon->rarity ?? 4,
                      'level'          => $inv->equipped_weapon->level,
                      'refinement'     => $inv->equipped_weapon->refinement,
                      'base_atk'       => $inv->equipped_weapon->weapon->base_atk,
                      'sub_stat_type'  => $inv->equipped_weapon->weapon->sub_stat_type,
                      'sub_stat_value' => $inv->equipped_weapon->weapon->sub_stat_value,
                      'passive_name'   => $inv->equipped_weapon->weapon->passive_name,
                      'passive_desc'   => $inv->equipped_weapon->weapon->passive_desc,
                      'icon_url'       => $inv->equipped_weapon->weapon->icon_url,
                    ] : null,
                    'total_cv'       => $inv->total_artifact_cv,
                    'active_sets'    => $inv->active_set_bonuses,
                    'artifacts'      => $inv->equipped_artifacts->map(function($a) {
                      $cv = 0;
                      foreach ($a->sub_stats ?? [] as $sub) {
                        $k = strtolower($sub['key'] ?? '');
                        if (in_array($k, ['crit_rate', 'critrate', 'critrate_'])) $cv += ((float)$sub['value'] * 2);
                        if (in_array($k, ['crit_dmg', 'critdmg', 'critdmg_'])) $cv += (float)$sub['value'];
                      }
                      return [
                        'slot'            => $a->slot_key,
                        'set_name'        => $a->artifactSet?->name ?? 'Unknown Set',
                        'rarity'          => $a->rarity,
                        'level'           => $a->level,
                        'main_stat'       => $a->main_stat_key,
                        'main_stat_value' => $a->main_stat_value,
                        'sub_stats'       => $a->sub_stats ?? [],
                        'cv'              => round($cv, 1),
                        'score'           => $a->score,
                        'score_rating'    => $a->score_rating,
                      ];
                    })->values(),
                  ];
                @endphp
                <button type="button" 
                        class="btn-action view-gear btn-view-gear" 
                        title="Lihat Detail Build & Artefak"
                        data-gear="{{ json_encode($gearData) }}">
                  <i class="bi bi-shield-check me-1"></i>Gear
                </button>
                <a href="{{ route('inventory.compare', ['account1_id' => $activeAccount->id, 'character_id' => $inv->character_id]) }}"
                   class="btn-action view-gear" 
                   style="background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.4); color: #fef08a; text-decoration: none; display: inline-flex; align-items: center;"
                   title="Bandingkan karakter ini dengan akun lain">
                  <i class="bi bi-arrow-left-right"></i>
                </a>
                <button class="btn-action edit"
                        title="Edit Build Karakter"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditInventoryChar"
                        data-id="{{ $inv->id }}"
                        data-name="{{ $char?->name }}"
                        data-level="{{ $inv->level }}"
                        data-ascension="{{ $inv->ascension }}"
                        data-const="{{ $inv->constellation }}"
                        data-na="{{ $inv->talent_attack }}"
                        data-es="{{ $inv->talent_skill }}"
                        data-eb="{{ $inv->talent_burst }}"
                        data-notes="{{ $inv->notes }}">
                  <i class="bi bi-sliders me-1"></i>Edit Build
                </button>
                <form action="{{ route('inventory.characters.destroy', $inv) }}" method="POST" class="d-inline form-delete">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn-action delete btn-delete-inv" data-name="{{ $char?->name }}" title="Hapus dari inventori">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </form>
              </div>

            </div>
          </div>
        @endforeach
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-center mt-4">
        {{ $inventoryCharacters->links('pagination::bootstrap-5') }}
      </div>
    @endif

  @endif

</div>

{{-- MODAL TAMBAH KARAKTER KE INVENTORI --}}
@if($activeAccount)
<div class="modal fade" id="modalAddInventoryChar" tabindex="-1" aria-labelledby="modalAddInvLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalAddInvLabel">
          <i class="bi bi-person-plus-fill me-2"></i>Tambah ke Inventori: {{ $activeAccount->nickname }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('inventory.characters.store') }}" method="POST">
        @csrf
        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">
        <div class="modal-body">
          {{-- Pilih Karakter --}}
          <div class="mb-3">
            <label class="form-label genshin-label">Pilih Karakter <span class="text-danger">*</span></label>
            <select name="character_id" class="form-select genshin-select" required>
              <option value="">-- Pilih Karakter Master --</option>
              @foreach($availableCharacters as $c)
                <option value="{{ $c->id }}">
                  {{ $c->name }} ({{ $c->element }} • ★{{ $c->rarity }})
                </option>
              @endforeach
            </select>
          </div>

          {{-- Level & Ascension --}}
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Level (1 - 90) <span class="text-danger">*</span></label>
              <input type="number" name="level" class="form-control genshin-input" min="1" max="90" value="90" required>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Ascension Phase <span class="text-danger">*</span></label>
              <select name="ascension" class="form-select genshin-select" required>
                <option value="6" selected>Phase 6 (Max Lv. 90)</option>
                <option value="5">Phase 5 (Max Lv. 80)</option>
                <option value="4">Phase 4 (Max Lv. 70)</option>
                <option value="3">Phase 3 (Max Lv. 60)</option>
                <option value="2">Phase 2 (Max Lv. 50)</option>
                <option value="1">Phase 1 (Max Lv. 40)</option>
                <option value="0">Phase 0 (Max Lv. 20)</option>
              </select>
            </div>
          </div>

          {{-- Konstelasi --}}
          <div class="mb-3">
            <label class="form-label genshin-label">Konstelasi (C0 - C6) <span class="text-danger">*</span></label>
            <select name="constellation" class="form-select genshin-select" required>
              <option value="0" selected>C0 (Belum ada konstelasi)</option>
              <option value="1">C1</option>
              <option value="2">C2</option>
              <option value="3">C3</option>
              <option value="4">C4</option>
              <option value="5">C5</option>
              <option value="6">C6 (Max Constellation)</option>
            </select>
          </div>

          {{-- Talent Level --}}
          <label class="form-label genshin-label mb-1">Level Talent (1 - 10)</label>
          <div class="row g-2 mb-3">
            <div class="col-4">
              <small class="text-muted d-block mb-1">Normal Atk</small>
              <input type="number" name="talent_attack" class="form-control genshin-input" min="1" max="10" value="8" required>
            </div>
            <div class="col-4">
              <small class="text-muted d-block mb-1">Skill (E)</small>
              <input type="number" name="talent_skill" class="form-control genshin-input" min="1" max="10" value="8" required>
            </div>
            <div class="col-4">
              <small class="text-muted d-block mb-1">Burst (Q)</small>
              <input type="number" name="talent_burst" class="form-control genshin-input" min="1" max="10" value="8" required>
            </div>
          </div>

          {{-- Catatan Build --}}
          <div class="mb-2">
            <label class="form-label genshin-label">Catatan Build / Role</label>
            <input type="text" name="notes" class="form-control genshin-input" placeholder="contoh: Main DPS Crimson Witch / Support Noblesse">
          </div>
        </div>
        <div class="modal-footer genshin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">
            <i class="bi bi-check-lg me-1"></i>Simpan ke Inventori
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDIT BUILD KARAKTER --}}
<div class="modal fade" id="modalEditInventoryChar" tabindex="-1" aria-labelledby="modalEditInvLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalEditInvLabel">
          <i class="bi bi-sliders me-2"></i>Edit Build: <span id="editCharTitle"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEditInventoryChar" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          {{-- Level & Ascension --}}
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Level (1 - 90) <span class="text-danger">*</span></label>
              <input type="number" id="editLevel" name="level" class="form-control genshin-input" min="1" max="90" required>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Ascension Phase <span class="text-danger">*</span></label>
              <select id="editAscension" name="ascension" class="form-select genshin-select" required>
                <option value="6">Phase 6 (Max Lv. 90)</option>
                <option value="5">Phase 5 (Max Lv. 80)</option>
                <option value="4">Phase 4 (Max Lv. 70)</option>
                <option value="3">Phase 3 (Max Lv. 60)</option>
                <option value="2">Phase 2 (Max Lv. 50)</option>
                <option value="1">Phase 1 (Max Lv. 40)</option>
                <option value="0">Phase 0 (Max Lv. 20)</option>
              </select>
            </div>
          </div>

          {{-- Konstelasi --}}
          <div class="mb-3">
            <label class="form-label genshin-label">Konstelasi (C0 - C6) <span class="text-danger">*</span></label>
            <select id="editConstellation" name="constellation" class="form-select genshin-select" required>
              <option value="0">C0</option>
              <option value="1">C1</option>
              <option value="2">C2</option>
              <option value="3">C3</option>
              <option value="4">C4</option>
              <option value="5">C5</option>
              <option value="6">C6</option>
            </select>
          </div>

          {{-- Talent Level --}}
          <label class="form-label genshin-label mb-1">Level Talent (1 - 15)</label>
          <div class="row g-2 mb-3">
            <div class="col-4">
              <small class="text-muted d-block mb-1">Normal Atk</small>
              <input type="number" id="editNA" name="talent_attack" class="form-control genshin-input" min="1" max="15" required>
            </div>
            <div class="col-4">
              <small class="text-muted d-block mb-1">Skill (E)</small>
              <input type="number" id="editES" name="talent_skill" class="form-control genshin-input" min="1" max="15" required>
            </div>
            <div class="col-4">
              <small class="text-muted d-block mb-1">Burst (Q)</small>
              <input type="number" id="editEB" name="talent_burst" class="form-control genshin-input" min="1" max="15" required>
            </div>
          </div>

          {{-- Catatan Build --}}
          <div class="mb-2">
            <label class="form-label genshin-label">Catatan Build / Role</label>
            <input type="text" id="editNotes" name="notes" class="form-control genshin-input">
          </div>
        </div>
        <div class="modal-footer genshin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">
            <i class="bi bi-save me-1"></i>Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL SYNC HOYOLAB --}}
<div class="modal fade" id="modalSyncHoyoLab" tabindex="-1" aria-labelledby="modalSyncLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalSyncLabel">
          <i class="bi bi-arrow-repeat me-2"></i>Sync Karakter via HoYoLAB
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('inventory.characters.sync-hoyolab') }}" method="POST" id="formSyncHoyoLab">
        @csrf
        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

        <div class="modal-body">
          {{-- Info Akun --}}
          <div class="p-3 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span style="font-size: 0.85rem; color: var(--text-primary); font-weight: 700;">
                <i class="bi bi-person-circle me-1 text-gold"></i>{{ $activeAccount->nickname }}
              </span>
              <span class="badge" style="background: rgba(229, 160, 41, 0.15); color: var(--accent-gold); border: 1px solid rgba(229, 160, 41, 0.3);">
                UID: {{ $activeAccount->uid }} ({{ strtoupper($activeAccount->server) }})
              </span>
            </div>
            <div style="font-size: 0.76rem; color: var(--text-secondary);">
              Microservice akan menarik seluruh karakter yang terdaftar pada Battle Chronicle akun ini.
            </div>
          </div>

          {{-- Indikator Cookie Tersimpan --}}
          @if(!empty($activeAccount->ltuid_v2) && !empty($activeAccount->ltoken_v2))
            <div class="alert alert-success d-flex align-items-center mb-3" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); color: #86efac; font-size: 0.78rem; border-radius: 8px;">
              <i class="bi bi-shield-check fs-5 me-2"></i>
              <div>
                <strong>Cookie tersimpan!</strong> Anda bisa langsung klik <em>"Mulai Sinkronisasi"</em> di bawah. Isi form jika ingin memperbarui cookie baru.
              </div>
            </div>
          @endif

          {{-- Input ltuid_v2 --}}
          <div class="mb-3">
            <label class="form-label genshin-label">Cookie ltuid_v2 <span class="text-danger">*</span></label>
            <input type="text" name="ltuid_v2" class="form-control genshin-input" 
                   value="{{ $activeAccount->ltuid_v2 ?? '' }}" 
                   placeholder="contoh: 123456789" 
                   {{ empty($activeAccount->ltuid_v2) ? 'required' : '' }}>
          </div>

          {{-- Input ltoken_v2 --}}
          <div class="mb-3">
            <label class="form-label genshin-label">Cookie ltoken_v2 <span class="text-danger">*</span></label>
            <input type="text" name="ltoken_v2" class="form-control genshin-input" 
                   value="{{ $activeAccount->ltoken_v2 ?? '' }}" 
                   placeholder="contoh: v2_xxxx..." 
                   {{ empty($activeAccount->ltoken_v2) ? 'required' : '' }}>
          </div>

          {{-- Checkbox Simpan Cookie --}}
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="save_credentials" value="1" id="saveCredentialsCheck" checked>
            <label class="form-check-label" for="saveCredentialsCheck" style="font-size: 0.78rem; color: var(--text-secondary);">
              Simpan cookie ini di akun game agar tidak perlu input ulang di masa depan
            </label>
          </div>

          {{-- Panduan Ambil Cookie --}}
          <div class="accordion" id="guideAccordion">
            <div class="accordion-item" style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); border-radius: 8px;">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#guideCollapse" style="background: transparent; color: var(--text-muted); font-size: 0.76rem; box-shadow: none;">
                  <i class="bi bi-question-circle me-1"></i>Cara mengambil cookie dari HoYoLAB
                </button>
              </h2>
              <div id="guideCollapse" class="accordion-collapse collapse" data-bs-parent="#guideAccordion">
                <div class="accordion-body p-3" style="font-size: 0.74rem; color: var(--text-secondary); line-height: 1.5;">
                  <ol class="ps-3 mb-1">
                    <li>Buka <a href="https://www.hoyolab.com" target="_blank" class="text-gold">hoyolab.com</a> di browser dan pastikan sudah login.</li>
                    <li>Tekan <kbd>F12</kbd> (Inspect Element) &gt; pilih tab <strong>Application</strong> (atau <strong>Storage</strong> di Firefox).</li>
                    <li>Di sidebar kiri, klik <strong>Cookies</strong> &gt; <code>https://www.hoyolab.com</code>.</li>
                    <li>Cari dan salin nilai kolom <em>Value</em> dari <code>ltuid_v2</code> dan <code>ltoken_v2</code>.</li>
                    <li>Pastikan pengaturan <strong>Battle Chronicle</strong> akun Anda di HoYoLAB disetel ke publik.</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer genshin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm" id="btnSubmitSync">
            <span class="btn-text"><i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai Sinkronisasi</span>
            <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Sedang Sync...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL SYNC ENKA (UID) --}}
<div class="modal fade" id="modalSyncEnkaChar" tabindex="-1" aria-labelledby="modalSyncEnkaCharLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalSyncEnkaCharLabel">
          <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Karakter via Enka.Network (UID)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('inventory.characters.sync-enka') }}" method="POST">
        @csrf
        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

        <div class="modal-body">
          <div class="p-3 mb-3 rounded" style="background: rgba(229,160,41,0.08); border: 1px solid rgba(229,160,41,0.25);">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <span class="badge" style="background: #22c55e; color: #fff; font-size: 0.72rem;">✨ Karakter, Senjata & Artefak</span>
              <span class="badge" style="background: #3b82f6; color: #fff; font-size: 0.72rem;">🔒 Tanpa Cookie / Password</span>
              <span class="badge" style="background: #eab308; color: #111; font-weight: 700; font-size: 0.72rem;">🔄 Auto-Replace Artefak Lama</span>
            </div>
            <p class="mb-1 text-white" style="font-size: 0.82rem; line-height: 1.5;">
              Tarik otomatis data karakter yang sedang Anda pajang di <strong>Character Showcase</strong> in-game profil Genshin Impact lengkap dengan level, talent, serta <strong>senjata dan artefak aktif</strong>.
            </p>
            <p class="mb-0 text-white-50" style="font-size: 0.76rem; line-height: 1.4;">
              <i class="bi bi-info-circle text-warning me-1"></i>Artefak lama pada karakter showcase akan otomatis digantikan/dihapus dengan artefak baru. Jika baru mengganti artefak di dalam game, tunggu 1–2 menit agar cache server Enka ter-update.
            </p>
          </div>

          {{-- Info Akun --}}
          <div class="p-2 mb-3 rounded d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
            <span style="font-size: 0.85rem; color: var(--text-primary); font-weight: 700;">
              <i class="bi bi-person-circle me-1 text-gold"></i>{{ $activeAccount->nickname }}
            </span>
            <span class="badge" style="background: rgba(229, 160, 41, 0.15); color: var(--accent-gold); border: 1px solid rgba(229, 160, 41, 0.3);">
              Server: {{ ucfirst($activeAccount->server ?? 'Asia') }}
            </span>
          </div>

          {{-- Input UID --}}
          <div class="mb-3">
            <label class="form-label genshin-label">UID Akun Genshin Impact <span class="text-danger">*</span></label>
            <input type="text" name="uid" class="form-control genshin-input" 
                   value="{{ $activeAccount->uid }}" placeholder="contoh: 809404073" required>
            <div class="form-text text-white-50 mt-1" style="font-size: 0.74rem;">
              <i class="bi bi-info-circle me-1 text-gold"></i>Pastikan opsi <em>"Tampilkan Detail Karakter"</em> / <em>"Show Character Details"</em> aktif di profil in-game.
            </div>
          </div>
        </div>
        <div class="modal-footer genshin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">
            <i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai Sinkronkan (UID)
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

{{-- MODAL DETAIL GEAR & BUILD KARAKTER --}}
<div class="modal fade" id="modalCharGearDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content genshin-modal-content" style="border: 1px solid rgba(228, 196, 133, 0.4); box-shadow: 0 10px 40px rgba(0,0,0,0.8);">
      <div class="modal-header genshin-modal-header" style="border-bottom: 1px solid rgba(228, 196, 133, 0.2);">
        <div class="d-flex align-items-center gap-3">
          <img id="mgCharAvatar" src="" alt="" class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover; border: 2px solid var(--genshin-gold);">
          <div>
            <div class="d-flex align-items-center gap-2">
              <h5 class="modal-title font-display text-gold mb-0" id="mgCharName">Nama Karakter</h5>
              <span class="badge" id="mgCharElement" style="font-size: 0.72rem;">Element</span>
              <span class="badge bg-warning text-dark fw-bold" id="mgCharConst" style="font-size: 0.72rem;">C0</span>
            </div>
            <div class="small text-white-50 mt-0.5">
              <span id="mgCharLevel">Lv. 90/90</span> • 
              <span id="mgCharTalents">NA 10 | ES 10 | EB 10</span>
            </div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        
        {{-- Section 1: Senjata Terpasang --}}
        <div class="mb-4">
          <h6 class="text-gold font-display mb-2 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
            <i class="bi bi-shield-shaded text-gold"></i>Senjata yang Digunakan
          </h6>
          <div id="mgWeaponBox" class="p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
            <!-- Dinamis via JS -->
          </div>
        </div>

        {{-- Section 2: Artefak Terpasang & Analisis --}}
        <div>
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <h6 class="text-gold font-display mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
              <i class="bi bi-gem text-gold"></i>Rincian 5 Slot Artefak
            </h6>
            <div class="d-flex align-items-center gap-2" id="mgArtifactSummaryBadges">
              <!-- Dinamis via JS -->
            </div>
          </div>

          <div id="mgArtifactsGrid" class="row g-2">
            <!-- Dinamis via JS -->
          </div>
        </div>

      </div>

      <div class="modal-footer genshin-modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Modal Edit Fill
  const modalEdit = document.getElementById('modalEditInventoryChar');
  if (modalEdit) {
    modalEdit.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const level = button.getAttribute('data-level');
      const ascension = button.getAttribute('data-ascension');
      const constellation = button.getAttribute('data-const');
      const na = button.getAttribute('data-na');
      const es = button.getAttribute('data-es');
      const eb = button.getAttribute('data-eb');
      const notes = button.getAttribute('data-notes');

      const form = document.getElementById('formEditInventoryChar');
      form.action = `/inventory/characters/${id}`;

      document.getElementById('editCharTitle').innerText = name;
      document.getElementById('editLevel').value = level;
      document.getElementById('editAscension').value = ascension;
      document.getElementById('editConstellation').value = constellation;
      document.getElementById('editNA').value = na;
      document.getElementById('editES').value = es;
      document.getElementById('editEB').value = eb;
      document.getElementById('editNotes').value = notes || '';
    });
  }

  // SweetAlert2 Delete Confirmation
  document.querySelectorAll('.btn-delete-inv').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const form = this.closest('form');
      const name = this.getAttribute('data-name');

      Swal.fire({
        title: 'Hapus Karakter?',
        text: `Karakter "${name}" akan dihapus dari inventori akun ini.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#4b5563',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        background: '#161926',
        color: '#f3f4f6'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });

  // Handle Loading on Sync Submit
  const formSync = document.getElementById('formSyncHoyoLab');
  if (formSync) {
    formSync.addEventListener('submit', function () {
      const btn = document.getElementById('btnSubmitSync');
      if (btn) {
        btn.disabled = true;
        btn.querySelector('.btn-text')?.classList.add('d-none');
        btn.querySelector('.btn-loading')?.classList.remove('d-none');
      }
    });
  }
  // Modal Gear Detail Handler
  const modalGear = new bootstrap.Modal(document.getElementById('modalCharGearDetail'));
  document.querySelectorAll('.btn-view-gear').forEach(btn => {
    btn.addEventListener('click', function() {
      const dataStr = this.getAttribute('data-gear');
      if (!dataStr) return;
      const data = JSON.parse(dataStr);

      // 1. Header Karakter
      document.getElementById('mgCharName').textContent = data.char_name || 'Karakter';
      document.getElementById('mgCharAvatar').src = data.icon_url || '';
      document.getElementById('mgCharElement').textContent = data.element || 'Unknown';
      document.getElementById('mgCharElement').style.backgroundColor = data.element_color || '#94a3b8';
      document.getElementById('mgCharConst').textContent = 'C' + (data.constellation ?? 0);
      document.getElementById('mgCharLevel').textContent = `Lv. ${data.level || 1}/${data.max_level || 90}`;
      document.getElementById('mgCharTalents').textContent = `NA ${data.talents.na} • ES ${data.talents.es} • EB ${data.talents.eb}`;

      // 2. Senjata
      const wBox = document.getElementById('mgWeaponBox');
      if (data.weapon) {
        const w = data.weapon;
        const isW5 = (w.rarity === 5);
        wBox.innerHTML = `
          <div class="d-flex align-items-start gap-3">
            ${w.icon_url ? `<img src="${w.icon_url}" class="rounded" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid ${isW5 ? '#eab308' : '#a855f7'}; background: ${isW5 ? '#eab30820' : '#a855f720'};">` : ''}
            <div class="flex-grow-1">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                <span class="fw-bold ${isW5 ? 'text-gold' : 'text-purple'}" style="font-size: 0.95rem;">${w.name} (${w.rarity}★)</span>
                <span class="badge bg-warning text-dark fw-bold">Lv. ${w.level} • Refinement ${w.refinement}</span>
              </div>
              <div class="small text-secondary mt-1">
                Base ATK: <strong class="text-white">${w.base_atk || '-'}</strong>
                ${w.sub_stat_type ? ` • Substat: <strong class="text-info">${w.sub_stat_type} (${w.sub_stat_value || '-'})</strong>` : ''}
              </div>
              ${w.passive_name ? `
                <div class="mt-2 p-2 rounded" style="background: rgba(0,0,0,0.3); font-size: 0.76rem; border-left: 3px solid ${isW5 ? '#eab308' : '#a855f7'};">
                  <strong class="text-white">${w.passive_name}:</strong> <span class="text-muted">${w.passive_desc || ''}</span>
                </div>
              ` : ''}
            </div>
          </div>
        `;
      } else {
        wBox.innerHTML = '<div class="text-muted small py-2"><i class="bi bi-info-circle me-1"></i>Belum ada senjata yang terpasang pada karakter ini di inventori.</div>';
      }

      // 3. Artefak Summary Badges
      const badgesBox = document.getElementById('mgArtifactSummaryBadges');
      let badgesHtml = '';
      if (data.total_cv > 0) {
        const cvColor = data.total_cv >= 140 ? 'bg-warning text-dark' : 'bg-dark text-info border border-info';
        badgesHtml += `<span class="badge ${cvColor}" style="font-size: 0.72rem;">Total ${data.total_cv} CV</span>`;
      }
      if (data.active_sets && data.active_sets.length > 0) {
        data.active_sets.forEach(s => {
          badgesHtml += `<span class="badge" style="background: rgba(228, 196, 133, 0.15); color: var(--genshin-gold); border: 1px solid rgba(228, 196, 133, 0.4); font-size: 0.72rem;">${s}</span>`;
        });
      }
      badgesBox.innerHTML = badgesHtml;

      // 4. Artefak Grid 5 Slot
      const gridBox = document.getElementById('mgArtifactsGrid');
      if (data.artifacts && data.artifacts.length > 0) {
        let gridHtml = '';
        const slotNames = {
          'flower': { name: 'Flower of Life', icon: '<i class="bi bi-flower1 me-1 text-gold"></i>' },
          'plume': { name: 'Plume of Death', icon: '<i class="bi bi-feather me-1 text-gold"></i>' },
          'sands': { name: 'Sands of Eon', icon: '<i class="bi bi-hourglass-split me-1 text-gold"></i>' },
          'goblet': { name: 'Goblet of Eonothem', icon: '<i class="bi bi-cup-straw me-1 text-gold"></i>' },
          'circlet': { name: 'Circlet of Logos', icon: '<i class="bi bi-gem me-1 text-gold"></i>' }
        };

        data.artifacts.forEach(art => {
          let subsHtml = '';
          if (art.sub_stats && art.sub_stats.length > 0) {
            art.sub_stats.forEach(sb => {
              const isCrit = (sb.key === 'crit_rate' || sb.key === 'crit_dmg');
              subsHtml += `<span class="badge ${isCrit ? 'bg-dark text-info border border-info border-opacity-50' : 'bg-secondary bg-opacity-25 text-white-50'}" style="font-size: 0.68rem;">${sb.key}: ${sb.value}</span> `;
            });
          }

          gridHtml += `
            <div class="col-12 col-md-6">
              <div class="p-2.5 rounded h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(228, 196, 133, 0.2);">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="badge bg-dark text-gold border border-warning" style="font-size: 0.68rem;">${slotNames[art.slot] ? (slotNames[art.slot].icon + slotNames[art.slot].name) : art.slot}</span>
                  <div class="d-flex align-items-center gap-1">
                    ${art.cv > 0 ? `<span class="badge bg-info text-dark" style="font-size: 0.65rem;">${art.cv} CV</span>` : ''}
                    <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.65rem;">+${art.level}</span>
                  </div>
                </div>
                <div class="fw-bold text-white small mb-1">${art.set_name}</div>
                <div class="small mb-1.5" style="color: var(--genshin-gold); font-size: 0.78rem;">
                  Main Stat: <strong>${art.main_stat}</strong> (${art.main_stat_value || '-'})
                </div>
                <div class="d-flex flex-wrap gap-1 mt-1">
                  ${subsHtml || '<span class="text-muted" style="font-size: 0.68rem;">Tidak ada substat</span>'}
                </div>
              </div>
            </div>
          `;
        });
        gridBox.innerHTML = gridHtml;
      } else {
        gridBox.innerHTML = '<div class="col-12 text-muted small py-3 text-center">Belum ada artefak yang terpasang pada karakter ini.</div>';
      }

      modalGear.show();
    });
  });
});
</script>
@endpush
@endsection
