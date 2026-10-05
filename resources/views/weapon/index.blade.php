@extends('layout.main')

@section('content')
    @php $title = 'Master Senjata'; @endphp
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
                    <i class="bi bi-shield-shaded me-2"></i>Master Senjata
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.84rem; margin-bottom: 0;">
                    Katalog senjata Genshin Impact untuk referensi inventori, scanning, dan optimasi build
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="char-stat-badge">
                    <span class="label">Total</span>
                    <span class="val">{{ $stats['total'] }}</span>
                </div>
                <div class="char-stat-badge star-5">
                    <span class="label">★ 5</span>
                    <span class="val">{{ $stats['star5'] }}</span>
                </div>
                <div class="char-stat-badge star-4">
                    <span class="label">★ 4</span>
                    <span class="val">{{ $stats['star4'] }}</span>
                </div>
                <button type="button" class="btn-genshin btn-genshin-sm ms-2"
                    style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5);"
                    data-bs-toggle="modal" data-bs-target="#modalSyncMasterWeapons">
                    <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Database Senjata
                </button>
                <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal" data-bs-target="#modalAddWeapon">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Senjata
                </button>
            </div>
        </div>

        {{-- Filter & Search Panel --}}
        <div class="filter-panel mb-4 animate-fade-in-up">
            <form method="GET" action="{{ route('weapon.index') }}" id="weaponFilterForm">
                <div class="row g-2 align-items-center">
                    {{-- Search Input --}}
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text genshin-input-group-text"><i
                                    class="bi bi-search text-gold"></i></span>
                            <input type="text" name="search" class="form-control genshin-input"
                                placeholder="Cari nama senjata..." value="{{ $filters['search'] ?? '' }}"
                                onchange="this.form.submit()">
                        </div>
                    </div>

                    {{-- Type Filter --}}
                    <div class="col-6 col-sm-4 col-md-3">
                        <select name="type" class="form-select form-select-sm genshin-select"
                            onchange="this.form.submit()">
                            <option value="all">Semua Tipe</option>
                            @foreach (['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'] as $t)
                                <option value="{{ $t }}" {{ ($filters['type'] ?? '') === $t ? 'selected' : '' }}>
                                    {{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rarity Filter --}}
                    <div class="col-6 col-sm-4 col-md-2">
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
                    <div class="col-12 col-sm-4 col-md-2">
                        @if (!empty(array_filter($filters ?? [])))
                            <a href="{{ route('weapon.index') }}" class="btn btn-sm btn-outline-secondary w-100"
                                style="border-radius: 8px; font-size: 0.8rem;">
                                <i class="bi bi-x-circle me-1"></i>Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Weapon Grid --}}
        @if ($weapons->isEmpty())
            <div class="empty-state animate-fade-in-up">
                <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;">⚔️</div>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Tidak ada senjata yang cocok dengan filter yang
                    dipilih.</p>
                <a href="{{ route('weapon.index') }}" class="btn-genshin btn-genshin-sm mt-2">
                    <i class="bi bi-arrow-repeat me-1"></i>Reset Filter
                </a>
            </div>
        @else
            <div class="row g-3">
                @foreach ($weapons as $w)
                    @php
                        $isFiveStar = $w->rarity === 5;
                    @endphp
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 animate-fade-in-up"
                        style="animation-delay: {{ ($loop->index % 12) * 0.04 }}s;">
                        <div class="inv-card {{ $isFiveStar ? 'card-star-5' : 'card-star-4' }}">

                            {{-- Header Card: Type & Rarity --}}
                            <div class="inv-card-header">
                                <span class="weapon-type-badge">
                                    {{ $w->type }}
                                </span>
                                <span class="inv-const-tag">
                                    ★{{ $w->rarity }}
                                </span>
                            </div>

                            {{-- Weapon Image Box --}}
                            <div class="inv-avatar-box">
                                @if ($w->icon_url)
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

                                {{-- Base ATK Overlay --}}
                                <div class="inv-level-badge">
                                    Base ATK {{ $w->base_atk }}
                                </div>
                            </div>

                            {{-- Weapon Body Info --}}
                            <div class="inv-body">
                                <h3 class="inv-name" title="{{ $w->name }}">{{ $w->name }}</h3>
                                <div class="inv-stars mb-2">
                                    @for ($i = 0; $i < $w->rarity; $i++)
                                        <i class="bi bi-star-fill text-gold"></i>
                                    @endfor
                                </div>

                                {{-- Sub Stat --}}
                                @if ($w->sub_stat_type)
                                    <div class="mb-2">
                                        <span class="badge"
                                            style="background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));">
                                            {{ $w->sub_stat_type }}: {{ $w->sub_stat_value }}
                                        </span>
                                    </div>
                                @else
                                    <div class="mb-2" style="visibility: hidden;">
                                        <span class="badge">-</span>
                                    </div>
                                @endif

                                {{-- Passive Skill Name --}}
                                @if ($w->passive_name)
                                    <div class="equipped-badge mb-1" title="{{ $w->passive_desc }}">
                                        <i class="bi bi-magic me-1 text-gold"></i>
                                        <span>{{ $w->passive_name }}</span>
                                    </div>
                                @else
                                    <div class="equipped-badge free mb-1">
                                        <i class="bi bi-dash me-1"></i>
                                        <span>Tanpa Pasif Khusus</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Action Buttons --}}
                            <div class="inv-footer">
                                <button class="btn-action edit" title="Edit Senjata" data-bs-toggle="modal"
                                    data-bs-target="#modalEditWeapon" data-id="{{ $w->id }}"
                                    data-name="{{ $w->name }}" data-type="{{ $w->type }}"
                                    data-rarity="{{ $w->rarity }}" data-atk="{{ $w->base_atk }}"
                                    data-sub-type="{{ $w->sub_stat_type }}" data-sub-val="{{ $w->sub_stat_value }}"
                                    data-p-name="{{ $w->passive_name }}" data-p-desc="{{ $w->passive_desc }}"
                                    data-icon="{{ $w->icon_url }}">
                                    <i class="bi bi-sliders me-1"></i>Edit
                                </button>
                                <form action="{{ route('weapon.destroy', $w) }}" method="POST"
                                    class="d-inline form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-action delete btn-delete-weapon"
                                        data-name="{{ $w->name }}" title="Hapus Senjata">
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
                {{ $weapons->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

    {{-- MODAL SYNC MASTER SENJATA --}}
    <div class="modal fade" id="modalSyncMasterWeapons" tabindex="-1" aria-labelledby="modalSyncMasterWeaponsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content genshin-modal-content">
                <div class="modal-header genshin-modal-header">
                    <h5 class="modal-title font-display text-gold" id="modalSyncMasterWeaponsLabel">
                        <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Seluruh Master Senjata
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('weapon.sync-all') }}" method="POST" id="formSyncMasterWeapons">
                    @csrf
                    <div class="modal-body">
                        <div class="p-3 mb-3 rounded"
                            style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.25);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge" style="background: #22c55e; color: #fff; font-size: 0.72rem;">✨
                                    Katalog Resmi Genshin</span>
                                <span class="badge" style="background: #3b82f6; color: #fff; font-size: 0.72rem;">⚡
                                    Otomatis 250+ Senjata</span>
                            </div>
                            <p class="mb-0 text-white" style="font-size: 0.82rem; line-height: 1.55;">
                                Sistem akan secara otomatis menyinkronkan seluruh database master senjata dengan katalog
                                resmi game terbaru (termasuk senjata Natlan, Fontaine, event weapon, dll.) langsung dari
                                repositori data Enka / Genshin.
                            </p>
                        </div>

                        <div class="p-3 mb-2 rounded"
                            style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                            <div style="font-size: 0.82rem; color: var(--text-primary); font-weight: 600;" class="mb-1">
                                <i class="bi bi-info-circle-fill me-1 text-gold"></i>Detail Operasi:
                            </div>
                            <ul class="mb-0 ps-3 text-white-50" style="font-size: 0.76rem; line-height: 1.6;">
                                <li>Senjata baru yang belum ada di database akan langsung ditambahkan secara otomatis.</li>
                                <li>Data tipe senjata, icon resolusi tinggi, rarity, dan base ATK akan diperbarui.</li>
                                <li>Data inventori senjata milik akun Anda tidak akan terhapus.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer genshin-modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary text-white"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-genshin btn-genshin-sm" id="btnSubmitSyncMasterWeapons">
                            <span class="btn-text"><i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai Sinkronisasi
                                Master</span>
                            <span class="btn-loading d-none"><span
                                    class="spinner-border spinner-border-sm me-1"></span>Sedang Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH SENJATA --}}
    <div class="modal fade" id="modalAddWeapon" tabindex="-1" aria-labelledby="modalAddWeaponLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content genshin-modal-content">
                <div class="modal-header genshin-modal-header">
                    <h5 class="modal-title font-display text-gold" id="modalAddWeaponLabel">
                        <i class="bi bi-shield-plus me-2"></i>Tambah Master Senjata
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('weapon.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label genshin-label">Nama Senjata <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control genshin-input"
                                placeholder="contoh: Staff of Homa" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label genshin-label">Tipe <span class="text-danger">*</span></label>
                                <select name="type" class="form-select genshin-select" required>
                                    @foreach (['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'] as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label genshin-label">Rarity <span class="text-danger">*</span></label>
                                <select name="rarity" class="form-select genshin-select" required>
                                    <option value="5" selected>★★★★★ (Bintang 5)</option>
                                    <option value="4">★★★★ (Bintang 4)</option>
                                    <option value="3">★★★ (Bintang 3)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label genshin-label">Base ATK (Lv.90)</label>
                                <input type="number" name="base_atk" class="form-control genshin-input" value="608"
                                    required>
                            </div>
                            <div class="col-4">
                                <label class="form-label genshin-label">Sub Stat</label>
                                <input type="text" name="sub_stat_type" class="form-control genshin-input"
                                    placeholder="CRIT DMG">
                            </div>
                            <div class="col-4">
                                <label class="form-label genshin-label">Nilai Sub Stat</label>
                                <input type="text" name="sub_stat_value" class="form-control genshin-input"
                                    placeholder="66.2%">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Nama Pasif</label>
                            <input type="text" name="passive_name" class="form-control genshin-input"
                                placeholder="contoh: Reckless Cinnabar">
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Deskripsi Pasif</label>
                            <textarea name="passive_desc" class="form-control genshin-input" rows="2"
                                placeholder="Deskripsi efek pasif senjata..."></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label genshin-label">URL Icon</label>
                            <input type="url" name="icon_url" class="form-control genshin-input"
                                placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer genshin-modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-genshin btn-genshin-sm">
                            <i class="bi bi-check-lg me-1"></i>Simpan Senjata
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT SENJATA --}}
    <div class="modal fade" id="modalEditWeapon" tabindex="-1" aria-labelledby="modalEditWeaponLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content genshin-modal-content">
                <div class="modal-header genshin-modal-header">
                    <h5 class="modal-title font-display text-gold" id="modalEditWeaponLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Senjata
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="formEditWeapon" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label genshin-label">Nama Senjata <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="editWName" name="name" class="form-control genshin-input"
                                required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label genshin-label">Tipe <span class="text-danger">*</span></label>
                                <select id="editWType" name="type" class="form-select genshin-select" required>
                                    @foreach (['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'] as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label genshin-label">Rarity <span class="text-danger">*</span></label>
                                <select id="editWRarity" name="rarity" class="form-select genshin-select" required>
                                    <option value="5">★★★★★ (Bintang 5)</option>
                                    <option value="4">★★★★ (Bintang 4)</option>
                                    <option value="3">★★★ (Bintang 3)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label genshin-label">Base ATK</label>
                                <input type="number" id="editWAtk" name="base_atk" class="form-control genshin-input"
                                    required>
                            </div>
                            <div class="col-4">
                                <label class="form-label genshin-label">Sub Stat</label>
                                <input type="text" id="editWSubType" name="sub_stat_type"
                                    class="form-control genshin-input">
                            </div>
                            <div class="col-4">
                                <label class="form-label genshin-label">Nilai Sub Stat</label>
                                <input type="text" id="editWSubVal" name="sub_stat_value"
                                    class="form-control genshin-input">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Nama Pasif</label>
                            <input type="text" id="editWPName" name="passive_name"
                                class="form-control genshin-input">
                        </div>
                        <div class="mb-3">
                            <label class="form-label genshin-label">Deskripsi Pasif</label>
                            <textarea id="editWPDesc" name="passive_desc" class="form-control genshin-input" rows="2"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label genshin-label">URL Icon</label>
                            <input type="url" id="editWIcon" name="icon_url" class="form-control genshin-input">
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEdit = document.getElementById('modalEditWeapon');
                if (modalEdit) {
                    modalEdit.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const id = button.getAttribute('data-id');
                        const name = button.getAttribute('data-name');
                        const type = button.getAttribute('data-type');
                        const rarity = button.getAttribute('data-rarity');
                        const atk = button.getAttribute('data-atk');
                        const subType = button.getAttribute('data-sub-type');
                        const subVal = button.getAttribute('data-sub-val');
                        const pName = button.getAttribute('data-p-name');
                        const pDesc = button.getAttribute('data-p-desc');
                        const icon = button.getAttribute('data-icon');

                        const form = document.getElementById('formEditWeapon');
                        form.action = `/weapon/${id}`;

                        document.getElementById('editWName').value = name;
                        document.getElementById('editWType').value = type;
                        document.getElementById('editWRarity').value = rarity;
                        document.getElementById('editWAtk').value = atk;
                        document.getElementById('editWSubType').value = subType || '';
                        document.getElementById('editWSubVal').value = subVal || '';
                        document.getElementById('editWPName').value = pName || '';
                        document.getElementById('editWPDesc').value = pDesc || '';
                        document.getElementById('editWIcon').value = icon || '';
                    });
                }

                document.querySelectorAll('.btn-delete-weapon').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const form = this.closest('form');
                        const name = this.getAttribute('data-name');

                        Swal.fire({
                            title: 'Hapus Senjata?',
                            text: `Senjata "${name}" akan dihapus dari master data.`,
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

                // Sync Master Weapons Loading State
                const formSyncMasterWep = document.getElementById('formSyncMasterWeapons');
                if (formSyncMasterWep) {
                    formSyncMasterWep.addEventListener('submit', function() {
                        const btn = document.getElementById('btnSubmitSyncMasterWeapons');
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
