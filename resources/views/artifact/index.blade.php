@extends('layout.main')

@section('content')
    @php $title = 'Master Artifact'; @endphp
    @include('layout.header')

    <div class="page-container" style="padding-top: 1.5rem; padding-bottom: 4rem;">

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="alert-toast success" id="alertToast">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Page Header & Stats --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
            <div>
                <h1 class="font-display text-gold mb-1" style="font-size: 1.35rem; letter-spacing: 0.08em;">
                    <i class="bi bi-gem me-2"></i>Master Artifact Set
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.84rem; margin-bottom: 0;">
                    Katalog set artifact Genshin Impact untuk referensi bonus 2-piece dan 4-piece build karakter
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="char-stat-badge">
                    <span class="label">Total Set</span>
                    <span class="val">{{ $stats['total'] }}</span>
                </div>
                <div class="char-stat-badge star-5">
                    <span class="label">★ 5</span>
                    <span class="val">{{ $stats['star5'] }}</span>
                </div>
                <button type="button" class="btn-genshin btn-genshin-sm ms-2"
                    style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5);"
                    data-bs-toggle="modal" data-bs-target="#modalSyncMasterArtifacts">
                    <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Database Artifact
                </button>
                <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal"
                    data-bs-target="#modalAddArtifactSet">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Set Baru
                </button>
            </div>
        </div>

        {{-- Filter & Search Panel --}}
        <div class="filter-panel mb-4 animate-fade-in-up">
            <form method="GET" action="{{ route('artifact.index') }}" id="artifactFilterForm">
                <div class="row g-2 align-items-center">
                    {{-- Search Input --}}
                    <div class="col-12 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text genshin-input-group-text"><i
                                    class="bi bi-search text-gold"></i></span>
                            <input type="text" name="search" class="form-control genshin-input"
                                placeholder="Cari nama set artifact..." value="{{ $filters['search'] ?? '' }}"
                                onchange="this.form.submit()">
                        </div>
                    </div>

                    {{-- Rarity Filter --}}
                    <div class="col-6 col-md-3">
                        <select name="rarity" class="form-select form-select-sm genshin-select"
                            onchange="this.form.submit()">
                            <option value="all">Semua Rarity</option>
                            <option value="5" {{ ($filters['rarity'] ?? '') === '5' ? 'selected' : '' }}>★★★★★ (Bintang
                                5)</option>
                            <option value="4" {{ ($filters['rarity'] ?? '') === '4' ? 'selected' : '' }}>★★★★ (Bintang
                                4)</option>
                        </select>
                    </div>

                    {{-- Reset --}}
                    <div class="col-6 col-md-3">
                        @if (!empty(array_filter($filters ?? [])))
                            <a href="{{ route('artifact.index') }}" class="btn btn-sm btn-outline-secondary w-100"
                                style="border-radius: 8px; font-size: 0.8rem;">
                                <i class="bi bi-x-circle me-1"></i>Reset Filter
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Artifact Grid --}}
        @if ($sets->isEmpty())
            <div class="empty-state animate-fade-in-up">
                <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;">💎</div>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Tidak ada set artifact yang cocok dengan filter
                    yang dipilih.</p>
                <a href="{{ route('artifact.index') }}" class="btn-genshin btn-genshin-sm mt-2">
                    <i class="bi bi-arrow-repeat me-1"></i>Reset Filter
                </a>
            </div>
        @else
            <div class="row g-3">
                @foreach ($sets as $s)
                    @php
                        $isFiveStar = $s->max_rarity === 5;
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4 animate-fade-in-up"
                        style="animation-delay: {{ ($loop->index % 12) * 0.04 }}s;">
                        <div class="inv-card {{ $isFiveStar ? 'card-star-5' : 'card-star-4' }}">

                            {{-- Header: Set Name & Rarity --}}
                            <div class="inv-card-header">
                                <span class="weapon-type-badge">
                                    @if ($s->set_id)
                                        <span class="text-white-50 me-1">#{{ $s->set_id }}</span>
                                    @endif
                                    Set Artifact
                                </span>
                                <span class="inv-const-tag">
                                    ★{{ $s->max_rarity }}
                                </span>
                            </div>

                            {{-- Image Box --}}
                            <div class="inv-avatar-box">
                                @if ($s->icon_url)
                                    @php
                                        $artifactImg = str_replace('gi.yatta.moe/assets/UI', 'enka.network/ui', $s->icon_url);
                                    @endphp
                                    <img src="{{ $artifactImg }}" alt="{{ $s->name }}" class="inv-avatar"
                                        loading="lazy" referrerpolicy="no-referrer"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="inv-avatar-placeholder"
                                        style="display: none; background: rgba(229, 160, 41, 0.1);">
                                        <i class="bi bi-gem" style="font-size: 2.5rem; color: var(--accent-gold);"></i>
                                    </div>
                                @else
                                    <div class="inv-avatar-placeholder" style="background: rgba(229, 160, 41, 0.1);">
                                        <i class="bi bi-gem" style="font-size: 2.5rem; color: var(--accent-gold);"></i>
                                    </div>
                                @endif

                                <div class="inv-level-badge">
                                    Max ★{{ $s->max_rarity }}
                                </div>
                            </div>

                            {{-- Body: Bonus Description --}}
                            <div class="inv-body text-start">
                                <h3 class="inv-name text-center" title="{{ $s->name }}">{{ $s->name }}</h3>
                                <div class="inv-stars mb-3 text-center">
                                    @for ($i = 0; $i < $s->max_rarity; $i++)
                                        <i class="bi bi-star-fill text-gold"></i>
                                    @endfor
                                </div>

                                {{-- 2-Piece Bonus --}}
                                <div class="artifact-bonus-box mb-2">
                                    <div class="bonus-title text-gold">
                                        <span class="badge bg-gold-subtle me-1">2-Pc</span> Bonus 2 Set:
                                    </div>
                                    <div class="bonus-desc">
                                        {{ $s->two_piece_bonus ?? '-' }}
                                    </div>
                                </div>

                                {{-- 4-Piece Bonus --}}
                                @if ($s->four_piece_bonus)
                                    <div class="artifact-bonus-box">
                                        <div class="bonus-title text-info">
                                            <span class="badge bg-info-subtle me-1">4-Pc</span> Bonus 4 Set:
                                        </div>
                                        <div class="bonus-desc">
                                            {{ Str::limit($s->four_piece_bonus, 130) }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Action Buttons (Sejajar & 34px) --}}
                            <div class="inv-footer">
                                <button class="btn-action edit" title="Edit Set Artifact" data-bs-toggle="modal"
                                    data-bs-target="#modalEditArtifactSet" data-id="{{ $s->id }}"
                                    data-setid="{{ $s->set_id }}"
                                    data-name="{{ $s->name }}" data-rarity="{{ $s->max_rarity }}"
                                    data-two="{{ $s->two_piece_bonus }}" data-four="{{ $s->four_piece_bonus }}"
                                    data-icon="{{ $s->icon_url }}">
                                    <i class="bi bi-sliders me-1"></i>Edit Set
                                </button>
                                <form action="{{ route('artifact.destroy', $s) }}" method="POST"
                                    class="d-inline form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-action delete btn-delete-set"
                                        data-name="{{ $s->name }}" title="Hapus Set">
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
                {{ $sets->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

    {{-- MODAL TAMBAH SET ARTIFACT --}}
    <div class="modal fade" id="modalAddArtifactSet" tabindex="-1" aria-labelledby="modalAddSetLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content genshin-modal-content">
                <div class="modal-header genshin-modal-header">
                    <h5 class="modal-title font-display text-gold" id="modalAddSetLabel">
                        <i class="bi bi-gem me-2"></i>Tambah Set Artifact
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('artifact.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label genshin-label">Nama Set <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control genshin-input"
                                    placeholder="contoh: Emblem of Severed Fate" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label genshin-label">Set ID <small class="text-white-50">(Game ID)</small></label>
                                <input type="number" name="set_id" class="form-control genshin-input"
                                    placeholder="15020">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Max Rarity <span class="text-danger">*</span></label>
                            <select name="max_rarity" class="form-select genshin-select" required>
                                <option value="5" selected>★★★★★ (Bintang 5)</option>
                                <option value="4">★★★★ (Bintang 4)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Bonus 2-Piece</label>
                            <textarea name="two_piece_bonus" class="form-control genshin-input" rows="2"
                                placeholder="contoh: Energy Recharge +20%"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Bonus 4-Piece</label>
                            <textarea name="four_piece_bonus" class="form-control genshin-input" rows="3"
                                placeholder="Deskripsi bonus efek 4 set..."></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label genshin-label">URL Icon (Flower of Life)</label>
                            <input type="url" name="icon_url" class="form-control genshin-input"
                                placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer genshin-modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-genshin btn-genshin-sm">
                            <i class="bi bi-check-lg me-1"></i>Simpan Set
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT SET ARTIFACT --}}
    <div class="modal fade" id="modalEditArtifactSet" tabindex="-1" aria-labelledby="modalEditSetLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content genshin-modal-content">
                <div class="modal-header genshin-modal-header">
                    <h5 class="modal-title font-display text-gold" id="modalEditSetLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Set: <span id="editSetTitle"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="formEditArtifactSet" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label genshin-label">Nama Set <span class="text-danger">*</span></label>
                                <input type="text" id="editSetName" name="name" class="form-control genshin-input"
                                    required>
                            </div>
                            <div class="col-4">
                                <label class="form-label genshin-label">Set ID <small class="text-white-50">(Game ID)</small></label>
                                <input type="number" id="editSetIdVal" name="set_id" class="form-control genshin-input"
                                    placeholder="15020">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Max Rarity <span class="text-danger">*</span></label>
                            <select id="editSetRarity" name="max_rarity" class="form-select genshin-select" required>
                                <option value="5">★★★★★ (Bintang 5)</option>
                                <option value="4">★★★★ (Bintang 4)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Bonus 2-Piece</label>
                            <textarea id="editSetTwo" name="two_piece_bonus" class="form-control genshin-input" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Bonus 4-Piece</label>
                            <textarea id="editSetFour" name="four_piece_bonus" class="form-control genshin-input" rows="3"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label genshin-label">URL Icon</label>
                            <input type="url" id="editSetIcon" name="icon_url" class="form-control genshin-input">
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

    {{-- MODAL SYNC MASTER ARTIFACT SETS --}}
    <div class="modal fade" id="modalSyncMasterArtifacts" tabindex="-1" aria-labelledby="modalSyncArtifactsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content genshin-modal-content">
                <div class="modal-header genshin-modal-header">
                    <h5 class="modal-title font-display text-gold" id="modalSyncArtifactsLabel">
                        <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Database Master Artifact Set
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('artifact.sync-all') }}" method="POST" id="formSyncMasterArtifacts">
                    @csrf
                    <div class="modal-body">
                        {{-- Pemilihan Sumber Data --}}
                        <div class="mb-3">
                            <label class="form-label genshin-label mb-2">
                                <i class="bi bi-hdd-network-fill me-1 text-gold"></i>Pilih Sumber Data (Data Source):
                            </label>
                            <div class="d-flex flex-column gap-2">
                                {{-- Opsi 1: Project Amber (Recommended) --}}
                                <label class="d-flex align-items-center p-2 rounded"
                                    style="background: rgba(229,160,41,0.08); border: 1.5px solid rgba(229,160,41,0.4); cursor: pointer; transition: all 0.2s ease;">
                                    <input type="radio" name="source" value="amber" checked class="form-check-input me-3"
                                        style="accent-color: #e5a029; margin-top: 0;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong class="text-white" style="font-size: 0.85rem;">Project Amber (gi.yatta.moe)</strong>
                                            <span class="badge"
                                                style="background: linear-gradient(135deg, #e5a029, #b45309); font-size: 0.65rem;">⭐ Direkomendasikan</span>
                                        </div>
                                        <div style="color: var(--text-secondary); font-size: 0.74rem; line-height: 1.4;">
                                            Katalog terlengkap (63+ set artefak), menyertakan bonus 2-set dan 4-set lengkap, max rarity, serta icon HD resmi.
                                        </div>
                                    </div>
                                </label>

                                {{-- Opsi 2: Enka.Network --}}
                                <label class="d-flex align-items-center p-2 rounded"
                                    style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s ease;">
                                    <input type="radio" name="source" value="enka" class="form-check-input me-3"
                                        style="accent-color: #e5a029; margin-top: 0;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong class="text-white" style="font-size: 0.85rem;">Enka.Network (Set Map)</strong>
                                            <span class="badge" style="background: #3b82f6; font-size: 0.65rem;">Default Komunitas</span>
                                        </div>
                                        <div style="color: var(--text-secondary); font-size: 0.74rem; line-height: 1.4;">
                                            Kamus Set ID bawaan dari Enka Network.
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="p-3 mb-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                            <div style="font-size: 0.82rem; color: var(--text-primary); font-weight: 600;" class="mb-1">
                                <i class="bi bi-info-circle-fill me-1 text-gold"></i>Detail Operasi:
                            </div>
                            <ul class="mb-0 ps-3 text-white-50" style="font-size: 0.76rem; line-height: 1.6;">
                                <li>Set baru yang belum ada di database akan langsung otomatis ditambahkan.</li>
                                <li>Bonus 2-Piece, 4-Piece, max rarity, icon, dan Set ID akan diperbarui jika ada revisi resmi.</li>
                                <li>Data inventori artefak milik akun pemain Anda tidak akan terhapus.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer genshin-modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary text-white"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-genshin btn-genshin-sm" id="btnSubmitSyncArtifacts">
                            <span class="btn-text"><i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai Sinkronisasi Master</span>
                            <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Sedang Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEdit = document.getElementById('modalEditArtifactSet');
                if (modalEdit) {
                    modalEdit.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const id = button.getAttribute('data-id');
                        const setid = button.getAttribute('data-setid');
                        const name = button.getAttribute('data-name');
                        const rarity = button.getAttribute('data-rarity');
                        const two = button.getAttribute('data-two');
                        const four = button.getAttribute('data-four');
                        const icon = button.getAttribute('data-icon');

                        const form = document.getElementById('formEditArtifactSet');
                        form.action = `/artifact/${id}`;

                        document.getElementById('editSetTitle').innerText = name;
                        document.getElementById('editSetName').value = name;
                        document.getElementById('editSetIdVal').value = setid || '';
                        document.getElementById('editSetRarity').value = rarity;
                        document.getElementById('editSetTwo').value = two || '';
                        document.getElementById('editSetFour').value = four || '';
                        document.getElementById('editSetIcon').value = icon || '';
                    });
                }

                // Loading state saat submit sync
                const formSync = document.getElementById('formSyncMasterArtifacts');
                if (formSync) {
                    formSync.addEventListener('submit', function() {
                        const btn = document.getElementById('btnSubmitSyncArtifacts');
                        if (btn) {
                            btn.disabled = true;
                            btn.querySelector('.btn-text').classList.add('d-none');
                            btn.querySelector('.btn-loading').classList.remove('d-none');
                        }
                    });
                }

                document.querySelectorAll('.btn-delete-set').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const form = this.closest('form');
                        const name = this.getAttribute('data-name');

                        Swal.fire({
                            title: 'Hapus Set Artifact?',
                            text: `Set "${name}" akan dihapus dari master data.`,
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
            });
        </script>
    @endpush
@endsection
