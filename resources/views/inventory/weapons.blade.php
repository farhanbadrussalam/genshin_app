@extends('layout.main')

@section('content')
    @php $title = 'Inventori Senjata'; @endphp
    @include('layout.header')

    <div class="page-container" style="padding-top: 1.5rem; padding-bottom: 4rem;">

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="alert-toast success" id="alertToast">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Page Header & Account Selector --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
            <div>
                <h1 class="font-display text-gold mb-1" style="font-size: 1.35rem; letter-spacing: 0.08em;">
                    <i class="bi bi-shield-shaded me-2"></i>Inventori Senjata
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.84rem; margin-bottom: 0;">
                    Pantau level, refinement, dan kepemilikan senjata pada akun game kamu
                </p>
            </div>

            {{-- Account Switcher --}}
            <div class="d-flex align-items-center gap-2">
                @if ($accounts->isNotEmpty())
                    <div class="account-selector-box">
                        <form method="GET" action="{{ route('inventory.weapons.index') }}" id="accountSelectForm">
                            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                            <input type="hidden" name="type" value="{{ $filters['type'] ?? '' }}">
                            <input type="hidden" name="rarity" value="{{ $filters['rarity'] ?? '' }}">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text genshin-input-group-text"><i
                                        class="bi bi-controller text-gold"></i></span>
                                <select name="account_id" class="form-select genshin-select" onchange="this.form.submit()"
                                    style="min-width: 180px;">
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ $activeAccount?->id === $acc->id ? 'selected' : '' }}>
                                            {{ $acc->nickname }} (UID: {{ $acc->uid }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                @endif

                @if ($activeAccount)
                    <button class="btn-genshin btn-genshin-sm"
                        style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5); color: #f3f4f6;"
                        data-bs-toggle="modal" data-bs-target="#modalSyncEnkaWeapon">
                        <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Enka (UID)
                    </button>
                    <button class="btn-genshin btn-genshin-sm"
                        style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(147, 51, 234, 0.2)); border-color: rgba(96, 165, 250, 0.5); color: #f3f4f6;"
                        data-bs-toggle="modal" data-bs-target="#modalSyncHoyoLabWeapon">
                        <i class="bi bi-arrow-repeat me-1"></i>Sync HoYoLAB
                    </button>
                    <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal"
                        data-bs-target="#modalAddInventoryWeapon">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Senjata
                    </button>
                @endif
            </div>
        </div>

        @if ($accounts->isEmpty())
            {{-- Empty State: Belum ada akun game --}}
            <div class="empty-state animate-fade-in-up">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">🎮</div>
                <h4 class="font-display text-gold">Belum Ada Akun Game</h4>
                <p style="color: var(--text-secondary); font-size: 0.88rem; max-width: 450px; margin: 0 auto 1.5rem;">
                    Tambahkan akun game Genshin Impact terlebih dahulu untuk mulai mendata inventori senjatamu.
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
                        <div class="stat-icon"><i class="bi bi-shield-shaded text-gold"></i></div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $stats['total'] }}</span>
                            <span class="stat-label">Total Senjata</span>
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
                            <span class="stat-value text-info">{{ $stats['max_level'] }}</span>
                            <span class="stat-label">Level 90 (Max)</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon text-success"><i class="bi bi-gem"></i></div>
                        <div class="stat-content">
                            <span class="stat-value text-success">{{ $stats['r5'] }}</span>
                            <span class="stat-label">Refinement R5</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Panel --}}
            <div class="filter-panel mb-4 animate-fade-in-up">
                <form method="GET" action="{{ route('inventory.weapons.index') }}" id="invWeaponFilterForm">
                    <input type="hidden" name="account_id" value="{{ $activeAccount?->id }}">
                    <div class="row g-2 align-items-center">
                        {{-- Search --}}
                        <div class="col-12 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text genshin-input-group-text"><i
                                        class="bi bi-search text-gold"></i></span>
                                <input type="text" name="search" class="form-control genshin-input"
                                    placeholder="Cari senjata dimiliki..." value="{{ $filters['search'] ?? '' }}"
                                    onchange="this.form.submit()">
                            </div>
                        </div>

                        {{-- Type --}}
                        <div class="col-6 col-sm-3 col-md-2">
                            <select name="type" class="form-select form-select-sm genshin-select"
                                onchange="this.form.submit()">
                                <option value="all">Semua Tipe</option>
                                @foreach (['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'] as $t)
                                    <option value="{{ $t }}"
                                        {{ ($filters['type'] ?? '') === $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rarity --}}
                        <div class="col-6 col-sm-3 col-md-2">
                            <select name="rarity" class="form-select form-select-sm genshin-select"
                                onchange="this.form.submit()">
                                <option value="all">Semua Rarity</option>
                                <option value="5" {{ ($filters['rarity'] ?? '') === '5' ? 'selected' : '' }}>★★★★★
                                    (★5)</option>
                                <option value="4" {{ ($filters['rarity'] ?? '') === '4' ? 'selected' : '' }}>★★★★
                                    (★4)</option>
                            </select>
                        </div>

                        {{-- Equipped Status --}}
                        <div class="col-6 col-sm-3 col-md-2">
                            <select name="equipped" class="form-select form-select-sm genshin-select"
                                onchange="this.form.submit()">
                                <option value="all">Semua Status</option>
                                <option value="yes" {{ ($filters['equipped'] ?? '') === 'yes' ? 'selected' : '' }}>
                                    Dipakai Karakter</option>
                                <option value="no" {{ ($filters['equipped'] ?? '') === 'no' ? 'selected' : '' }}>Tidak
                                    Dipakai</option>
                            </select>
                        </div>

                        {{-- Sorting --}}
                        <div class="col-6 col-sm-3 col-md-2">
                            <select name="sort" class="form-select form-select-sm genshin-select"
                                onchange="this.form.submit()">
                                <option value="level_desc"
                                    {{ ($filters['sort'] ?? '') === 'level_desc' ? 'selected' : '' }}>Level Tertinggi
                                </option>
                                <option value="refine_desc"
                                    {{ ($filters['sort'] ?? '') === 'refine_desc' ? 'selected' : '' }}>Refinement R5
                                </option>
                                <option value="name_asc" {{ ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' }}>
                                    Nama (A-Z)</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Inventory Grid --}}
            @if ($inventoryWeapons->isEmpty())
                <div class="empty-state animate-fade-in-up">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;">⚔️</div>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        @if (!empty($filters['search']) || !empty($filters['type']) || !empty($filters['rarity']))
                            Tidak ada senjata yang cocok dengan kriteria filter.
                        @else
                            Akun <strong>{{ $activeAccount?->nickname }}</strong> belum memiliki data senjata inventori.
                        @endif
                    </p>
                    <button class="btn-genshin btn-genshin-sm mt-2" data-bs-toggle="modal"
                        data-bs-target="#modalAddInventoryWeapon">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Senjata Pertama
                    </button>
                </div>
            @else
                <div class="row g-3">
                    @foreach ($inventoryWeapons as $invWep)
                        @php
                            $w = $invWep->weapon;
                            $isFiveStar = ($w?->rarity ?? 4) === 5;
                            $equipped = $invWep->equippedCharacter;
                        @endphp
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 animate-fade-in-up"
                            style="animation-delay: {{ ($loop->index % 12) * 0.04 }}s;">
                            <div class="inv-card {{ $isFiveStar ? 'card-star-5' : 'card-star-4' }}">

                                {{-- Header Card: Type & Refinement --}}
                                <div class="inv-card-header">
                                    <span class="weapon-type-badge">
                                        {{ $w?->type }}
                                    </span>
                                    <span class="inv-const-tag {{ $invWep->refinement === 5 ? 'c6-glow' : '' }}">
                                        R{{ $invWep->refinement }}
                                    </span>
                                </div>

                                {{-- Avatar & Level Overlay --}}
                                <div class="inv-avatar-box">
                                    @if ($w?->icon_url)
                                        <img src="{{ $w->icon_url }}" alt="{{ $w->name }}" class="inv-avatar"
                                            loading="lazy" referrerpolicy="no-referrer"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="inv-avatar-placeholder"
                                            style="display: none; background: rgba(229, 160, 41, 0.1);">
                                            <i class="bi bi-shield-shaded"
                                                style="font-size: 2.5rem; color: var(--accent-gold);"></i>
                                        </div>
                                    @else
                                        <div class="inv-avatar-placeholder" style="background: rgba(229, 160, 41, 0.1);">
                                            <i class="bi bi-shield-shaded"
                                                style="font-size: 2.5rem; color: var(--accent-gold);"></i>
                                        </div>
                                    @endif

                                    {{-- Level Badge Overlay --}}
                                    <div class="inv-level-badge">
                                        Lv. {{ $invWep->level }}<span class="cap">/{{ $invWep->max_level }}</span>
                                    </div>
                                </div>

                                {{-- Body Info --}}
                                <div class="inv-body">
                                    <h3 class="inv-name" title="{{ $w?->name }}">
                                        {{ $w?->name }}
                                    </h3>
                                    <div class="inv-stars mb-2">
                                        @for ($i = 0; $i < ($w?->rarity ?? 4); $i++)
                                            <i class="bi bi-star-fill text-gold"></i>
                                        @endfor
                                    </div>

                                    {{-- Weapon Stats --}}
                                    <div class="d-flex justify-content-center gap-2 mb-2">
                                        <span class="badge"
                                            style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3);">
                                            <i class="bi bi-lightning-charge-fill me-1"></i>{{ $w?->base_atk }}
                                        </span>
                                        @if ($w?->sub_stat_type)
                                            <span class="badge"
                                                style="background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); border: 1px solid var(--border-color);">
                                                {{ $w->sub_stat_type }}: {{ $w->sub_stat_value }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Equipped Status --}}
                                    @if ($equipped)
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

                                {{-- Footer Action Buttons --}}
                                <div class="inv-footer">
                                    <button class="btn-action edit" title="Edit Senjata" data-bs-toggle="modal"
                                        data-bs-target="#modalEditInventoryWeapon" data-id="{{ $invWep->id }}"
                                        data-name="{{ $w?->name }}" data-level="{{ $invWep->level }}"
                                        data-ascension="{{ $invWep->ascension }}"
                                        data-refine="{{ $invWep->refinement }}"
                                        data-char="{{ $invWep->equipped_character_id ?? '' }}"
                                        data-notes="{{ $invWep->notes }}">
                                        <i class="bi bi-sliders me-1"></i>Edit
                                    </button>
                                    <form action="{{ route('inventory.weapons.destroy', $invWep) }}" method="POST"
                                        class="d-inline form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn-action delete btn-delete-inv-wep"
                                            data-name="{{ $w?->name }}" title="Hapus dari inventori">
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
                    {{ $inventoryWeapons->links('pagination::bootstrap-5') }}
                </div>
            @endif

        @endif

    </div>

    {{-- MODAL TAMBAH SENJATA KE INVENTORI --}}
    @if ($activeAccount)
        <div class="modal fade" id="modalAddInventoryWeapon" tabindex="-1" aria-labelledby="modalAddWepLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content genshin-modal-content">
                    <div class="modal-header genshin-modal-header">
                        <h5 class="modal-title font-display text-gold" id="modalAddWepLabel">
                            <i class="bi bi-shield-plus me-2"></i>Tambah ke Inventori Senjata
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('inventory.weapons.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">
                        <div class="modal-body">
                            {{-- Pilih Senjata --}}
                            <div class="mb-3">
                                <label class="form-label genshin-label">Pilih Senjata Master <span
                                        class="text-danger">*</span></label>
                                <select name="weapon_id" class="form-select genshin-select" required>
                                    <option value="">-- Pilih Senjata --</option>
                                    @foreach ($availableWeapons as $wep)
                                        <option value="{{ $wep->id }}">
                                            {{ $wep->name }} ({{ $wep->type }} • ★{{ $wep->rarity }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Level & Ascension --}}
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label genshin-label">Level (1 - 90) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="level" class="form-control genshin-input"
                                        min="1" max="90" value="90" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label genshin-label">Ascension Phase <span
                                            class="text-danger">*</span></label>
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

                            {{-- Refinement --}}
                            <div class="mb-3">
                                <label class="form-label genshin-label">Refinement Rank (R1 - R5) <span
                                        class="text-danger">*</span></label>
                                <select name="refinement" class="form-select genshin-select" required>
                                    <option value="1" selected>R1</option>
                                    <option value="2">R2</option>
                                    <option value="3">R3</option>
                                    <option value="4">R4</option>
                                    <option value="5">R5 (Max Refinement)</option>
                                </select>
                            </div>

                            {{-- Equip ke Karakter --}}
                            <div class="mb-3">
                                <label class="form-label genshin-label">Equip ke Karakter (Opsional)</label>
                                <select name="equipped_character_id" class="form-select genshin-select">
                                    <option value="">-- Tidak Dipakai (Tersimpan di Tas) --</option>
                                    @foreach ($accountCharacters as $ch)
                                        <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Notes --}}
                            <div class="mb-2">
                                <label class="form-label genshin-label">Catatan Tambahan</label>
                                <input type="text" name="notes" class="form-control genshin-input"
                                    placeholder="contoh: Senjata cadangan / build alternatif">
                            </div>
                        </div>
                        <div class="modal-footer genshin-modal-footer">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-genshin btn-genshin-sm">
                                <i class="bi bi-check-lg me-1"></i>Simpan ke Inventori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT SENJATA INVENTORI --}}
        <div class="modal fade" id="modalEditInventoryWeapon" tabindex="-1" aria-labelledby="modalEditWepLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content genshin-modal-content">
                    <div class="modal-header genshin-modal-header">
                        <h5 class="modal-title font-display text-gold" id="modalEditWepLabel">
                            <i class="bi bi-sliders me-2"></i>Edit Senjata: <span id="editWepTitle"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form id="formEditInventoryWeapon" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label genshin-label">Level (1 - 90) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" id="editWepLevel" name="level"
                                        class="form-control genshin-input" min="1" max="90" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label genshin-label">Ascension Phase <span
                                            class="text-danger">*</span></label>
                                    <select id="editWepAscension" name="ascension" class="form-select genshin-select"
                                        required>
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

                            <div class="mb-3">
                                <label class="form-label genshin-label">Refinement Rank (R1 - R5) <span
                                        class="text-danger">*</span></label>
                                <select id="editWepRefine" name="refinement" class="form-select genshin-select" required>
                                    <option value="1">R1</option>
                                    <option value="2">R2</option>
                                    <option value="3">R3</option>
                                    <option value="4">R4</option>
                                    <option value="5">R5</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label genshin-label">Equip ke Karakter</label>
                                <select id="editWepEquipped" name="equipped_character_id"
                                    class="form-select genshin-select">
                                    <option value="">-- Tidak Dipakai (Tersimpan di Tas) --</option>
                                    @foreach ($accountCharacters as $ch)
                                        <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label genshin-label">Catatan Tambahan</label>
                                <input type="text" id="editWepNotes" name="notes"
                                    class="form-control genshin-input">
                            </div>
                        </div>
                        <div class="modal-footer genshin-modal-footer">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-genshin btn-genshin-sm">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL SYNC HOYOLAB SENJATA --}}
        <div class="modal fade" id="modalSyncHoyoLabWeapon" tabindex="-1" aria-labelledby="modalSyncWepLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content genshin-modal-content">
                    <div class="modal-header genshin-modal-header">
                        <h5 class="modal-title font-display text-gold" id="modalSyncWepLabel">
                            <i class="bi bi-arrow-repeat me-2"></i>Sync Senjata via HoYoLAB
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('inventory.weapons.sync-hoyolab') }}" method="POST"
                        id="formSyncHoyoLabWeapon">
                        @csrf
                        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

                        <div class="modal-body">
                            {{-- Info Akun --}}
                            <div class="p-3 mb-3"
                                style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 10px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size: 0.85rem; color: var(--text-primary); font-weight: 700;">
                                        <i class="bi bi-person-circle me-1 text-gold"></i>{{ $activeAccount->nickname }}
                                    </span>
                                    <span class="badge"
                                        style="background: rgba(229, 160, 41, 0.15); color: var(--accent-gold); border: 1px solid rgba(229, 160, 41, 0.3);">
                                        UID: {{ $activeAccount->uid }} ({{ strtoupper($activeAccount->server) }})
                                    </span>
                                </div>
                                <div style="font-size: 0.76rem; color: var(--text-secondary);">
                                    Microservice akan menarik seluruh senjata yang sedang dipakai oleh karakter di Battle
                                    Chronicle akun ini.
                                </div>
                            </div>

                            {{-- Indikator Cookie Tersimpan --}}
                            @if (!empty($activeAccount->ltuid_v2) && !empty($activeAccount->ltoken_v2))
                                <div class="alert alert-success d-flex align-items-center mb-3"
                                    style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); color: #86efac; font-size: 0.78rem; border-radius: 8px;">
                                    <i class="bi bi-shield-check fs-5 me-2"></i>
                                    <div>
                                        <strong>Cookie tersimpan!</strong> Anda bisa langsung klik <em>"Mulai
                                            Sinkronisasi"</em> di bawah. Isi form jika ingin memperbarui cookie baru.
                                    </div>
                                </div>
                            @endif

                            {{-- Input ltuid_v2 --}}
                            <div class="mb-3">
                                <label class="form-label genshin-label">Cookie ltuid_v2 <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="ltuid_v2" class="form-control genshin-input"
                                    value="{{ $activeAccount->ltuid_v2 ?? '' }}" placeholder="contoh: 123456789"
                                    {{ empty($activeAccount->ltuid_v2) ? 'required' : '' }}>
                            </div>

                            {{-- Input ltoken_v2 --}}
                            <div class="mb-3">
                                <label class="form-label genshin-label">Cookie ltoken_v2 <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="ltoken_v2" class="form-control genshin-input"
                                    value="{{ $activeAccount->ltoken_v2 ?? '' }}" placeholder="contoh: v2_xxxx..."
                                    {{ empty($activeAccount->ltoken_v2) ? 'required' : '' }}>
                            </div>

                            {{-- Checkbox Simpan Cookie --}}
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="save_credentials" value="1"
                                    id="saveCredentialsCheckWeapon" checked>
                                <label class="form-check-label" for="saveCredentialsCheckWeapon"
                                    style="font-size: 0.78rem; color: var(--text-secondary);">
                                    Simpan cookie ini di akun game agar tidak perlu input ulang di masa depan
                                </label>
                            </div>

                            {{-- Panduan Ambil Cookie --}}
                            <div class="accordion" id="guideAccordionWeapon">
                                <div class="accordion-item"
                                    style="background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); border-radius: 8px;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 px-3" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#guideCollapseWeapon"
                                            style="background: transparent; color: var(--text-muted); font-size: 0.76rem; box-shadow: none;">
                                            <i class="bi bi-question-circle me-1"></i>Cara mengambil cookie dari HoYoLAB
                                        </button>
                                    </h2>
                                    <div id="guideCollapseWeapon" class="accordion-collapse collapse"
                                        data-bs-parent="#guideAccordionWeapon">
                                        <div class="accordion-body p-3"
                                            style="font-size: 0.74rem; color: var(--text-secondary); line-height: 1.5;">
                                            <ol class="ps-3 mb-1">
                                                <li>Buka <a href="https://www.hoyolab.com" target="_blank"
                                                        class="text-gold">hoyolab.com</a> di browser dan pastikan sudah
                                                    login.</li>
                                                <li>Tekan <kbd>F12</kbd> (Inspect Element) &gt; pilih tab
                                                    <strong>Application</strong> (atau <strong>Storage</strong> di Firefox).
                                                </li>
                                                <li>Di sidebar kiri, klik <strong>Cookies</strong> &gt;
                                                    <code>https://www.hoyolab.com</code>.
                                                </li>
                                                <li>Cari dan salin nilai kolom <em>Value</em> dari <code>ltuid_v2</code> dan
                                                    <code>ltoken_v2</code>.
                                                </li>
                                                <li>Pastikan pengaturan <strong>Battle Chronicle</strong> akun Anda di
                                                    HoYoLAB disetel ke publik.</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer genshin-modal-footer">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-genshin btn-genshin-sm" id="btnSubmitSyncWeapon">
                                <span class="btn-text"><i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai
                                    Sinkronisasi</span>
                                <span class="btn-loading d-none"><span
                                        class="spinner-border spinner-border-sm me-1"></span>Sedang Sync...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL SYNC ENKA (UID) SENJATA --}}
        <div class="modal fade" id="modalSyncEnkaWeapon" tabindex="-1" aria-labelledby="modalSyncEnkaWepLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content genshin-modal-content">
                    <div class="modal-header genshin-modal-header">
                        <h5 class="modal-title font-display text-gold" id="modalSyncEnkaWepLabel">
                            <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Senjata via Enka.Network
                            (UID)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('inventory.weapons.sync-enka') }}" method="POST">
                        @csrf
                        <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

                        <div class="modal-body">
                            <div class="p-3 mb-3 rounded"
                                style="background: rgba(229,160,41,0.08); border: 1px solid rgba(229,160,41,0.25);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge" style="background: #22c55e; color: #fff; font-size: 0.72rem;">✨
                                        Level & Refinement Asli</span>
                                    <span class="badge" style="background: #3b82f6; color: #fff; font-size: 0.72rem;">🔒
                                        Tanpa Cookie / Password</span>
                                </div>
                                <p class="mb-0 text-white" style="font-size: 0.82rem; line-height: 1.5;">
                                    Tarik otomatis senjata yang sedang terpasang pada karakter di <strong>Character
                                        Showcase</strong> in-game profil Genshin Impact lengkap dengan level senjata,
                                    ascension phase, dan tingkat refinement (R1 - R5).
                                </p>
                            </div>

                            {{-- Info Akun --}}
                            <div class="p-2 mb-3 rounded d-flex justify-content-between align-items-center"
                                style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                                <span style="font-size: 0.85rem; color: var(--text-primary); font-weight: 700;">
                                    <i class="bi bi-person-circle me-1 text-gold"></i>{{ $activeAccount->nickname }}
                                </span>
                                <span class="badge"
                                    style="background: rgba(229, 160, 41, 0.15); color: var(--accent-gold); border: 1px solid rgba(229, 160, 41, 0.3);">
                                    Server: {{ ucfirst($activeAccount->server ?? 'Asia') }}
                                </span>
                            </div>

                            {{-- Input UID --}}
                            <div class="mb-3">
                                <label class="form-label genshin-label">UID Akun Genshin Impact <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="uid" class="form-control genshin-input"
                                    value="{{ $activeAccount->uid }}" placeholder="contoh: 809404073" required>
                                <div class="form-text text-white-50 mt-1" style="font-size: 0.74rem;">
                                    <i class="bi bi-info-circle me-1 text-gold"></i>Pastikan opsi <em>"Tampilkan Detail
                                        Karakter"</em> / <em>"Show Character Details"</em> aktif di profil in-game.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer genshin-modal-footer">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-white"
                                data-bs-dismiss="modal">Batal</button>
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
            document.addEventListener('DOMContentLoaded', function() {
                const modalEdit = document.getElementById('modalEditInventoryWeapon');
                if (modalEdit) {
                    modalEdit.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const id = button.getAttribute('data-id');
                        const name = button.getAttribute('data-name');
                        const level = button.getAttribute('data-level');
                        const ascension = button.getAttribute('data-ascension');
                        const refine = button.getAttribute('data-refine');
                        const char = button.getAttribute('data-char');
                        const notes = button.getAttribute('data-notes');

                        const form = document.getElementById('formEditInventoryWeapon');
                        form.action = `/inventory/weapons/${id}`;

                        document.getElementById('editWepTitle').innerText = name;
                        document.getElementById('editWepLevel').value = level;
                        document.getElementById('editWepAscension').value = ascension;
                        document.getElementById('editWepRefine').value = refine;
                        document.getElementById('editWepEquipped').value = char || '';
                        document.getElementById('editWepNotes').value = notes || '';
                    });
                }

                document.querySelectorAll('.btn-delete-inv-wep').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const form = this.closest('form');
                        const name = this.getAttribute('data-name');

                        Swal.fire({
                            title: 'Hapus Senjata?',
                            text: `Senjata "${name}" akan dihapus dari inventori akun ini.`,
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
                const formSyncWep = document.getElementById('formSyncHoyoLabWeapon');
                if (formSyncWep) {
                    formSyncWep.addEventListener('submit', function() {
                        const btn = document.getElementById('btnSubmitSyncWeapon');
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
