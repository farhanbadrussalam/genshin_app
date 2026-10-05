@extends('layout.main')

@section('content')
@php $title = 'Inventori Karakter'; @endphp
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
        <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5);" data-bs-toggle="modal" data-bs-target="#modalSyncEnkaChar">
          <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Enka (UID)
        </button>
        <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(147, 51, 234, 0.2)); border-color: rgba(96, 165, 250, 0.5);" data-bs-toggle="modal" data-bs-target="#modalSyncHoyoLab">
          <i class="bi bi-arrow-repeat me-1"></i>Sync HoYoLAB
        </button>
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
          <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(147, 51, 234, 0.25)); border-color: rgba(96, 165, 250, 0.6);" data-bs-toggle="modal" data-bs-target="#modalSyncHoyoLab">
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
              </div>

              {{-- Footer Action Buttons --}}
              <div class="inv-footer">
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
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge" style="background: #22c55e; color: #fff; font-size: 0.72rem;">✨ Level & Talent Asli</span>
              <span class="badge" style="background: #3b82f6; color: #fff; font-size: 0.72rem;">🔒 Tanpa Cookie / Password</span>
            </div>
            <p class="mb-0 text-white" style="font-size: 0.82rem; line-height: 1.5;">
              Tarik otomatis data karakter yang sedang Anda pajang di <strong>Character Showcase</strong> in-game profil Genshin Impact lengkap dengan level, ascension, konstelasi, serta level talent (Normal Attack, Skill, Burst).
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
});
</script>
@endpush
@endsection
