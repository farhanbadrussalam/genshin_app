@extends('layout.main')

@section('content')
@php $title = 'Inventori Artifact'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 4rem;">

  {{-- Flash Message --}}
  @if(session('success'))
    <div class="alert-toast success" id="alertToast">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
  @endif

  {{-- Page Header & Account Selector --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.35rem; letter-spacing: 0.08em;">
        <i class="bi bi-gem me-2"></i>Inventori Artifact
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.84rem; margin-bottom: 0;">
        Koleksi artifact, slot piece, main stat, dan roll substat pada akun game kamu
      </p>
    </div>

    {{-- Account Switcher --}}
    <div class="d-flex align-items-center gap-2">
      @if($accounts->isNotEmpty())
        <div class="account-selector-box">
          <form method="GET" action="{{ route('inventory.artifacts.index') }}" id="accountSelectForm">
            <input type="hidden" name="slot" value="{{ $filters['slot'] ?? '' }}">
            <input type="hidden" name="set_id" value="{{ $filters['set_id'] ?? '' }}">
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
        <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5); color: #f3f4f6;" data-bs-toggle="modal" data-bs-target="#modalSyncEnkaArtifact">
          <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Enka (UID)
        </button>
        <button class="btn-genshin btn-genshin-sm" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(147, 51, 234, 0.2)); border-color: rgba(96, 165, 250, 0.5); color: #f3f4f6;" data-bs-toggle="modal" data-bs-target="#modalSyncHoyoLabArtifact">
          <i class="bi bi-arrow-repeat me-1"></i>Sync HoYoLAB
        </button>
        <a href="{{ route('inventory.good.index', ['account_id' => $activeAccount->id]) }}" class="btn-genshin btn-genshin-sm" style="background: rgba(228, 196, 133, 0.15); border-color: rgba(228, 196, 133, 0.5); color: var(--genshin-gold);">
          <i class="bi bi-arrow-left-right me-1"></i>GOOD (Export/Import)
        </a>
        <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal" data-bs-target="#modalAddInventoryArtifact">
          <i class="bi bi-plus-lg me-1"></i>Tambah Artifact
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
        Tambahkan akun game Genshin Impact terlebih dahulu untuk mulai mendata inventori artifactmu.
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
          <div class="stat-icon text-gold"><i class="bi bi-gem"></i></div>
          <div class="stat-content">
            <span class="stat-value">{{ $stats['total'] }}</span>
            <span class="stat-label">Total Artifact</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon text-warning"><i class="bi bi-stars"></i></div>
          <div class="stat-content">
            <span class="stat-value text-warning">{{ $stats['star5'] }}</span>
            <span class="stat-label">Bintang 5 (★5)</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon text-info"><i class="bi bi-award-fill"></i></div>
          <div class="stat-content">
            <span class="stat-value text-info">{{ $stats['max_lvl'] }}</span>
            <span class="stat-label">Level +20 (Max)</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon text-success"><i class="bi bi-person-check-fill"></i></div>
          <div class="stat-content">
            <span class="stat-value text-success">{{ $stats['equipped'] }}</span>
            <span class="stat-label">Sedang Dipakai</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Filter Panel --}}
    <div class="filter-panel mb-4 animate-fade-in-up">
      <form method="GET" action="{{ route('inventory.artifacts.index') }}" id="invArtifactFilterForm">
        <input type="hidden" name="account_id" value="{{ $activeAccount?->id }}">
        <div class="row g-2 align-items-center">
          {{-- Search Set --}}
          <div class="col-12 col-md-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text genshin-input-group-text"><i class="bi bi-search text-gold"></i></span>
              <input type="text" name="search" class="form-control genshin-input" 
                     placeholder="Cari nama set..." 
                     value="{{ $filters['search'] ?? '' }}"
                     onchange="this.form.submit()">
            </div>
          </div>

          {{-- Slot Piece --}}
          <div class="col-6 col-sm-3 col-md-2">
            <select name="slot" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
              <option value="all">Semua Slot</option>
              <option value="flower" {{ ($filters['slot'] ?? '') === 'flower' ? 'selected' : '' }}>🌸 Flower</option>
              <option value="plume" {{ ($filters['slot'] ?? '') === 'plume' ? 'selected' : '' }}>🪶 Plume</option>
              <option value="sands" {{ ($filters['slot'] ?? '') === 'sands' ? 'selected' : '' }}>⏳ Sands</option>
              <option value="goblet" {{ ($filters['slot'] ?? '') === 'goblet' ? 'selected' : '' }}>🍷 Goblet</option>
              <option value="circlet" {{ ($filters['slot'] ?? '') === 'circlet' ? 'selected' : '' }}>👑 Circlet</option>
            </select>
          </div>

          {{-- Set Filter --}}
          <div class="col-6 col-sm-3 col-md-3">
            <select name="set_id" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
              <option value="all">Semua Set Artifact</option>
              @foreach($availableSets as $set)
                <option value="{{ $set->id }}" {{ ($filters['set_id'] ?? '') == $set->id ? 'selected' : '' }}>
                  {{ $set->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Equipped Status --}}
          <div class="col-6 col-sm-3 col-md-2">
            <select name="equipped" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
              <option value="all">Semua Status</option>
              <option value="yes" {{ ($filters['equipped'] ?? '') === 'yes' ? 'selected' : '' }}>Dipakai Karakter</option>
              <option value="no" {{ ($filters['equipped'] ?? '') === 'no' ? 'selected' : '' }}>Tersimpan di Tas</option>
            </select>
          </div>

          {{-- Sorting --}}
          <div class="col-6 col-sm-3 col-md-2">
            <select name="sort" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
              <option value="level_desc" {{ ($filters['sort'] ?? '') === 'level_desc' ? 'selected' : '' }}>Level Tertinggi</option>
              <option value="level_asc" {{ ($filters['sort'] ?? '') === 'level_asc' ? 'selected' : '' }}>Level Terendah</option>
              <option value="slot" {{ ($filters['sort'] ?? '') === 'slot' ? 'selected' : '' }}>Urut Slot (🌸-👑)</option>
              <option value="rarity_desc" {{ ($filters['sort'] ?? '') === 'rarity_desc' ? 'selected' : '' }}>Rarity (★5-★4)</option>
            </select>
          </div>
        </div>
      </form>
    </div>

    {{-- Artifact Grid --}}
    @if($inventoryArtifacts->isEmpty())
      <div class="empty-state animate-fade-in-up">
        <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;">💎</div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
          @if(!empty($filters['search']) || !empty($filters['slot']) || !empty($filters['set_id']))
            Tidak ada artifact yang cocok dengan kriteria filter.
          @else
            Akun <strong>{{ $activeAccount?->nickname }}</strong> belum memiliki data artifact inventori.
          @endif
        </p>
        <button class="btn-genshin btn-genshin-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalAddInventoryArtifact">
          <i class="bi bi-plus-lg me-1"></i>Tambah Artifact Pertama
        </button>
      </div>
    @else
      <div class="row g-3">
        @foreach($inventoryArtifacts as $art)
          @php
            $set = $art->artifactSet;
            $isFiveStar = $art->rarity === 5;
            $equipped = $art->equippedCharacter;
          @endphp
          <div class="col-12 col-sm-6 col-md-4 col-lg-3 animate-fade-in-up" style="animation-delay: {{ ($loop->index % 12) * 0.04 }}s;">
            <div class="inv-card {{ $isFiveStar ? 'card-star-5' : 'card-star-4' }}">
              
              {{-- Header: Slot Label & Level --}}
              <div class="inv-card-header">
                <span class="weapon-type-badge">
                  <i class="bi {{ $art->slot_icon }} me-1 text-gold"></i>{{ $art->slot_label }}
                </span>
                <span class="inv-const-tag {{ $art->level >= 20 ? 'c6-glow' : '' }}">
                  +{{ $art->level }}
                </span>
              </div>

              {{-- Image Box --}}
              <div class="inv-avatar-box">
                @php
                  $artIcon = $art->icon_url ?: $set?->icon_url;
                @endphp
                @if($artIcon)
                  <img src="{{ $artIcon }}" 
                       alt="{{ $set?->name }} ({{ $art->slot_label }})" 
                       class="inv-avatar"
                       loading="lazy"
                       referrerpolicy="no-referrer"
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class="inv-avatar-placeholder" style="display: none; background: rgba(229, 160, 41, 0.1);">
                    <i class="bi {{ $art->slot_icon }}" style="font-size: 2.5rem; color: var(--accent-gold);"></i>
                  </div>
                @else
                  <div class="inv-avatar-placeholder" style="background: rgba(229, 160, 41, 0.1);">
                    <i class="bi {{ $art->slot_icon }}" style="font-size: 2.5rem; color: var(--accent-gold);"></i>
                  </div>
                @endif

                {{-- Main Stat Overlay --}}
                <div class="inv-level-badge">
                  <span class="text-gold fw-bold">{{ $art->main_stat_label }}</span> {{ $art->main_stat_value }}
                </div>
              </div>

              {{-- Body: Set Name & Sub-stats --}}
              <div class="inv-body">
                <h3 class="inv-name" title="{{ $set?->name }}">{{ $set?->name }}</h3>
                <div class="inv-stars mb-2">
                  @for($i = 0; $i < $art->rarity; $i++)
                    <i class="bi bi-star-fill text-gold"></i>
                  @endfor
                </div>

                {{-- Sub Stats Pill List --}}
                <div class="artifact-substats-box mb-2">
                  @if(!empty($art->sub_stats) && is_array($art->sub_stats))
                    @foreach($art->sub_stats as $sub)
                      <div class="substat-item">
                        <span class="substat-dot">•</span>
                        <span class="substat-name">{{ ucwords(str_replace('_', ' ', $sub['key'] ?? '')) }}</span>
                        <span class="substat-val">+{{ $sub['value'] ?? '' }}</span>
                      </div>
                    @endforeach
                  @else
                    <div class="substat-item empty text-muted">
                      <span>Belum ada roll substat</span>
                    </div>
                  @endif
                </div>

                {{-- Equipped Status --}}
                @if($equipped)
                  <div class="equipped-badge mb-1" title="Digunakan oleh {{ $equipped->name }}">
                    <i class="bi bi-person-fill me-1 text-gold"></i>
                    <span>Dipakai: <strong>{{ $equipped->name }}</strong></span>
                  </div>
                @else
                  <div class="equipped-badge free mb-1">
                    <i class="bi bi-box-arrow-in-down me-1"></i>
                    <span>Tersimpan di Tas</span>
                  </div>
                @endif
              </div>

              {{-- Footer Action Buttons (Sejajar & 34px) --}}
              <div class="inv-footer">
                <button class="btn-action edit" 
                        title="Edit Artifact"
                        data-bs-toggle="modal" 
                        data-bs-target="#modalEditInventoryArtifact"
                        data-id="{{ $art->id }}"
                        data-set="{{ $art->artifact_set_id }}"
                        data-slot="{{ $art->slot_key }}"
                        data-rarity="{{ $art->rarity }}"
                        data-level="{{ $art->level }}"
                        data-main-key="{{ $art->main_stat_key }}"
                        data-main-val="{{ $art->main_stat_value }}"
                        data-char="{{ $art->equipped_character_id ?? '' }}"
                        data-notes="{{ $art->notes }}">
                  <i class="bi bi-sliders me-1"></i>Edit
                </button>
                <form action="{{ route('inventory.artifacts.destroy', $art) }}" method="POST" class="d-inline form-delete">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn-action delete btn-delete-inv-art" data-name="{{ $set?->name }} ({{ $art->slot_label }})" title="Hapus dari inventori">
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
        {{ $inventoryArtifacts->links('pagination::bootstrap-5') }}
      </div>
    @endif

  @endif

</div>

{{-- MODAL TAMBAH ARTIFACT KE INVENTORI --}}
@if($activeAccount)
<div class="modal fade" id="modalAddInventoryArtifact" tabindex="-1" aria-labelledby="modalAddArtLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalAddArtLabel">
          <i class="bi bi-gem me-2"></i>Tambah Artifact ke Inventori
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('inventory.artifacts.store') }}" method="POST">
        @csrf
        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">
        <div class="modal-body">
          {{-- Set & Slot --}}
          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label genshin-label">Pilih Set <span class="text-danger">*</span></label>
              <select name="artifact_set_id" class="form-select genshin-select" required>
                @foreach($availableSets as $s)
                  <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-5">
              <label class="form-label genshin-label">Slot Piece <span class="text-danger">*</span></label>
              <select name="slot_key" class="form-select genshin-select" required>
                <option value="flower">🌸 Flower of Life</option>
                <option value="plume">🪶 Plume of Death</option>
                <option value="sands">⏳ Sands of Eon</option>
                <option value="goblet">🍷 Goblet of Eonothem</option>
                <option value="circlet">👑 Circlet of Logos</option>
              </select>
            </div>
          </div>

          {{-- Rarity & Level --}}
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Rarity <span class="text-danger">*</span></label>
              <select name="rarity" class="form-select genshin-select" required>
                <option value="5" selected>★★★★★ (Bintang 5)</option>
                <option value="4">★★★★ (Bintang 4)</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Level (+0 s/d +20) <span class="text-danger">*</span></label>
              <input type="number" name="level" class="form-control genshin-input" min="0" max="20" value="20" required>
            </div>
          </div>

          {{-- Main Stat Key & Value --}}
          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label genshin-label">Main Stat <span class="text-danger">*</span></label>
              <select name="main_stat_key" class="form-select genshin-select" required>
                <option value="hp">HP Flat (Flower)</option>
                <option value="atk">ATK Flat (Plume)</option>
                <option value="atk_percent">ATK%</option>
                <option value="hp_percent">HP%</option>
                <option value="def_percent">DEF%</option>
                <option value="energy_recharge">Energy Recharge%</option>
                <option value="elemental_mastery">Elemental Mastery</option>
                <option value="crit_rate">CRIT Rate%</option>
                <option value="crit_dmg">CRIT DMG%</option>
                <option value="healing_bonus">Healing Bonus%</option>
                <option value="pyro_dmg">Pyro DMG Bonus%</option>
                <option value="hydro_dmg">Hydro DMG Bonus%</option>
                <option value="dendro_dmg">Dendro DMG Bonus%</option>
                <option value="electro_dmg">Electro DMG Bonus%</option>
                <option value="anemo_dmg">Anemo DMG Bonus%</option>
                <option value="cryo_dmg">Cryo DMG Bonus%</option>
                <option value="geo_dmg">Geo DMG Bonus%</option>
                <option value="physical_dmg">Physical DMG Bonus%</option>
              </select>
            </div>
            <div class="col-5">
              <label class="form-label genshin-label">Nilai Stat <span class="text-danger">*</span></label>
              <input type="text" name="main_stat_value" class="form-control genshin-input" placeholder="contoh: 46.6%" value="46.6%" required>
            </div>
          </div>

          {{-- Equip ke Karakter --}}
          <div class="mb-3">
            <label class="form-label genshin-label">Equip ke Karakter</label>
            <select name="equipped_character_id" class="form-select genshin-select">
              <option value="">-- Tidak Dipakai (Tersimpan di Tas) --</option>
              @foreach($accountCharacters as $ch)
                <option value="{{ $ch->id }}">{{ $ch->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Catatan --}}
          <div class="mb-2">
            <label class="form-label genshin-label">Catatan Tambahan</label>
            <input type="text" name="notes" class="form-control genshin-input" placeholder="contoh: Substat 40+ CV / Off-piece Goblet">
          </div>
        </div>
        <div class="modal-footer genshin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">
            <i class="bi bi-check-lg me-1"></i>Simpan Artifact
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDIT ARTIFACT INVENTORI --}}
<div class="modal fade" id="modalEditInventoryArtifact" tabindex="-1" aria-labelledby="modalEditArtLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalEditArtLabel">
          <i class="bi bi-sliders me-2"></i>Edit Data Artifact
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEditInventoryArtifact" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label genshin-label">Pilih Set <span class="text-danger">*</span></label>
              <select id="editArtSet" name="artifact_set_id" class="form-select genshin-select" required>
                @foreach($availableSets as $s)
                  <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-5">
              <label class="form-label genshin-label">Slot Piece <span class="text-danger">*</span></label>
              <select id="editArtSlot" name="slot_key" class="form-select genshin-select" required>
                <option value="flower">🌸 Flower of Life</option>
                <option value="plume">🪶 Plume of Death</option>
                <option value="sands">⏳ Sands of Eon</option>
                <option value="goblet">🍷 Goblet of Eonothem</option>
                <option value="circlet">👑 Circlet of Logos</option>
              </select>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Rarity <span class="text-danger">*</span></label>
              <select id="editArtRarity" name="rarity" class="form-select genshin-select" required>
                <option value="5">★★★★★ (Bintang 5)</option>
                <option value="4">★★★★ (Bintang 4)</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Level (+0 s/d +20) <span class="text-danger">*</span></label>
              <input type="number" id="editArtLevel" name="level" class="form-control genshin-input" min="0" max="20" required>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label genshin-label">Main Stat <span class="text-danger">*</span></label>
              <select id="editArtMainKey" name="main_stat_key" class="form-select genshin-select" required>
                <option value="hp">HP Flat</option>
                <option value="atk">ATK Flat</option>
                <option value="atk_percent">ATK%</option>
                <option value="hp_percent">HP%</option>
                <option value="def_percent">DEF%</option>
                <option value="energy_recharge">Energy Recharge%</option>
                <option value="elemental_mastery">Elemental Mastery</option>
                <option value="crit_rate">CRIT Rate%</option>
                <option value="crit_dmg">CRIT DMG%</option>
                <option value="healing_bonus">Healing Bonus%</option>
                <option value="pyro_dmg">Pyro DMG Bonus%</option>
                <option value="hydro_dmg">Hydro DMG Bonus%</option>
                <option value="dendro_dmg">Dendro DMG Bonus%</option>
                <option value="electro_dmg">Electro DMG Bonus%</option>
                <option value="anemo_dmg">Anemo DMG Bonus%</option>
                <option value="cryo_dmg">Cryo DMG Bonus%</option>
                <option value="geo_dmg">Geo DMG Bonus%</option>
                <option value="physical_dmg">Physical DMG Bonus%</option>
              </select>
            </div>
            <div class="col-5">
              <label class="form-label genshin-label">Nilai Stat <span class="text-danger">*</span></label>
              <input type="text" id="editArtMainVal" name="main_stat_value" class="form-control genshin-input" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label genshin-label">Equip ke Karakter</label>
            <select id="editArtChar" name="equipped_character_id" class="form-select genshin-select">
              <option value="">-- Tidak Dipakai (Tersimpan di Tas) --</option>
              @foreach($accountCharacters as $ch)
                <option value="{{ $ch->id }}">{{ $ch->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-2">
            <label class="form-label genshin-label">Catatan</label>
            <input type="text" id="editArtNotes" name="notes" class="form-control genshin-input">
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

{{-- MODAL SYNC HOYOLAB ARTIFACT --}}
<div class="modal fade" id="modalSyncHoyoLabArtifact" tabindex="-1" aria-labelledby="modalSyncArtLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalSyncArtLabel">
          <i class="bi bi-arrow-repeat me-2"></i>Sync Artifact via HoYoLAB
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('inventory.artifacts.sync-hoyolab') }}" method="POST" id="formSyncHoyoLabArtifact">
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
              Microservice akan menarik seluruh artifact yang sedang dipakai oleh karakter di Battle Chronicle akun ini.
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
            <input class="form-check-input" type="checkbox" name="save_credentials" value="1" id="saveCredentialsCheckArtifact" checked>
            <label class="form-check-label" for="saveCredentialsCheckArtifact" style="font-size: 0.78rem; color: var(--text-secondary);">
              Simpan cookie ini di akun game agar tidak perlu input ulang di masa depan
            </label>
          </div>

          {{-- Panduan Ambil Cookie --}}
          <div class="accordion" id="guideAccordionArtifact">
            <div class="accordion-item" style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); border-radius: 8px;">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#guideCollapseArtifact" style="background: transparent; color: var(--text-muted); font-size: 0.76rem; box-shadow: none;">
                  <i class="bi bi-question-circle me-1"></i>Cara mengambil cookie dari HoYoLAB
                </button>
              </h2>
              <div id="guideCollapseArtifact" class="accordion-collapse collapse" data-bs-parent="#guideAccordionArtifact">
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
          <button type="submit" class="btn-genshin btn-genshin-sm" id="btnSubmitSyncArtifact">
            <span class="btn-text"><i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai Sinkronisasi</span>
            <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Sedang Sync...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL SYNC ENKA (UID) ARTIFACT --}}
<div class="modal fade" id="modalSyncEnkaArtifact" tabindex="-1" aria-labelledby="modalSyncEnkaArtLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalSyncEnkaArtLabel">
          <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Artifact via Enka.Network (UID)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('inventory.artifacts.sync-enka') }}" method="POST">
        @csrf
        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

        <div class="modal-body">
          <div class="p-3 mb-3 rounded" style="background: rgba(229,160,41,0.08); border: 1px solid rgba(229,160,41,0.25);">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge" style="background: #22c55e; color: #fff; font-size: 0.72rem;">✨ Sub-Stat Asli 100%</span>
              <span class="badge" style="background: #3b82f6; color: #fff; font-size: 0.72rem;">🔒 Tanpa Cookie / Password</span>
            </div>
            <p class="mb-0 text-white" style="font-size: 0.82rem; line-height: 1.5;">
              Tarik otomatis seluruh artifact yang sedang terpasang pada karakter di <strong>Character Showcase</strong> in-game profil Genshin Impact Anda lengkap dengan <strong>seluruh sub-stat asli</strong> dan perhitungan skor kualitasnya!
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
  const modalEdit = document.getElementById('modalEditInventoryArtifact');
  if (modalEdit) {
    modalEdit.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const set = button.getAttribute('data-set');
      const slot = button.getAttribute('data-slot');
      const rarity = button.getAttribute('data-rarity');
      const level = button.getAttribute('data-level');
      const mainKey = button.getAttribute('data-main-key');
      const mainVal = button.getAttribute('data-main-val');
      const char = button.getAttribute('data-char');
      const notes = button.getAttribute('data-notes');

      const form = document.getElementById('formEditInventoryArtifact');
      form.action = `/inventory/artifacts/${id}`;

      document.getElementById('editArtSet').value = set;
      document.getElementById('editArtSlot').value = slot;
      document.getElementById('editArtRarity').value = rarity;
      document.getElementById('editArtLevel').value = level;
      document.getElementById('editArtMainKey').value = mainKey;
      document.getElementById('editArtMainVal').value = mainVal;
      document.getElementById('editArtChar').value = char || '';
      document.getElementById('editArtNotes').value = notes || '';
    });
  }

  document.querySelectorAll('.btn-delete-inv-art').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const form = this.closest('form');
      const name = this.getAttribute('data-name');

      Swal.fire({
        title: 'Hapus Artifact?',
        text: `Artifact "${name}" akan dihapus dari inventori akun ini.`,
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
  const formSyncArt = document.getElementById('formSyncHoyoLabArtifact');
  if (formSyncArt) {
    formSyncArt.addEventListener('submit', function () {
      const btn = document.getElementById('btnSubmitSyncArtifact');
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
