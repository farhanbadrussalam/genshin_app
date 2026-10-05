@extends('layout.main')

@section('content')
@php $title = 'Kalkulator Upgrade'; @endphp
@include('layout.header')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/calculator.css') }}">
@endpush

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 5rem;">

  {{-- Header & Account Switcher --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h2 class="font-display text-gold mb-1" style="font-size: 1.4rem;">
        <i class="bi bi-calculator-fill me-2"></i>Kalkulator Upgrade
      </h2>
      <p style="color: var(--text-secondary); font-size: 0.85rem;" class="mb-0">
        Hitung kebutuhan material karakter & senjata, lalu integrasikan langsung menjadi Task
      </p>
    </div>

    {{-- Switcher Akun --}}
    @if($gameAccounts->count() > 0)
    <div class="d-flex align-items-center gap-2">
      <label for="calcAccountSwitcher" class="text-muted small mb-0 d-none d-sm-inline">Akun:</label>
      <select id="calcAccountSwitcher" class="form-select form-select-sm genshin-select" style="min-width: 200px;" onchange="switchAccount(this.value)">
        @foreach($gameAccounts as $acc)
          <option value="{{ $acc->id }}" {{ $activeAccount && $activeAccount->id == $acc->id ? 'selected' : '' }}>
            {{ $acc->nickname }} (UID: {{ $acc->uid }})
          </option>
        @endforeach
      </select>
    </div>
    @endif
  </div>

  {{-- Tab Selection (Character vs Weapon) --}}
  <div class="d-flex gap-2 mb-4">
    <button type="button" class="btn btn-outline-warning calc-tab-btn active" id="tabCharacterBtn" onclick="switchTab('character')">
      <i class="bi bi-person-fill me-1"></i>Upgrade Karakter
    </button>
    <button type="button" class="btn btn-outline-warning calc-tab-btn" id="tabWeaponBtn" onclick="switchTab('weapon')">
      <i class="bi bi-shield-shaded me-1"></i>Upgrade Senjata
    </button>
  </div>

  {{-- Card Form Input --}}
  <div class="calc-card mb-4">
    <form id="calcForm" onsubmit="calculateUpgrade(event)">
      <input type="hidden" id="calcType" value="character">

      {{-- Character Form Inputs --}}
      <div id="characterInputSection">
        <div class="row g-3 mb-3">
          <div class="col-12 col-md-6">
            <label class="form-label text-gold small fw-bold">Pilih Karakter:</label>
            <select class="form-select genshin-select" id="charSelect" onchange="onCharacterChange(this)">
              <option value="">-- Pilih dari Karakter Akun / Master --</option>
              @if($inventoryCharacters->count() > 0)
                <optgroup label="Karakter Milik Akun Ini">
                  @foreach($inventoryCharacters as $invChar)
                    <option value="{{ $invChar->character?->name }}" 
                      data-level="{{ $invChar->level }}" 
                      data-asc="{{ $invChar->ascension }}" 
                      data-image="{{ $invChar->character?->icon_url }}"
                      data-attack="{{ $invChar->talent_attack }}"
                      data-skill="{{ $invChar->talent_skill }}"
                      data-burst="{{ $invChar->talent_burst }}">
                      {{ $invChar->character?->name }} (Lv. {{ $invChar->level }}, T: {{ $invChar->talent_attack }}/{{ $invChar->talent_skill }}/{{ $invChar->talent_burst }})
                    </option>
                  @endforeach
                </optgroup>
              @endif
              <optgroup label="Semua Master Karakter">
                @foreach($allCharacters as $char)
                  <option value="{{ $char->name }}" data-image="{{ $char->icon_url }}" data-level="1" data-asc="0" data-attack="1" data-skill="1" data-burst="1">
                    {{ $char->name }} ({{ $char->element }}, {{ $char->rarity }}★)
                  </option>
                @endforeach
              </optgroup>
            </select>
          </div>

          <div class="col-12 col-md-6 d-flex align-items-center">
            <div id="selectedCharPreview" class="d-flex align-items-center gap-3" style="display: none !important;">
              <img id="charPreviewImg" src="" alt="" style="width: 54px; height: 54px; border-radius: 50%; border: 2px solid var(--color-gold); object-fit: cover;">
              <div>
                <h6 class="text-light mb-0" id="charPreviewName"></h6>
                <span class="badge bg-secondary" id="charPreviewInfo"></span>
              </div>
            </div>
          </div>
        </div>

        {{-- Level Range --}}
        <div class="level-slider-box mb-3">
          <label class="form-label text-gold small fw-bold mb-2">Level Karakter Target:</label>
          <div class="row g-3 align-items-center">
            <div class="col-6 col-sm-3">
              <label class="form-label text-muted small mb-1">Level Sekarang:</label>
              <select class="form-select form-select-sm genshin-select" id="charCurLvl">
                @foreach([1, 20, 40, 50, 60, 70, 80, 89] as $lvl)
                  <option value="{{ $lvl }}">{{ $lvl }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6 col-sm-3">
              <label class="form-label text-gold small mb-1">Level Target:</label>
              <select class="form-select form-select-sm genshin-select" id="charTarLvl">
                @foreach([20, 40, 50, 60, 70, 80, 90] as $lvl)
                  <option value="{{ $lvl }}" {{ $lvl == 90 ? 'selected' : '' }}>{{ $lvl }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Talent Levels --}}
        <div class="level-slider-box mb-3">
          <label class="form-label text-gold small fw-bold mb-2">Talent Upgrade (Sekarang -> Target):</label>
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <div class="talent-stepper-box">
                <span class="small text-light">Normal Attack:</span>
                <div class="d-flex align-items-center gap-1">
                  <select class="form-select form-select-sm genshin-select" style="width: 60px;" id="charCurNa">
                    @for($i=1; $i<=10; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                  </select>
                  <span class="text-muted">→</span>
                  <select class="form-select form-select-sm genshin-select" style="width: 60px;" id="charTarNa">
                    @for($i=1; $i<=10; $i++) <option value="{{ $i }}" {{ $i == 8 ? 'selected' : '' }}>{{ $i }}</option> @endfor
                  </select>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="talent-stepper-box">
                <span class="small text-light">Elemental Skill:</span>
                <div class="d-flex align-items-center gap-1">
                  <select class="form-select form-select-sm genshin-select" style="width: 60px;" id="charCurSkill">
                    @for($i=1; $i<=10; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                  </select>
                  <span class="text-muted">→</span>
                  <select class="form-select form-select-sm genshin-select" style="width: 60px;" id="charTarSkill">
                    @for($i=1; $i<=10; $i++) <option value="{{ $i }}" {{ $i == 9 ? 'selected' : '' }}>{{ $i }}</option> @endfor
                  </select>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="talent-stepper-box">
                <span class="small text-light">Elemental Burst:</span>
                <div class="d-flex align-items-center gap-1">
                  <select class="form-select form-select-sm genshin-select" style="width: 60px;" id="charCurBurst">
                    @for($i=1; $i<=10; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                  </select>
                  <span class="text-muted">→</span>
                  <select class="form-select form-select-sm genshin-select" style="width: 60px;" id="charTarBurst">
                    @for($i=1; $i<=10; $i++) <option value="{{ $i }}" {{ $i == 10 ? 'selected' : '' }}>{{ $i }}</option> @endfor
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Weapon Form Inputs --}}
      <div id="weaponInputSection" style="display: none;">
        <div class="row g-3 mb-3">
          <div class="col-12 col-md-6">
            <label class="form-label text-gold small fw-bold">Pilih Senjata:</label>
            <select class="form-select genshin-select" id="wepSelect" onchange="onWeaponChange(this)">
              <option value="">-- Pilih dari Senjata Akun / Master --</option>
              @if($inventoryWeapons->count() > 0)
                <optgroup label="Senjata Milik Akun Ini">
                  @foreach($inventoryWeapons as $invWep)
                    <option value="{{ $invWep->weapon?->name }}" 
                      data-level="{{ $invWep->level }}" 
                      data-asc="{{ $invWep->ascension }}" 
                      data-image="{{ $invWep->weapon?->icon_url }}">
                      {{ $invWep->weapon?->name }} (Lv. {{ $invWep->level }}, R{{ $invWep->refinement }})
                    </option>
                  @endforeach
                </optgroup>
              @endif
              <optgroup label="Semua Master Senjata">
                @foreach($allWeapons as $wep)
                  <option value="{{ $wep->name }}" data-image="{{ $wep->icon_url }}" data-level="1" data-asc="0">
                    {{ $wep->name }} ({{ $wep->type }}, {{ $wep->rarity }}★)
                  </option>
                @endforeach
              </optgroup>
            </select>
          </div>

          <div class="col-12 col-md-6 d-flex align-items-center">
            <div id="selectedWepPreview" class="d-flex align-items-center gap-3" style="display: none !important;">
              <img id="wepPreviewImg" src="" alt="" style="width: 54px; height: 54px; border-radius: 8px; border: 2px solid var(--color-gold); object-fit: contain; background: rgba(0,0,0,0.4);">
              <div>
                <h6 class="text-light mb-0" id="wepPreviewName"></h6>
                <span class="badge bg-secondary" id="wepPreviewInfo"></span>
              </div>
            </div>
          </div>
        </div>

        {{-- Level Range Senjata --}}
        <div class="level-slider-box mb-3">
          <label class="form-label text-gold small fw-bold mb-2">Level Senjata Target:</label>
          <div class="row g-3 align-items-center">
            <div class="col-6 col-sm-3">
              <label class="form-label text-muted small mb-1">Level Sekarang:</label>
              <select class="form-select form-select-sm genshin-select" id="wepCurLvl">
                @foreach([1, 20, 40, 50, 60, 70, 80, 89] as $lvl)
                  <option value="{{ $lvl }}">{{ $lvl }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6 col-sm-3">
              <label class="form-label text-gold small mb-1">Level Target:</label>
              <select class="form-select form-select-sm genshin-select" id="wepTarLvl">
                @foreach([20, 40, 50, 60, 70, 80, 90] as $lvl)
                  <option value="{{ $lvl }}" {{ $lvl == 90 ? 'selected' : '' }}>{{ $lvl }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      {{-- Action Button --}}
      <div class="text-end">
        <button type="submit" class="btn btn-warning px-4" id="btnCalculate" style="height: 38px;">
          <i class="bi bi-cpu-fill me-1"></i>Hitung Kebutuhan Material
        </button>
      </div>
    </form>
  </div>

  {{-- Calculation Results Section --}}
  <div id="calcResultSection" style="display: none;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h4 class="text-gold font-display mb-1" id="resTargetTitle">Hasil Perhitungan Material</h4>
        <p class="text-muted small mb-0">Perbandingan kebutuhan material upgrade dengan stok yang tersimpan di tas inventori akun</p>
      </div>
      <div>
        <button type="button" class="btn btn-success px-4" onclick="openCreateTaskModal()" style="height: 36px;">
          <i class="bi bi-list-task me-1"></i>Buat Task Upgrade Otomatis
        </button>
      </div>
    </div>

    {{-- Grid Material Items --}}
    <div class="mat-calc-grid mb-4" id="materialsGrid"></div>
  </div>

</div>

{{-- Modal Konfirmasi Buat Task --}}
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light border-secondary">
      <form id="createTaskForm" onsubmit="submitCreateTask(event)">
        <div class="modal-header border-secondary">
          <h5 class="modal-title text-gold">
            <i class="bi bi-list-check me-2"></i>Jadikan Task Baru
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small text-muted">Nama Task:</label>
            <input type="text" class="form-control bg-black text-light border-secondary" id="modalTaskName" required>
          </div>

          <div class="mb-3">
            <label class="form-label small text-muted">Jenis Task:</label>
            <select class="form-select genshin-select" id="modalTaskJenis">
              <option value="stat">Stat (Ascension)</option>
              <option value="talent">Talent</option>
              <option value="weapon">Weapon</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small text-muted">Prioritas Task (1 = Tertinggi):</label>
            <input type="number" class="form-control bg-black text-light border-secondary" id="modalTaskPrioritas" value="1" min="1" max="10" required>
          </div>

          <div class="mb-3">
            <label class="form-label small text-muted">Material yang dimasukkan ke Task:</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="materialFilterOption" id="optDeficit" value="deficit" checked>
              <label class="form-check-label small text-light" for="optDeficit">
                Hanya material yang <strong>kurang / belum cukup</strong> (Recommended)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="materialFilterOption" id="optAll" value="all">
              <label class="form-check-label small text-light" for="optAll">
                Semua total material yang dibutuhkan
              </label>
            </div>
          </div>

          <div id="modalMaterialsPreview" class="border border-secondary border-opacity-25 rounded p-2" style="max-height: 180px; overflow-y: auto;"></div>
        </div>
        <div class="modal-footer border-secondary p-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="height: 34px;">Batal</button>
          <button type="submit" class="btn btn-success btn-sm px-3" id="btnSubmitTask" style="height: 34px;">
            <i class="bi bi-check-lg me-1"></i>Simpan ke Daftar Task
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let currentTab = 'character';
  let calculationData = null;
  const calcUrl = "{{ route('calculator.calculate') }}";
  const createTaskUrl = "{{ route('calculator.createTask') }}";
  const activeAccountId = {{ $activeAccount ? $activeAccount->id : 'null' }};
  const csrfToken = "{{ csrf_token() }}";

  function switchTab(tab) {
    currentTab = tab;
    document.getElementById('calcType').value = tab;

    if (tab === 'character') {
      document.getElementById('tabCharacterBtn').classList.add('active');
      document.getElementById('tabWeaponBtn').classList.remove('active');
      document.getElementById('characterInputSection').style.display = 'block';
      document.getElementById('weaponInputSection').style.display = 'none';
    } else {
      document.getElementById('tabWeaponBtn').classList.add('active');
      document.getElementById('tabCharacterBtn').classList.remove('active');
      document.getElementById('weaponInputSection').style.display = 'block';
      document.getElementById('characterInputSection').style.display = 'none';
    }
    document.getElementById('calcResultSection').style.display = 'none';
  }

  function onCharacterChange(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
      document.getElementById('selectedCharPreview').style.setProperty('display', 'none', 'important');
      return;
    }

    const lvl = opt.dataset.level || 1;
    const img = opt.dataset.image || '';
    const na = opt.dataset.attack || 1;
    const skill = opt.dataset.skill || 1;
    const burst = opt.dataset.burst || 1;

    document.getElementById('charPreviewImg').src = img;
    document.getElementById('charPreviewName').innerText = opt.value;
    document.getElementById('charPreviewInfo').innerText = `Level ${lvl} | Talent: ${na}/${skill}/${burst}`;
    document.getElementById('selectedCharPreview').style.removeProperty('display');

    // Auto-select dropdown
    $('#charCurLvl').val(lvl);
    $('#charCurNa').val(na);
    $('#charCurSkill').val(skill);
    $('#charCurBurst').val(burst);
  }

  function onWeaponChange(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
      document.getElementById('selectedWepPreview').style.setProperty('display', 'none', 'important');
      return;
    }

    const lvl = opt.dataset.level || 1;
    const img = opt.dataset.image || '';

    document.getElementById('wepPreviewImg').src = img;
    document.getElementById('wepPreviewName').innerText = opt.value;
    document.getElementById('wepPreviewInfo').innerText = `Level ${lvl}`;
    document.getElementById('selectedWepPreview').style.removeProperty('display');

    $('#wepCurLvl').val(lvl);
  }

  function calculateUpgrade(e) {
    e.preventDefault();

    let name, curLvl, tarLvl, payload;
    const btn = document.getElementById('btnCalculate');

    if (currentTab === 'character') {
      name = document.getElementById('charSelect').value;
      if (!name) {
        Swal.fire({ icon: 'warning', title: 'Pilih Karakter', text: 'Silakan pilih karakter yang ingin dihitung!' });
        return;
      }
      curLvl = parseInt(document.getElementById('charCurLvl').value);
      tarLvl = parseInt(document.getElementById('charTarLvl').value);
      const curNa = parseInt(document.getElementById('charCurNa').value);
      const tarNa = parseInt(document.getElementById('charTarNa').value);
      const curSkill = parseInt(document.getElementById('charCurSkill').value);
      const tarSkill = parseInt(document.getElementById('charTarSkill').value);
      const curBurst = parseInt(document.getElementById('charCurBurst').value);
      const tarBurst = parseInt(document.getElementById('charTarBurst').value);

      payload = {
        type: 'character',
        name: name,
        current_level: curLvl,
        target_level: tarLvl,
        current_talents: [curNa, curSkill, curBurst],
        target_talents: [tarNa, tarSkill, tarBurst],
        account_id: activeAccountId
      };
    } else {
      name = document.getElementById('wepSelect').value;
      if (!name) {
        Swal.fire({ icon: 'warning', title: 'Pilih Senjata', text: 'Silakan pilih senjata yang ingin dihitung!' });
        return;
      }
      curLvl = parseInt(document.getElementById('wepCurLvl').value);
      tarLvl = parseInt(document.getElementById('wepTarLvl').value);

      payload = {
        type: 'weapon',
        name: name,
        current_level: curLvl,
        target_level: tarLvl,
        account_id: activeAccountId
      };
    }

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Menghitung...`;

    fetch(calcUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-cpu-fill me-1"></i>Hitung Kebutuhan Material`;

      if (data.success) {
        calculationData = data;
        renderResults(data);
      } else {
        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan saat menghitung.' });
      }
    })
    .catch(err => {
      console.error(err);
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-cpu-fill me-1"></i>Hitung Kebutuhan Material`;
      Swal.fire({ icon: 'error', title: 'Koneksi Error', text: 'Gagal menghubungi server kalkulator.' });
    });
  }

  function renderResults(data) {
    const grid = document.getElementById('materialsGrid');
    grid.innerHTML = '';

    document.getElementById('resTargetTitle').innerHTML = `
      Kebutuhan Upgrade: <span class="text-white">${data.name}</span> 
      <span class="badge bg-warning text-dark ms-2">Lv. ${data.cur_lvl} → ${data.tar_lvl}</span>
    `;

    if (!data.materials || data.materials.length === 0) {
      grid.innerHTML = `<div class="col-12 text-center text-muted py-4">Tidak ada kebutuhan material (Level sudah maksimal).</div>`;
      document.getElementById('calcResultSection').style.display = 'block';
      return;
    }

    data.materials.forEach(mat => {
      const isOk = mat.status_ok;
      const statusClass = isOk ? 'status-sufficient' : 'status-shortage';
      const badgeHtml = isOk 
        ? `<span class="badge bg-success-subtle text-success border border-success" style="font-size: 0.72rem;">Tercukupi</span>`
        : `<span class="badge bg-danger-subtle text-danger border border-danger" style="font-size: 0.72rem;">Kurang ${mat.remaining.toLocaleString()}</span>`;

      const itemHtml = `
        <div class="mat-calc-item ${statusClass}">
          <img src="${mat.image || 'https://placehold.co/48x48/1e2337/gold?text=Item'}" alt="${mat.name}" class="mat-calc-img" onerror="this.src='https://placehold.co/48x48/1e2337/gold?text=Item'">
          <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between">
              <span class="fw-bold text-light text-truncate small" title="${mat.name}">${mat.name}</span>
              ${badgeHtml}
            </div>
            <div class="d-flex align-items-center justify-content-between mt-1 text-muted" style="font-size: 0.75rem;">
              <span>Butuh: <strong class="text-warning">${mat.required.toLocaleString()}</strong></span>
              <span>Di Tas: <strong class="text-light">${mat.owned.toLocaleString()}</strong></span>
            </div>
          </div>
        </div>
      `;
      grid.insertAdjacentHTML('beforeend', itemHtml);
    });

    document.getElementById('calcResultSection').style.display = 'block';
    document.getElementById('calcResultSection').scrollIntoView({ behavior: 'smooth' });
  }

  let taskModalObj = null;
  function openCreateTaskModal() {
    if (!calculationData || !calculationData.materials) return;

    let defaultTitle = `Upgrade ${calculationData.name} (Lv ${calculationData.cur_lvl} → ${calculationData.tar_lvl})`;
    document.getElementById('modalTaskName').value = defaultTitle;
    document.getElementById('modalTaskJenis').value = calculationData.type === 'weapon' ? 'weapon' : 'stat';

    updateModalPreview();

    // Listen change
    $('input[name="materialFilterOption"]').off('change').on('change', updateModalPreview);

    if (!taskModalObj) {
      taskModalObj = new bootstrap.Modal(document.getElementById('createTaskModal'));
    }
    taskModalObj.show();
  }

  function updateModalPreview() {
    const filter = $('input[name="materialFilterOption"]:checked').val();
    const container = document.getElementById('modalMaterialsPreview');
    container.innerHTML = '';

    const list = calculationData.materials.filter(m => {
      if (!m.material_id) return false; // Abaikan item jika tidak terdaftar di DB
      if (filter === 'deficit') {
        return m.remaining > 0;
      }
      return m.required > 0;
    });

    if (list.length === 0) {
      container.innerHTML = `<div class="text-center text-muted small py-2">Semua material sudah tercukupi di inventori!</div>`;
      return;
    }

    list.forEach(m => {
      const amt = filter === 'deficit' ? m.remaining : m.required;
      container.insertAdjacentHTML('beforeend', `
        <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-secondary border-opacity-10 small">
          <span class="text-light">${m.name}</span>
          <span class="text-warning fw-bold">${amt.toLocaleString()}x</span>
        </div>
      `);
    });
  }

  function submitCreateTask(e) {
    e.preventDefault();
    const filter = $('input[name="materialFilterOption"]:checked').val();
    const taskName = document.getElementById('modalTaskName').value;
    const jenis = document.getElementById('modalTaskJenis').value;
    const prioritas = document.getElementById('modalTaskPrioritas').value;

    const materialsPayload = [];
    calculationData.materials.forEach(m => {
      if (!m.material_id) return;
      const amt = filter === 'deficit' ? m.remaining : m.required;
      if (amt > 0) {
        materialsPayload.push({
          material_id: m.material_id,
          amount: amt
        });
      }
    });

    if (materialsPayload.length === 0) {
      Swal.fire({
        icon: 'info',
        title: 'Tidak Ada Material Ditambahkan',
        text: 'Semua material sudah tercukupi atau tidak ada material yang valid.'
      });
      return;
    }

    const btn = document.getElementById('btnSubmitTask');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...`;

    let imageUrl = '';
    if (calculationData.type === 'character') {
      const opt = document.querySelector('#charSelect option:checked');
      imageUrl = opt ? opt.dataset.image : '';
    } else {
      const opt = document.querySelector('#wepSelect option:checked');
      imageUrl = opt ? opt.dataset.image : '';
    }

    fetch(createTaskUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        task_name: taskName,
        jenis: jenis,
        prioritas: prioritas,
        image_url: imageUrl,
        materials: materialsPayload
      })
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check-lg me-1"></i>Simpan ke Daftar Task`;

      if (data.success) {
        if (taskModalObj) taskModalObj.hide();
        Swal.fire({
          icon: 'success',
          title: 'Task Berhasil Dibuat!',
          text: 'Mengalihkan ke halaman Task...',
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          window.location.href = data.redirect;
        });
      } else {
        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal membuat task.' });
      }
    })
    .catch(err => {
      console.error(err);
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check-lg me-1"></i>Simpan ke Daftar Task`;
      Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
    });
  }

  function switchAccount(accId) {
    const url = new URL(window.location.href);
    url.searchParams.set('account_id', accId);
    window.location.href = url.toString();
  }
</script>
@endsection
