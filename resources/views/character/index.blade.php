@extends('layout.main')

@section('content')
@php $title = 'Master Karakter'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 4rem;">

  {{-- Flash Message --}}
  @if(session('success'))
    <div class="alert-toast success" id="alertToast">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
  @endif

  {{-- Page Header & Stats --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.35rem; letter-spacing: 0.08em;">
        <i class="bi bi-people-fill me-2"></i>Master Karakter
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.84rem; margin-bottom: 0;">
        Database karakter Genshin Impact untuk referensi inventori, scanning, dan tracking build
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
      <button type="button" class="btn-genshin btn-genshin-sm ms-2" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5);" data-bs-toggle="modal" data-bs-target="#modalSyncMasterCharacters">
        <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Database Karakter
      </button>
      <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal" data-bs-target="#modalAddCharacter">
        <i class="bi bi-plus-lg me-1"></i>Tambah Manual
      </button>
    </div>
  </div>

  {{-- Filter & Search Bar --}}
  <div class="filter-panel mb-4 animate-fade-in-up">
    <form method="GET" action="{{ route('character.index') }}" id="filterForm">
      <div class="row g-2 align-items-center">
        {{-- Search Input --}}
        <div class="col-12 col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text genshin-input-group-text"><i class="bi bi-search text-gold"></i></span>
            <input type="text" name="search" class="form-control genshin-input" 
                   placeholder="Cari nama karakter..." 
                   value="{{ $filters['search'] ?? '' }}"
                   onchange="this.form.submit()">
          </div>
        </div>

        {{-- Elemen Filter --}}
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

        {{-- Rarity Filter --}}
        <div class="col-6 col-sm-4 col-md-2">
          <select name="rarity" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
            <option value="all">Semua Rarity</option>
            <option value="5" {{ ($filters['rarity'] ?? '') === '5' ? 'selected' : '' }}>★★★★★ (Bintang 5)</option>
            <option value="4" {{ ($filters['rarity'] ?? '') === '4' ? 'selected' : '' }}>★★★★ (Bintang 4)</option>
          </select>
        </div>

        {{-- Weapon Filter --}}
        <div class="col-6 col-sm-4 col-md-2">
          <select name="weapon" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
            <option value="all">Semua Senjata</option>
            @foreach(['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'] as $wep)
              <option value="{{ $wep }}" {{ ($filters['weapon'] ?? '') === $wep ? 'selected' : '' }}>
                {{ $wep }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Reset Filter Button --}}
        <div class="col-6 col-md-2 d-flex gap-2">
          @if(!empty(array_filter($filters ?? [])))
            <a href="{{ route('character.index') }}" class="btn btn-sm btn-outline-secondary w-100" style="border-radius: 8px; font-size: 0.8rem;">
              <i class="bi bi-x-circle me-1"></i>Reset
            </a>
          @endif
        </div>
      </div>
    </form>
  </div>

  {{-- Element Quick Badges --}}
  <div class="d-flex flex-wrap gap-2 mb-4 animate-fade-in-up">
    @php
      $elementsMeta = [
        'Pyro'    => ['color' => '#ef4444', 'icon' => '🔥'],
        'Hydro'   => ['color' => '#3b82f6', 'icon' => '💧'],
        'Anemo'   => ['color' => '#10b981', 'icon' => '🍃'],
        'Electro' => ['color' => '#a855f7', 'icon' => '⚡'],
        'Dendro'  => ['color' => '#22c55e', 'icon' => '🌿'],
        'Cryo'    => ['color' => '#06b6d4', 'icon' => '❄️'],
        'Geo'     => ['color' => '#eab308', 'icon' => '🪨'],
      ];
    @endphp

    <a href="{{ route('character.index', array_merge($filters ?? [], ['element' => 'all'])) }}"
       class="element-badge-pill {{ empty($filters['element']) || $filters['element'] === 'all' ? 'active' : '' }}"
       style="--pill-color: var(--accent-gold);">
      ✦ Semua ({{ $stats['total'] }})
    </a>

    @foreach($elementsMeta as $elName => $elData)
      <a href="{{ route('character.index', array_merge($filters ?? [], ['element' => $elName])) }}"
         class="element-badge-pill {{ ($filters['element'] ?? '') === $elName ? 'active' : '' }}"
         style="--pill-color: {{ $elData['color'] }};">
        {{ $elData['icon'] }} {{ $elName }}
      </a>
    @endforeach
  </div>

  {{-- Character Grid --}}
  @if($characters->isEmpty())
    <div class="empty-state animate-fade-in-up">
      <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;">✦</div>
      <p style="color: var(--text-secondary); font-size: 0.9rem;">Tidak ada karakter yang cocok dengan filter yang dipilih.</p>
      <a href="{{ route('character.index') }}" class="btn-genshin btn-genshin-sm mt-2">
        <i class="bi bi-arrow-repeat me-1"></i>Reset Filter
      </a>
    </div>
  @else
    <div class="row g-3">
      @foreach($characters as $character)
        @php
          $elementColor = $character->element_color;
          $isFiveStar = $character->rarity === 5;
        @endphp
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 animate-fade-in-up" style="animation-delay: {{ ($loop->index % 12) * 0.04 }}s;">
          <div class="char-card {{ $isFiveStar ? 'card-star-5' : 'card-star-4' }}">
            {{-- Element Badge Top Left --}}
            <span class="char-element-badge" style="background-color: {{ $elementColor }};">
              {{ $character->element }}
            </span>

            {{-- Action Buttons Top Right --}}
            <div class="char-actions">
              <button class="char-action-btn edit" 
                      title="Edit Karakter"
                      data-bs-toggle="modal" 
                      data-bs-target="#modalEditCharacter"
                      data-id="{{ $character->id }}"
                      data-name="{{ $character->name }}"
                      data-element="{{ $character->element }}"
                      data-weapon="{{ $character->weapon_type }}"
                      data-rarity="{{ $character->rarity }}"
                      data-region="{{ $character->region }}"
                      data-icon="{{ $character->icon_url }}">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <form action="{{ route('character.destroy', $character) }}" method="POST" class="d-inline form-delete">
                @csrf
                @method('DELETE')
                <button type="button" class="char-action-btn delete btn-delete-char" data-name="{{ $character->name }}" title="Hapus Karakter">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </form>
            </div>

            {{-- Character Image Box --}}
            <div class="char-avatar-wrapper">
              @if($character->icon_url)
                <img src="{{ $character->icon_url }}" 
                     alt="{{ $character->name }}" 
                     class="char-avatar"
                     loading="lazy"
                     referrerpolicy="no-referrer"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="char-avatar-placeholder" style="display: none; background: {{ $elementColor }}20;">
                  <span style="color: {{ $elementColor }}; font-weight: bold; font-size: 1.5rem;">
                    {{ substr($character->name, 0, 1) }}
                  </span>
                </div>
              @else
                <div class="char-avatar-placeholder" style="background: {{ $elementColor }}20;">
                  <span style="color: {{ $elementColor }}; font-weight: bold; font-size: 1.5rem;">
                    {{ substr($character->name, 0, 1) }}
                  </span>
                </div>
              @endif
            </div>

            {{-- Character Info Box --}}
            <div class="char-info">
              <div class="char-stars">
                @for($i = 0; $i < $character->rarity; $i++)
                  <i class="bi bi-star-fill text-gold"></i>
                @endfor
              </div>
              <h3 class="char-name" title="{{ $character->name }}">{{ $character->name }}</h3>
              <div class="char-meta">
                <span class="meta-tag">{{ $character->weapon_type }}</span>
                @if($character->region)
                  <span class="meta-tag region">{{ $character->region }}</span>
                @endif
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
      {{ $characters->links('pagination::bootstrap-5') }}
    </div>
  @endif

</div>

{{-- MODAL SYNC MASTER DATABASE KARAKTER --}}
<div class="modal fade" id="modalSyncMasterCharacters" tabindex="-1" aria-labelledby="modalSyncMasterLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalSyncMasterLabel">
          <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Database Master Karakter
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('character.sync-all') }}" method="POST" id="formSyncMasterChars">
        @csrf
          {{-- Pemilihan Sumber Data --}}
          <div class="mb-3">
            <label class="form-label genshin-label mb-2">
              <i class="bi bi-hdd-network-fill me-1 text-gold"></i>Pilih Sumber Data (Data Source):
            </label>
            <div class="d-flex flex-column gap-2">
              {{-- Opsi 1: Project Amber (Recommended) --}}
              <label class="d-flex align-items-center p-2 rounded" style="background: rgba(229,160,41,0.08); border: 1.5px solid rgba(229,160,41,0.4); cursor: pointer; transition: all 0.2s ease;">
                <input type="radio" name="source" value="amber" checked class="form-check-input me-3" style="accent-color: #e5a029; margin-top: 0;">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <strong class="text-white" style="font-size: 0.85rem;">Project Amber (gi.yatta.moe)</strong>
                    <span class="badge" style="background: linear-gradient(135deg, #e5a029, #b45309); font-size: 0.65rem;">⭐ Direkomendasikan</span>
                  </div>
                  <div style="color: var(--text-secondary); font-size: 0.74rem; line-height: 1.4;">
                    Katalog terlengkap (136+ karakter), otomatis menyertakan Region/Bangsa, dan aset icon HD resmi.
                  </div>
                </div>
              </label>

              {{-- Opsi 2: Enka.Network --}}
              <label class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s ease;">
                <input type="radio" name="source" value="enka" class="form-check-input me-3" style="accent-color: #e5a029; margin-top: 0;">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <strong class="text-white" style="font-size: 0.85rem;">Enka.Network (API Docs Store)</strong>
                    <span class="badge" style="background: #3b82f6; font-size: 0.65rem;">Default Komunitas</span>
                  </div>
                  <div style="color: var(--text-secondary); font-size: 0.74rem; line-height: 1.4;">
                    Katalog data raw JSON dari repositori dokumentasi Enka.Network.
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
              <li>Karakter baru yang belum ada di database akan langsung ditambahkan.</li>
              <li>Data elemen, rarity, jenis senjata, region, dan icon karakter yang sudah ada akan diperbarui jika ada revisi resmi.</li>
              <li>Data inventori karakter milik akun Anda tidak akan terhapus.</li>
            </ul>
          </div>
        </div>
        <div class="modal-footer genshin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm" id="btnSubmitSyncMaster">
            <span class="btn-text"><i class="bi bi-cloud-arrow-down-fill me-1"></i>Mulai Sinkronisasi Master</span>
            <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Sedang Memproses...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL TAMBAH KARAKTER --}}
<div class="modal fade" id="modalAddCharacter" tabindex="-1" aria-labelledby="modalAddCharLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalAddCharLabel">
          <i class="bi bi-person-plus-fill me-2"></i>Tambah Karakter
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('character.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label genshin-label">Nama Karakter <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control genshin-input" placeholder="contoh: Hu Tao" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Elemen <span class="text-danger">*</span></label>
              <select name="element" class="form-select genshin-select" required>
                @foreach(['Pyro', 'Hydro', 'Anemo', 'Electro', 'Dendro', 'Cryo', 'Geo'] as $el)
                  <option value="{{ $el }}">{{ $el }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Rarity <span class="text-danger">*</span></label>
              <select name="rarity" class="form-select genshin-select" required>
                <option value="5">★★★★★ (Bintang 5)</option>
                <option value="4" selected>★★★★ (Bintang 4)</option>
              </select>
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Senjata <span class="text-danger">*</span></label>
              <select name="weapon_type" class="form-select genshin-select" required>
                @foreach(['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'] as $wep)
                  <option value="{{ $wep }}">{{ $wep }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Region</label>
              <input type="text" name="region" class="form-control genshin-input" placeholder="contoh: Liyue">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label genshin-label">URL Icon Avatar</label>
            <input type="url" name="icon_url" class="form-control genshin-input" placeholder="https://...">
            <small style="color: var(--text-muted); font-size: 0.75rem;">Opsional. Boleh dikosongkan.</small>
          </div>
        </div>
        <div class="modal-footer genshin-modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">
            <i class="bi bi-check-lg me-1"></i>Simpan Karakter
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDIT KARAKTER --}}
<div class="modal fade" id="modalEditCharacter" tabindex="-1" aria-labelledby="modalEditCharLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal-content">
      <div class="modal-header genshin-modal-header">
        <h5 class="modal-title font-display text-gold" id="modalEditCharLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Karakter
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEditCharacter" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label genshin-label">Nama Karakter <span class="text-danger">*</span></label>
            <input type="text" id="editName" name="name" class="form-control genshin-input" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Elemen <span class="text-danger">*</span></label>
              <select id="editElement" name="element" class="form-select genshin-select" required>
                @foreach(['Pyro', 'Hydro', 'Anemo', 'Electro', 'Dendro', 'Cryo', 'Geo'] as $el)
                  <option value="{{ $el }}">{{ $el }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Rarity <span class="text-danger">*</span></label>
              <select id="editRarity" name="rarity" class="form-select genshin-select" required>
                <option value="5">★★★★★ (Bintang 5)</option>
                <option value="4">★★★★ (Bintang 4)</option>
              </select>
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label genshin-label">Senjata <span class="text-danger">*</span></label>
              <select id="editWeapon" name="weapon_type" class="form-select genshin-select" required>
                @foreach(['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'] as $wep)
                  <option value="{{ $wep }}">{{ $wep }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6">
              <label class="form-label genshin-label">Region</label>
              <input type="text" id="editRegion" name="region" class="form-control genshin-input">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label genshin-label">URL Icon Avatar</label>
            <input type="url" id="editIcon" name="icon_url" class="form-control genshin-input">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Modal Edit Fill
  const modalEdit = document.getElementById('modalEditCharacter');
  if (modalEdit) {
    modalEdit.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const element = button.getAttribute('data-element');
      const weapon = button.getAttribute('data-weapon');
      const rarity = button.getAttribute('data-rarity');
      const region = button.getAttribute('data-region');
      const icon = button.getAttribute('data-icon');

      const form = document.getElementById('formEditCharacter');
      form.action = `/character/${id}`;

      document.getElementById('editName').value = name;
      document.getElementById('editElement').value = element;
      document.getElementById('editWeapon').value = weapon;
      document.getElementById('editRarity').value = rarity;
      document.getElementById('editRegion').value = region || '';
      document.getElementById('editIcon').value = icon || '';
    });
  }

  // SweetAlert2 Delete Confirmation
  document.querySelectorAll('.btn-delete-char').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const form = this.closest('form');
      const name = this.getAttribute('data-name');

      Swal.fire({
        title: 'Hapus Karakter?',
        text: `Karakter "${name}" akan dihapus dari master data.`,
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

  // Sync Master Form Loading State
  const formSyncMaster = document.getElementById('formSyncMasterChars');
  if (formSyncMaster) {
    formSyncMaster.addEventListener('submit', function () {
      const btn = document.getElementById('btnSubmitSyncMaster');
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
