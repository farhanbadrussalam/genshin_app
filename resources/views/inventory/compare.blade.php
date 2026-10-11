@extends('layout.main')

@section('content')
@php $title = 'Komparasi Karakter 2 Akun'; @endphp
@include('layout.header')

@push('styles')
<style>
    .compare-page-container {
        padding-top: 1.5rem;
        padding-bottom: 5rem;
    }
    .compare-card-main {
        background: rgba(15, 18, 33, 0.9);
        border: 1px solid rgba(200, 170, 110, 0.35);
        border-radius: 20px;
        backdrop-filter: blur(14px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.65);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .hero-element-pyro    { background: linear-gradient(135deg, rgba(239, 68, 68, 0.25) 0%, rgba(249, 115, 22, 0.15) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .hero-element-hydro   { background: linear-gradient(135deg, rgba(14, 165, 233, 0.25) 0%, rgba(59, 130, 246, 0.15) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .hero-element-anemo   { background: linear-gradient(135deg, rgba(20, 184, 166, 0.25) 0%, rgba(45, 212, 191, 0.15) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .hero-element-electro { background: linear-gradient(135deg, rgba(168, 85, 247, 0.25) 0%, rgba(192, 132, 252, 0.15) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .hero-element-dendro  { background: linear-gradient(135deg, rgba(34, 197, 94, 0.25) 0%, rgba(132, 204, 22, 0.15) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .hero-element-cryo    { background: linear-gradient(135deg, rgba(56, 189, 248, 0.25) 0%, rgba(165, 243, 252, 0.15) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .hero-element-geo     { background: linear-gradient(135deg, rgba(245, 158, 11, 0.25) 0%, rgba(251, 191, 36, 0.15) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .hero-element-default { background: linear-gradient(135deg, rgba(200, 170, 110, 0.2) 0%, rgba(30, 41, 59, 0.2) 50%, rgba(15, 18, 33, 0.95) 100%); }
    .char-avatar-hero {
        width: 84px;
        height: 84px;
        border-radius: 18px;
        object-fit: cover;
        background: rgba(0, 0, 0, 0.4);
        border: 2px solid rgba(200, 170, 110, 0.5);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    }
    .account-column-box {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 16px;
        padding: 1.25rem;
        height: 100%;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .account-column-box:hover {
        background: rgba(255, 255, 255, 0.035);
        border-color: rgba(200, 170, 110, 0.3);
    }
    .weapon-img-box {
        width: 62px;
        height: 62px;
        border-radius: 12px;
        object-fit: cover;
        background: rgba(0, 0, 0, 0.35);
        flex-shrink: 0;
    }
    .badge-constellation {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
        background: rgba(245, 158, 11, 0.18);
        border: 1px solid rgba(245, 158, 11, 0.45);
        color: #fef08a;
    }
    .badge-refinement {
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(168, 85, 247, 0.2);
        border: 1px solid rgba(168, 85, 247, 0.45);
        color: #e9d5ff;
    }
    .talent-pill {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        min-width: 55px;
    }
    .talent-crown { color: #ffd700; font-weight: 800; }
    .artifact-slot-row {
        background: rgba(0, 0, 0, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 0.85rem;
        margin-bottom: 0.75rem;
        transition: border-color 0.2s ease;
    }
    .artifact-slot-row:hover { border-color: rgba(200, 170, 110, 0.25); }
    .art-piece-img {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
        background: rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        flex-shrink: 0;
    }
    .substat-badge {
        font-size: 0.75rem;
        padding: 0.2rem 0.45rem;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #cbd5e1;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .substat-crit {
        background: rgba(245, 158, 11, 0.15) !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
        color: #fef08a !important;
        font-weight: 600;
    }
    .score-badge-sm {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        border-radius: 6px;
    }
    .rating-OP { background: #ff2d55; color: #fff; }
    .rating-SS { background: #af52de; color: #fff; }
    .rating-S  { background: #5856d6; color: #fff; }
    .rating-A  { background: #007aff; color: #fff; }
    .rating-B  { background: #34c759; color: #fff; }
    .rating-C  { background: #ff9500; color: #fff; }
    .rating-D  { background: #8e8e93; color: #fff; }
    .rarity-bg-5 { border-color: rgba(229, 160, 41, 0.6) !important; }
    .rarity-bg-4 { border-color: rgba(168, 85, 247, 0.6) !important; }
    .rarity-bg-3 { border-color: rgba(56, 189, 248, 0.5) !important; }
    .vs-divider-badge {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #c8aa6e, #dfc085);
        color: #0f1221;
        font-weight: 800;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(200, 170, 110, 0.4);
    }
</style>
@endpush
<main class="page-container compare-page-container">
    {{-- Header Judul & Toolbar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 font-display text-gold mb-1 d-flex align-items-center">
                <i class="bi bi-arrow-left-right me-2"></i>Komparasi Build 2 Akun
            </h1>
            <p class="small text-secondary mb-0" style="color: #cbd5e1 !important;">
                Bandingkan 1 karakter secara berdampingan dalam satu card: status level, konstelasi, talent, senjata, set bonus & detail ke-5 slot artefak.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('inventory.characters.index') }}" class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-person-badge-fill"></i>
                <span>Inventori Karakter</span>
            </a>
            <a href="{{ route('build-planner.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 text-light">
                <i class="bi bi-person-gear text-gold"></i>
                <span>Build Planner</span>
            </a>
        </div>
    </div>

    {{-- Form Filter Pemilih 2 Akun & Karakter --}}
    <div class="card mb-4" style="background: rgba(19, 23, 42, 0.85); border: 1px solid rgba(200, 170, 110, 0.25); border-radius: 14px;">
        <div class="card-body p-3">
            <form action="{{ route('inventory.compare') }}" method="GET" id="compareForm" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label text-gold small fw-bold mb-1">
                        <i class="bi bi-person-fill me-1"></i>Akun 1 (Sisi Kiri)
                    </label>
                    <select class="form-select form-select-sm genshin-select" name="account1_id" id="account1_id" onchange="this.form.submit()">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ ($account1 && $account1->id == $acc->id) ? 'selected' : '' }}>
                                {{ $acc->nickname }} (UID: {{ $acc->uid }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label text-gold small fw-bold mb-1">
                        <i class="bi bi-person-bounding-box me-1"></i>Pilih Karakter yang Dibandingkan
                    </label>
                    <select class="form-select form-select-sm genshin-select" name="character_id" id="character_id" onchange="this.form.submit()">
                        <optgroup label="✦ Dimiliki Kedua Akun (Rekomendasi)">
                            @foreach($allCharacters->where('owned_both', true) as $c)
                                <option value="{{ $c->id }}" {{ ($selectedCharacter && $selectedCharacter->id == $c->id) ? 'selected' : '' }}>
                                    ★ {{ $c->name }} ({{ $c->element }} &bull; {{ $c->rarity }}★)
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="✦ Semua Karakter">
                            @foreach($allCharacters as $c)
                                @if(!$c->owned_both)
                                    <option value="{{ $c->id }}" {{ ($selectedCharacter && $selectedCharacter->id == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->element }} &bull; {{ $c->rarity }}★) {{ $c->owned_1 ? '• [Akun 1]' : ($c->owned_2 ? '• [Akun 2]' : '') }}
                                    </option>
                                @endif
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label text-gold small fw-bold mb-0">
                            <i class="bi bi-person-fill me-1"></i>Akun 2 (Sisi Kanan)
                        </label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-warning small" onclick="swapAccounts()" title="Tukar posisi Akun 1 & Akun 2">
                            <i class="bi bi-arrow-left-right me-1"></i>Tukar Posisi
                        </button>
                    </div>
                    <select class="form-select form-select-sm genshin-select" name="account2_id" id="account2_id" onchange="this.form.submit()">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ ($account2 && $account2->id == $acc->id) ? 'selected' : '' }}>
                                {{ $acc->nickname }} (UID: {{ $acc->uid }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if(!$selectedCharacter)
        <div class="alert alert-warning">Silakan pilih karakter untuk dibandingkan.</div>
    @else
        @php
            $elemClass = match(strtolower($selectedCharacter->element ?? '')) {
                'pyro'    => 'hero-element-pyro',
                'hydro'   => 'hero-element-hydro',
                'anemo'   => 'hero-element-anemo',
                'electro' => 'hero-element-electro',
                'dendro'  => 'hero-element-dendro',
                'cryo'    => 'hero-element-cryo',
                'geo'     => 'hero-element-geo',
                default   => 'hero-element-default',
            };
        @endphp

        {{-- 1 UNIFIED COMPARISON CARD --}}
        <div class="compare-card-main">
            {{-- 1. HERO HEADER KARAKTER --}}
            <div class="p-3 p-md-4 border-bottom border-secondary border-opacity-25 {{ $elemClass }}">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $selectedCharacter->icon_url ?: 'https://gi.yatta.moe/assets/UI/UI_AvatarIcon_0.png' }}" 
                             alt="{{ $selectedCharacter->name }}" 
                             class="char-avatar-hero">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h2 class="h4 font-display text-white mb-0 fw-bold">{{ $selectedCharacter->name }}</h2>
                                <span class="badge" style="background: rgba(229, 160, 41, 0.2); border: 1px solid rgba(229, 160, 41, 0.4); color: #ffd700;">
                                    {{ str_repeat('★', $selectedCharacter->rarity) }}
                                </span>
                                <span class="badge border border-secondary text-uppercase small" style="background: rgba(0,0,0,0.3); font-size: 0.72rem;">
                                    {{ $selectedCharacter->element }}
                                </span>
                                <span class="badge border border-secondary text-capitalize small" style="background: rgba(0,0,0,0.3); font-size: 0.72rem;">
                                    {{ $selectedCharacter->weapon_type }}
                                </span>
                            </div>
                            <div class="small text-secondary" style="color: #cbd5e1 !important;">
                                {{ $selectedCharacter->region ?? 'Teyvat' }} &bull; {{ $selectedCharacter->title ?? 'Character Profile' }}
                            </div>
                        </div>
                    </div>

                    @if(!empty($comparison))
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if(isset($comparison['cv_winner']) && $comparison['cv_winner'] > 0)
                                <div class="px-3 py-2 rounded-3 text-center" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(200, 170, 110, 0.3);">
                                    <span class="d-block text-secondary small" style="font-size: 0.72rem;">Highest Crit Value</span>
                                    <span class="text-gold fw-bold small">
                                        {{ $comparison['cv_winner'] === 1 ? ($account1?->nickname ?? 'Akun 1') : ($account2?->nickname ?? 'Akun 2') }}
                                        (+{{ $comparison['cv_diff'] }} CV)
                                    </span>
                                </div>
                            @endif
                            @if(isset($comparison['const_winner']) && $comparison['const_winner'] > 0)
                                <div class="px-3 py-2 rounded-3 text-center" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(245, 158, 11, 0.3);">
                                    <span class="d-block text-secondary small" style="font-size: 0.72rem;">Konstelasi Tertinggi</span>
                                    <span class="text-warning fw-bold small">
                                        {{ $comparison['const_winner'] === 1 ? ($account1?->nickname ?? 'Akun 1') : ($account2?->nickname ?? 'Akun 2') }}
                                        (+{{ $comparison['const_diff'] }} Const)
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. BAR HEADER AKUN 1 VS AKUN 2 --}}
            <div class="p-3 border-bottom border-secondary border-opacity-25" style="background: rgba(0, 0, 0, 0.25);">
                <div class="row align-items-center g-2 text-center text-md-start">
                    <div class="col-5">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle p-2 d-none d-sm-flex align-items-center justify-content-center" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; width: 34px; height: 34px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-white fs-6 d-block">{{ $account1?->nickname ?? 'Akun 1' }}</span>
                                <span class="small text-secondary" style="font-size: 0.75rem;">UID: {{ $account1?->uid ?? '-' }} &bull; {{ strtoupper($account1?->server ?? '') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 text-center d-flex justify-content-center">
                        <div class="vs-divider-badge">VS</div>
                    </div>
                    <div class="col-5 text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <div class="text-end">
                                <span class="fw-bold text-white fs-6 d-block">{{ $account2?->nickname ?? 'Akun 2' }}</span>
                                <span class="small text-secondary" style="font-size: 0.75rem;">UID: {{ $account2?->uid ?? '-' }} &bull; {{ strtoupper($account2?->server ?? '') }}</span>
                            </div>
                            <div class="rounded-circle p-2 d-none d-sm-flex align-items-center justify-content-center" style="background: rgba(168, 85, 247, 0.2); color: #c084fc; width: 34px; height: 34px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- 3. KONTEN PERBANDINGAN BERDAMPINGAN --}}
            <div class="p-3 p-md-4">
                
                {{-- 1. STATUS KARAKTER & TALENT --}}
                <div class="mb-4">
                    <h5 class="text-gold font-display fs-6 mb-3 d-flex align-items-center">
                        <i class="bi bi-person-lines-fill me-2"></i>1. Status Karakter, Konstelasi & Talent
                    </h5>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="account-column-box">
                                @if($build1 && $build1['is_owned'])
                                    @php $char1 = $build1['character']; @endphp
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <span class="text-secondary small d-block">Level & Ascension</span>
                                            <span class="fs-5 fw-bold text-white">Lv. {{ $char1->level }}</span>
                                            <span class="text-muted small">/ {{ $char1->max_level ?? 90 }} (A{{ $char1->ascension }})</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-secondary small d-block">Konstelasi</span>
                                            <span class="badge-constellation">
                                                <i class="bi bi-stars"></i> C{{ $char1->constellation }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-top border-secondary border-opacity-25">
                                        <span class="text-secondary small d-block mb-2">Level Talent (Normal &bull; Skill &bull; Burst)</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="talent-pill flex-fill">
                                                <span class="text-muted" style="font-size: 0.68rem;">Normal</span>
                                                <span class="fw-bold {{ ($char1->talent_attack >= 10) ? 'talent-crown' : 'text-white' }}">
                                                    Lv. {{ $char1->talent_attack ?: '-' }}
                                                </span>
                                            </div>
                                            <div class="talent-pill flex-fill">
                                                <span class="text-muted" style="font-size: 0.68rem;">Skill</span>
                                                <span class="fw-bold {{ ($char1->talent_skill >= 10) ? 'talent-crown' : 'text-white' }}">
                                                    Lv. {{ $char1->talent_skill ?: '-' }}
                                                </span>
                                            </div>
                                            <div class="talent-pill flex-fill">
                                                <span class="text-muted" style="font-size: 0.68rem;">Burst</span>
                                                <span class="fw-bold {{ ($char1->talent_burst >= 10) ? 'talent-crown' : 'text-white' }}">
                                                    Lv. {{ $char1->talent_burst ?: '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-x-circle fs-3 d-block mb-1 text-danger opacity-75"></i>
                                        <span>Karakter ini belum dimiliki di akun {{ $account1?->nickname ?? 'Akun 1' }}.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="account-column-box">
                                @if($build2 && $build2['is_owned'])
                                    @php $char2 = $build2['character']; @endphp
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <span class="text-secondary small d-block">Level & Ascension</span>
                                            <span class="fs-5 fw-bold text-white">Lv. {{ $char2->level }}</span>
                                            <span class="text-muted small">/ {{ $char2->max_level ?? 90 }} (A{{ $char2->ascension }})</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-secondary small d-block">Konstelasi</span>
                                            <span class="badge-constellation">
                                                <i class="bi bi-stars"></i> C{{ $char2->constellation }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-top border-secondary border-opacity-25">
                                        <span class="text-secondary small d-block mb-2">Level Talent (Normal &bull; Skill &bull; Burst)</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="talent-pill flex-fill">
                                                <span class="text-muted" style="font-size: 0.68rem;">Normal</span>
                                                <span class="fw-bold {{ ($char2->talent_attack >= 10) ? 'talent-crown' : 'text-white' }}">
                                                    Lv. {{ $char2->talent_attack ?: '-' }}
                                                </span>
                                            </div>
                                            <div class="talent-pill flex-fill">
                                                <span class="text-muted" style="font-size: 0.68rem;">Skill</span>
                                                <span class="fw-bold {{ ($char2->talent_skill >= 10) ? 'talent-crown' : 'text-white' }}">
                                                    Lv. {{ $char2->talent_skill ?: '-' }}
                                                </span>
                                            </div>
                                            <div class="talent-pill flex-fill">
                                                <span class="text-muted" style="font-size: 0.68rem;">Burst</span>
                                                <span class="fw-bold {{ ($char2->talent_burst >= 10) ? 'talent-crown' : 'text-white' }}">
                                                    Lv. {{ $char2->talent_burst ?: '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-x-circle fs-3 d-block mb-1 text-danger opacity-75"></i>
                                        <span>Karakter ini belum dimiliki di akun {{ $account2?->nickname ?? 'Akun 2' }}.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. SENJATA YANG SEDANG DIGUNAKAN --}}
                <div class="mb-4">
                    <h5 class="text-gold font-display fs-6 mb-3 d-flex align-items-center">
                        <i class="bi bi-shield-shaded me-2"></i>2. Senjata yang Sedang Digunakan (Equipped Weapon)
                    </h5>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="account-column-box">
                                @if($build1 && $build1['weapon'] && $build1['weapon']->weapon)
                                    @php $w1 = $build1['weapon']; $mWeap1 = $w1->weapon; @endphp
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $mWeap1->icon_url ?: 'https://gi.yatta.moe/assets/UI/UI_EquipIcon_Sword_0.png' }}" 
                                             alt="{{ $mWeap1->name }}" 
                                             class="weapon-img-box rarity-bg-{{ $mWeap1->rarity }}">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <h6 class="text-white fw-bold mb-0">{{ $mWeap1->name }}</h6>
                                                <span class="badge-refinement">R{{ $w1->refinement }}</span>
                                                <span class="badge bg-dark border border-secondary text-gold small" style="font-size: 0.7rem;">
                                                    {{ str_repeat('★', $mWeap1->rarity) }}
                                                </span>
                                            </div>
                                            <div class="small text-secondary mb-1">
                                                Level: <span class="text-white fw-bold">Lv. {{ $w1->level }}</span> / {{ $w1->max_level ?? 90 }} (A{{ $w1->ascension }})
                                            </div>
                                            <div class="small text-muted text-truncate" style="max-width: 320px; font-size: 0.75rem;">
                                                Tipe: {{ ucfirst($mWeap1->type) }} &bull; {{ $mWeap1->sub_stat_type ?? 'Sub-stat Weapon' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-shield-slash fs-4 d-block mb-1 opacity-50"></i>
                                        <span>Tidak ada senjata yang terpasang di akun ini.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="account-column-box">
                                @if($build2 && $build2['weapon'] && $build2['weapon']->weapon)
                                    @php $w2 = $build2['weapon']; $mWeap2 = $w2->weapon; @endphp
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $mWeap2->icon_url ?: 'https://gi.yatta.moe/assets/UI/UI_EquipIcon_Sword_0.png' }}" 
                                             alt="{{ $mWeap2->name }}" 
                                             class="weapon-img-box rarity-bg-{{ $mWeap2->rarity }}">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <h6 class="text-white fw-bold mb-0">{{ $mWeap2->name }}</h6>
                                                <span class="badge-refinement">R{{ $w2->refinement }}</span>
                                                <span class="badge bg-dark border border-secondary text-gold small" style="font-size: 0.7rem;">
                                                    {{ str_repeat('★', $mWeap2->rarity) }}
                                                </span>
                                            </div>
                                            <div class="small text-secondary mb-1">
                                                Level: <span class="text-white fw-bold">Lv. {{ $w2->level }}</span> / {{ $w2->max_level ?? 90 }} (A{{ $w2->ascension }})
                                            </div>
                                            <div class="small text-muted text-truncate" style="max-width: 320px; font-size: 0.75rem;">
                                                Tipe: {{ ucfirst($mWeap2->type) }} &bull; {{ $mWeap2->sub_stat_type ?? 'Sub-stat Weapon' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-shield-slash fs-4 d-block mb-1 opacity-50"></i>
                                        <span>Tidak ada senjata yang terpasang di akun ini.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. RINGKASAN SET BONUS & STATS ARTEFAK --}}
                <div class="mb-4">
                    <h5 class="text-gold font-display fs-6 mb-3 d-flex align-items-center">
                        <i class="bi bi-trophy-fill me-2"></i>3. Ringkasan Build Artefak (Set Bonus, CV & Score)
                    </h5>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="account-column-box">
                                <div class="row g-2 text-center mb-3">
                                    <div class="col-4">
                                        <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block text-secondary small" style="font-size: 0.7rem;">Total Artifact CV</span>
                                            <span class="fs-5 fw-bold text-gold">{{ $build1['total_cv'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block text-secondary small" style="font-size: 0.7rem;">Total CRIT (R / D)</span>
                                            <span class="fw-bold text-white small" style="font-size: 0.85rem;">
                                                {{ $build1['total_crit_rate'] ?? 0 }}% / {{ $build1['total_crit_dmg'] ?? 0 }}%
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block text-secondary small" style="font-size: 0.7rem;">Total Artifact Score</span>
                                            <span class="fs-5 fw-bold text-info">{{ $build1['total_artifact_score'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-secondary small d-block mb-1">Set Bonus yang Aktif:</span>
                                    @if(!empty($build1['active_set_bonuses']))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($build1['active_set_bonuses'] as $sb)
                                                <span class="badge" style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #86efac; font-size: 0.76rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>{{ $sb['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">Tidak ada set bonus aktif.</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="account-column-box">
                                <div class="row g-2 text-center mb-3">
                                    <div class="col-4">
                                        <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block text-secondary small" style="font-size: 0.7rem;">Total Artifact CV</span>
                                            <span class="fs-5 fw-bold text-gold">{{ $build2['total_cv'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block text-secondary small" style="font-size: 0.7rem;">Total CRIT (R / D)</span>
                                            <span class="fw-bold text-white small" style="font-size: 0.85rem;">
                                                {{ $build2['total_crit_rate'] ?? 0 }}% / {{ $build2['total_crit_dmg'] ?? 0 }}%
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                                            <span class="d-block text-secondary small" style="font-size: 0.7rem;">Total Artifact Score</span>
                                            <span class="fs-5 fw-bold text-info">{{ $build2['total_artifact_score'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-secondary small d-block mb-1">Set Bonus yang Aktif:</span>
                                    @if(!empty($build2['active_set_bonuses']))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($build2['active_set_bonuses'] as $sb)
                                                <span class="badge" style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #86efac; font-size: 0.76rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>{{ $sb['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">Tidak ada set bonus aktif.</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- 4. DETAIL LENGKAP 5 SLOT ARTEFAK BERDAMPINGAN --}}
                @php
                    $artifactSlotKeys = [
                        'flower'  => ['label' => 'Flower of Life (Bunga)', 'icon' => 'bi-flower1'],
                        'plume'   => ['label' => 'Plume of Death (Bulu)',  'icon' => 'bi-feather'],
                        'sands'   => ['label' => 'Sands of Eon (Jam)',     'icon' => 'bi-hourglass-split'],
                        'goblet'  => ['label' => 'Goblet of Eonothem (Piala)', 'icon' => 'bi-cup-straw'],
                        'circlet' => ['label' => 'Circlet of Logos (Mahkota)', 'icon' => 'bi-circle-half'],
                    ];
                @endphp

                <div class="mb-4">
                    <h5 class="text-gold font-display fs-6 mb-3 d-flex align-items-center">
                        <i class="bi bi-flower1 me-2"></i>4. Detail Sub-Stats & Main-Stats ke-5 Slot Artefak
                    </h5>

                    @foreach($artifactSlotKeys as $slotKey => $slotMeta)
                        @php
                            $art1 = $build1['artifacts'][$slotKey] ?? null;
                            $art2 = $build2['artifacts'][$slotKey] ?? null;
                        @endphp

                        <div class="artifact-slot-row">
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom border-secondary border-opacity-25">
                                <span class="fw-bold text-gold small d-flex align-items-center">
                                    <i class="bi {{ $slotMeta['icon'] }} me-1"></i>{{ $slotMeta['label'] }}
                                </span>
                                <span class="badge bg-dark border border-secondary text-secondary small text-uppercase" style="font-size: 0.68rem;">
                                    Slot: {{ $slotKey }}
                                </span>
                            </div>

                            <div class="row g-3">
                                {{-- Slot Akun 1 --}}
                                <div class="col-12 col-md-6 border-end-md border-secondary border-opacity-25">
                                    @if($art1)
                                        <div class="d-flex align-items-start gap-2">
                                            <img src="{{ $art1->piece_icon_url ?: $art1->icon_url ?: 'https://enka.network/ui/UI_RelicIcon_10001_4.png' }}" 
                                                 alt="{{ $art1->artifactSet?->name ?? 'Artifact' }}" 
                                                 class="art-piece-img rarity-bg-{{ $art1->rarity }}">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                                    <div>
                                                        <span class="fw-bold text-white small d-block">{{ $art1->artifactSet?->name ?? 'Artifact Set' }}</span>
                                                        <span class="text-muted" style="font-size: 0.7rem;">+{{ $art1->level }} &bull; {{ str_repeat('★', $art1->rarity) }}</span>
                                                    </div>
                                                    @if($art1->score !== null)
                                                        <span class="score-badge-sm rating-{{ $art1->score_rating ?? 'A' }}">
                                                            {{ $art1->score_rating ?? 'A' }} ({{ round($art1->score, 1) }})
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mb-1">
                                                    <span class="badge" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #7dd3fc; font-size: 0.72rem;">
                                                        Main: {{ $art1->main_stat_label }} {{ $art1->main_stat_value }}
                                                    </span>
                                                </div>

                                                @if(!empty($art1->sub_stats) && is_array($art1->sub_stats))
                                                    <div class="d-flex flex-wrap gap-1 pt-1">
                                                        @foreach($art1->sub_stats as $sub)
                                                            @php
                                                                $k = strtolower($sub['key'] ?? '');
                                                                $isCrit = in_array($k, ['crit_rate', 'critrate', 'critrate_', 'cr', 'crit_dmg', 'critdmg', 'critdmg_', 'cd']);
                                                                $valStr = is_numeric($sub['value']) ? (float)$sub['value'] : $sub['value'];
                                                                if (str_contains($k, 'percent') || str_contains($k, 'pct') || str_contains($k, 'crit') || str_contains($k, 'er')) {
                                                                    if (!str_ends_with((string)$valStr, '%')) $valStr .= '%';
                                                                }
                                                                $labelKey = match($k) {
                                                                    'crit_rate', 'cr' => 'CRIT Rate',
                                                                    'crit_dmg', 'cd'  => 'CRIT DMG',
                                                                    'atk_percent', 'atk_pct' => 'ATK%',
                                                                    'hp_percent', 'hp_pct' => 'HP%',
                                                                    'def_percent', 'def_pct' => 'DEF%',
                                                                    'energy_recharge', 'er' => 'ER',
                                                                    'elemental_mastery', 'em' => 'EM',
                                                                    'atk', 'flat_atk' => 'Flat ATK',
                                                                    'hp', 'flat_hp' => 'Flat HP',
                                                                    'def', 'flat_def' => 'Flat DEF',
                                                                    default => strtoupper(str_replace('_', ' ', $k)),
                                                                };
                                                            @endphp
                                                            <span class="substat-badge {{ $isCrit ? 'substat-crit' : '' }}">
                                                                @if($isCrit)<i class="bi bi-star-fill text-warning" style="font-size: 0.6rem;"></i>@endif
                                                                {{ $labelKey }}: +{{ $valStr }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="py-2 text-muted small text-center">
                                            <i class="bi bi-dash-circle me-1"></i>Slot ini kosong di akun {{ $account1?->nickname ?? 'Akun 1' }}.
                                        </div>
                                    @endif
                                </div>

                                {{-- Slot Akun 2 --}}
                                <div class="col-12 col-md-6">
                                    @if($art2)
                                        <div class="d-flex align-items-start gap-2">
                                            <img src="{{ $art2->piece_icon_url ?: $art2->icon_url ?: 'https://enka.network/ui/UI_RelicIcon_10001_4.png' }}" 
                                                 alt="{{ $art2->artifactSet?->name ?? 'Artifact' }}" 
                                                 class="art-piece-img rarity-bg-{{ $art2->rarity }}">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                                    <div>
                                                        <span class="fw-bold text-white small d-block">{{ $art2->artifactSet?->name ?? 'Artifact Set' }}</span>
                                                        <span class="text-muted" style="font-size: 0.7rem;">+{{ $art2->level }} &bull; {{ str_repeat('★', $art2->rarity) }}</span>
                                                    </div>
                                                    @if($art2->score !== null)
                                                        <span class="score-badge-sm rating-{{ $art2->score_rating ?? 'A' }}">
                                                            {{ $art2->score_rating ?? 'A' }} ({{ round($art2->score, 1) }})
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mb-1">
                                                    <span class="badge" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #7dd3fc; font-size: 0.72rem;">
                                                        Main: {{ $art2->main_stat_label }} {{ $art2->main_stat_value }}
                                                    </span>
                                                </div>

                                                @if(!empty($art2->sub_stats) && is_array($art2->sub_stats))
                                                    <div class="d-flex flex-wrap gap-1 pt-1">
                                                        @foreach($art2->sub_stats as $sub)
                                                            @php
                                                                $k = strtolower($sub['key'] ?? '');
                                                                $isCrit = in_array($k, ['crit_rate', 'critrate', 'critrate_', 'cr', 'crit_dmg', 'critdmg', 'critdmg_', 'cd']);
                                                                $valStr = is_numeric($sub['value']) ? (float)$sub['value'] : $sub['value'];
                                                                if (str_contains($k, 'percent') || str_contains($k, 'pct') || str_contains($k, 'crit') || str_contains($k, 'er')) {
                                                                    if (!str_ends_with((string)$valStr, '%')) $valStr .= '%';
                                                                }
                                                                $labelKey = match($k) {
                                                                    'crit_rate', 'cr' => 'CRIT Rate',
                                                                    'crit_dmg', 'cd'  => 'CRIT DMG',
                                                                    'atk_percent', 'atk_pct' => 'ATK%',
                                                                    'hp_percent', 'hp_pct' => 'HP%',
                                                                    'def_percent', 'def_pct' => 'DEF%',
                                                                    'energy_recharge', 'er' => 'ER',
                                                                    'elemental_mastery', 'em' => 'EM',
                                                                    'atk', 'flat_atk' => 'Flat ATK',
                                                                    'hp', 'flat_hp' => 'Flat HP',
                                                                    'def', 'flat_def' => 'Flat DEF',
                                                                    default => strtoupper(str_replace('_', ' ', $k)),
                                                                };
                                                            @endphp
                                                            <span class="substat-badge {{ $isCrit ? 'substat-crit' : '' }}">
                                                                @if($isCrit)<i class="bi bi-star-fill text-warning" style="font-size: 0.6rem;"></i>@endif
                                                                {{ $labelKey }}: +{{ $valStr }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="py-2 text-muted small text-center">
                                            <i class="bi bi-dash-circle me-1"></i>Slot ini kosong di akun {{ $account2?->nickname ?? 'Akun 2' }}.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 5. VERDICT / PERBANDINGAN KEUNGGULAN --}}
                <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(200, 170, 110, 0.25);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check2-all text-gold fs-5"></i>
                        <span class="fw-bold text-white small">Rangkuman Hasil Komparasi:</span>
                    </div>
                    <div class="row g-2 text-secondary small">
                        <div class="col-12 col-md-4">
                            • Level Karakter: 
                            @if(isset($comparison['level_winner']))
                                @if($comparison['level_winner'] === 0)
                                    <span class="text-white fw-semibold">Imbang (Sama kuat)</span>
                                @else
                                    <span class="text-success fw-semibold">
                                        {{ $comparison['level_winner'] === 1 ? ($account1?->nickname ?? 'Akun 1') : ($account2?->nickname ?? 'Akun 2') }} lebih tinggi (+{{ $comparison['level_diff'] }} Lv)
                                    </span>
                                @endif
                            @endif
                        </div>
                        <div class="col-12 col-md-4">
                            • Konstelasi: 
                            @if(isset($comparison['const_winner']))
                                @if($comparison['const_winner'] === 0)
                                    <span class="text-white fw-semibold">Imbang</span>
                                @else
                                    <span class="text-warning fw-semibold">
                                        {{ $comparison['const_winner'] === 1 ? ($account1?->nickname ?? 'Akun 1') : ($account2?->nickname ?? 'Akun 2') }} lebih tinggi (C{{ $comparison['const_winner'] === 1 ? ($build1['character']->constellation ?? 0) : ($build2['character']->constellation ?? 0) }})
                                    </span>
                                @endif
                            @endif
                        </div>
                        <div class="col-12 col-md-4">
                            • Crit Value Artefak: 
                            @if(isset($comparison['cv_winner']))
                                @if($comparison['cv_winner'] === 0)
                                    <span class="text-white fw-semibold">Imbang</span>
                                @else
                                    <span class="text-gold fw-semibold">
                                        {{ $comparison['cv_winner'] === 1 ? ($account1?->nickname ?? 'Akun 1') : ($account2?->nickname ?? 'Akun 2') }} unggul (+{{ $comparison['cv_diff'] }} CV)
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif
</main>

@push('scripts')
<script>
    function swapAccounts() {
        const acc1 = document.getElementById('account1_id');
        const acc2 = document.getElementById('account2_id');
        if (acc1 && acc2) {
            const temp = acc1.value;
            acc1.value = acc2.value;
            acc2.value = temp;
            document.getElementById('compareForm').submit();
        }
    }
</script>
@endpush
@endsection