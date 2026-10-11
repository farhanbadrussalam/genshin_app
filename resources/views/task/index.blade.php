@extends('layout.main')

@section('content')
@php $title = 'Task Tracker'; @endphp
@include('layout.header')

@push('styles')
<style>
    /* ─── Task Tracker Enhanced Checklist Styles ─── */
    .task-page-container {
        padding-top: 1.25rem;
        padding-bottom: 5rem;
    }

    .task-card {
        background: rgba(19, 23, 42, 0.88);
        border: 1px solid rgba(200, 170, 110, 0.25);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .task-card:hover {
        border-color: rgba(200, 170, 110, 0.45);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55);
    }
    .task-card.task-completed {
        background: rgba(16, 24, 38, 0.75);
        border-color: rgba(34, 197, 94, 0.3);
        opacity: 0.9;
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
        width: 52px;
        height: 52px;
        border-radius: 12px;
        object-fit: cover;
        background: rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(200, 170, 110, 0.35);
        flex-shrink: 0;
    }

    .task-title-text {
        font-size: 1.15rem;
        font-weight: 700;
        color: #f8fafc;
        margin-bottom: 0.25rem;
    }

    .task-sub-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 12px;
        padding: 0.65rem 0.85rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        transition: all 0.2s ease;
    }
    .task-sub-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(200, 170, 110, 0.3);
    }
    .task-sub-item.sub-item-done {
        background: rgba(34, 197, 94, 0.08);
        border-color: rgba(34, 197, 94, 0.35);
    }
    .task-sub-item.sub-item-done .sub-mat-name {
        text-decoration: line-through;
        opacity: 0.75;
    }

    .task-sub-img {
        width: 40px;
        height: 40px;
        border-radius: 10px;
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

    .badge-farming-today {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: rgba(16, 185, 129, 0.18) !important;
        border: 1px solid rgba(16, 185, 129, 0.45) !important;
        color: #6ee7b7 !important;
        display: inline-flex;
        align-items: center;
        line-height: 1.2;
    }

    .checklist-progress-text {
        font-size: 0.75rem;
        color: #94a3b8 !important;
        font-weight: 500;
    }

    /* Checkbox kustom yang elegan */
    .chk-subtask {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        cursor: pointer;
        accent-color: #10b981;
    }

    .task-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* Tab Filter Pills */
    .nav-task-tabs .nav-link {
        color: #94a3b8;
        border-radius: 10px;
        padding: 0.45rem 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .nav-task-tabs .nav-link.active {
        background: linear-gradient(135deg, rgba(200, 170, 110, 0.2), rgba(223, 192, 133, 0.1));
        color: #ffd700;
        border-color: rgba(200, 170, 110, 0.45);
    }
    .nav-task-tabs .nav-link:hover:not(.active) {
        color: #f8fafc;
        background: rgba(255, 255, 255, 0.04);
    }

    .genshin-modal-content {
        background: #121524 !important;
        border: 1px solid rgba(200, 170, 110, 0.35) !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8) !important;
        color: #f1f2f6;
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
                <i class="bi bi-list-check me-2"></i>Task Checklist & Roadmap
            </h1>
            <p class="small text-secondary mb-0" style="color: #cbd5e1 !important;">
                Checklist target upgrade ascension karakter & senjata. Tandai selesai langsung saat upgrade di game tanpa perlu input hitungan material.
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

    {{-- Account Switcher Bar --}}
    <div class="account-selector-bar d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-4 rounded-3" 
         style="background: rgba(19, 23, 42, 0.85); border: 1px solid rgba(200, 170, 110, 0.3); backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);">
        <div class="d-flex align-items-center gap-3">
            <div class="account-badge-avatar d-flex align-items-center justify-content-center shadow-sm"
                 style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, rgba(200, 170, 110, 0.25), rgba(223, 192, 133, 0.1)); border: 1px solid rgba(200, 170, 110, 0.45); color: #ffd700; font-size: 1.35rem;">
                <i class="bi bi-controller"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-white fw-bold fs-6">{{ $activeAccount?->nickname ?? 'Semua Akun' }}</span>
                    @if($activeAccount)
                        <span class="badge border border-info border-opacity-25 bg-info bg-opacity-10 text-info font-monospace small" style="font-size: 0.72rem;">
                            UID: {{ $activeAccount->uid }}
                        </span>
                        <span class="badge border border-warning border-opacity-25 bg-warning bg-opacity-10 text-warning text-uppercase small" style="font-size: 0.72rem;">
                            {{ $activeAccount->server }}
                        </span>
                    @endif
                </div>
                <div class="small text-secondary" style="font-size: 0.8rem; color: #94a3b8 !important;">
                    Menampilkan task checklist khusus untuk akun ini.
                </div>
            </div>
        </div>
        
        {{-- Akun Switcher Pills --}}
        <div class="d-flex align-items-center gap-2 ms-auto">
            <span class="small text-gold fw-semibold d-none d-md-inline" style="font-size: 0.8rem;">Ganti Akun:</span>
            <div class="btn-group" role="group">
                @foreach($accounts as $acc)
                    <a href="{{ route('task.index', ['account_id' => $acc->id, 'status' => $currentStatus]) }}" 
                       class="btn btn-sm {{ ($activeAccount && $activeAccount->id == $acc->id) ? 'btn-warning fw-bold text-dark shadow-sm' : 'btn-outline-secondary text-light' }}"
                       style="border-radius: 8px; margin-right: 4px; font-size: 0.8rem; {{ ($activeAccount && $activeAccount->id == $acc->id) ? 'background: linear-gradient(135deg, #c8aa6e, #dfc085); border-color: #dfc085;' : 'border-color: rgba(255, 255, 255, 0.15);' }}">
                        <i class="bi bi-person-fill me-1"></i>{{ $acc->nickname }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-content">
                    <span class="stat-value text-warning">{{ $activeCount }}</span>
                    <span class="stat-label">Sedang Dikerjakan</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon text-success"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-content">
                    <span class="stat-value text-success">{{ $completedCount }}</span>
                    <span class="stat-label">Sudah Selesai</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon text-danger"><i class="bi bi-fire"></i></div>
                <div class="stat-content">
                    <span class="stat-value text-danger">{{ $highPriorityCount }}</span>
                    <span class="stat-label">Prioritas P1-P3 (Aktif)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Filter Status (Sedang Dikerjakan vs Selesai vs Semua) --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-2 rounded-3" style="background: rgba(19, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08);">
        <ul class="nav nav-pills nav-task-tabs gap-1" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $currentStatus === 'start' ? 'active' : '' }}" 
                   href="{{ route('task.index', ['account_id' => $activeAccount?->id, 'status' => 'start']) }}">
                    <i class="bi bi-clock-history me-1"></i>Sedang Dikerjakan
                    <span class="badge rounded-pill ms-1" style="background: rgba(255, 255, 255, 0.15); color: #f8fafc; font-size: 0.72rem;">{{ $activeCount }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $currentStatus === 'complete' ? 'active' : '' }}" 
                   href="{{ route('task.index', ['account_id' => $activeAccount?->id, 'status' => 'complete']) }}">
                    <i class="bi bi-check-all me-1"></i>Sudah Selesai
                    <span class="badge rounded-pill ms-1" style="background: rgba(255, 255, 255, 0.15); color: #f8fafc; font-size: 0.72rem;">{{ $completedCount }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $currentStatus === 'all' ? 'active' : '' }}" 
                   href="{{ route('task.index', ['account_id' => $activeAccount?->id, 'status' => 'all']) }}">
                    <i class="bi bi-grid-fill me-1"></i>Semua Task
                </a>
            </li>
        </ul>

        {{-- Search input --}}
        <div class="ms-auto" style="min-width: 220px;">
            <input type="text" class="form-control form-control-sm genshin-select" id="filterTaskInput"
                   placeholder="Cari task / karakter / bahan...">
        </div>
    </div>

    {{-- Task Toolbar & Collapse Controls --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 px-1">
        <div class="small text-secondary" id="taskCountLabel" style="color: #cbd5e1 !important;">
            Menampilkan <strong class="text-white">{{ $dataTask->count() }}</strong> target upgrade
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill" id="btnExpandAllTasks" title="Buka seluruh rincian checklist task" style="font-size: 0.78rem; border-color: rgba(255, 255, 255, 0.18); color: #cbd5e1;">
                <i class="bi bi-chevron-bar-expand me-1 text-gold"></i>Buka Semua
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill" id="btnCollapseAllTasks" title="Tutup seluruh rincian checklist task" style="font-size: 0.78rem; border-color: rgba(255, 255, 255, 0.18); color: #cbd5e1;">
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
                $isCompleted = ($task->status === 'complete');
            @endphp

            <div class="task-card {{ $isCompleted ? 'task-completed' : '' }} animate-fade-in-up" 
                 id="taskCard_{{ $task->id }}" 
                 data-jenis="{{ strtolower($task->jenis) }}">
                
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
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h3 class="task-title-text h5 mb-0 {{ $isCompleted ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $task->nama_task }}
                                </h3>
                                @if($isCompleted)
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; font-size: 0.72rem; font-weight: 600;">
                                        <i class="bi bi-check2-circle me-1"></i>Selesai
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                <span class="badge-jenis">{{ $task->jenis }}</span>
                                <span class="badge-priority" style="background: {{ $prioBg }}; border: 1px solid {{ $prioBorder }}; color: {{ $prioColor }};">
                                    P{{ $task->prioritas }}
                                </span>
                                @if($task->has_farming_today && !$isCompleted)
                                    <span class="badge-farming-today">
                                        <i class="bi bi-calendar-check-fill me-1"></i>Buka Hari Ini ({{ $todayName }})
                                    </span>
                                @endif
                                <span class="checklist-progress-text" id="progressText_{{ $task->id }}">
                                    <strong style="color: #cbd5e1;">{{ $task->completed_subtasks }}/{{ $task->total_subtasks }}</strong> Checklist Selesai ({{ $task->progress_pct }}%)
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Tindakan Cepat di Header --}}
                    <div class="d-flex align-items-center gap-2">
                        @if(!$isCompleted)
                            <a href="{{ route('task.toggle-status', ['id' => $task->id, 'redirect_status' => $currentStatus]) }}"
                               onclick="return confirm('Tandai task \"{{ $task->nama_task }}\" sebagai sudah selesai dikerjakan?');"
                               class="btn btn-success btn-sm fw-bold px-3 shadow-sm d-flex align-items-center gap-1"
                               style="background: linear-gradient(135deg, #10b981, #059669); border: none;"
                               title="Tandai task ini sudah selesai dikerjakan">
                                <i class="bi bi-check-lg fs-6"></i>
                                <span class="d-none d-sm-inline">Tandai Selesai</span>
                            </a>
                        @else
                            <a href="{{ route('task.toggle-status', ['id' => $task->id, 'redirect_status' => $currentStatus]) }}"
                               class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1"
                               title="Kembalikan task ke daftar aktif">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span class="d-none d-sm-inline">Buka Kembali</span>
                            </a>
                        @endif

                        <button type="button" class="btn btn-task-toggle text-secondary" data-bs-toggle="collapse" data-bs-target="#taskCollapse{{ $task->id }}" title="Buka / Tutup Rincian Task">
                            <i class="bi bi-chevron-up toggle-icon"></i>
                        </button>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="progress mb-3" style="height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 4px;">
                    <div class="progress-bar bg-success" role="progressbar" id="progressBar_{{ $task->id }}"
                         style="width: {{ $task->progress_pct }}%;" aria-valuenow="{{ $task->progress_pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                {{-- Collapsible Content: Sub Tasks & Actions --}}
                <div class="collapse show task-details-collapse" id="taskCollapse{{ $task->id }}">
                    <div class="pt-1">
                        {{-- Daftar Resep Kebutuhan & Checklist Per Material --}}
                        <div class="task-sub-items mb-2">
                    @forelse ($task->sub_task as $sub_task)
                        @php
                            $mat = $sub_task->material;
                            if (!$mat) continue;
                            $daysArr = is_string($mat->daysofweek) ? json_decode($mat->daysofweek) : [];
                            $isSubDone = $sub_task->is_completed || $isCompleted;
                        @endphp
                        <div class="task-sub-item {{ $isSubDone ? 'sub-item-done' : '' }}" id="subItemRow_{{ $sub_task->id }}">
                            <div class="d-flex align-items-center gap-3 overflow-hidden">
                                {{-- Checkbox Santai per Material --}}
                                <input type="checkbox" 
                                       class="chk-subtask form-check-input" 
                                       id="chkSub_{{ $sub_task->id }}"
                                       {{ $isSubDone ? 'checked' : '' }}
                                       onchange="toggleSubChecklist({{ $sub_task->id }}, {{ $task->id }})"
                                       title="Centang jika kebutuhan material ini sudah tercukupi / selesai difarming">

                                <img src="{{ $mat->images }}" alt="{{ $mat->name }}" class="task-sub-img" loading="lazy" referrerpolicy="no-referrer">
                                <div class="overflow-hidden">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="sub-mat-name fw-bold text-white small text-truncate">{{ $mat->name }}</span>
                                        @if($mat->is_available_today)
                                            <span class="badge-farming-today" style="font-size: 0.65rem; padding: 0.15rem 0.45rem;">
                                                <i class="bi bi-calendar-check-fill me-0.5"></i>Domain Buka Hari Ini
                                            </span>
                                        @elseif(!empty($daysArr))
                                            <span class="badge" style="background: rgba(148, 163, 184, 0.12); border: 1px solid rgba(148, 163, 184, 0.3); color: #cbd5e1; font-size: 0.65rem;">
                                                <i class="bi bi-calendar-event me-0.5"></i>Buka: {{ implode(', ', $daysArr) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-secondary small" style="font-size: 0.75rem; color: #94a3b8 !important;">
                                        {{ $mat->category ?? 'Material' }} &bull; {{ $mat->dropdomain ?? 'Farm Domain / Drops' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Target Kebutuhan --}}
                            <div class="text-end flex-shrink-0">
                                <span class="badge border border-warning border-opacity-30 text-gold fw-bold px-2 py-1" style="background: rgba(200, 170, 110, 0.1); font-size: 0.8rem;">
                                    Target: {{ $sub_task->amount }}x
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small py-2 text-center">
                            Tidak ada rincian material kebutuhan pada task ini.
                        </div>
                    @endforelse
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
                        @if(!$isCompleted)
                            <a href="{{ route('task.toggle-status', ['id' => $task->id, 'redirect_status' => $currentStatus]) }}"
                               onclick="return confirm('Tandai task \"{{ $task->nama_task }}\" sebagai sudah selesai dikerjakan?');"
                               class="btn btn-success btn-sm fw-bold px-3 shadow-sm d-flex align-items-center gap-1"
                               style="background: linear-gradient(135deg, #10b981, #059669); border: none;"
                               title="Tandai task ini sudah selesai dikerjakan">
                                <i class="bi bi-check-circle-fill me-1"></i>Selesai Dikerjakan
                            </a>
                        @else
                            <a href="{{ route('task.toggle-status', ['id' => $task->id, 'redirect_status' => $currentStatus]) }}"
                               class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 text-light">
                                <i class="bi bi-arrow-counterclockwise me-1 text-warning"></i>Buka Kembali ke Aktif
                            </a>
                        @endif
                    </div>
                </div>
                    </div>
                </div>

            </div>
        @empty
            <div class="text-center py-5 my-4 rounded-3" style="background: rgba(19, 23, 42, 0.6); border: 1px dashed rgba(200, 170, 110, 0.3);">
                <i class="bi bi-check2-all text-gold opacity-50 d-block mb-3" style="font-size: 3rem;"></i>
                <h4 class="h5 text-white fw-bold mb-1">
                    {{ $currentStatus === 'complete' ? 'Belum Ada Task yang Selesai' : 'Tidak Ada Task yang Sedang Dikerjakan' }}
                </h4>
                <p class="text-secondary small mb-3">
                    {{ $currentStatus === 'complete' ? 'Tandai task sebagai selesai jika Anda sudah menyelesaikan upgrade.' : 'Tambahkan target upgrade baru atau buat rencana dari Kalkulator!' }}
                </p>
                <button type="button" class="btn btn-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#tambahTask">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Task Sekarang
                </button>
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
                    {{-- 0. Akun Game Target --}}
                    <div class="mb-3">
                        <label class="form-label text-gold small fw-bold mb-1">
                            <i class="bi bi-controller me-1"></i>Akun Game Target
                        </label>
                        <select class="form-select form-select-sm genshin-select" id="game_account_id" name="game_account_id">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (isset($activeAccount) && $activeAccount->id == $acc->id) ? 'selected' : '' }}>
                                    {{ $acc->nickname }} (UID: {{ $acc->uid }} &bull; Server: {{ strtoupper($acc->server) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

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

                        {{-- Senjata Selector --}}
                        <div class="col-12" id="boxSelectWeapon" style="display: none;">
                            <label class="form-label text-gold small fw-bold mb-1">
                                <i class="bi bi-shield-shaded me-1"></i>Pilih Senjata
                            </label>
                            <select class="form-select form-select-sm genshin-select" id="select_weapon" name="target_weapon_id" style="width: 100%;">
                                <option value="">-- Cari & Pilih Senjata --</option>
                                @foreach($weapons as $w)
                                    <option value="{{ $w->id }}" data-name="{{ $w->name }}" data-icon="{{ $w->icon_url }}">
                                        {{ $w->name }} ({{ ucfirst($w->type) }} &bull; {{ $w->rarity }}★)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 3. Detail Nama & Prioritas Task --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label class="form-label text-gold small fw-bold mb-1">
                                <i class="bi bi-card-text me-1"></i>Nama Task
                            </label>
                            <input type="text" class="form-control form-control-sm genshin-select"
                                   id="nameTask" name="nameTask"
                                   placeholder="Contoh: Upgrade Furina Lv 90" required>
                            <input type="hidden" id="urlImage" name="urlImage">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-gold small fw-bold mb-1">
                                <i class="bi bi-sort-numeric-down me-1"></i>Prioritas (1 = Tertinggi)
                            </label>
                            <select class="form-select form-select-sm genshin-select" id="prioritas" name="prioritas">
                                <option value="1">1 - Sangat Mendesak / Utama</option>
                                <option value="2">2 - Prioritas Tinggi</option>
                                <option value="3" selected>3 - Standar / Normal</option>
                                <option value="4">4 - Santai / Sekunder</option>
                                <option value="5">5 - Jangka Panjang</option>
                            </select>
                        </div>
                    </div>

                    <hr class="genshin-divider">

                    {{-- 4. Daftar Bahan / Material Kebutuhan --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label text-gold small fw-bold mb-0">
                            <i class="bi bi-gem me-1"></i>Kebutuhan Material Farming (Opsional)
                        </label>
                        <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2" onclick="addMaterialRow()" style="font-size: 0.75rem;">
                            <i class="bi bi-plus me-1"></i>Tambah Bahan Manual
                        </button>
                    </div>

                    <div id="material_list" class="mb-3">
                        {{-- Row material dinamis akan di-append ke sini --}}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-4" style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                        Simpan Task
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
                    <div class="mb-3">
                        <label class="form-label small text-secondary mb-1" style="color: #cbd5e1 !important;">
                            <i class="bi bi-controller me-1 text-gold"></i>Akun Game Target
                        </label>
                        <select class="form-select form-select-sm genshin-select" id="game_account_idEdit" name="game_account_id">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->nickname }} (UID: {{ $acc->uid }} &bull; {{ strtoupper($acc->server) }})</option>
                            @endforeach
                        </select>
                    </div>
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

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label small text-secondary mb-0" style="color: #cbd5e1 !important;">
                            Target Kebutuhan Material
                        </label>
                        <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2" onclick="addEditMaterialRowManually()" style="font-size: 0.75rem;">
                            <i class="bi bi-plus me-1"></i>Tambah Bahan
                        </button>
                    </div>

                    <div id="material_listEdit" class="mb-3"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-4" style="background: linear-gradient(135deg, #c8aa6e, #dfc085); border: none; color: #111827;">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    window.masterMaterials = @json($dataMaterial);

    // Toggle Checklist Santai per Material via AJAX
    function toggleSubChecklist(subId, taskId) {
        const chk = document.getElementById('chkSub_' + subId);
        const row = document.getElementById('subItemRow_' + subId);
        if (!chk) return;

        fetch('{{ url("task/toggle-subtask") }}/' + subId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.is_completed) {
                    row.classList.add('sub-item-done');
                } else {
                    row.classList.remove('sub-item-done');
                }
                // Update progress bar & label
                const bar = document.getElementById('progressBar_' + taskId);
                const txt = document.getElementById('progressText_' + taskId);
                if (bar) bar.style.width = data.progress_pct + '%';
                if (txt) txt.innerText = `${data.done_subtasks}/${data.total_subtasks} Checklist Selesai (${data.progress_pct}%)`;

                if (data.parent_status === 'complete') {
                    const card = document.getElementById('taskCard_' + taskId);
                    if (card) card.classList.add('task-completed');
                }
            }
        })
        .catch(err => {
            console.error('Error toggling subtask:', err);
        });
    }

    // Expand All / Collapse All functionality
    $('#btnExpandAllTasks').on('click', function() {
        $('.task-details-collapse').collapse('show');
        $('.btn-task-toggle').removeClass('collapsed');
    });

    $('#btnCollapseAllTasks').on('click', function() {
        $('.task-details-collapse').collapse('hide');
        $('.btn-task-toggle').addClass('collapsed');
    });

    // Toggle icon rotation on collapse events
    $(document).on('show.bs.collapse', '.task-details-collapse', function() {
        const id = $(this).attr('id');
        $(`[data-bs-target="#${id}"]`).removeClass('collapsed');
    });

    $(document).on('hide.bs.collapse', '.task-details-collapse', function() {
        const id = $(this).attr('id');
        $(`[data-bs-target="#${id}"]`).addClass('collapsed');
    });

    // Make whole header clickable to toggle collapse
    $(document).on('click', '.task-header-clickable', function(e) {
        if ($(e.target).closest('a, button, input, .btn').length) {
            return;
        }
        const targetId = $(this).data('target');
        if (targetId) {
            $(targetId).collapse('toggle');
        }
    });

    // Filter pencarian task
    document.getElementById('filterTaskInput')?.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('#taskListContainer .task-card').forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
        });
    });

    // Helper Select Jenis Task di Modal Tambah
    function onJenisTaskChanged(val) {
        if (val === 'weapon') {
            $('#boxSelectCharacter').hide();
            $('#boxSelectWeapon').show();
        } else if (val === 'custom') {
            $('#boxSelectCharacter').hide();
            $('#boxSelectWeapon').hide();
        } else {
            $('#boxSelectCharacter').show();
            $('#boxSelectWeapon').hide();
        }
    }

    $('#select_character').on('change', function() {
        const opt = $(this).find(':selected');
        const name = opt.data('name');
        const icon = opt.data('icon');
        if (name) {
            $('#nameTask').val('Upgrade ' + name);
            $('#urlImage').val(icon || '');
        }
    });

    $('#select_weapon').on('change', function() {
        const opt = $(this).find(':selected');
        const name = opt.data('name');
        const icon = opt.data('icon');
        if (name) {
            $('#nameTask').val('Upgrade ' + name);
            $('#urlImage').val(icon || '');
        }
    });

    // Tambah row material di form tambah
    window.materialRowCount = 0;
    function addMaterialRow(matId = '', amount = 1) {
        const rowId = 'mat_row_' + window.materialRowCount++;
        let options = '<option value="">-- Pilih Material --</option>';
        window.masterMaterials.forEach(m => {
            const sel = (m.id == matId) ? 'selected' : '';
            options += `<option value="${m.id}" ${sel}>${m.name} (${m.rarity}★)</option>`;
        });

        const html = `
            <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" id="${rowId}" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                <select class="form-select form-select-sm genshin-select flex-grow-1" name="namaMaterial[]" required>
                    ${options}
                </select>
                <div class="d-flex align-items-center gap-1" style="width: 140px;">
                    <span class="small text-secondary">Target:</span>
                    <input type="number" class="form-control form-control-sm genshin-select text-center" name="amount[]" value="${amount}" min="1" required>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="$('#${rowId}').remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
        $('#material_list').append(html);
    }

    // Modal Edit Task
    function editTaskModal(btn) {
        const data = $(btn).data('task');
        if (!data) return;

        $('#formEditTask').attr('action', '{{ url("task") }}/' + data.id);
        $('#nameTaskEdit').val(data.nama_task);
        $('#prioritasEdit').val(data.prioritas);
        $('#jenis_taskEdit').val(data.jenis);
        $('#urlImageEdit').val(data.images);
        $('#game_account_idEdit').val(data.game_account_id || '');

        $('#material_listEdit').empty();
        if (data.sub_task && data.sub_task.length > 0) {
            data.sub_task.forEach(st => {
                addEditMaterialRow(st.material_id, st.amount);
            });
        }

        $('#modalEditTask').modal('show');
    }

    window.editRowCount = 0;
    function addEditMaterialRow(matId = '', amount = 1) {
        const rowId = 'edit_mat_row_' + window.editRowCount++;
        let options = '<option value="">-- Pilih Material --</option>';
        window.masterMaterials.forEach(m => {
            const sel = (m.id == matId) ? 'selected' : '';
            options += `<option value="${m.id}" ${sel}>${m.name} (${m.rarity}★)</option>`;
        });

        const html = `
            <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" id="${rowId}" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                <select class="form-select form-select-sm genshin-select flex-grow-1" name="namaMaterial[]" required>
                    ${options}
                </select>
                <div class="d-flex align-items-center gap-1" style="width: 140px;">
                    <span class="small text-secondary">Target:</span>
                    <input type="number" class="form-control form-control-sm genshin-select text-center" name="amount[]" value="${amount}" min="1" required>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="$('#${rowId}').remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
        $('#material_listEdit').append(html);
    }

    function addEditMaterialRowManually() {
        addEditMaterialRow('', 1);
    }
</script>
@endpush
@endsection