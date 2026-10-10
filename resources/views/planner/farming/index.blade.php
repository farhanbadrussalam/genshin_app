@extends('layout.main')

@push('styles')
<style>
    /* Genshin Farming Planner - High Contrast & Dark Theme */
    .farming-container {
        color: #f8fafc !important;
    }
    
    .text-light-contrast {
        color: #f8fafc !important;
    }
    
    .text-muted-contrast {
        color: #cbd5e1 !important;
    }

    /* High-Contrast Gold & Warning Badges (Fixes visibility issue) */
    .badge-kurang {
        background: #f59e0b !important;
        color: #0f172a !important;
        font-weight: 800 !important;
        font-size: 0.76rem !important;
        padding: 5px 11px !important;
        border-radius: 6px !important;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35) !important;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        border: none !important;
        letter-spacing: 0.02em;
    }

    .badge-genshin-gold {
        background: rgba(200, 170, 110, 0.16) !important;
        color: #f6e6ba !important;
        border: 1px solid rgba(200, 170, 110, 0.45) !important;
        font-weight: 600 !important;
    }

    .text-warning-contrast {
        color: #facc15 !important;
        font-weight: 700 !important;
    }

    /* Card Spacing & Appearance */
    .farming-card {
        background: rgba(22, 26, 42, 0.88);
        border: 1px solid rgba(200, 170, 110, 0.25);
        border-radius: 14px;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        padding: 22px 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .farming-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.55);
        border-color: rgba(200, 170, 110, 0.45);
    }

    .farming-card-open {
        border-color: rgba(34, 197, 94, 0.55) !important;
        box-shadow: 0 8px 24px rgba(34, 197, 94, 0.12), 0 0 0 1px rgba(34, 197, 94, 0.2);
    }

    .farming-card-open:hover {
        border-color: rgba(34, 197, 94, 0.8) !important;
        box-shadow: 0 12px 28px rgba(34, 197, 94, 0.2);
    }

    .farming-card-header {
        padding-bottom: 14px;
        margin-bottom: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .metric-card {
        background: rgba(18, 22, 36, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 20px 22px;
        transition: all 0.2s ease;
    }

    .metric-card:hover {
        border-color: rgba(200, 170, 110, 0.35);
        background: rgba(26, 32, 52, 0.9);
    }

    .route-tab-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(200, 170, 110, 0.25);
        color: #cbd5e1;
        font-weight: 500;
        font-size: 0.85rem;
        padding: 7px 16px;
        border-radius: 20px;
        transition: all 0.2s ease;
    }

    .route-tab-btn:hover, .route-tab-btn.active {
        background: linear-gradient(135deg, rgba(200, 170, 110, 0.25), rgba(200, 170, 110, 0.1));
        border-color: #c8aa6e;
        color: #ffffff;
    }

    .material-item-row {
        background: rgba(14, 17, 28, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 10px;
        padding: 12px 16px;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .material-item-row:hover {
        background: rgba(20, 25, 42, 0.8);
        border-color: rgba(200, 170, 110, 0.25);
    }

    .material-img-wrapper {
        width: 40px;
        height: 40px;
        min-width: 40px;
        background: rgba(0, 0, 0, 0.45);
        border-radius: 8px;
        border: 1px solid rgba(200, 170, 110, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .table-farming {
        --bs-table-bg: transparent;
        --bs-table-color: #f8fafc;
        --bs-table-hover-bg: rgba(255, 255, 255, 0.04);
        --bs-table-border-color: rgba(255, 255, 255, 0.08);
        color: #f8fafc !important;
    }

    .table-farming thead th {
        background: rgba(14, 18, 30, 0.95) !important;
        color: #c8aa6e !important;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 2px solid rgba(200, 170, 110, 0.3);
        padding: 14px 18px;
    }

    .table-farming tbody td {
        padding: 14px 18px;
        vertical-align: middle;
        color: #f8fafc;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .table-farming tbody tr:hover td {
        background: rgba(255, 255, 255, 0.03);
    }

    .badge-today-pulse {
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5); }
        70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
</style>
@endpush

@section('content')
@include('layout.header')

<main class="container py-4 farming-container">
    {{-- Header Banner --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 text-gold mb-0 fw-bold"><i class="bi bi-compass-fill me-2"></i>Farming Planner</h1>
                <span class="badge badge-genshin-gold px-2.5 py-1" style="font-size: 0.75rem;">
                    <i class="bi bi-calendar-event me-1"></i>{{ $today->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <p class="text-muted-contrast mb-0 small">
                Rute dan prioritas pengumpulan material dari task aktif Anda hari ini.
            </p>
        </div>
        
        {{-- Account Switcher --}}
        <form method="GET" class="d-flex align-items-center gap-2">
            <div class="input-group">
                <span class="input-group-text genshin-input-group-text py-1" style="font-size: 0.85rem;">
                    <i class="bi bi-person-badge text-gold me-1"></i>Akun
                </span>
                <select name="account_id" class="form-select form-select-sm genshin-select" onchange="this.form.submit()" style="min-width: 220px;">
                    @forelse($accounts as $account)
                        <option value="{{ $account->id }}" @selected($activeAccount?->id === $account->id)>
                            {{ $account->nickname }} ({{ $account->uid }})
                        </option>
                    @empty
                        <option>Belum ada akun</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>

    @if($tasks->isEmpty())
        {{-- Empty State --}}
        <div class="farming-card p-5 text-center my-4">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width: 72px; height: 72px; background: rgba(200, 170, 110, 0.1); border: 1px solid rgba(200, 170, 110, 0.3);">
                <i class="bi bi-calendar-x text-gold" style="font-size: 2.2rem;"></i>
            </div>
            <h3 class="h5 text-light-contrast fw-bold mb-2">Belum Ada Task Aktif</h3>
            <p class="text-muted-contrast mx-auto mb-4" style="max-width: 480px; font-size: 0.9rem;">
                Anda belum memiliki task aktif untuk dilacak. Buat task upgrade karakter atau senjata untuk menghasilkan rekomendasi rute farming otomatis.
            </p>
            <a href="{{ route('task.index') }}" class="btn btn-warning px-4 py-2 fw-semibold"
               style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                <i class="bi bi-plus-circle me-1"></i>Buat Task Sekarang
            </a>
        </div>
    @else
        {{-- Quick Stat Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="metric-card h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3);">
                        <i class="bi bi-list-check text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted-contrast small" style="font-size: 0.78rem;">Task Aktif</div>
                        <div class="fs-4 fw-bold text-light-contrast lh-1 mt-1">{{ $tasks->count() }}</div>
                        <div class="text-secondary small mt-1" style="font-size: 0.7rem;">Target karakter/senjata</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-warning-contrast"></i>
                    </div>
                    <div>
                        <div class="text-muted-contrast small" style="font-size: 0.78rem;">Material Kurang</div>
                        <div class="fs-4 fw-bold text-warning-contrast lh-1 mt-1">{{ $plan->where('missing', '>', 0)->count() }}</div>
                        <div class="text-secondary small mt-1" style="font-size: 0.7rem;">Total item belum cukup</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 50px; height: 50px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);">
                        <i class="bi bi-calendar-check-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted-contrast small" style="font-size: 0.78rem;">Domain Buka Hari Ini</div>
                        <div class="fs-4 fw-bold text-success lh-1 mt-1">{{ $domainPlan->where('available_today', true)->count() }}</div>
                        <div class="text-secondary small mt-1" style="font-size: 0.7rem;">Dari {{ $domainPlan->count() }} domain task</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 50px; height: 50px; background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3);">
                        <i class="bi bi-shield-check text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted-contrast small" style="font-size: 0.78rem;">Material Terpenuhi</div>
                        <div class="fs-4 fw-bold text-info lh-1 mt-1">{{ $plan->where('is_ready', true)->count() }}</div>
                        <div class="text-secondary small mt-1" style="font-size: 0.7rem;">Stok sudah cukup</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 1: Rute Farming yang Disarankan (Khusus Domain Berjadwal) --}}
        <div class="mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 text-gold fw-bold mb-1">
                        <i class="bi bi-signpost-2-fill me-2"></i>Rute Farming yang Disarankan
                    </h2>
                    <p class="text-muted-contrast small mb-0">
                        Khusus domain buku talenta dan material senjata yang memiliki jadwal rotasi hari tertentu.
                    </p>
                </div>
                
                {{-- Quick Filter Buttons --}}
                <div class="d-flex flex-wrap gap-2" id="routeFilterGroup">
                    <button type="button" class="route-tab-btn active" onclick="filterRoutes('all', this)">
                        Semua Domain ({{ $domainPlan->count() }})
                    </button>
                    <button type="button" class="route-tab-btn" onclick="filterRoutes('today', this)">
                        <i class="bi bi-lightning-fill text-success me-1"></i>Buka Hari Ini ({{ $domainPlan->where('available_today', true)->count() }})
                    </button>
                    <button type="button" class="route-tab-btn" onclick="filterRoutes('closed', this)">
                        <i class="bi bi-clock me-1"></i>Tutup Hari Ini ({{ $domainPlan->where('available_today', false)->count() }})
                    </button>
                </div>
            </div>

            <div class="row g-4" id="routeCardsContainer">
                @forelse($domainPlan as $domain)
                    @php
                        $isOpenToday = $domain['available_today'];
                        $filterClass = $isOpenToday ? 'route-is-today' : 'route-is-closed';
                    @endphp
                    <div class="col-lg-6 route-card-col {{ $filterClass }}">
                        <div class="farming-card h-100 {{ $isOpenToday ? 'farming-card-open' : '' }}">
                            {{-- Card Header --}}
                            <div class="farming-card-header d-flex justify-content-between align-items-start gap-3">
                                <div class="overflow-hidden">
                                    <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 0.68rem;">
                                            <i class="bi bi-door-open me-1"></i>Domain Berjadwal
                                        </span>
                                        <span class="text-secondary small text-truncate" style="font-size: 0.74rem;">
                                            {{ $domain['primary_source'] }}
                                        </span>
                                    </div>
                                    <h3 class="h6 text-light-contrast fw-bold mb-0 text-truncate" title="{{ $domain['group'] }}">
                                        {{ $domain['group'] }}
                                    </h3>
                                </div>

                                <div class="text-end flex-shrink-0">
                                    @if($isOpenToday)
                                        <span class="badge bg-success text-white px-2 py-1 badge-today-pulse" style="font-size: 0.72rem;">
                                            <i class="bi bi-lightning-fill me-0.5"></i>Buka Hari Ini
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-clock me-0.5"></i>Tutup Hari Ini
                                        </span>
                                    @endif
                                    <div class="text-warning-contrast small mt-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-exclamation-circle me-1"></i>Total Kurang: {{ number_format($domain['total_missing']) }}
                                    </div>
                                </div>
                            </div>

                            {{-- Material Items List --}}
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($domain['materials'] as $item)
                                    <div class="material-item-row d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3 overflow-hidden">
                                            <div class="material-img-wrapper">
                                                @if(!empty($item['material']->images))
                                                    <img src="{{ $item['material']->images }}" width="36" height="36"
                                                         class="rounded" style="object-fit: cover;" alt="{{ $item['material']->name }}">
                                                @else
                                                    <i class="bi bi-gem text-gold"></i>
                                                @endif
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="text-light-contrast fw-semibold small text-truncate">
                                                    {{ $item['material']->name }}
                                                </div>
                                                <div class="d-flex align-items-center gap-2 mt-0.5 flex-wrap">
                                                    <span class="text-muted-contrast" style="font-size: 0.72rem;">
                                                        Stok: <strong class="text-light">{{ number_format($item['owned']) }}</strong> / {{ number_format($item['required']) }}
                                                    </span>
                                                    <span class="text-secondary" style="font-size: 0.65rem;">&bull;</span>
                                                    <span class="text-muted-contrast" style="font-size: 0.72rem;">
                                                        Untuk: <span class="text-gold">{{ implode(', ', $item['tasks']) }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end flex-shrink-0">
                                            <span class="badge badge-kurang">
                                                <i class="bi bi-dash-circle-fill me-0.5"></i>Kurang {{ number_format($item['missing']) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="farming-card text-center py-4">
                            <i class="bi bi-calendar-check text-gold fs-2 mb-2 d-inline-block"></i>
                            <h4 class="h6 text-light-contrast fw-bold mb-1">Tidak Ada Domain Terjadwal pada Task Aktif</h4>
                            <p class="text-muted-contrast small mb-0">Seluruh kebutuhan task aktif Anda saat ini dapat diperoleh setiap hari di Open World (lihat tabel rincian di bawah).</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Section 2: Detail Analisis Seluruh Material --}}
        <div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 text-gold fw-bold mb-1">
                        <i class="bi bi-table me-2"></i>Detail Analisis Seluruh Material
                    </h2>
                    <p class="text-muted-contrast small mb-0">
                        Tabel rincian stok inventaris vs kebutuhan target untuk semua task aktif (termasuk drop open world & monster).
                    </p>
                </div>
                
                {{-- Search Filter for Table --}}
                <div style="min-width: 250px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text genshin-input-group-text py-1">
                            <i class="bi bi-search text-gold"></i>
                        </span>
                        <input type="text" id="tableMaterialSearch" class="form-control genshin-select"
                               placeholder="Cari material atau task..." onkeyup="filterMaterialTable()">
                    </div>
                </div>
            </div>

            <div class="farming-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-farming align-middle mb-0" id="materialPlanTable">
                        <thead>
                            <tr>
                                <th style="width: 28%;">Material</th>
                                <th style="width: 22%;">Lokasi / Sumber</th>
                                <th style="width: 15%;">Jadwal Buka</th>
                                <th style="width: 15%;">Untuk Task</th>
                                <th style="width: 10%; text-align: center;">Stok / Target</th>
                                <th style="width: 10%; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plan as $item)
                                @php
                                    $progress = $item['required'] > 0 ? min(100, round(($item['owned'] / $item['required']) * 100)) : 100;
                                @endphp
                                <tr>
                                    {{-- Kolom Material --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="material-img-wrapper flex-shrink-0" style="width: 36px; height: 36px;">
                                                @if(!empty($item['material']->images))
                                                    <img src="{{ $item['material']->images }}" width="32" height="32"
                                                         class="rounded" style="object-fit: cover;" alt="{{ $item['material']->name }}">
                                                @else
                                                    <i class="bi bi-gem text-gold"></i>
                                                @endif
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="text-light-contrast fw-semibold small text-truncate">
                                                    {{ $item['material']->name }}
                                                </div>
                                                <div class="text-secondary small" style="font-size: 0.68rem;">
                                                    {{ $item['material']->materialtype ?: 'Item' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom Lokasi / Sumber --}}
                                    <td>
                                        <div class="text-light-contrast small fw-medium text-truncate" title="{{ $item['domain'] }}">
                                            <i class="bi bi-geo-alt text-gold me-1"></i>{{ $item['domain'] }}
                                        </div>
                                        <div class="text-muted-contrast small text-truncate mt-0.5" style="font-size: 0.68rem;">
                                            {{ $item['primary_source'] }}
                                        </div>
                                    </td>

                                    {{-- Kolom Jadwal Buka --}}
                                    <td>
                                        @if($item['is_available_today'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>{{ $item['status_label'] }}
                                            </span>
                                            <div class="text-secondary small mt-0.5" style="font-size: 0.68rem;">
                                                {{ $item['days_formatted'] }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-clock me-1"></i>{{ $item['status_label'] }}
                                            </span>
                                            <div class="text-secondary small mt-0.5" style="font-size: 0.68rem;">
                                                {{ $item['days_formatted'] }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom Untuk Task --}}
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($item['tasks'] as $tName)
                                                <span class="badge bg-dark border border-secondary text-light px-2 py-0.5" style="font-size: 0.7rem;">
                                                    {{ $tName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Kolom Stok / Target --}}
                                    <td class="text-center">
                                        <div class="small fw-semibold text-light-contrast mb-1" style="font-size: 0.78rem;">
                                            {{ number_format($item['owned']) }} / {{ number_format($item['required']) }}
                                        </div>
                                        <div class="progress" style="height: 4px; background: rgba(255, 255, 255, 0.08); border-radius: 4px;">
                                            <div class="progress-bar {{ $item['is_ready'] ? 'bg-success' : 'bg-warning' }}"
                                                 role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="text-center">
                                        @if($item['is_ready'])
                                            <span class="badge bg-success text-white px-2 py-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-check2 me-0.5"></i>Cukup
                                            </span>
                                        @else
                                            <span class="badge badge-kurang" style="font-size: 0.72rem; padding: 3px 8px !important;">
                                                -{{ number_format($item['missing']) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</main>

@push('scripts')
<script>
    function filterRoutes(type, btn) {
        $('.route-tab-btn').removeClass('active');
        $(btn).addClass('active');

        if (type === 'all') {
            $('.route-card-col').fadeIn(200);
        } else if (type === 'today') {
            $('.route-card-col').hide();
            $('.route-card-col.route-is-today').fadeIn(200);
        } else if (type === 'closed') {
            $('.route-card-col').hide();
            $('.route-card-col.route-is-closed').fadeIn(200);
        }
    }

    function filterMaterialTable() {
        let val = $('#tableMaterialSearch').val().toLowerCase().trim();
        $('#materialPlanTable tbody tr').each(function() {
            let rowText = $(this).text().toLowerCase();
            if (!val || rowText.indexOf(val) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
</script>
@endpush
@endsection