@extends('layout.main')

@section('content')
@php $title = 'Build Planner'; @endphp
@include('layout.header')

@push('styles')
<style>
    /* ─── Build Planner Custom Styles ─── */
    .build-planner-container {
        padding-top: 1.5rem;
        padding-bottom: 3.5rem;
    }

    .bp-card {
        background: rgba(19, 23, 42, 0.85);
        border: 1px solid rgba(200, 170, 110, 0.2);
        border-radius: 14px;
        backdrop-filter: blur(8px);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .bp-card:hover {
        border-color: rgba(200, 170, 110, 0.4);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    .bp-card-header-title {
        color: var(--color-gold, #c8aa6e);
        font-weight: 600;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(200, 170, 110, 0.15);
    }

    /* Text Legibility & High Contrast */
    .bp-text-primary {
        color: #f8fafc !important;
    }
    .bp-text-secondary {
        color: #cbd5e1 !important;
    }
    .bp-text-muted {
        color: #94a3b8 !important;
    }
    .bp-text-gold {
        color: #e8d5a3 !important;
    }

    /* Substat & Role Badges */
    .bp-substat-pill {
        background: rgba(200, 170, 110, 0.15);
        border: 1px solid rgba(200, 170, 110, 0.35);
        color: #fef08a !important;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .bp-role-badge {
        background: rgba(56, 189, 248, 0.15);
        border: 1px solid rgba(56, 189, 248, 0.4);
        color: #7dd3fc !important;
        font-weight: 600;
        padding: 0.4rem 0.9rem;
        border-radius: 8px;
        font-size: 0.85rem;
    }

    .bp-set-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
    }
    .bp-set-badge.active-4 {
        background: rgba(34, 197, 94, 0.2);
        border: 1px solid rgba(34, 197, 94, 0.4);
        color: #86efac !important;
    }
    .bp-set-badge.active-2 {
        background: rgba(59, 130, 246, 0.2);
        border: 1px solid rgba(59, 130, 246, 0.4);
        color: #93c5fd !important;
    }
    .bp-set-badge.inactive {
        background: rgba(148, 163, 184, 0.15);
        border: 1px solid rgba(148, 163, 184, 0.3);
        color: #cbd5e1 !important;
    }

    /* Hero Character Box */
    .bp-hero-card {
        background: linear-gradient(135deg, rgba(26, 31, 53, 0.9) 0%, rgba(15, 18, 32, 0.95) 100%);
        border: 1px solid rgba(200, 170, 110, 0.3);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    .bp-char-avatar {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

    /* Weapon & Item Thumbnails */
    .bp-item-thumb {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        background: rgba(0, 0, 0, 0.3);
        flex-shrink: 0;
    }
    .bp-star-5 {
        border: 1px solid rgba(234, 179, 8, 0.6);
        background: rgba(234, 179, 8, 0.12);
    }
    .bp-star-4 {
        border: 1px solid rgba(168, 85, 247, 0.6);
        background: rgba(168, 85, 247, 0.12);
    }

    .bp-equipped-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(200, 170, 110, 0.25);
        border-radius: 10px;
        padding: 0.85rem;
    }

    .bp-opt-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.55rem 0.65rem;
        border-radius: 8px;
        transition: background 0.15s ease;
    }
    .bp-opt-row:hover {
        background: rgba(255, 255, 255, 0.04);
    }

    /* Artifact Slot Cards */
    .bp-slot-card {
        background: rgba(19, 23, 42, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .bp-slot-card:hover {
        border-color: rgba(200, 170, 110, 0.35);
        transform: translateY(-2px);
    }
    .bp-slot-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0.85rem;
        background: rgba(0, 0, 0, 0.25);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px 12px 0 0;
    }

    .bp-candidate-box {
        background: rgba(6, 182, 212, 0.08);
        border: 1px dashed rgba(6, 182, 212, 0.4);
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
    }
    .bp-best-active-box {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.25);
        border-radius: 8px;
        padding: 0.4rem 0.65rem;
    }

    .bp-score-pill {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(200, 170, 110, 0.2);
        color: #fef08a !important;
        border: 1px solid rgba(200, 170, 110, 0.4);
    }

    .bi-feather {
        display: inline-block;
        width: 1em;
        height: 1em;
        vertical-align: -0.125em;
        background-color: currentColor;
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z'/%3E%3Cline x1='16' y1='8' x2='2' y2='22'/%3E%3Cline x1='17.5' y1='15' x2='9' y2='15'/%3E%3C/svg%3E") no-repeat center / contain;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z'/%3E%3Cline x1='16' y1='8' x2='2' y2='22'/%3E%3Cline x1='17.5' y1='15' x2='9' y2='15'/%3E%3C/svg%3E") no-repeat center / contain;
    }
</style>
@endpush

<main class="page-container build-planner-container">
    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 bp-text-gold mb-1 d-flex align-items-center">
                <i class="bi bi-person-gear me-2 text-gold"></i>Build Planner
            </h1>
            <p class="bp-text-secondary mb-0">
                Tinjau rekomendasi build, senjata kompatibel, dan optimasi target artifact dari inventori akun.
            </p>
        </div>
    </div>

    {{-- Filter Selector Panel --}}
    <div class="filter-panel mb-4">
        <form method="GET" action="{{ route('build-planner.index') }}" id="buildFilterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small bp-text-secondary mb-1">
                        <i class="bi bi-person-badge me-1 text-gold"></i>Akun Game
                    </label>
                    <select name="account_id" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
                        @forelse($accounts as $account)
                            <option value="{{ $account->id }}" @selected($activeAccount?->id === $account->id)>
                                {{ $account->nickname }} (UID: {{ $account->uid }})
                            </option>
                        @empty
                            <option value="">Belum ada akun terdaftar</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small bp-text-secondary mb-1">
                        <i class="bi bi-person-check me-1 text-gold"></i>Pilih Karakter
                    </label>
                    <select name="character_id" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
                        @forelse($characters as $char)
                            <option value="{{ $char->id }}" @selected($selectedCharacter?->id === $char->id)>
                                {{ $char->name }} ({{ $char->element }} &bull; {{ $char->weapon_type }})
                            </option>
                        @empty
                            <option value="">Belum ada karakter</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold shadow-sm" style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                        <i class="bi bi-arrow-clockwise me-1"></i>Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if(!$selectedCharacter)
        <div class="alert alert-info bp-text-primary" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.35);">
            <i class="bi bi-info-circle-fill me-2"></i>Tambahkan karakter atau akun Genshin terlebih dahulu untuk mulai merencanakan build.
        </div>
    @else
        @php
            $elemColor = $selectedCharacter->element_color ?? '#94a3b8';
            $is5StarChar = ($selectedCharacter->rarity ?? 4) === 5;
        @endphp

        {{-- Character Hero Card --}}
        <div class="bp-hero-card p-3 p-md-4 mb-4" style="border-left: 5px solid {{ $elemColor }};">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                @if($selectedCharacter->icon_url)
                    <img src="{{ $selectedCharacter->icon_url }}" 
                         alt="{{ $selectedCharacter->name }}" 
                         class="bp-char-avatar {{ $is5StarChar ? 'bp-star-5' : 'bp-star-4' }}"
                         loading="lazy"
                         referrerpolicy="no-referrer"
                         style="border-color: {{ $elemColor }};">
                @endif
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h2 class="h4 bp-text-primary mb-0 fw-bold">{{ $selectedCharacter->name }}</h2>
                        <span class="badge" style="background: {{ $elemColor }}; color: #ffffff; font-weight: 700; font-size: 0.72rem; padding: 0.25rem 0.55rem;">
                            {{ $selectedCharacter->element }}
                        </span>
                        <span class="small" style="color: {{ $is5StarChar ? '#fbbf24' : '#c084fc' }};">
                            @for($i = 0; $i < ($selectedCharacter->rarity ?? 4); $i++)★@endfor
                        </span>
                    </div>
                    <div class="bp-text-secondary small d-flex flex-wrap align-items-center gap-2">
                        <span><i class="bi bi-shield-shaded me-1 text-gold"></i>{{ $selectedCharacter->weapon_type }}</span>
                        <span>&bull;</span>
                        @if($characterInventory)
                            <span class="text-white fw-semibold">Lv. {{ $characterInventory->level }}</span>
                            <span class="badge" style="background: rgba(255, 215, 0, 0.15); border: 1px solid rgba(255, 215, 0, 0.4); color: #fef08a;">
                                C{{ $characterInventory->constellation }}
                            </span>
                        @else
                            <span class="bp-text-muted">Belum ada di inventori akun</span>
                        @endif
                    </div>
                </div>
                <div class="ms-md-auto">
                    <span class="bp-role-badge">
                        <i class="bi bi-crosshair me-1"></i>{{ $guidance['role'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Row: Build Guidance (Prioritas Substat & Target Main Stat) --}}
        <div class="row g-3 mb-4">
            {{-- Prioritas Substat --}}
            <div class="col-lg-6">
                <div class="bp-card h-100 p-3 p-md-4">
                    <div class="bp-card-header-title">
                        <i class="bi bi-stars text-warning"></i>Prioritas Substat
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($guidance['priorities'] as $priority)
                            <span class="bp-substat-pill">
                                <i class="bi bi-check2-circle"></i>{{ $priority }}
                            </span>
                        @endforeach
                    </div>
                    <div class="p-2 rounded" style="background: rgba(0, 0, 0, 0.25); border-left: 3px solid rgba(200, 170, 110, 0.5);">
                        <p class="small bp-text-secondary mb-0">
                            <i class="bi bi-info-circle me-1 text-gold"></i>{{ $guidance['note'] }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Target Main Stat --}}
            <div class="col-lg-6">
                <div class="bp-card h-100 p-3 p-md-4">
                    <div class="bp-card-header-title">
                        <i class="bi bi-bullseye text-info"></i>Target Main Stat per Slot
                    </div>
                    <div class="d-flex flex-column gap-2">
                        @php
                            $slotIcons = [
                                'sands'   => 'bi-hourglass-split',
                                'goblet'  => 'bi-cup-straw',
                                'circlet' => 'bi-circle-half',
                            ];
                        @endphp
                        @foreach($guidance['main_stats'] as $slot => $target)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05);">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi {{ $slotIcons[$slot] ?? 'bi-gem' }} text-gold fs-6"></i>
                                    <span class="bp-text-primary fw-semibold small text-capitalize">{{ $slot }}</span>
                                </div>
                                <span class="bp-text-gold small fw-bold text-end">{{ $target }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Row: Senjata & Artifact Set Aktif --}}
        <div class="row g-3 mb-4">
            {{-- Senjata --}}
            <div class="col-lg-6">
                <div class="bp-card h-100 p-3 p-md-4">
                    <div class="bp-card-header-title">
                        <i class="bi bi-sword text-gold"></i>Rekomendasi & Status Senjata
                    </div>

                    {{-- Senjata Terpasang --}}
                    @if($equippedWeapon?->weapon)
                        @php
                            $weap = $equippedWeapon->weapon;
                            $is5StarWeap = ($weap->rarity ?? 4) === 5;
                        @endphp
                        <div class="bp-equipped-box mb-3">
                            <div class="small bp-text-muted mb-2 text-uppercase fw-bold" style="letter-spacing: 0.05em;">
                                <i class="bi bi-check-circle-fill text-success me-1"></i>Senjata Terpasang
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                @if($weap->icon_url)
                                    <img src="{{ $weap->icon_url }}" 
                                         alt="{{ $weap->name }}" 
                                         class="bp-item-thumb {{ $is5StarWeap ? 'bp-star-5' : 'bp-star-4' }}"
                                         loading="lazy"
                                         referrerpolicy="no-referrer">
                                @endif
                                <div>
                                    <div class="bp-text-primary fw-bold">{{ $weap->name }}</div>
                                    <div class="bp-text-secondary small d-flex flex-wrap align-items-center gap-2">
                                        <span style="color: {{ $is5StarWeap ? '#fbbf24' : '#c084fc' }};">
                                            @for($i = 0; $i < ($weap->rarity ?? 4); $i++)★@endfor
                                        </span>
                                        <span>&bull;</span>
                                        <span class="text-white fw-semibold">Lv. {{ $equippedWeapon->level }}</span>
                                        <span>&bull;</span>
                                        <span class="badge" style="background: rgba(200, 170, 110, 0.2); border: 1px solid rgba(200, 170, 110, 0.4); color: #fef08a;">
                                            R{{ $equippedWeapon->refinement }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning small mb-3 py-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fef08a;">
                            <i class="bi bi-exclamation-triangle me-1"></i>Belum ada senjata yang dipasang pada karakter ini.
                        </div>
                    @endif

                    {{-- Senjata Lain di Inventori --}}
                    <div class="small bp-text-muted mb-2 text-uppercase fw-bold" style="letter-spacing: 0.05em;">
                        Pilihan Senjata {{ $selectedCharacter->weapon_type }} di Inventori:
                    </div>
                    <div class="d-flex flex-column gap-1">
                        @forelse($weaponOptions as $option)
                            @php
                                $optWeap = $option->weapon;
                                $is5StarOpt = ($optWeap?->rarity ?? 4) === 5;
                                $isEquippedNow = $equippedWeapon && $equippedWeapon->id === $option->id;
                            @endphp
                            <div class="bp-opt-row border-bottom border-secondary border-opacity-25">
                                <div class="d-flex align-items-center gap-2">
                                    @if($optWeap?->icon_url)
                                        <img src="{{ $optWeap->icon_url }}" 
                                             alt="" 
                                             class="bp-item-thumb {{ $is5StarOpt ? 'bp-star-5' : 'bp-star-4' }}"
                                             style="width: 32px; height: 32px;"
                                             loading="lazy"
                                             referrerpolicy="no-referrer">
                                    @endif
                                    <div>
                                        <span class="bp-text-primary small fw-semibold">{{ $optWeap?->name }}</span>
                                        @if($isEquippedNow)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;">Terpasang</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="bp-text-secondary small d-flex align-items-center gap-2">
                                    <span style="color: {{ $is5StarOpt ? '#fbbf24' : '#c084fc' }}; font-size: 0.75rem;">
                                        @for($i = 0; $i < ($optWeap?->rarity ?? 4); $i++)★@endfor
                                    </span>
                                    <span class="text-white">Lv. {{ $option->level }}</span>
                                    <span class="badge bg-secondary-subtle text-light border border-secondary" style="font-size: 0.68rem;">
                                        R{{ $option->refinement }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="bp-text-muted small mb-0">Tidak ada senjata {{ $selectedCharacter->weapon_type }} lainnya di inventori akun.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Artifact Set Aktif --}}
            <div class="col-lg-6">
                <div class="bp-card h-100 p-3 p-md-4">
                    <div class="bp-card-header-title">
                        <i class="bi bi-shield-check text-success"></i>Artifact Set Aktif
                    </div>

                    @forelse($setSummary as $set)
                        @php
                            $badgeClass = $set['count'] >= 4 ? 'active-4' : ($set['count'] >= 2 ? 'active-2' : 'inactive');
                            $bonusText = $set['count'] >= 4 ? $set['four_piece_bonus'] : ($set['count'] >= 2 ? $set['two_piece_bonus'] : 'Butuh 1 piece lagi untuk mengaktifkan bonus 2-set.');
                        @endphp
                        <div class="p-3 rounded mb-2" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    @if(!empty($set['icon_url']))
                                        <img src="{{ $set['icon_url'] }}" alt="" class="rounded" width="28" height="28" style="object-fit:cover; background: rgba(0,0,0,0.3);">
                                    @else
                                        <i class="bi bi-gem text-gold"></i>
                                    @endif
                                    <span class="bp-text-primary fw-bold">{{ $set['name'] }}</span>
                                </div>
                                <span class="bp-set-badge {{ $badgeClass }}">
                                    {{ $set['count'] }} Piece @if($set['count'] >= 2)(Aktif)@endif
                                </span>
                            </div>
                            <div class="small bp-text-secondary" style="line-height: 1.45;">
                                {{ $bonusText }}
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-secondary small bp-text-secondary py-3" style="background: rgba(255, 255, 255, 0.04); border: 1px dashed rgba(255, 255, 255, 0.15);">
                            <i class="bi bi-info-circle me-1"></i>Belum ada artifact yang terpasang pada karakter ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Section: Artifact per Slot --}}
        <div class="d-flex align-items-center justify-content-between mt-4 mb-3">
            <h2 class="h5 bp-text-gold mb-0 d-flex align-items-center">
                <i class="bi bi-grid-3x3-gap-fill me-2 text-gold"></i>Status Artifact per Slot
            </h2>
            <span class="bp-text-muted small">
                Diurutkan berdasarkan skor tersimpan & level
            </span>
        </div>

        <div class="row g-3">
            @php
                $slotLabels = [
                    'flower'  => ['label' => 'Flower of Life', 'icon' => 'bi-flower1'],
                    'plume'   => ['label' => 'Plume of Death', 'icon' => 'bi-feather'],
                    'sands'   => ['label' => 'Sands of Eon', 'icon' => 'bi-hourglass-split'],
                    'goblet'  => ['label' => 'Goblet of Eonothem', 'icon' => 'bi-cup-straw'],
                    'circlet' => ['label' => 'Circlet of Logos', 'icon' => 'bi-circle-half'],
                ];
            @endphp

            @foreach($slotLabels as $slot => $meta)
                @php
                    $equipped = $equippedArtifacts->get($slot);
                    $best = $bestArtifacts->get($slot);
                    $isBestEquipped = $equipped && $best && $equipped->id === $best->id;
                @endphp
                <div class="col-md-6 col-xl">
                    <div class="bp-slot-card h-100 d-flex flex-column">
                        {{-- Slot Header --}}
                        <div class="bp-slot-header">
                            <span class="bp-text-gold fw-bold small d-flex align-items-center gap-1">
                                <i class="bi {{ $meta['icon'] }} text-gold"></i>{{ $meta['label'] }}
                            </span>
                            @if($isBestEquipped)
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">
                                    <i class="bi bi-check2"></i>Optimal
                                </span>
                            @endif
                        </div>

                        {{-- Slot Content --}}
                        <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                            {{-- Equipped Item --}}
                            <div class="mb-3">
                                <div class="small bp-text-muted text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                    Terpasang:
                                </div>
                                @if($equipped)
                                    @php
                                        $equippedRarity = $equipped->rarity ?? 5;
                                        $equippedScore = $equipped->score ? number_format($equipped->score, 1) : '-';
                                    @endphp
                                    <div class="d-flex align-items-start gap-2">
                                        @if($equipped->piece_icon_url)
                                            <img src="{{ $equipped->piece_icon_url }}" 
                                                 alt="" 
                                                 class="bp-item-thumb {{ $equippedRarity === 5 ? 'bp-star-5' : 'bp-star-4' }}"
                                                 style="width: 38px; height: 38px;"
                                                 loading="lazy"
                                                 referrerpolicy="no-referrer">
                                        @endif
                                        <div class="overflow-hidden">
                                            <div class="bp-text-primary fw-bold small text-truncate" title="{{ $equipped->artifactSet?->name ?? 'Artifact' }}">
                                                {{ $equipped->artifactSet?->name ?? 'Artifact' }}
                                            </div>
                                            <div class="bp-text-secondary small d-flex flex-wrap align-items-center gap-1 mt-1">
                                                <span class="text-white fw-semibold">Lv. {{ $equipped->level }}</span>
                                                <span>&bull;</span>
                                                <span class="bp-score-pill">
                                                    Skor: {{ $equippedScore }}
                                                </span>
                                            </div>
                                            @if($equipped->main_stat_label)
                                                <div class="bp-text-muted mt-1" style="font-size: 0.72rem;">
                                                    {{ $equipped->main_stat_label }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="p-2 rounded text-warning small" style="background: rgba(245, 158, 11, 0.1); border: 1px dashed rgba(245, 158, 11, 0.3);">
                                        <i class="bi bi-dash-circle me-1"></i>Belum dipasang
                                    </div>
                                @endif
                            </div>

                            {{-- Candidate Recommendation --}}
                            @if($best && (!$equipped || $best->id !== $equipped->id))
                                @php
                                    $bestRarity = $best->rarity ?? 5;
                                    $bestScore = $best->score ? number_format($best->score, 1) : '-';
                                @endphp
                                <div class="bp-candidate-box mt-auto">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-info fw-bold" style="font-size: 0.72rem;">
                                            <i class="bi bi-lightning-charge-fill me-1"></i>Kandidat Terbaik:
                                        </span>
                                    </div>
                                    <div class="bp-text-primary fw-semibold small text-truncate" title="{{ $best->artifactSet?->name ?? 'Artifact' }}">
                                        {{ $best->artifactSet?->name ?? 'Artifact' }}
                                    </div>
                                    <div class="bp-text-secondary small d-flex flex-wrap align-items-center gap-1 mt-1">
                                        <span class="text-white fw-semibold">Lv. {{ $best->level }}</span>
                                        <span>&bull;</span>
                                        <span class="badge" style="background: rgba(6, 182, 212, 0.2); border: 1px solid rgba(6, 182, 212, 0.4); color: #67e8f9;">
                                            Skor: {{ $bestScore }}
                                        </span>
                                    </div>
                                </div>
                            @elseif($isBestEquipped)
                                <div class="bp-best-active-box mt-auto text-center">
                                    <span class="text-success small fw-semibold" style="font-size: 0.75rem;">
                                        <i class="bi bi-shield-fill-check me-1"></i>Sudah yang terbaik di inventori
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Footer Note --}}
        <div class="p-3 rounded mt-4" style="background: rgba(19, 23, 42, 0.6); border: 1px solid rgba(200, 170, 110, 0.15);">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-info-circle-fill text-gold mt-1"></i>
                <div class="small bp-text-secondary">
                    Kandidat artifact terbaik diurutkan otomatis dari skor tersimpan di database lalu level.
                    Gunakan menu <a href="{{ route('artifact-scoring.index') }}" class="text-gold fw-bold text-decoration-none">Artifact Scoring</a> untuk menghitung atau memperbarui bobot skor artifact inventori akun Anda.
                </div>
            </div>
        </div>
    @endif
</main>
@endsection
