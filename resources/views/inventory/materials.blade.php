@extends('layout.main')

@section('content')
@php $title = 'Inventori Material'; @endphp
@include('layout.header')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/inventory-materials.css') }}">
@endpush

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 5rem;">

  {{-- Header & Account Selector --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h2 class="font-display text-gold mb-1" style="font-size: 1.4rem;">
        <i class="bi bi-gem me-2"></i>Inventori Material
      </h2>
      <p style="color: var(--text-secondary); font-size: 0.85rem;" class="mb-0">
        Kelola persediaan material, bahan upgrade, dan resource akun game
      </p>
    </div>

    {{-- Switcher Akun --}}
    @if($gameAccounts->count() > 0)
    <div class="d-flex align-items-center gap-2">
      <label for="accountSwitcher" class="text-muted small mb-0 d-none d-sm-inline">Akun:</label>
      <select id="accountSwitcher" class="form-select form-select-sm genshin-select" style="min-width: 200px;" onchange="switchAccount(this.value)">
        @foreach($gameAccounts as $acc)
          <option value="{{ $acc->id }}" {{ $activeAccount && $activeAccount->id == $acc->id ? 'selected' : '' }}>
            {{ $acc->nickname }} (UID: {{ $acc->uid }})
          </option>
        @endforeach
      </select>
    </div>
    @endif
  </div>

  {{-- Filter & Search Bar --}}
  <div class="card bg-dark border-secondary border-opacity-25 mb-4 shadow-sm">
    <div class="card-body p-3">
      <form action="{{ route('inventory.materials.index') }}" method="GET" class="row g-2 align-items-center">
        @if($activeAccount)
          <input type="hidden" name="account_id" value="{{ $activeAccount->id }}">
        @endif

        {{-- Search Input --}}
        <div class="col-12 col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-transparent border-secondary text-muted">
              <i class="bi bi-search"></i>
            </span>
            <input type="text" name="search" class="form-control bg-transparent text-light border-secondary" placeholder="Cari nama material / tipe..." value="{{ $search }}">
            @if(!empty($search))
              <a href="{{ route('inventory.materials.index', ['account_id' => $activeAccount?->id, 'family_id' => $familyId, 'only_owned' => $onlyOwned ? 1 : null]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i>
              </a>
            @endif
          </div>
        </div>

        {{-- Family Filter --}}
        <div class="col-12 col-sm-6 col-md-4">
          <select name="family_id" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
            <option value="">-- Semua Kategori Family ({{ $families->count() }}) --</option>
            @foreach($families as $fam)
              <option value="{{ $fam->id }}" {{ $familyId == $fam->id ? 'selected' : '' }}>
                {{ $fam->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Checkbox Hanya Dimiliki --}}
        <div class="col-12 col-sm-6 col-md-3 d-flex align-items-center justify-content-md-end gap-2">
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="onlyOwnedCheck" name="only_owned" value="1" {{ $onlyOwned ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label small text-light" for="onlyOwnedCheck">Hanya yang dimiliki</label>
          </div>
          <button type="submit" class="btn btn-warning btn-sm px-3" style="height: 32px;">
            <i class="bi bi-funnel-fill me-1"></i>Filter
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Material Cards Grid --}}
  @if($materials->count() > 0)
    <div class="inv-mat-grid mb-4">
      @foreach($materials as $mat)
        @php
          $amt = $invMap[$mat->id] ?? ($mat->amount ?? 0);
          $rarity = $mat->rarity ?? 1;
        @endphp
        <div class="mat-card mat-rarity-{{ $rarity }}">
          <div class="mat-img-box">
            @if($mat->images)
              <img src="{{ $mat->images }}" alt="{{ $mat->name }}" class="mat-img" loading="lazy" onerror="this.onerror=null; this.src='https://placehold.co/120x120/1e2337/gold?text=Item';">
            @else
              <i class="bi bi-gem" style="font-size: 3rem; color: rgba(255,215,0,0.3);"></i>
            @endif

            <span class="badge position-absolute top-0 end-0 m-2" style="background: rgba(0,0,0,0.6); font-size: 0.7rem; border: 1px solid rgba(255,255,255,0.1);">
              @for($i=0; $i<$rarity; $i++)★@endfor
            </span>
          </div>

          <div class="mat-body">
            <div class="mat-name" title="{{ $mat->name }}">{{ $mat->name }}</div>
            <div class="mat-category" title="{{ $mat->family?->name ?? $mat->category }}">
              {{ $mat->family?->name ?? ($mat->category ?: 'Material') }}
            </div>

            <div class="mat-amount-row">
              <button type="button" class="mat-step-btn" onclick="adjustAmount({{ $mat->id }}, -1)" title="Kurang 1">
                <i class="bi bi-dash"></i>
              </button>

              <span class="mat-amount-display" id="amt-display-{{ $mat->id }}" onclick="openEditModal({{ $mat->id }}, '{{ addslashes($mat->name) }}', {{ $amt }})" title="Klik untuk edit jumlah">
                {{ number_format($amt) }}
              </span>

              <button type="button" class="mat-step-btn" onclick="adjustAmount({{ $mat->id }}, 1)" title="Tambah 1">
                <i class="bi bi-plus"></i>
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
      {{ $materials->links('pagination::bootstrap-5') }}
    </div>
  @else
    <div class="text-center py-5" style="color: var(--text-muted);">
      <i class="bi bi-box-seam" style="font-size: 3.5rem; opacity: 0.3;"></i>
      <h5 class="mt-3 text-light">Tidak ada material ditemukan</h5>
      <p class="small text-muted">Coba ubah kata kunci pencarian atau matikan filter "Hanya yang dimiliki".</p>
    </div>
  @endif

</div>

{{-- Modal Edit Manual Stok --}}
<div class="modal fade" id="editAmountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content bg-dark text-light border-secondary">
      <form id="editAmountForm" onsubmit="submitEditAmount(event)">
        <div class="modal-header border-secondary">
          <h6 class="modal-title text-gold" id="modalMatName">Edit Jumlah Material</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="modalMatId" name="material_id">
          <div class="mb-3">
            <label for="modalMatAmount" class="form-label small text-muted">Jumlah Dimiliki:</label>
            <input type="number" class="form-control bg-black text-light border-secondary text-center font-monospace fs-5" id="modalMatAmount" min="0" required>
          </div>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickInput(0)">0</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addQuickInput(10)">+10</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addQuickInput(50)">+50</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addQuickInput(100)">+100</button>
          </div>
        </div>
        <div class="modal-footer border-secondary p-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="height: 34px;">Batal</button>
          <button type="submit" class="btn btn-warning btn-sm px-3" style="height: 34px;">
            <i class="bi bi-check-lg me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const activeAccountId = {{ $activeAccount ? $activeAccount->id : 'null' }};
  const updateUrl = "{{ route('inventory.materials.updateAmount') }}";
  const adjustUrl = "{{ route('inventory.materials.quickAdjust') }}";
  const csrfToken = "{{ csrf_token() }}";

  function switchAccount(accId) {
    const url = new URL(window.location.href);
    url.searchParams.set('account_id', accId);
    window.location.href = url.toString();
  }

  function adjustAmount(matId, delta) {
    if (!activeAccountId) {
      Swal.fire({
        icon: 'warning',
        title: 'Akun Belum Dipilih',
        text: 'Silakan pilih atau tambahkan akun game terlebih dahulu.'
      });
      return;
    }

    const displayElem = document.getElementById(`amt-display-${matId}`);
    let currentAmt = parseInt(displayElem.innerText.replace(/,/g, '')) || 0;
    let nextAmt = Math.max(0, currentAmt + delta);
    displayElem.innerText = nextAmt.toLocaleString();

    fetch(adjustUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        game_account_id: activeAccountId,
        material_id: matId,
        delta: delta
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        displayElem.innerText = parseInt(data.amount).toLocaleString();
      }
    })
    .catch(err => {
      console.error(err);
      displayElem.innerText = currentAmt.toLocaleString();
    });
  }

  let editModalObj = null;
  function openEditModal(matId, matName, currentAmt) {
    if (!activeAccountId) {
      Swal.fire({
        icon: 'warning',
        title: 'Akun Belum Dipilih',
        text: 'Silakan pilih atau tambahkan akun game terlebih dahulu.'
      });
      return;
    }

    document.getElementById('modalMatId').value = matId;
    document.getElementById('modalMatName').innerText = matName;
    document.getElementById('modalMatAmount').value = currentAmt;

    if (!editModalObj) {
      editModalObj = new bootstrap.Modal(document.getElementById('editAmountModal'));
    }
    editModalObj.show();
  }

  function setQuickInput(val) {
    document.getElementById('modalMatAmount').value = val;
  }

  function addQuickInput(delta) {
    const input = document.getElementById('modalMatAmount');
    let val = parseInt(input.value) || 0;
    input.value = Math.max(0, val + delta);
  }

  function submitEditAmount(e) {
    e.preventDefault();
    const matId = document.getElementById('modalMatId').value;
    const newAmt = parseInt(document.getElementById('modalMatAmount').value) || 0;

    fetch(updateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        game_account_id: activeAccountId,
        material_id: matId,
        amount: newAmt
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const displayElem = document.getElementById(`amt-display-${matId}`);
        if (displayElem) {
          displayElem.innerText = parseInt(data.amount).toLocaleString();
        }
        if (editModalObj) editModalObj.hide();
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire({
        icon: 'error',
        title: 'Gagal Menyimpan',
        text: 'Terjadi kesalahan saat memperbarui stok material.'
      });
    });
  }
</script>
@endsection
