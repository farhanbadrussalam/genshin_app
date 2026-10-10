@extends('layout.main')

@section('content')
@php $title = 'Task Tracker'; @endphp
@include('layout.header')

@push('styles')
<style>
    /* ─── Task Tracker Enhanced Styles ─── */
    .task-page-container {
        padding-top: 1.25rem;
        padding-bottom: 5rem;
    }

    .task-card {
        background: rgba(19, 23, 42, 0.85);
        border: 1px solid rgba(200, 170, 110, 0.2);
        border-radius: 14px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        backdrop-filter: blur(8px);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .task-card:hover {
        border-color: rgba(200, 170, 110, 0.45);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
    }

    .task-header-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.85rem;
        margin-bottom: 0.85rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .task-thumb {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(200, 170, 110, 0.3);
        flex-shrink: 0;
    }

    .task-title-text {
        font-size: 1.15rem;
        font-weight: 700;
        color: #f8fafc;
        margin-bottom: 0.2rem;
    }

    .task-sub-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .task-sub-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(200, 170, 110, 0.25);
    }

    .task-sub-img {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        object-fit: cover;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        flex-shrink: 0;
    }

    .badge-jenis {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: rgba(56, 189, 248, 0.15);
        border: 1px solid rgba(56, 189, 248, 0.35);
        color: #7dd3fc;
    }

    .badge-priority {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
    }

    .status-ok {
        background: rgba(34, 197, 94, 0.18);
        border: 1px solid rgba(34, 197, 94, 0.4);
        color: #86efac;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.82rem;
    }

    .status-need {
        background: rgba(245, 158, 11, 0.18);
        border: 1px solid rgba(245, 158, 11, 0.4);
        color: #fef08a;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.82rem;
    }

    .rarity-stars {
        color: #fbbf24;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .task-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* Modal Form Custom Styles */
    .genshin-modal-content {
        background: #121524 !important;
        border: 1px solid rgba(200, 170, 110, 0.35) !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8) !important;
        color: #f1f2f6;
    }
    .genshin-modal-content .modal-header {
        border-bottom: 1px solid rgba(200, 170, 110, 0.2);
    }
    .genshin-modal-content .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .material-target-row {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 0.45rem 0.65rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.4rem;
    }

    /* Task Card Collapse & Toggle */
    .btn-task-toggle {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #cbd5e1;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
        padding: 0;
    }
    .btn-task-toggle:hover {
        background: rgba(200, 170, 110, 0.2);
        border-color: #c8aa6e;
        color: #ffffff;
    }
    .btn-task-toggle .toggle-icon {
        transition: transform 0.25s ease;
        font-size: 1.1rem;
    }
    .btn-task-toggle.collapsed .toggle-icon {
        transform: rotate(180deg);
    }
    .task-header-clickable {
        cursor: pointer;
        user-select: none;
        transition: background 0.15s ease;
    }
    .task-header-clickable:hover {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
    }
</style>
@endpush

<main class="page-container task-page-container">
    {{-- Header & Action Bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 font-display text-gold mb-1 d-flex align-items-center">
                <i class="bi bi-list-task me-2"></i>Task Tracker
            </h1>
            <p class="small text-secondary mb-0" style="color: #cbd5e1 !important;">
                Kelola target upgrade ascension karakter, level talent, dan senjata beserta kalkulasi material farming.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm d-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#tambahTask"
                    style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                <i class="bi bi-plus-lg fs-6"></i>
                <span>Tambah Task</span>
            </button>
            <a href="{{ route('calculator.index') }}" class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-calculator-fill"></i>
                <span class="d-none d-sm-inline">Kalkulator</span>
            </a>
            <a href="{{ route('farming-planner.index') }}" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-map-fill"></i>
                <span class="d-none d-sm-inline">Farming</span>
            </a>
        </div>
    </div>

    {{-- Stats Bar --}}
    @php
        $totalTask = $dataTask->count();
        $readyUpgrade = $dataTask->where('statusUpgrade', true)->count();
        $needMaterial = $totalTask - $readyUpgrade;
        $highPriority = $dataTask->where('prioritas', '<=', 3)->count();
    @endphp
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-gold"><i class="bi bi-list-check"></i></div>
                <div class="stat-content">
                    <span class="stat-value">{{ $totalTask }}</span>
                    <span class="stat-label">Total Task</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-success"><i class="bi bi-arrow-up-circle-fill"></i></div>
                <div class="stat-content">
                    <span class="stat-value text-success">{{ $readyUpgrade }}</span>
                    <span class="stat-label">Siap Upgrade</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-content">
                    <span class="stat-value text-warning">{{ $needMaterial }}</span>
                    <span class="stat-label">Masih Kurang</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-danger"><i class="bi bi-fire"></i></div>
                <div class="stat-content">
                    <span class="stat-value text-danger">{{ $highPriority }}</span>
                    <span class="stat-label">Prioritas P1-P3</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="filter-panel mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text genshin-input-group-text">
                        <i class="bi bi-search text-gold"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm genshin-select" id="filterTaskInput"
                           placeholder="Cari nama task, jenis, atau nama material...">
                    <button class="btn btn-secondary btn-sm" type="button" id="clearTaskFilter" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select form-select-sm genshin-select" id="filterJenisSelect">
                    <option value="all">Semua Jenis</option>
                    <option value="stat">Karakter / Stat</option>
                    <option value="talent">Talent</option>
                    <option value="weapon">Senjata</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select form-select-sm genshin-select" id="filterStatusSelect">
                    <option value="all">Semua Kesiapan</option>
                    <option value="ready">Siap Upgrade Saja</option>
                    <option value="need">Masih Butuh Material</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Task Toolbar & Collapse Controls --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 px-1">
        <div class="small text-secondary" id="taskCountLabel" style="color: #cbd5e1 !important;">
            Menampilkan <strong class="text-white">{{ $dataTask->count() }}</strong> task aktif
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill" id="btnExpandAllTasks" title="Buka seluruh rincian material task" style="font-size: 0.78rem; border-color: rgba(255, 255, 255, 0.18); color: #cbd5e1;">
                <i class="bi bi-chevron-bar-expand me-1 text-gold"></i>Buka Semua
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill" id="btnCollapseAllTasks" title="Tutup seluruh rincian material task" style="font-size: 0.78rem; border-color: rgba(255, 255, 255, 0.18); color: #cbd5e1;">
                <i class="bi bi-chevron-bar-contract me-1 text-gold"></i>Tutup Semua
            </button>
        </div>
    </div>

    {{-- Task List Container --}}
    <div id="taskListContainer">
        @forelse($dataTask as $index => $task)
            @php
                $prio = $task->prioritas;
                $prioBg = $prio <= 3 ? 'rgba(239, 68, 68, 0.15)' : ($prio <= 6 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(59, 130, 246, 0.15)');
                $prioBorder = $prio <= 3 ? 'rgba(239, 68, 68, 0.4)' : ($prio <= 6 ? 'rgba(245, 158, 11, 0.4)' : 'rgba(59, 130, 246, 0.4)');
                $prioColor = $prio <= 3 ? '#fca5a5' : ($prio <= 6 ? '#fef08a' : '#93c5fd');

                $totalSub = $task->sub_task->count();
                $readySub = 0;
                foreach ($task->sub_task as $st) {
                    $mAmount = $st->material?->amount ?? 0;
                    if (isset($st->material?->hasilCraft)) $mAmount += $st->material->hasilCraft;
                    if ($mAmount >= ($st->amount ?? 0)) $readySub++;
                }
                $progressPercent = $totalSub > 0 ? round(($readySub / $totalSub) * 100) : 0;
            @endphp

            <div class="task-card animate-fade-in-up" data-jenis="{{ strtolower($task->jenis) }}" data-ready="{{ $task->statusUpgrade ? '1' : '0' }}">
                {{-- Task Header --}}
                <div class="task-header-box task-header-clickable" data-target="#taskCollapse{{ $task->id }}">
                    <div class="d-flex align-items-center gap-3">
                        @if($task->images)
                            <img src="{{ $task->images }}" alt="{{ $task->nama_task }}" class="task-thumb"
                                 loading="lazy" referrerpolicy="no-referrer"
                                 onerror="this.src='https://enka.network/ui/UI_AvatarIcon_Paimon.png'">
                        @else
                            <div class="task-thumb d-flex align-items-center justify-content-center text-gold fs-5">
                                <i class="bi bi-star-fill"></i>
                            </div>
                        @endif
                        <div>
                            <div class="task-title-text">{{ $task->nama_task }}</div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge-jenis">{{ $task->jenis }}</span>
                                <span class="badge-priority" style="background: {{ $prioBg }}; border: 1px solid {{ $prioBorder }}; color: {{ $prioColor }};">
                                    P{{ $task->prioritas }}
                                </span>
                                <span class="small text-secondary" style="color: #cbd5e1 !important; font-size: 0.75rem;">
                                    {{ $readySub }}/{{ $totalSub }} Material Siap ({{ $progressPercent }}%)
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($task->statusUpgrade)
                            <span class="badge status-ok d-flex align-items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i>Siap Upgrade
                            </span>
                        @else
                            <span class="badge status-need d-flex align-items-center gap-1">
                                <i class="bi bi-hourglass-split"></i>Butuh Material
                            </span>
                        @endif

                        <button type="button" class="btn btn-task-toggle text-secondary"
                                data-bs-toggle="collapse"
                                data-bs-target="#taskCollapse{{ $task->id }}"
                                aria-expanded="true"
                                aria-controls="taskCollapse{{ $task->id }}"
                                title="Buka / Tutup Rincian Material">
                            <i class="bi bi-chevron-up toggle-icon"></i>
                        </button>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="progress mb-2" style="height: 5px; background: rgba(255, 255, 255, 0.08); border-radius: 4px;">
                    <div class="progress-bar {{ $task->statusUpgrade ? 'bg-success' : 'bg-warning' }}" role="progressbar"
                         style="width: {{ $progressPercent }}%;" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                {{-- Collapsible Content: Sub Tasks & Actions --}}
                <div class="collapse show task-details-collapse" id="taskCollapse{{ $task->id }}">
                    <div class="pt-2">
                        {{-- Sub Tasks / Target Materials --}}
                        <div class="task-sub-items mb-2">
                    @foreach ($task->sub_task as $sub_task)
                        @php
                            $mat = $sub_task->material;
                            if (!$mat) continue;
                            $dimiliki = $mat->amount ?? 0;
                            if (isset($mat->hasilCraft)) {
                                $dimiliki += $mat->hasilCraft;
                            }
                            $dibutuhkan = $sub_task->amount ?? 0;
                            $isOk = $dimiliki >= $dibutuhkan;
                            $daysArr = is_string($mat->daysofweek) ? json_decode($mat->daysofweek) : [];
                            $sourceArr = is_string($mat->source) ? json_decode($mat->source) : [];
                        @endphp
                        <div class="task-sub-item" data-subtask="{{ $sub_task }}" onclick="showMaterial(this)" style="cursor: pointer;">
                            <img src="{{ $mat->images }}" alt="{{ $mat->name }}" class="task-sub-img" loading="lazy" referrerpolicy="no-referrer">
                            <div class="sub-info flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-white small text-truncate">{{ $mat->name }}</span>
                                    @if(isset($mat->rarity) && $mat->rarity > 0)
                                        <span class="rarity-stars">{{ str_repeat('★', $mat->rarity) }}</span>
                                    @endif
                                </div>
                                <div class="small d-flex flex-wrap align-items-center gap-2 mt-1" style="font-size: 0.72rem; color: #94a3b8;">
                                    @if(!empty($daysArr) && count($daysArr) > 0)
                                        <span class="badge bg-dark border border-secondary border-opacity-50 text-light px-1 py-0" style="font-size: 0.68rem;">
                                            {{ implode(', ', $daysArr) }}
                                        </span>
                                    @endif
                                    @if(!empty($sourceArr) && isset($sourceArr[0]))
                                        <span class="text-truncate">{{ $sourceArr[0] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="{{ $isOk ? 'status-ok' : 'status-need' }}"
                                      data-needed="{{ $dibutuhkan }}"
                                      data-craft="{{ isset($mat->hasilCraft) ? $mat->hasilCraft : 0 }}">
                                    {{ $dimiliki }}/{{ $dibutuhkan }}
                                </span>
                                <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                                    <button type="button" class="btn btn-secondary btn-sm py-0 px-2"
                                            style="height: 28px;"
                                            data-material-id="{{ $mat->id }}"
                                            onclick="quickChangeSubtaskMaterial(event, this, -1)"
                                            title="Kurang 1 {{ $mat->name }}">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm py-0 px-2"
                                            style="height: 28px;"
                                            data-material-id="{{ $mat->id }}"
                                            onclick="quickChangeSubtaskMaterial(event, this, 1)"
                                            title="Tambah 1 {{ $mat->name }}">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Task Card Footer Actions --}}
                <div class="task-actions justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-warning btn-sm"
                                onclick="editTaskModal(this)"
                                data-task="{{ json_encode($task) }}"
                                title="Edit Detail Task">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>
                        <form action="{{ route('task.destroy', $task->id) }}" method="POST" style="margin: 0;"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus task \"{{ $task->nama_task }}\"?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm" type="submit" title="Hapus Task">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </div>
                    <div>
                        @if($task->statusUpgrade)
                            <a href="{{ url('taskComplete/' . $task->id) }}"
                               onclick="return confirm('Selesaikan dan upgrade task {{ $task->nama_task }}? Material inventori akan dikurangi.')"
                               class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                                <i class="bi bi-arrow-up-circle-fill me-1"></i>Upgrade Sekarang
                            </a>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm disabled" disabled title="Material belum mencukupi">
                                <i class="bi bi-lock-fill me-1"></i>Belum Cukup
                            </button>
                        @endif
                    </div>
                </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-4 p-4 p-md-5 text-center position-relative overflow-hidden shadow-lg mb-4"
                 style="background: linear-gradient(135deg, rgba(26, 31, 56, 0.85) 0%, rgba(15, 18, 35, 0.95) 100%); border: 1px solid rgba(200, 170, 110, 0.3); backdrop-filter: blur(12px);">
                <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 240px; height: 3px; background: linear-gradient(90deg, transparent, #c8aa6e, transparent);"></div>
                
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle p-3"
                     style="width: 76px; height: 76px; background: rgba(200, 170, 110, 0.1); border: 1px solid rgba(200, 170, 110, 0.25);">
                    <i class="bi bi-magic text-gold" style="font-size: 2.3rem;"></i>
                </div>

                <h4 class="font-display text-gold mb-2">Belum Ada Target Upgrade Aktif</h4>
                <p class="text-secondary mx-auto mb-4" style="color: #cbd5e1 !important; max-width: 620px; font-size: 0.92rem;">
                    Mulai lacak kebutuhan upgrade karakter Anda! Tambahkan karakter untuk <strong>Upgrade Talent</strong>, dan seluruh material (Buku Talent, Monster Drop, Weekly Boss, & Crown) akan <span class="text-gold fw-bold">langsung terisi otomatis</span> tanpa perlu input manual.
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                    <button type="button" class="btn btn-warning px-4 py-2 fw-bold shadow"
                            style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #0f1223;"
                            onclick="openAddTalentTaskModal()">
                        <i class="bi bi-magic me-1"></i>Tambah Karakter Upgrade Talent (Otomatis)
                    </button>
                    <button type="button" class="btn btn-outline-light px-3 py-2 fw-semibold"
                            data-bs-toggle="modal" data-bs-target="#tambahTask" onclick="$('#jenis_task').val('stat'); onJenisTaskChanged('stat');">
                        <i class="bi bi-plus-lg me-1"></i>Target Custom / Ascension
                    </button>
                </div>

                <div class="pt-3 border-top" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                    <div class="small fw-semibold text-secondary mb-3 text-uppercase" style="letter-spacing: 0.05em; color: #94a3b8 !important; font-size: 0.75rem;">
                        -> Atau pilih karakter rekomendasi sekali klik:
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @php
                            $quickPickNames = ['Raiden Shogun', 'Nahida', 'Furina', 'Arlecchino', 'Mavuika', 'Hu Tao', 'Kaedehara Kazuha', 'Bennett'];
                            $quickPickChars = $characters->whereIn('name', $quickPickNames);
                        @endphp
                        @foreach($quickPickChars as $qc)
                            <button type="button" class="btn btn-sm text-start d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill"
                                    style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(200, 170, 110, 0.25); color: #f8fafc; transition: all 0.2s ease;"
                                    onmouseover="this.style.background='rgba(200, 170, 110, 0.15)'; this.style.borderColor='#c8aa6e';"
                                    onmouseout="this.style.background='rgba(255, 255, 255, 0.04)'; this.style.borderColor='rgba(200, 170, 110, 0.25)';"
                                    onclick="quickSelectCharacterTalent({{ $qc->id }}, '{{ addslashes($qc->name) }}', '{{ $qc->icon_url }}')">
                                <img src="{{ $qc->icon_url }}" width="26" height="26" class="rounded-circle" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(200, 170, 110, 0.3);">
                                <span class="fw-semibold small">{{ $qc->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</main>

{{-- Modal Tambah Task Baru --}}
<div class="modal fade" id="tambahTask" tabindex="-1" aria-labelledby="tambahTaskTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form action="{{ route('task.store') }}" method="POST" id="formTambahTask" class="w-100">
            @csrf
            <div class="modal-content genshin-modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-display text-gold" id="tambahTaskTitle">
                        <i class="bi bi-plus-circle-fill me-2"></i>Tambah Task Upgrade Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    {{-- 1. Jenis Task --}}
                    <div class="mb-3">
                        <label class="form-label text-gold small fw-bold mb-1">
                            <i class="bi bi-tag-fill me-1"></i>Jenis Task
                        </label>
                        <select class="form-select form-select-sm genshin-select" id="jenis_task" name="jenis_task" onchange="onJenisTaskChanged(this.value)">
                            <option value="stat">Karakter - Ascension & Level</option>
                            <option value="talent">Karakter - Level Talent</option>
                            <option value="weapon">Senjata - Ascension & Level</option>
                            <option value="custom">Custom / Bebas</option>
                        </select>
                    </div>

                    {{-- 2. Target Selector (Karakter / Senjata) --}}
                    <div class="row g-2 mb-3">
                        {{-- Karakter Selector --}}
                        <div class="col-12" id="boxSelectCharacter">
                            <label class="form-label text-gold small fw-bold mb-1">
                                <i class="bi bi-person-fill me-1"></i>Pilih Karakter
                            </label>
                            <select class="form-select form-select-sm genshin-select" id="select_character" name="target_character_id" style="width: 100%;">
                                <option value="">-- Cari & Pilih Karakter --</option>
                                @foreach($characters as $c)
                                    <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-icon="{{ $c->icon_url }}">
                                        {{ $c->name }} ({{ $c->element }} &bull; {{ $c->weapon_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        
                        {{-- Panel Auto-Populate Talent --}}
                        <div class="col-12" id="boxTalentPreset" style="display: none;">
                            <div class="p-3 rounded mt-1" style="background: rgba(200, 170, 110, 0.06); border: 1px solid rgba(200, 170, 110, 0.3);">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-gold small fw-bold d-flex align-items-center">
                                        <i class="bi bi-magic me-1"></i>Preset Material Talent (Auto-Populate)
                                    </span>
                                    <span class="badge" style="background: rgba(200, 170, 110, 0.2); color: #c8aa6e; font-size: 0.7rem;">
                                        Database Lokal
                                    </span>
                                </div>
                                <div class="small text-secondary mb-2" style="color: #cbd5e1 !important; font-size: 0.78rem;">
                                    Material upgrade talent (Buku Domain, Enemy Drop, Weekly Boss, & Crown) langsung terisi otomatis:
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <button type="button" class="btn btn-warning btn-sm py-1 px-2 talent-preset-btn active"
                                             data-curr="1" data-target="8" data-count="1"
                                             onclick="applyTalentPreset(1, 8, 1, this)">
                                        Lv 1 -> 8 (Standar)
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2 talent-preset-btn"
                                             data-curr="1" data-target="10" data-count="1"
                                             onclick="applyTalentPreset(1, 10, 1, this)">
                                        Lv 1 -> 10 (Crown)
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2 talent-preset-btn"
                                             data-curr="1" data-target="6" data-count="1"
                                             onclick="applyTalentPreset(1, 6, 1, this)">
                                        Lv 1 -> 6 (Budget)
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2 talent-preset-btn"
                                             data-curr="1" data-target="8" data-count="3"
                                             onclick="applyTalentPreset(1, 8, 3, this)">
                                        3 Talent ke Lv 8
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2 talent-preset-btn"
                                             data-curr="1" data-target="10" data-count="3"
                                             onclick="applyTalentPreset(1, 10, 3, this)">
                                        Triple Crown (3x Lv 10)
                                    </button>
                                </div>
                                <div id="talentAutoLoading" style="display: none;" class="text-gold small py-1">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Mengambil kebutuhan material...
                                </div>
                                <div id="talentAutoSuccess" style="display: none;" class="text-success small py-1 fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i>Material talent berhasil dimasukkan ke daftar target!
                                </div>
                            </div>
                        </div>

{{-- Senjata Selector --}}
                        <div class="col-12" id="boxSelectWeapon" style="display: none;">
                            <label class="form-label text-gold small fw-bold mb-1">
                                <i class="bi bi-shield-shaded me-1"></i>Pilih Senjata
                            </label>
                            <select class="form-select form-select-sm genshin-select" id="select_weapon" name="target_weapon_id" style="width: 100%;">
                                <option value="">-- Cari & Pilih Senjata --</option>
                                @foreach($weapons as $w)
                                    <option value="{{ $w->id }}" data-name="{{ $w->name }}" data-icon="{{ $w->icon_url }}">
                                        {{ $w->name }} ({{ $w->type }} &bull; ★{{ $w->rarity }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 3. Detail Task (Nama, Icon Preview, Prioritas) --}}
                    <div class="p-3 rounded mb-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <div class="text-center">
                                    <img src="https://enka.network/ui/UI_AvatarIcon_Paimon.png" alt="" id="previewImageTask"
                                         class="rounded" width="60" height="60" style="object-fit: cover; border: 1px solid rgba(200, 170, 110, 0.4); background: rgba(0,0,0,0.4);"
                                         onerror="this.src='https://enka.network/ui/UI_AvatarIcon_Paimon.png'">
                                    <input type="hidden" id="urlImage" name="urlImage">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-2">
                                    <label class="form-label small text-secondary mb-1" style="color: #cbd5e1 !important;">Nama Task</label>
                                    <input type="text" class="form-control form-control-sm genshin-select" id="nameTask" name="nameTask"
                                           placeholder="Nama task (mis: Ascension Nahida)" required>
                                </div>
                                <div>
                                    <label class="form-label small text-secondary mb-1" style="color: #cbd5e1 !important;">Prioritas (1 = Tertinggi, 10 = Terendah)</label>
                                    <input type="number" class="form-control form-control-sm genshin-select" id="prioritas" name="prioritas"
                                           value="1" min="1" max="10" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Target Material --}}
                    <div class="p-3 rounded mb-2" style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(200, 170, 110, 0.2);">
                        <label class="form-label text-gold small fw-bold mb-2 d-flex align-items-center">
                            <i class="bi bi-gem me-1"></i>Pilih Material yang Dibutuhkan
                        </label>
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-12 col-md-7">
                                <select class="form-select form-select-sm genshin-select" id="material_picker" style="width: 100%;">
                                    <option value="">-- Pilih Material --</option>
                                    @foreach ($dataMaterial as $material)
                                        <option value="{{ $material->id }}" data-name="{{ $material->name }}" data-img="{{ $material->images }}">
                                            {{ $material->name }} (Dimiliki: {{ $material->amount }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-7 col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text genshin-input-group-text">Butuh</span>
                                    <input type="number" class="form-control form-control-sm genshin-select" id="material_amount_input"
                                           value="1" min="1" placeholder="Jumlah">
                                </div>
                            </div>
                            <div class="col-5 col-md-2">
                                <button type="button" class="btn btn-success btn-sm w-100 fw-bold" onclick="addMaterialToCreateList()">
                                    <i class="bi bi-plus-lg me-1"></i>Tambah
                                </button>
                            </div>
                        </div>

                        {{-- Container List Material yang Ditambahkan --}}
                        <div id="material_target_list" class="mt-2" style="display: none;">
                            <div class="small text-secondary mb-1 fw-semibold" style="color: #cbd5e1 !important; font-size: 0.75rem;">
                                Daftar Material yang Dipantau:
                            </div>
                            <div id="material_target_items"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 fw-bold shadow"
                            style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                        <i class="bi bi-check-lg me-1"></i>Simpan Task
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Task --}}
<div class="modal fade" id="modalEditTask" tabindex="-1" aria-labelledby="modalEditTaskTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form action="#" method="POST" id="formEditTask" class="w-100">
            @csrf
            @method('PUT')
            <div class="modal-content genshin-modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-display text-gold" id="modalEditTaskTitle">
                        <i class="bi bi-pencil-square me-2"></i>Edit Task
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-secondary mb-1" style="color: #cbd5e1 !important;">Jenis Task</label>
                            <select class="form-select form-select-sm genshin-select" id="jenis_taskEdit" name="jenis_taskEdit">
                                <option value="stat">Karakter / Stat</option>
                                <option value="talent">Talent</option>
                                <option value="weapon">Senjata</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary mb-1" style="color: #cbd5e1 !important;">Prioritas (1 = Tertinggi)</label>
                            <input type="number" class="form-control form-control-sm genshin-select" id="prioritasEdit" name="prioritasEdit" min="1" max="10" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-secondary mb-1" style="color: #cbd5e1 !important;">Nama Task</label>
                        <input type="text" class="form-control form-control-sm genshin-select" id="nameTaskEdit" name="nameTaskEdit" required>
                        <input type="hidden" id="urlImageEdit" name="urlImageEdit">
                    </div>

                    <hr class="genshin-divider">

                    {{-- Edit Material Target --}}
                    <div class="p-3 rounded mb-2" style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(200, 170, 110, 0.2);">
                        <label class="form-label text-gold small fw-bold mb-2">
                            <i class="bi bi-gem me-1"></i>Target Material
                        </label>
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-8">
                                <select class="form-select form-select-sm genshin-select" id="material_nameEdit" style="width: 100%;">
                                    <option value="">-- Pilih Material Baru --</option>
                                    @foreach ($dataMaterial as $material)
                                        <option value="{{ $material->id }}" data-name="{{ $material->name }}" data-img="{{ $material->images }}">
                                            {{ $material->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-success btn-sm w-100 fw-bold" type="button" onclick="btnMaterialEditAdd()">
                                    <i class="bi bi-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </div>

                        <div id="material_listEdit"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 fw-bold"
                            style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                        <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Material & Crafting Hub --}}
<div class="modal fade" tabindex="-1" id="modalDetailMaterial">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content genshin-modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-gold font-display" id="titleModal">Detail Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="materialCrafting"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Craft Range --}}
<div class="modal fade" tabindex="-1" id="modalRangeCraft" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content genshin-modal-content">
            <form action="{{ route('craftingBuild') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-gold font-display">Crafting Material</h5>
                </div>
                <div class="modal-body p-3">
                    <p style="color: #cbd5e1; font-size: 0.88rem;">
                        Crafting target material: <strong class="text-gold" id="titleCraftModal"></strong>
                    </p>
                    <input type="hidden" id="formidmaterial" name="formidmaterial">
                    <label class="form-label small text-secondary" style="color: #cbd5e1 !important;">Geser Jumlah Craft:</label>
                    <input type="range" class="form-range w-100 mb-2" min="0" step="1" id="formRangeCraft" name="formRangeCraft">
                    <div class="p-2 rounded text-center" style="background: rgba(200, 170, 110, 0.1); border: 1px solid rgba(200, 170, 110, 0.3);">
                        <span style="color: #cbd5e1; font-size: 0.85rem;">Hasil yang didapat: </span>
                        <strong class="text-gold fs-5 ms-1" id="valueCraft">0</strong> item
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                            onclick="$('#modalDetailMaterial').modal('show')">Batal</button>
                    <button class="btn btn-warning btn-sm fw-bold px-3" type="submit"
                            style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                        <i class="bi bi-hammer me-1"></i>Eksekusi Craft
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.createMaterialCounter = 0;
    window.editMaterialNo = 0;

    $(document).ready(function() {
        // Global Collapse / Expand All Tasks
        $('#btnExpandAllTasks').on('click', function() {
            $('.task-details-collapse').collapse('show');
            $('.btn-task-toggle').removeClass('collapsed').attr('aria-expanded', 'true');
        });

        $('#btnCollapseAllTasks').on('click', function() {
            $('.task-details-collapse').collapse('hide');
            $('.btn-task-toggle').addClass('collapsed').attr('aria-expanded', 'false');
        });

        // Click on header box to toggle collapse (ignoring clicks on buttons/links)
        $(document).on('click', '.task-header-clickable', function(e) {
            if ($(e.target).closest('button, a, input, select').length === 0) {
                let targetId = $(this).data('target');
                if (targetId) {
                    $(targetId).collapse('toggle');
                }
            }
        });

        // Sync toggle button rotation with collapse events
        $(document).on('hidden.bs.collapse', '.task-details-collapse', function () {
            let taskId = $(this).attr('id').replace('taskCollapse', '');
            $('[data-bs-target="#taskCollapse' + taskId + '"]').addClass('collapsed').attr('aria-expanded', 'false');
        });
        $(document).on('shown.bs.collapse', '.task-details-collapse', function () {
            let taskId = $(this).attr('id').replace('taskCollapse', '');
            $('[data-bs-target="#taskCollapse' + taskId + '"]').removeClass('collapsed').attr('aria-expanded', 'true');
        });

        // Init Select2 in modals
        $('#select_character').select2({
            dropdownParent: $('#tambahTask'),
            placeholder: "-- Cari Karakter --"
        });
        $('#select_weapon').select2({
            dropdownParent: $('#tambahTask'),
            placeholder: "-- Cari Senjata --"
        });
        $('#material_picker').select2({
            dropdownParent: $('#tambahTask'),
            placeholder: "-- Cari Material --"
        });
        $('#material_nameEdit').select2({
            dropdownParent: $('#modalEditTask'),
            placeholder: "-- Cari Material --"
        });

        // Trigger on Character select
        $('#select_character').on('change', function() {
            let opt = $(this).find('option:selected');
            let name = opt.data('name');
            let icon = opt.data('icon');
            let charId = $(this).val();
            if (name) {
                let jenis = $('#jenis_task').val();
                let prefix = jenis === 'talent' ? 'Talent ' : 'Ascension ';
                $('#nameTask').val(prefix + name);
                if (icon) {
                    $('#previewImageTask').attr('src', icon);
                    $('#urlImage').val(icon);
                }
                if (jenis === 'talent' && charId) {
                    fetchAndApplyTalentMaterials(charId, currentTalentPreset.curr, currentTalentPreset.target, currentTalentPreset.count);
                }
            }
        });

        // Trigger on Weapon select
        $('#select_weapon').on('change', function() {
            let opt = $(this).find('option:selected');
            let name = opt.data('name');
            let icon = opt.data('icon');
            if (name) {
                $('#nameTask').val('Upgrade ' + name);
                if (icon) {
                    $('#previewImageTask').attr('src', icon);
                    $('#urlImage').val(icon);
                }
            }
        });

        // Live Filter Task
        function applyTaskFilters() {
            let q = $('#filterTaskInput').val().toLowerCase().trim();
            let selectedJenis = $('#filterJenisSelect').val();
            let selectedStatus = $('#filterStatusSelect').val();
            $('#clearTaskFilter').toggle(q.length > 0);

            let matchCount = 0;
            $('#taskListContainer .task-card').each(function() {
                let $card = $(this);
                let title = $card.find('.task-title-text').text().toLowerCase();
                let cardJenis = $card.data('jenis') || '';
                let isReady = $card.data('ready') == '1';
                let matNames = $card.find('.sub-info').text().toLowerCase();

                let matchesText = (title.includes(q) || cardJenis.includes(q) || matNames.includes(q));
                let matchesJenis = (selectedJenis === 'all' || cardJenis === selectedJenis);
                let matchesStatus = (selectedStatus === 'all' || (selectedStatus === 'ready' && isReady) || (selectedStatus === 'need' && !isReady));

                if (matchesText && matchesJenis && matchesStatus) {
                    $card.show();
                    matchCount++;
                } else {
                    $card.hide();
                }
            });

            if (matchCount === 0) {
                if ($('#noTaskFilterMatch').length === 0) {
                    $('#taskListContainer').append(`
                        <div id="noTaskFilterMatch" class="text-center py-5" style="color: #94a3b8;">
                            <i class="bi bi-search" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0" style="font-size: 0.88rem;">Tidak ada task yang cocok dengan kriteria filter.</p>
                        </div>
                    `);
                } else {
                    $('#noTaskFilterMatch').show();
                }
            } else {
                $('#noTaskFilterMatch').hide();
            }
        }

        $('#filterTaskInput').on('input', applyTaskFilters);
        $('#filterJenisSelect, #filterStatusSelect').on('change', applyTaskFilters);
        $('#clearTaskFilter').on('click', function() {
            $('#filterTaskInput').val('').trigger('input').focus();
        });

        // Flash message with SweetAlert2
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2500,
                showConfirmButton: false
            });
        @endif
    });

    let currentTalentPreset = { curr: 1, target: 8, count: 1 };

    function openAddTalentTaskModal() {
        $('#jenis_task').val('talent');
        onJenisTaskChanged('talent');
        $('#tambahTask').modal('show');
    }

    function quickSelectCharacterTalent(charId, charName, iconUrl) {
        $('#jenis_task').val('talent');
        onJenisTaskChanged('talent');
        $('#select_character').val(charId).trigger('change');
        $('#tambahTask').modal('show');
    }

    function applyTalentPreset(curr, target, count, btn) {
        currentTalentPreset = { curr: curr, target: target, count: count };
        $('.talent-preset-btn').removeClass('active btn-warning').addClass('btn-outline-warning');
        if (btn) {
            $(btn).removeClass('btn-outline-warning').addClass('active btn-warning');
        }
        let charId = $('#select_character').val();
        if (charId) {
            fetchAndApplyTalentMaterials(charId, curr, target, count);
        }
    }

    function fetchAndApplyTalentMaterials(charId, curr, target, count) {
        if (!charId) return;
        $('#talentAutoLoading').show();
        $('#talentAutoSuccess').hide();

        $.ajax({
            url: "{{ url('task/character-talent-materials') }}/" + charId,
            type: 'GET',
            data: {
                current_level: curr,
                target_level: target,
                talent_count: count
            },
            success: function(res) {
                $('#talentAutoLoading').hide();
                if (res && res.success && res.materials && res.materials.length > 0) {
                    $('#material_target_items').empty();
                    window.createMaterialCounter = 0;


                    let prefix = count > 1 ? `Upgrade 3 Talent ${res.character_name} (Lv ${curr} -> ${target})` : `Upgrade Talent ${res.character_name} (Lv ${curr} -> ${target})`;
                    $('#nameTask').val(prefix);
                    if (res.icon_url) {
                        $('#previewImageTask').attr('src', res.icon_url);
                        $('#urlImage').val(res.icon_url);
                    }

                    res.materials.forEach(mat => {
                        let rowId = 'create_mat_row_' + window.createMaterialCounter;
                        let domainInfo = '';
                        if (mat.is_available_today) {
                            domainInfo = `<span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0" style="font-size:0.65rem;"><i class="bi bi-calendar-check me-0.5"></i>Domain Buka Hari Ini</span>`;
                        } else if (mat.dropdomain && mat.daysofweek && mat.daysofweek.length > 0) {
                            let days = mat.daysofweek.slice(0, 2).join(', ');
                            domainInfo = `<span class="badge bg-secondary-subtle text-secondary border px-1 py-0" style="font-size:0.65rem;"><i class="bi bi-clock me-0.5"></i>${days}</span>`;
                        }

                        let imgHtml = mat.images 
                            ? `<img src="${mat.images}" width="28" height="28" class="rounded" style="object-fit: cover; background: rgba(0,0,0,0.3);">` 
                            : `<i class="bi bi-gem text-gold"></i>`;

                        let rowHtml = `
                            <div class="material-target-row" id="${rowId}">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    ${imgHtml}
                                    <div class="overflow-hidden">
                                        <div class="text-white small fw-semibold text-truncate">${mat.name}</div>
                                        <div class="d-flex align-items-center gap-1 mt-0.5">
                                            ${domainInfo}
                                            <span class="badge bg-dark text-secondary px-1 py-0" style="font-size:0.65rem;">Stok: ${mat.stock}</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="namaMaterial[]" value="${mat.material_id}">
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="width: 100px;">
                                        <span class="input-group-text genshin-input-group-text py-0" style="font-size: 0.7rem;">Butuh</span>
                                        <input type="number" name="amount[]" class="form-control form-control-sm genshin-select text-center p-1"
                                               value="${mat.required}" min="1" required>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm py-0 px-2" style="height: 28px;"
                                            onclick="$('#${rowId}').remove(); if($('#material_target_items').children().length===0) $('#material_target_list').hide();"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        $('#material_target_items').append(rowHtml);
                        window.createMaterialCounter++;
                    });

                    $('#material_target_list').show();
                    $('#talentAutoSuccess').fadeIn().delay(3000).fadeOut();
                }
            },
            error: function() {
                $('#talentAutoLoading').hide();
            }
        });
    }

    function onJenisTaskChanged(val) {
        if (val === 'weapon') {
            $('#boxSelectCharacter').hide();
            $('#boxSelectWeapon').show();
            $('#boxTalentPreset').hide();
            $('#select_weapon').trigger('change');
        } else if (val === 'custom') {
            $('#boxSelectCharacter').hide();
            $('#boxSelectWeapon').hide();
            $('#boxTalentPreset').hide();
            $('#nameTask').val('').attr('placeholder', 'Nama task custom (bebas)');
            $('#previewImageTask').attr('src', 'https://enka.network/ui/UI_AvatarIcon_Paimon.png');
            $('#urlImage').val('');
        } else if (val === 'talent') {
            $('#boxSelectCharacter').show();
            $('#boxSelectWeapon').hide();
            $('#boxTalentPreset').slideDown();
            let charId = $('#select_character').val();
            if (charId) {
                fetchAndApplyTalentMaterials(charId, currentTalentPreset.curr, currentTalentPreset.target, currentTalentPreset.count);
            }
        } else {
            // stat (Ascension)
            $('#boxSelectCharacter').show();
            $('#boxSelectWeapon').hide();
            $('#boxTalentPreset').hide();
            $('#select_character').trigger('change');
        }
    }

    function addMaterialToCreateList() {
        let matId = $('#material_picker').val();
        let amount = parseInt($('#material_amount_input').val()) || 1;
        if (!matId) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Material',
                text: 'Silakan pilih material terlebih dahulu dari dropdown.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        let opt = $('#material_picker').find('option:selected');
        let matName = opt.data('name');
        let matImg = opt.data('img');
        let rowId = 'create_mat_row_' + window.createMaterialCounter;

        let rowHtml = `
            <div class="material-target-row" id="${rowId}">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    ${matImg ? `<img src="${matImg}" width="28" height="28" class="rounded" style="object-fit: cover; background: rgba(0,0,0,0.3);">` : `<i class="bi bi-gem text-gold"></i>`}
                    <span class="text-white small fw-semibold text-truncate">${matName}</span>
                    <input type="hidden" name="namaMaterial[]" value="${matId}">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 100px;">
                        <span class="input-group-text genshin-input-group-text py-0" style="font-size: 0.7rem;">Jml</span>
                        <input type="number" name="amount[]" class="form-control form-control-sm genshin-select text-center p-1"
                               value="${amount}" min="1" required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm py-0 px-2" style="height: 28px;"
                            onclick="$('#${rowId}').remove(); if($('#material_target_items').children().length===0) $('#material_target_list').hide();"
                            title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        $('#material_target_items').append(rowHtml);
        $('#material_target_list').show();
        $('#material_picker').val('').trigger('change');
        $('#material_amount_input').val(1);
        window.createMaterialCounter++;
    }

    function editTaskModal(btn) {
        const data = $(btn).data('task');
        $('#formEditTask').attr('action', "{{ url('task') }}/" + data.id);
        $('#jenis_taskEdit').val(data.jenis);
        $('#nameTaskEdit').val(data.nama_task);
        $('#urlImageEdit').val(data.images);
        $('#prioritasEdit').val(data.prioritas);

        $('#material_listEdit').empty();
        window.editMaterialNo = 0;

        if (data.sub_task && data.sub_task.length > 0) {
            data.sub_task.forEach(sub => {
                let matName = sub.material ? sub.material.name : ('Material ID ' + sub.material_id);
                let matImg = sub.material ? sub.material.images : '';
                addEditMaterialRow(sub.material_id, matName, sub.amount, matImg);
            });
        }

        $('#modalEditTask').modal('show');
    }

    function addEditMaterialRow(matId, matName, amount, matImg = '') {
        let rowId = 'edit_material_row_' + window.editMaterialNo;
        let content = `
            <div class="material-target-row" id="${rowId}">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    ${matImg ? `<img src="${matImg}" width="28" height="28" class="rounded" style="object-fit: cover;">` : `<i class="bi bi-gem text-gold"></i>`}
                    <span class="text-white small fw-semibold text-truncate">${matName}</span>
                    <input type="hidden" name="namaMaterial[]" value="${matId}">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 100px;">
                        <span class="input-group-text genshin-input-group-text py-0" style="font-size: 0.7rem;">Jml</span>
                        <input type="number" class="form-control form-control-sm genshin-select text-center p-1"
                               name="amount[]" value="${amount}" min="1" required>
                    </div>
                    <button class="btn btn-danger btn-sm py-0 px-2" type="button" style="height: 28px;"
                            onclick="$('#${rowId}').remove()" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#material_listEdit').append(content);
        window.editMaterialNo++;
    }

    function btnMaterialEditAdd() {
        let id = $('#material_nameEdit').val();
        if (id) {
            let opt = $("#material_nameEdit option:selected");
            let text = opt.data('name') || opt.text();
            let img = opt.data('img') || '';
            addEditMaterialRow(id, text, 1, img);
            $('#material_nameEdit').val("").trigger('change');
        }
    }

    function quickChangeSubtaskMaterial(e, btn, delta) {
        e.stopPropagation();
        const $btn = $(btn);
        const materialId = $btn.data('material-id');
        const $subItem = $btn.closest('.task-sub-item');
        const $badge = $subItem.find('.status-need, .status-ok');
        const $taskCard = $btn.closest('.task-card');
        const needed = parseInt($badge.data('needed')) || 0;
        const craft = parseInt($badge.data('craft')) || 0;
        const iconClass = delta > 0 ? 'bi-plus-lg' : 'bi-dash-lg';

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;" role="status"></span>');

        $.ajax({
            url: "{{ route('editMaterial') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                formidUpdatematerial: materialId,
                formAmountMaterial: delta
            },
            success: function(res) {
                $btn.prop('disabled', false).html(`<i class="bi ${iconClass}"></i>`);
                if (res && res.success) {
                    let totalDimiliki = res.new_amount + craft;
                    $badge.text(`${totalDimiliki}/${needed}`);

                    if (totalDimiliki >= needed) {
                        $badge.removeClass('status-need').addClass('status-ok');
                    } else {
                        $badge.removeClass('status-ok').addClass('status-need');
                    }

                    let allOk = true;
                    $taskCard.find('.task-sub-item').each(function() {
                        let $b = $(this).find('.status-need, .status-ok');
                        if ($b.hasClass('status-need')) {
                            allOk = false;
                        }
                    });

                    $taskCard.attr('data-ready', allOk ? '1' : '0');
                    const $actionsDiv = $taskCard.find('.task-actions');
                    const taskId = $taskCard.find('form[action*="task/"]').attr('action').split('/').pop();

                    if (allOk) {
                        $taskCard.find('.task-header-box .badge').removeClass('status-need').addClass('status-ok').html('<i class="bi bi-check-circle-fill me-1"></i>Siap Upgrade');
                        $taskCard.find('.progress-bar').removeClass('bg-warning').addClass('bg-success');
                        $actionsDiv.find('.task-upgrade-btn-wrapper').html(`
                            <a href="{{ url('taskComplete') }}/${taskId}"
                               onclick="return confirm('Selesaikan dan upgrade task ini? Material inventori akan dikurangi.')"
                               class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                                <i class="bi bi-arrow-up-circle-fill me-1"></i>Upgrade Sekarang
                            </a>
                        `);
                    } else {
                        $taskCard.find('.task-header-box .badge').removeClass('status-ok').addClass('status-need').html('<i class="bi bi-hourglass-split me-1"></i>Butuh Material');
                        $taskCard.find('.progress-bar').removeClass('bg-success').addClass('bg-warning');
                    }
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(`<i class="bi ${iconClass}"></i>`);
            }
        });
    }

    function showMaterial(obj) {
        const data = $(obj).data('subtask');
        if (!data || !data.material) return;
        let content = '';

        if (data.material.sumberCraft && data.material.sumberCraft.length > 0) {
            for (const material of data.material.sumberCraft) {
                let stars = material.rarity ? '★'.repeat(material.rarity) : '';
                content += `
                    <div class="task-sub-item mb-2 align-items-center justify-content-between flex-wrap p-2">
                        <div class="d-flex align-items-center gap-2">
                            <img src="${material.images}" alt="" class="task-sub-img">
                            <div class="sub-info">
                                <div class="sub-name text-white fw-semibold small">${material.name}</div>
                                ${stars ? `<div class="rarity-stars">${stars}</div>` : ''}
                                <div class="small text-secondary" style="font-size: 0.72rem;"><span class="text-gold fw-bold">${material.amount}</span> dimiliki</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1 mt-1 mt-sm-0">
                            <button class="btn btn-warning btn-sm px-2 fw-semibold"
                                data-idmaterial="${material.id}"
                                data-name="${material.name}"
                                data-amount="${material.amount}"
                                onclick="craftMaterial(this)" title="Craft Material">
                                <i class="bi bi-hammer me-1"></i>Craft
                            </button>
                        </div>
                    </div>
                `;
            }
        }

        let mainStars = data.material.rarity ? '★'.repeat(data.material.rarity) : '';
        content += `
            <div class="task-sub-item align-items-center justify-content-between flex-wrap p-2" style="background: rgba(200, 170, 110, 0.08); border-color: rgba(200, 170, 110, 0.3);">
                <div class="d-flex align-items-center gap-2">
                    <img src="${data.material.images}" alt="" class="task-sub-img">
                    <div class="sub-info">
                        <div class="sub-name text-white fw-bold small">${data.material.name}</div>
                        ${mainStars ? `<div class="rarity-stars">${mainStars}</div>` : ''}
                        <div class="small text-secondary" style="font-size: 0.75rem;">Stok: <strong class="text-gold">${data.material.amount}</strong> item</div>
                    </div>
                </div>
            </div>
        `;

        $('#materialCrafting').html(content);
        $('#titleModal').html(data.material.name);
        $('#modalDetailMaterial').modal('show');
    }

    function craftMaterial(obj) {
        const id = $(obj).data('idmaterial');
        const amount = parseInt($(obj).data('amount') / 3);
        const name = $(obj).data('name');

        $('#titleCraftModal').html(name);
        $('#formidmaterial').val(id);
        $('#formRangeCraft').attr('max', amount);
        $('#formRangeCraft').val(amount > 0 ? 1 : 0);
        $('#valueCraft').html($('#formRangeCraft').val());

        $('#formRangeCraft').off('input').on('input', function(event) {
            $('#valueCraft').html(event.target.value);
        });

        $('#modalRangeCraft').modal('show');
        $('#modalDetailMaterial').modal('hide');
    }
</script>
@endpush
@endsection
