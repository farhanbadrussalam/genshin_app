@extends('layout.main')

@section('content')
@php $title = 'Party Analyzer & Team Builder'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 5rem;">

  {{-- Page Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.5rem; letter-spacing: 0.08em;">
        <i class="bi bi-shield-shaded me-2"></i>Party Builder, Benchmark & Resonansi Elemental
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 0;">
        Rancang kombinasi party, pantau resonansi elemental real-time, evaluasi benchmark pengujian DPS dummy, dan ekspor ke Gemini AI.
      </p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-sm btn-outline-warning" id="btnScrollToSaved">
        <i class="bi bi-collection-fill me-1"></i>Party Tersimpan (<span id="savedPartiesCounter">{{ count($formattedSavedParties) }}</span>)
      </button>
      <button type="button" class="btn btn-genshin btn-genshin-sm" id="btnNewPartyHeader">
        <i class="bi bi-plus-circle me-1"></i>Party Baru
      </button>
    </div>
  </div>

  {{-- MAIN BUILDER SECTION --}}
  <div class="card genshin-panel-card mb-4 border-gold-glow animate-fade-in-up" id="partyBuilderSection">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom-genshin py-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill bg-gold text-dark fw-bold px-3 py-2" id="builderModeBadge">
          <i class="bi bi-hammer me-1"></i>Mode: Buat Party Baru
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger d-none" id="btnCancelEdit">
          <i class="bi bi-x-circle me-1"></i>Batal Edit
        </button>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnClearSlots" title="Kosongkan semua slot">
          <i class="bi bi-arrow-counterclockwise me-1"></i>Kosongkan Slot
        </button>
        <button type="button" class="btn btn-genshin btn-genshin-sm" id="btnSaveParty">
          <i class="bi bi-bookmark-check-fill me-1"></i><span id="btnSaveText">Simpan Party</span>
        </button>
      </div>
    </div>

    <div class="card-body p-3 p-md-4">
      {{-- Party Info Form --}}
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-5">
          <label class="form-label small text-gold fw-bold mb-1">
            <i class="bi bi-card-heading me-1"></i>Nama Party <span class="text-danger">*</span>
          </label>
          <input type="text" id="inputPartyName" class="form-control genshin-input" placeholder="Contoh: Raiden National, Hu Tao Double Hydro, Anemo Hypercarry..." value="Party Baru">
        </div>

        <div class="col-12 col-md-3">
          <label class="form-label small text-gold fw-bold mb-1">
            <i class="bi bi-person-badge me-1"></i>Filter Akun Game
          </label>
          <select id="selectGameAccount" class="form-select genshin-select">
            <option value="">Semua Karakter (Global)</option>
            @foreach($gameAccounts as $acc)
              <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                {{ $acc->nickname }} (UID: {{ $acc->uid }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label small text-gold fw-bold mb-1">
            <i class="bi bi-chat-left-text me-1"></i>Catatan Tim (Opsional)
          </label>
          <input type="text" id="inputPartyNotes" class="form-control genshin-input" placeholder="Rotasi, strategi, build artifact...">
        </div>
      </div>

      {{-- 4 PARTY SLOTS --}}
      <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <label class="form-label small text-gold fw-bold mb-0">
            <i class="bi bi-people-fill me-1"></i>Susunan 4 Karakter Party
            <span class="text-muted fw-normal ms-1" style="font-size: 0.8rem;">(Klik salah satu slot untuk mengganti, atau klik karakter di bawah)</span>
          </label>
          <span class="badge bg-dark border border-secondary text-warning" id="slotFilledCount">
            0 / 4 Karakter Terisi
          </span>
        </div>

        <div class="row g-3 row-cols-2 row-cols-md-4" id="partySlotsContainer">
          @for($i = 0; $i < 4; $i++)
            <div class="col">
              <div class="party-slot-card empty {{ $i === 0 ? 'slot-active' : '' }}" data-slot="{{ $i }}" id="partySlot_{{ $i }}">
                <div class="slot-active-indicator">
                  <i class="bi bi-check2-circle me-1"></i>Target Aktif
                </div>
                <button type="button" class="btn-slot-remove d-none" data-slot="{{ $i }}" title="Hapus karakter dari slot ini">
                  <i class="bi bi-x"></i>
                </button>
                <div class="slot-content">
                  <div class="slot-empty-state">
                    <div class="slot-plus-icon">
                      <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="slot-number-badge">Slot {{ $i + 1 }}</div>
                    <div class="slot-hint">Pilih Karakter</div>
                  </div>
                  <div class="slot-filled-state d-none">
                    <div class="slot-element-badge"></div>
                    <div class="slot-avatar-wrapper">
                      <img src="" alt="" class="slot-avatar">
                    </div>
                    <div class="slot-char-name"></div>
                    <div class="slot-char-meta">
                      <span class="slot-char-weapon"></span>
                      <span class="slot-char-stars"></span>
                    </div>
                    <div class="slot-char-extra d-none"></div>
                  </div>
                </div>
              </div>
            </div>
          @endfor
        </div>
      </div>

      {{-- ELEMENTAL RESONANCE PANEL --}}
      <div class="elemental-resonance-box p-3 rounded" id="elementalResonanceBox">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom-genshin">
          <div class="d-flex align-items-center gap-2">
            <div class="resonance-icon-orb">
              <i class="bi bi-lightning-charge-fill text-warning"></i>
            </div>
            <div>
              <h5 class="mb-0 text-gold font-display" style="font-size: 1.05rem;">
                Resonansi Elemental
              </h5>
              <div class="small text-muted" id="resonanceSummaryText">
                Menghitung efek elemen dari susunan party...
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-1" id="partyElementsPills"></div>
        </div>

        <div class="row g-2" id="resonanceCardsList"></div>
      </div>

    </div>
  </div>

  {{-- CHARACTER PICKER / ROSTER GRID --}}
  <div class="card genshin-panel-card mb-5 animate-fade-in-up" id="characterCatalogCard">
    <div class="card-header border-bottom-genshin py-3">
      <div class="row g-3 align-items-center justify-content-between">
        <div class="col-12 col-lg-4">
          <h5 class="text-gold font-display mb-0" style="font-size: 1.05rem;">
            <i class="bi bi-grid-fill me-2"></i>Katalog Karakter
            <span class="text-muted fw-normal small ms-1" id="catalogCountLabel">({{ count($characters) }})</span>
          </h5>
          <div class="small text-muted">
            Klik kartu karakter untuk mengisi <strong class="text-warning" id="activeSlotNotice">Slot 1</strong>
          </div>
        </div>

        {{-- Search Input --}}
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text genshin-input-group-text"><i class="bi bi-search text-gold"></i></span>
            <input type="text" id="charSearchInput" class="form-control genshin-input" placeholder="Cari nama karakter (misal: Raiden, Hu Tao)...">
            <button class="btn btn-outline-secondary" type="button" id="btnClearSearch" title="Reset pencarian">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        {{-- Filters (Rarity & Weapon) --}}
        <div class="col-12 col-sm-6 col-lg-4 d-flex gap-2">
          <select id="filterRarity" class="form-select form-select-sm genshin-select">
            <option value="all">Semua Rarity</option>
            <option value="5">⭐⭐⭐⭐⭐ Bintang 5</option>
            <option value="4">⭐⭐⭐⭐ Bintang 4</option>
          </select>

          <select id="filterWeapon" class="form-select form-select-sm genshin-select">
            <option value="all">Semua Senjata</option>
            <option value="Sword">Sword</option>
            <option value="Claymore">Claymore</option>
            <option value="Polearm">Polearm</option>
            <option value="Bow">Bow</option>
            <option value="Catalyst">Catalyst</option>
          </select>
        </div>
      </div>

      {{-- Elemental Filter Pills --}}
      <div class="d-flex flex-wrap align-items-center gap-1 mt-3 pt-2 border-top-genshin">
        <span class="small text-muted me-2"><i class="bi bi-funnel me-1"></i>Elemen:</span>
        <button type="button" class="btn-element-filter active" data-element="all">
          Semua Elemen
        </button>
        @foreach([
          'Pyro' => ['color' => '#ef4444', 'icon' => 'bi-fire'],
          'Hydro' => ['color' => '#3b82f6', 'icon' => 'bi-droplet-fill'],
          'Anemo' => ['color' => '#10b981', 'icon' => 'bi-wind'],
          'Electro' => ['color' => '#a855f7', 'icon' => 'bi-lightning-charge-fill'],
          'Dendro' => ['color' => '#22c55e', 'icon' => 'bi-flower1'],
          'Cryo' => ['color' => '#06b6d4', 'icon' => 'bi-snow'],
          'Geo' => ['color' => '#eab308', 'icon' => 'bi-shield-shaded'],
        ] as $elem => $meta)
          <button type="button" class="btn-element-filter" data-element="{{ $elem }}" style="--elem-color: {{ $meta['color'] }};">
            <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $elem }}
          </button>
        @endforeach
      </div>
    </div>

    <div class="card-body p-3 p-md-4">
      <div class="row g-2 g-md-3 row-cols-3 row-cols-sm-4 row-cols-md-6 row-cols-lg-6" id="characterGrid"></div>

      <div class="text-center py-5 d-none" id="catalogEmptyState">
        <i class="bi bi-emoji-frown text-muted" style="font-size: 2.5rem;"></i>
        <h6 class="text-muted mt-2 mb-1">Tidak ada karakter yang cocok dengan filter</h6>
        <p class="small text-secondary mb-3">Coba ubah kata kunci pencarian atau reset filter elemen/senjata.</p>
        <button type="button" class="btn btn-sm btn-outline-warning" id="btnResetFilters">
          <i class="bi bi-arrow-clockwise me-1"></i>Reset Semua Filter
        </button>
      </div>
    </div>
  </div>

  {{-- SAVED PARTIES LIST SECTION --}}
  <div class="saved-parties-section" id="savedPartiesSection">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h4 class="text-gold font-display mb-1" style="font-size: 1.25rem;">
          <i class="bi bi-collection-fill me-2"></i>Daftar Party Saya
        </h4>
        <p class="small text-muted mb-0">
          Koleksi formasi party yang telah kamu simpan beserta riwayat hasil benchmark DPS dan ekspor AI.
        </p>
      </div>
      <div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRefreshSavedParties">
          <i class="bi bi-arrow-clockwise me-1"></i>Refresh Daftar
        </button>
      </div>
    </div>

    <div class="row g-3" id="savedPartiesGrid"></div>

    {{-- Empty State for Saved Parties --}}
    <div class="card genshin-panel-card p-5 text-center d-none" id="savedPartiesEmptyState">
      <div class="mb-3">
        <i class="bi bi-shield-plus text-warning" style="font-size: 3rem; opacity: 0.6;"></i>
      </div>
      <h5 class="text-gold font-display mb-1">Belum Ada Party yang Disimpan</h5>
      <p class="small text-muted mx-auto mb-4" style="max-width: 460px;">
        Pilih 1 hingga 4 karakter favoritmu di atas, periksa resonansi elemental yang aktif, lalu tekan tombol <strong>"Simpan Party"</strong> untuk menyimpannya di sini.
      </p>
      <div>
        <button type="button" class="btn btn-genshin btn-genshin-sm" id="btnEmptyStateCreate">
          <i class="bi bi-plus-circle me-1"></i>Mulai Racik Party Baru
        </button>
      </div>
    </div>
  </div>

</div>

{{-- =========================================================================
     MODAL 1: SCAN & INPUT HASIL PENGUJIAN BENCHMARK
     ========================================================================= --}}
<div class="modal fade" id="modalScanBenchmark" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content genshin-panel-card border-gold-glow">
      <div class="modal-header border-bottom-genshin">
        <h5 class="modal-title text-gold font-display" style="font-size: 1.2rem;">
          <i class="bi bi-camera-fill me-2"></i>Scan & Input Hasil Pengujian (DPS Test Dummy++)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3 p-md-4">
        {{-- Target Party Info Bar --}}
        <div class="p-2 mb-3 rounded bg-dark border border-secondary d-flex align-items-center justify-content-between">
          <div>
            <span class="small text-muted">Target Party:</span>
            <strong class="text-warning ms-1" id="scanTargetPartyName">-</strong>
          </div>
          <span class="badge bg-gold text-dark fw-bold">Gemini 2.5 Flash Vision Ready</span>
        </div>

        {{-- Dropzone & Upload Action --}}
        <div class="benchmark-dropzone mb-4" id="benchmarkDropzone">
          <input type="file" id="benchmarkImageInput" accept="image/*" class="d-none">
          <div class="dropzone-inner text-center p-4">
            <i class="bi bi-cloud-arrow-up text-warning" style="font-size: 2.8rem;"></i>
            <h6 class="text-gold mt-2 mb-1">Unggah Screenshot Hasil Uji (DPS Test Dummy++)</h6>
            <p class="small text-muted mb-3" style="max-width: 480px; margin: 0 auto;">
              Drop file gambar di sini atau klik untuk memilih file. Sistem akan mengekstrak otomatis seluruh angka (DPS, Waktu, Distribusi Karakter/Elemen, Rotasi 1-7, dan Stat Karakter) via Gemini Vision AI.
            </p>
            <button type="button" class="btn btn-genshin btn-genshin-sm" id="btnSelectBenchmarkImage">
              <i class="bi bi-image me-1"></i>Pilih Gambar Screenshot
            </button>
          </div>

          {{-- Loading Overlay --}}
          <div class="dropzone-loading d-none text-center p-4" id="scanLoadingOverlay">
            <div class="spinner-border text-warning mb-2" role="status" style="width: 3rem; height: 3rem;"></div>
            <h6 class="text-gold mb-1">Menganalisis Screenshot dengan Gemini Vision AI...</h6>
            <p class="small text-muted mb-0">Mengekstrak DPS, tabel rotasi, distribusi damage, dan snapshot stat karakter...</p>
          </div>
        </div>

        {{-- Form Preview & Edit --}}
        <form id="formBenchmarkData">
          <input type="hidden" id="benchPartyId" name="saved_party_id">
          <input type="hidden" id="benchScreenshotPath" name="screenshot_path">

          {{-- Nav Tabs --}}
          <ul class="nav nav-tabs border-bottom-genshin mb-3" role="tablist">
            <li class="nav-item">
              <button class="nav-link active text-gold fw-bold" data-bs-toggle="tab" data-bs-target="#tabBenchMain" type="button">
                <i class="bi bi-speedometer2 me-1"></i>1. Ringkasan & Target
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link text-gold fw-bold" data-bs-toggle="tab" data-bs-target="#tabBenchDmg" type="button">
                <i class="bi bi-pie-chart-fill me-1"></i>2. Distribusi DMG
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link text-gold fw-bold" data-bs-toggle="tab" data-bs-target="#tabBenchRotations" type="button">
                <i class="bi bi-arrow-repeat me-1"></i>3. Hasil Rotasi (1-N)
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link text-gold fw-bold" data-bs-toggle="tab" data-bs-target="#tabBenchStats" type="button">
                <i class="bi bi-person-lines-fill me-1"></i>4. Stat 4 Karakter
              </button>
            </li>
          </ul>

          <div class="tab-content">
            {{-- Tab 1: Ringkasan Utama & Target Dummy --}}
            <div class="tab-pane fade show active" id="tabBenchMain">
              <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-3">
                  <label class="form-label small text-gold fw-bold mb-1">Preset Pengujian</label>
                  <input type="text" id="benchPresetName" class="form-control genshin-input" value="Spiral Abyss Lantai 12">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <label class="form-label small text-gold fw-bold mb-1">Rata-rata DPS</label>
                  <input type="number" id="benchDps" class="form-control genshin-input text-warning fw-bold" placeholder="Contoh: 116129">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <label class="form-label small text-gold fw-bold mb-1">Total Waktu (Detik)</label>
                  <input type="number" step="0.01" id="benchTotalTime" class="form-control genshin-input" placeholder="Contoh: 195.87">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <label class="form-label small text-gold fw-bold mb-1">Total Damage</label>
                  <input type="number" id="benchTotalDmg" class="form-control genshin-input" placeholder="Contoh: 22745933">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <label class="form-label small text-gold fw-bold mb-1">Hit Terkuat (Nuke)</label>
                  <input type="number" id="benchMaxHit" class="form-control genshin-input" placeholder="Contoh: 406242">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <label class="form-label small text-gold fw-bold mb-1">Target Dummy</label>
                  <input type="text" id="benchDummyName" class="form-control genshin-input" value="SSR Teddy">
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                  <label class="form-label small text-gold fw-bold mb-1">Level Dummy</label>
                  <input type="number" id="benchDummyLevel" class="form-control genshin-input" value="100">
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                  <label class="form-label small text-gold fw-bold mb-1">UID</label>
                  <input type="text" id="benchUid" class="form-control genshin-input" placeholder="809404073">
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                  <label class="form-label small text-gold fw-bold mb-1">GUID Stage</label>
                  <input type="text" id="benchGuid" class="form-control genshin-input" placeholder="13031458938">
                </div>
              </div>
            </div>

            {{-- Tab 2: Distribusi DMG Karakter & Elemen --}}
            <div class="tab-pane fade" id="tabBenchDmg">
              <div class="row g-3">
                <div class="col-12 col-lg-7">
                  <h6 class="text-gold small fw-bold mb-2"><i class="bi bi-people me-1"></i>Distribusi Damage 4 Karakter</h6>
                  <div id="benchCharDmgInputs" class="d-flex flex-column gap-2"></div>
                </div>
                <div class="col-12 col-lg-5">
                  <h6 class="text-gold small fw-bold mb-2"><i class="bi bi-pie-chart me-1"></i>Distribusi Damage Elemen (%)</h6>
                  <div class="row g-2" id="benchElemDmgInputs"></div>
                </div>
              </div>
            </div>

            {{-- Tab 3: Hasil Rotasi --}}
            <div class="tab-pane fade" id="tabBenchRotations">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-gold small fw-bold mb-0"><i class="bi bi-arrow-repeat me-1"></i>Rincian Rotasi Pengujian</h6>
                <button type="button" class="btn btn-xs btn-outline-warning" id="btnAddRotationRow">
                  <i class="bi bi-plus me-1"></i>Tambah Baris Rotasi
                </button>
              </div>
              <div class="table-responsive">
                <table class="table table-dark table-sm table-bordered align-middle" id="tableBenchRotations">
                  <thead class="text-gold small">
                    <tr>
                      <th style="width: 80px;">Rotasi #</th>
                      <th>DPS Rotasi</th>
                      <th>Total Damage Rotasi</th>
                      <th>Durasi (Detik)</th>
                      <th style="width: 50px;">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyBenchRotations"></tbody>
                </table>
              </div>
            </div>

            {{-- Tab 4: Snapshot Stat 4 Karakter --}}
            <div class="tab-pane fade" id="tabBenchStats">
              <div class="row g-3" id="benchCharStatsCards"></div>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer border-top-genshin">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-genshin btn-genshin-sm" id="btnSaveBenchmarkData">
          <i class="bi bi-check-circle-fill me-1"></i>Simpan Hasil Pengujian ke Party Ini
        </button>
      </div>
    </div>
  </div>
</div>

{{-- =========================================================================
     MODAL 2: DETAIL LAPORAN & EVALUASI BENCHMARK (GAME ACCURATE DASHBOARD)
     ========================================================================= --}}
<div class="modal fade" id="modalViewBenchmark" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content genshin-panel-card border-gold-glow">
      <div class="modal-header border-bottom-genshin py-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-gold text-dark fw-bold" id="viewBenchPresetBadge">Spiral Abyss Lantai 12</span>
          <h5 class="modal-title text-gold font-display mb-0" style="font-size: 1.25rem;">
            DPS Test Dummy++ — Hasil Pengujian
          </h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3 p-md-4" style="background: #0d101a;">
        {{-- Top 4 Big Metric Cards --}}
        <div class="row g-2 mb-3">
          <div class="col-6 col-md-3">
            <div class="bench-top-metric-card text-center p-2 rounded">
              <div class="metric-label">DPS</div>
              <div class="metric-value text-gold" id="viewBenchDps">0</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="bench-top-metric-card text-center p-2 rounded">
              <div class="metric-label">Waktu</div>
              <div class="metric-value text-white" id="viewBenchTime">0 d</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="bench-top-metric-card text-center p-2 rounded">
              <div class="metric-label">Total DMG</div>
              <div class="metric-value text-gold" id="viewBenchTotalDmg">0</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="bench-top-metric-card text-center p-2 rounded">
              <div class="metric-label">Hit Terkuat</div>
              <div class="metric-value text-gold" id="viewBenchMaxHit">0</div>
            </div>
          </div>
        </div>

        {{-- Automated Theorycrafting Diagnostic Box --}}
        <div class="p-3 mb-3 rounded border border-warning" id="viewBenchDiagnosticsBox" style="background: rgba(234, 179, 8, 0.05);">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom border-secondary">
            <div class="d-flex align-items-center gap-2">
              <span class="badge px-3 py-2 fw-bold" id="viewBenchTierBadge" style="font-size: 0.9rem;">Tier SS</span>
              <span class="text-white fw-bold" id="viewBenchArchetypeLabel">Hypercarry Tim</span>
              <span class="badge bg-dark border border-secondary text-info" id="viewBenchDominantElemLabel">Anemo Dominant</span>
            </div>
            <div class="small text-muted" id="viewBenchDate">Diuji baru saja</div>
          </div>
          <div class="row g-2 small">
            <div class="col-12 col-md-6" id="viewBenchPositives"></div>
            <div class="col-12 col-md-6" id="viewBenchWarnings"></div>
          </div>
        </div>

        {{-- Middle 3 Columns: Target Dummy & Res | Character DMG Dist | Rotations --}}
        <div class="row g-3 mb-3">
          {{-- Dummy Target & Modifiers --}}
          <div class="col-12 col-md-3">
            <div class="p-2 rounded h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
              <div class="small text-muted text-uppercase fw-bold mb-1">Modifikator / Dummy</div>
              <div class="text-center py-2">
                <i class="bi bi-box text-warning" style="font-size: 2rem;"></i>
                <div class="fw-bold text-gold" id="viewBenchDummyLabel">SSR Teddy Lv. 100</div>
              </div>
              <div class="small text-gold fw-bold text-center mb-1">RESISTENSI ELEMENTAL</div>
              <div class="d-flex flex-wrap justify-content-center gap-1 text-center" id="viewBenchResBadges"></div>
            </div>
          </div>

          {{-- Character & Element Damage Distribution --}}
          <div class="col-12 col-md-5">
            <div class="p-2 rounded h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
              <div class="small text-gold fw-bold mb-2"><i class="bi bi-bar-chart-fill me-1"></i>Distribusi DMG Karakter</div>
              <div id="viewBenchCharDmgBars" class="d-flex flex-column gap-2 mb-3"></div>

              <div class="small text-gold fw-bold mb-1"><i class="bi bi-pie-chart-fill me-1"></i>Distribusi DMG Elemental</div>
              <div id="viewBenchElemDmgSummary" class="d-flex flex-wrap gap-1"></div>
            </div>
          </div>

          {{-- Rotations List (1 to 7) --}}
          <div class="col-12 col-md-4">
            <div class="p-2 rounded h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
              <div class="small text-gold fw-bold mb-2"><i class="bi bi-arrow-repeat me-1"></i>Hasil Rotasi</div>
              <div class="table-responsive" style="max-height: 220px;">
                <table class="table table-dark table-sm table-borderless small mb-0">
                  <tbody id="viewBenchRotationsList"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {{-- Bottom Row: 4 Character Snapshot Stats --}}
        <div>
          <div class="small text-gold fw-bold mb-2"><i class="bi bi-person-lines-fill me-1"></i>Snapshot Stat 4 Karakter</div>
          <div class="row g-2" id="viewBenchCharStatsRow"></div>
        </div>

        {{-- Footer Meta --}}
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary small text-muted">
          <span id="viewBenchGuid">GUID Stage: -</span>
          <span id="viewBenchUid">UID: -</span>
        </div>
      </div>

      <div class="modal-footer border-top-genshin d-flex justify-content-between">
        <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeleteCurrentBenchmark">
          <i class="bi bi-trash3 me-1"></i>Hapus Uji Ini
        </button>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-outline-info" id="btnExportGeminiFromView">
            <i class="bi bi-cpu-fill me-1"></i>Export Prompt Gemini AI
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- =========================================================================
     MODAL 3: EXPORT PROMPT UNTUK GEMINI AI
     ========================================================================= --}}
<div class="modal fade" id="modalGeminiExport" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content genshin-panel-card border-gold-glow">
      <div class="modal-header border-bottom-genshin">
        <h5 class="modal-title text-gold font-display" style="font-size: 1.15rem;">
          <i class="bi bi-cpu-fill text-warning me-2"></i>Export Prompt Theorycrafter untuk Gemini AI
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3 p-md-4">
        <p class="small text-muted mb-3">
          Prompt Markdown di bawah ini telah dirancang khusus dengan persona <strong>Theorycrafter Profesional</strong> dan memuat seluruh data numerik pengujian tim Anda. Anda dapat langsung menyalinnya untuk ditanyakan ke Google Gemini / Claude / ChatGPT.
        </p>

        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
          <button type="button" class="btn btn-genshin btn-genshin-sm" id="btnCopyPromptToClipboard">
            <i class="bi bi-clipboard-check me-1"></i>Salin Prompt ke Clipboard
          </button>
          <a href="#" class="btn btn-sm btn-outline-info" id="btnDownloadMdLink">
            <i class="bi bi-filetype-md me-1"></i>Download .md
          </a>
          <a href="#" class="btn btn-sm btn-outline-secondary" id="btnDownloadJsonLink">
            <i class="bi bi-filetype-json me-1"></i>Download .json
          </a>
        </div>

        <div class="position-relative">
          <textarea id="geminiPromptTextarea" class="form-control genshin-input" rows="14" style="font-family: monospace; font-size: 0.82rem;" readonly></textarea>
        </div>
      </div>

      <div class="modal-footer border-top-genshin">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  :root {
    --bg-panel: #141824;
    --bg-panel-dark: #0f121b;
    --bg-slot-empty: rgba(255, 255, 255, 0.02);
    --border-genshin: rgba(200, 170, 110, 0.2);
    --border-gold-glow: rgba(234, 179, 8, 0.4);
    --color-gold: #c8aa6e;
    --color-gold-bright: #fbbf24;
  }

  .genshin-panel-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-genshin);
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
  }

  .border-bottom-genshin { border-bottom: 1px solid var(--border-genshin) !important; }
  .border-top-genshin { border-top: 1px solid var(--border-genshin) !important; }

  .border-gold-glow {
    border-color: rgba(200, 170, 110, 0.4) !important;
    box-shadow: 0 0 20px rgba(200, 170, 110, 0.08);
  }

  .genshin-input, .genshin-select {
    background-color: var(--bg-panel-dark) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #f3f4f6 !important;
    border-radius: 8px;
  }

  .genshin-input:focus, .genshin-select:focus {
    border-color: var(--color-gold) !important;
    box-shadow: 0 0 0 0.2rem rgba(200, 170, 110, 0.2) !important;
  }

  .genshin-input-group-text {
    background-color: var(--bg-panel-dark);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-right: none;
    color: var(--color-gold);
    border-radius: 8px 0 0 8px;
  }

  /* ─── Party Slot Cards ──────────────────────────── */
  .party-slot-card {
    position: relative;
    border-radius: 12px;
    min-height: 190px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.25s ease;
    user-select: none;
    overflow: hidden;
  }

  .party-slot-card.empty {
    background: var(--bg-slot-empty);
    border: 2px dashed rgba(255, 255, 255, 0.15);
  }

  .party-slot-card.empty:hover {
    border-color: var(--color-gold);
    background: rgba(200, 170, 110, 0.05);
    transform: translateY(-2px);
  }

  .party-slot-card.slot-active {
    border: 2px solid var(--color-gold-bright) !important;
    box-shadow: 0 0 16px rgba(251, 191, 36, 0.35);
  }

  .slot-active-indicator {
    display: none;
    position: absolute;
    top: 6px;
    left: 6px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #111;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 2px 7px;
    border-radius: 4px;
    z-index: 5;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
  }

  .party-slot-card.slot-active .slot-active-indicator {
    display: flex;
    align-items: center;
  }

  .slot-empty-state {
    text-align: center;
    padding: 1rem 0.5rem;
  }

  .slot-plus-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.6rem;
    color: rgba(255, 255, 255, 0.4);
    font-size: 1.4rem;
    transition: all 0.2s ease;
  }

  .party-slot-card.empty:hover .slot-plus-icon {
    color: var(--color-gold);
    border-color: var(--color-gold);
    background: rgba(200, 170, 110, 0.15);
    transform: scale(1.08);
  }

  .slot-number-badge {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--color-gold);
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .slot-hint {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.45);
    margin-top: 2px;
  }

  .party-slot-card.filled {
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 0.8rem 0.6rem;
    background: linear-gradient(180deg, var(--slot-glow, rgba(255,255,255,0.08)) 0%, var(--bg-panel-dark) 85%);
  }

  .party-slot-card.filled:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
  }

  .slot-element-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 5px;
    color: #fff;
    z-index: 4;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
  }

  .btn-slot-remove {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.25);
    border: 1px solid rgba(239, 68, 68, 0.6);
    color: #fca5a5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    z-index: 6;
    transition: all 0.2s ease;
  }

  .btn-slot-remove:hover {
    background: #ef4444;
    color: #fff;
    transform: scale(1.1);
  }

  .slot-avatar-wrapper {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    margin: 0.6rem auto 0.5rem;
    position: relative;
    padding: 3px;
    background: radial-gradient(circle, var(--slot-color, #c8aa6e) 0%, transparent 70%);
  }

  .slot-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    background: #181d29;
    border: 2px solid var(--slot-color, #c8aa6e);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
  }

  .slot-char-name {
    font-weight: 700;
    font-size: 0.88rem;
    color: #f3f4f6;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }

  .slot-char-meta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 2px;
  }

  .slot-char-stars {
    color: #fbbf24;
    font-size: 0.68rem;
    letter-spacing: -1px;
  }

  /* ─── Elemental Resonance Panel ─────────────────── */
  .elemental-resonance-box {
    background: rgba(15, 19, 29, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.08);
  }

  .resonance-icon-orb {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(234, 179, 8, 0.15);
    border: 1px solid rgba(234, 179, 8, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
  }

  .element-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.74rem;
    font-weight: 700;
    color: #fff;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.15);
  }

  .resonance-card {
    background: var(--bg-panel-dark);
    border-radius: 10px;
    border: 1px solid var(--res-color, rgba(255, 255, 255, 0.15));
    padding: 0.85rem 1rem;
    position: relative;
    transition: all 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
  }

  .resonance-card.active {
    box-shadow: 0 0 16px color-mix(in srgb, var(--res-color, #c8aa6e) 30%, transparent);
    background: linear-gradient(135deg, color-mix(in srgb, var(--res-color, #c8aa6e) 15%, transparent) 0%, var(--bg-panel-dark) 80%);
  }

  .resonance-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 0.35rem;
  }

  .resonance-title {
    font-weight: 700;
    font-size: 0.9rem;
    color: #f3f4f6;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .resonance-badge-status {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 4px;
  }

  .resonance-desc {
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.4;
    margin-bottom: 0;
  }

  /* ─── Element Filter Buttons ────────────────────── */
  .btn-element-filter {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7);
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 0.74rem;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
  }

  .btn-element-filter:hover {
    color: #fff;
    border-color: var(--elem-color, var(--color-gold));
    background: rgba(255, 255, 255, 0.08);
  }

  .btn-element-filter.active {
    background: color-mix(in srgb, var(--elem-color, var(--color-gold)) 25%, transparent);
    border-color: var(--elem-color, var(--color-gold));
    color: #fff;
    box-shadow: 0 0 10px color-mix(in srgb, var(--elem-color, var(--color-gold)) 40%, transparent);
  }

  /* ─── Character Catalog Grid ────────────────────── */
  .char-picker-card {
    position: relative;
    background: var(--bg-panel-dark);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0.6rem 0.4rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    overflow: hidden;
  }

  .char-picker-card:hover {
    transform: translateY(-3px);
    border-color: var(--elem-color, var(--color-gold));
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.5);
  }

  .char-picker-card.is-selected-in-party {
    border-color: #10b981 !important;
    background: rgba(16, 185, 129, 0.08);
    opacity: 0.75;
  }

  .char-picker-card.is-selected-in-party::after {
    content: "DIPILIH";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(16, 185, 129, 0.9);
    color: #000;
    font-size: 0.6rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding: 2px 0;
  }

  .char-avatar-ring {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    margin: 0 auto 0.4rem;
    position: relative;
    padding: 2px;
    background: radial-gradient(circle, var(--elem-color, #c8aa6e) 0%, transparent 70%);
  }

  .char-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    background: #181d29;
    border: 1.5px solid var(--elem-color, #c8aa6e);
  }

  .char-card-name {
    font-size: 0.78rem;
    font-weight: 700;
    color: #f3f4f6;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .char-card-badges {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 0.65rem;
  }

  .char-elem-tag {
    font-size: 0.6rem;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 3px;
    color: #fff;
  }

  /* ─── Saved Party Cards ─────────────────────────── */
  .saved-party-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-genshin);
    border-radius: 12px;
    padding: 1.1rem;
    transition: all 0.25s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .saved-party-card:hover {
    border-color: rgba(200, 170, 110, 0.45);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  }

  .saved-party-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 0.8rem;
  }

  .saved-party-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--color-gold-bright);
    margin-bottom: 2px;
  }

  .saved-party-chars {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 0.85rem;
  }

  .saved-char-mini {
    position: relative;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .saved-char-mini img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--char-elem-color, #c8aa6e);
    background: #181d29;
  }

  .saved-char-elem-icon {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--char-elem-color, #c8aa6e);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.55rem;
    border: 1px solid #111;
  }

  .saved-party-resonances {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 0.85rem;
  }

  .saved-res-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    background: color-mix(in srgb, var(--res-color, #c8aa6e) 20%, transparent);
    border: 1px solid var(--res-color, #c8aa6e);
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .saved-party-notes {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
    background: rgba(0, 0, 0, 0.25);
    border-radius: 6px;
    padding: 6px 10px;
    margin-bottom: 0.85rem;
    font-style: italic;
  }

  /* Benchmark Summary on Card */
  .saved-bench-box {
    background: rgba(15, 18, 28, 0.7);
    border-radius: 8px;
    padding: 8px 10px;
    margin-bottom: 0.85rem;
    border-left: 3px solid var(--bench-tier-color, #f59e0b);
  }

  .saved-party-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    padding-top: 0.7rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }

  /* ─── Benchmark Modals & Views ──────────────────── */
  .benchmark-dropzone {
    border: 2px dashed rgba(200, 170, 110, 0.35);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.02);
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
  }

  .benchmark-dropzone:hover {
    border-color: var(--color-gold);
    background: rgba(200, 170, 110, 0.04);
  }

  .bench-top-metric-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(200, 170, 110, 0.3);
  }

  .bench-top-metric-card .metric-label {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.05em;
  }

  .bench-top-metric-card .metric-value {
    font-size: 1.45rem;
    font-weight: 800;
    font-family: monospace, sans-serif;
  }

  .bench-char-stat-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 0.6rem;
    font-size: 0.72rem;
  }

  .bench-char-stat-card .stat-row {
    display: flex;
    justify-content: space-between;
    padding: 1px 0;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
  }

  .bench-char-stat-card .stat-row:last-child {
    border-bottom: none;
  }
</style>
@endpush

@push('scripts')
<script>
  // ─── INITIAL DATA FROM SERVER ──────────────────────────
  window.allCharacters = @json($characters);
  window.savedParties = @json($formattedSavedParties);
  const csrfToken = '{{ csrf_token() }}';

  // ─── ELEMENT COLORS & METADATA ─────────────────────────
  const ELEMENT_CONFIG = {
    'Pyro': { color: '#ef4444', icon: 'bi-fire' },
    'Hydro': { color: '#3b82f6', icon: 'bi-droplet-fill' },
    'Anemo': { color: '#10b981', icon: 'bi-wind' },
    'Electro': { color: '#a855f7', icon: 'bi-lightning-charge-fill' },
    'Dendro': { color: '#22c55e', icon: 'bi-flower1' },
    'Cryo': { color: '#06b6d4', icon: 'bi-snow' },
    'Geo': { color: '#eab308', icon: 'bi-shield-shaded' },
    'Physical': { color: '#94a3b8', icon: 'bi-shield' },
    'Multi': { color: '#f59e0b', icon: 'bi-shield-fill-check' }
  };

  // ─── CLIENT STATE ──────────────────────────────────────
  const partyState = {
    name: 'Party Baru',
    notes: '',
    accountId: '{{ $selectedAccountId ?? "" }}',
    slots: [null, null, null, null],
    activeSlotIndex: 0,
    editingPartyId: null
  };

  const filterState = {
    search: '',
    element: 'all',
    rarity: 'all',
    weapon: 'all'
  };

  // Active benchmark viewing / scanning
  let activeBenchmark = null;
  let activeScanPartyId = null;

  // ─── ELEMENTAL RESONANCE DEFINITIONS ───────────────────
  const RESONANCE_DEFINITIONS = {
    'Pyro': {
      name: 'Fervent Flames',
      element: 'Pyro',
      badge: 'Pyro + Pyro',
      icon: 'bi-fire',
      color: '#ef4444',
      desc: 'Meningkatkan Base ATK sebesar +25% dan mengurangi durasi terkena efek Cryo sebesar 40%.'
    },
    'Hydro': {
      name: 'Soothing Water',
      element: 'Hydro',
      badge: 'Hydro + Hydro',
      icon: 'bi-droplet-fill',
      color: '#3b82f6',
      desc: 'Meningkatkan Max HP seluruh party sebesar +25% dan mengurangi durasi terkena efek Pyro sebesar 40%.'
    },
    'Cryo': {
      name: 'Shattering Ice',
      element: 'Cryo',
      badge: 'Cryo + Cryo',
      icon: 'bi-snow',
      color: '#06b6d4',
      desc: 'Meningkatkan CRIT Rate sebesar +15% saat menyerang musuh yang terkena Cryo atau status Frozen.'
    },
    'Electro': {
      name: 'High Voltage',
      element: 'Electro',
      badge: 'Electro + Electro',
      icon: 'bi-lightning-charge-fill',
      color: '#a855f7',
      desc: 'Mengurangi durasi terkena Hydro 40%. Reaksi Superconduct, Overload, Electro-Charged, Quicken, Aggravate, atau Hyperbloom 100% memicu Electro Elemental Particle (CD 5s).'
    },
    'Geo': {
      name: 'Enduring Rock',
      element: 'Geo',
      badge: 'Geo + Geo',
      icon: 'bi-shield-shaded',
      color: '#eab308',
      desc: 'Kekuatan Shield +15%. Karakter yang dilindungi shield mendapatkan peningkatan DMG +15%, dan menyebabkan Geo RES musuh berkurang 20% selama 15s.'
    },
    'Anemo': {
      name: 'Impetuous Winds',
      element: 'Anemo',
      badge: 'Anemo + Anemo',
      icon: 'bi-wind',
      color: '#10b981',
      desc: 'Konsumsi Stamina berkurang 15%, Movement SPD meningkat 10%, dan Cooldown Skill berkurang 5%.'
    },
    'Dendro': {
      name: 'Sprawling Greenery',
      element: 'Dendro',
      badge: 'Dendro + Dendro',
      icon: 'bi-flower1',
      color: '#22c55e',
      desc: 'Meningkatkan Elemental Mastery sebesar +50 poin. Memicu reaksi Dendro memberikan buff EM tambahan hingga +30/+20 poin.'
    },
    'Multi': {
      name: 'Protective Canopy',
      element: 'Multi',
      badge: '4 Elemen Unik',
      icon: 'bi-shield-fill-check',
      color: '#f59e0b',
      desc: 'Meningkatkan seluruh Elemental RES & Physical RES seluruh anggota party sebesar +15%.'
    }
  };

  // ─── INITIALIZATION ON DOM READY ───────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    renderSlots();
    renderResonances();
    renderCharacterCatalog();
    renderSavedParties();
    bindEvents();
    bindBenchmarkEvents();
  });

  // ─── BIND CORE BUILDER EVENTS ──────────────────────────
  function bindEvents() {
    document.getElementById('inputPartyName').addEventListener('input', function (e) {
      partyState.name = e.target.value.trim();
    });

    document.getElementById('inputPartyNotes').addEventListener('input', function (e) {
      partyState.notes = e.target.value.trim();
    });

    document.getElementById('selectGameAccount').addEventListener('change', function (e) {
      partyState.accountId = e.target.value;
      fetchCharactersForAccount(e.target.value);
    });

    document.querySelectorAll('.party-slot-card').forEach(slotEl => {
      slotEl.addEventListener('click', function (e) {
        if (e.target.closest('.btn-slot-remove')) return;
        const index = parseInt(this.getAttribute('data-slot'));
        setActiveSlot(index);
      });
    });

    document.querySelectorAll('.btn-slot-remove').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const index = parseInt(this.getAttribute('data-slot'));
        removeCharacterFromSlot(index);
      });
    });

    document.getElementById('btnClearSlots').addEventListener('click', clearAllSlots);
    document.getElementById('btnSaveParty').addEventListener('click', saveOrUpdateParty);
    document.getElementById('btnNewPartyHeader').addEventListener('click', resetToNewParty);
    document.getElementById('btnCancelEdit').addEventListener('click', resetToNewParty);

    document.getElementById('btnScrollToSaved').addEventListener('click', function () {
      document.getElementById('savedPartiesSection').scrollIntoView({ behavior: 'smooth' });
    });

    document.getElementById('charSearchInput').addEventListener('input', function (e) {
      filterState.search = e.target.value.trim().toLowerCase();
      renderCharacterCatalog();
    });

    document.getElementById('btnClearSearch').addEventListener('click', function () {
      document.getElementById('charSearchInput').value = '';
      filterState.search = '';
      renderCharacterCatalog();
    });

    document.querySelectorAll('.btn-element-filter').forEach(btn => {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.btn-element-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterState.element = this.getAttribute('data-element');
        renderCharacterCatalog();
      });
    });

    document.getElementById('filterRarity').addEventListener('change', function (e) {
      filterState.rarity = e.target.value;
      renderCharacterCatalog();
    });

    document.getElementById('filterWeapon').addEventListener('change', function (e) {
      filterState.weapon = e.target.value;
      renderCharacterCatalog();
    });

    document.getElementById('btnResetFilters').addEventListener('click', function () {
      filterState.search = '';
      filterState.element = 'all';
      filterState.rarity = 'all';
      filterState.weapon = 'all';
      document.getElementById('charSearchInput').value = '';
      document.getElementById('filterRarity').value = 'all';
      document.getElementById('filterWeapon').value = 'all';
      document.querySelectorAll('.btn-element-filter').forEach(b => {
        b.classList.toggle('active', b.getAttribute('data-element') === 'all');
      });
      renderCharacterCatalog();
    });

    document.getElementById('btnRefreshSavedParties').addEventListener('click', fetchSavedPartiesList);
    document.getElementById('btnEmptyStateCreate').addEventListener('click', function () {
      document.getElementById('partyBuilderSection').scrollIntoView({ behavior: 'smooth' });
      setActiveSlot(0);
    });
  }

  // ─── SLOT MANAGEMENT ───────────────────────────────────
  function setActiveSlot(index) {
    partyState.activeSlotIndex = index;
    document.querySelectorAll('.party-slot-card').forEach((el, i) => {
      el.classList.toggle('slot-active', i === index);
    });
    document.getElementById('activeSlotNotice').textContent = `Slot ${index + 1}`;
  }

  function selectCharacter(char) {
    const existingIndex = partyState.slots.findIndex(s => s && s.id === char.id);
    if (existingIndex !== -1) {
      removeCharacterFromSlot(existingIndex);
      return;
    }

    const targetSlot = partyState.activeSlotIndex;
    partyState.slots[targetSlot] = char;

    renderSlots();
    renderResonances();
    renderCharacterCatalog();

    const nextEmptyIndex = partyState.slots.findIndex(s => s === null);
    if (nextEmptyIndex !== -1) {
      setActiveSlot(nextEmptyIndex);
    }
  }

  function removeCharacterFromSlot(slotIndex) {
    partyState.slots[slotIndex] = null;
    renderSlots();
    renderResonances();
    renderCharacterCatalog();
    setActiveSlot(slotIndex);
  }

  function clearAllSlots() {
    partyState.slots = [null, null, null, null];
    renderSlots();
    renderResonances();
    renderCharacterCatalog();
    setActiveSlot(0);
  }

  function renderSlots() {
    const filledCount = partyState.slots.filter(Boolean).length;
    document.getElementById('slotFilledCount').textContent = `${filledCount} / 4 Karakter Terisi`;

    partyState.slots.forEach((char, index) => {
      const slotEl = document.getElementById(`partySlot_${index}`);
      const emptyState = slotEl.querySelector('.slot-empty-state');
      const filledState = slotEl.querySelector('.slot-filled-state');
      const removeBtn = slotEl.querySelector('.btn-slot-remove');

      if (char) {
        slotEl.classList.remove('empty');
        slotEl.classList.add('filled');
        emptyState.classList.add('d-none');
        filledState.classList.remove('d-none');
        removeBtn.classList.remove('d-none');

        const elemColor = char.element_color || (ELEMENT_CONFIG[char.element]?.color ?? '#c8aa6e');
        const elemIcon = ELEMENT_CONFIG[char.element]?.icon ?? 'bi-circle';

        slotEl.style.setProperty('--slot-color', elemColor);
        slotEl.style.setProperty('--slot-glow', colorMixWithAlpha(elemColor, 0.25));

        const elemBadge = filledState.querySelector('.slot-element-badge');
        elemBadge.style.backgroundColor = elemColor;
        elemBadge.innerHTML = `<i class="bi ${elemIcon}"></i> ${char.element}`;

        const avatar = filledState.querySelector('.slot-avatar');
        avatar.src = char.icon_url || '';
        avatar.alt = char.name;

        filledState.querySelector('.slot-char-name').textContent = char.name;
        filledState.querySelector('.slot-char-name').title = char.name;

        filledState.querySelector('.slot-char-weapon').textContent = char.weapon_type;
        filledState.querySelector('.slot-char-stars').textContent = '★'.repeat(char.rarity || 5);

        const extraDiv = filledState.querySelector('.slot-char-extra');
        if (char.level !== null && char.level !== undefined) {
          extraDiv.classList.remove('d-none');
          extraDiv.innerHTML = `<span class="badge bg-dark border border-secondary text-warning" style="font-size: 0.65rem;">Lv.${char.level} C${char.constellation ?? 0}</span>`;
        } else {
          extraDiv.classList.add('d-none');
          extraDiv.innerHTML = '';
        }
      } else {
        slotEl.classList.remove('filled');
        slotEl.classList.add('empty');
        slotEl.style.removeProperty('--slot-color');
        slotEl.style.removeProperty('--slot-glow');
        emptyState.classList.remove('d-none');
        filledState.classList.add('d-none');
        removeBtn.classList.add('d-none');
      }
    });
  }

  // ─── ELEMENTAL RESONANCE CALCULATION ───────────────────
  function calculateActiveResonances(characters) {
    if (!characters || characters.length === 0) return [];

    const elementCounts = {};
    characters.forEach(c => {
      if (!c || !c.element) return;
      const el = c.element.charAt(0).toUpperCase() + c.element.slice(1).toLowerCase();
      elementCounts[el] = (elementCounts[el] || 0) + 1;
    });

    const isFullParty = characters.length === 4;
    const resonances = [];

    for (const [el, count] of Object.entries(elementCounts)) {
      if (count >= 2 && RESONANCE_DEFINITIONS[el]) {
        resonances.push({
          ...RESONANCE_DEFINITIONS[el],
          count: count,
          is_active: isFullParty
        });
      }
    }

    if (Object.keys(elementCounts).length === 4) {
      resonances.push({
        ...RESONANCE_DEFINITIONS['Multi'],
        count: 4,
        is_active: isFullParty
      });
    }

    return resonances;
  }

  function renderResonances() {
    const presentChars = partyState.slots.filter(Boolean);
    const elementCounts = {};
    presentChars.forEach(c => {
      const el = c.element.charAt(0).toUpperCase() + c.element.slice(1).toLowerCase();
      elementCounts[el] = (elementCounts[el] || 0) + 1;
    });

    const pillsContainer = document.getElementById('partyElementsPills');
    pillsContainer.innerHTML = '';
    for (const [elem, count] of Object.entries(elementCounts)) {
      const meta = ELEMENT_CONFIG[elem] || { color: '#c8aa6e', icon: 'bi-circle' };
      const pill = document.createElement('span');
      pill.className = 'element-badge-pill';
      pill.style.borderColor = meta.color;
      pill.style.backgroundColor = colorMixWithAlpha(meta.color, 0.2);
      pill.innerHTML = `<i class="bi ${meta.icon}"></i> ${count}x ${elem}`;
      pillsContainer.appendChild(pill);
    }

    const resonances = calculateActiveResonances(presentChars);
    const cardsContainer = document.getElementById('resonanceCardsList');
    const summaryText = document.getElementById('resonanceSummaryText');

    cardsContainer.innerHTML = '';

    if (resonances.length === 0) {
      if (presentChars.length === 0) {
        summaryText.textContent = 'Pilih karakter untuk melihat efek resonansi elemental tim.';
      } else {
        summaryText.textContent = `Membutuhkan minimal 2 karakter berelemen sama atau 4 elemen berbeda untuk mengaktifkan resonansi.`;
      }

      cardsContainer.innerHTML = `
        <div class="col-12">
          <div class="p-3 rounded text-center" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.12);">
            <i class="bi bi-info-circle text-muted me-1"></i>
            <span class="small text-muted">
              Belum ada Resonansi Elemental yang aktif. Susun minimal 2 karakter berelemen sama atau 4 elemen berbeda di dalam tim yang beranggotakan 4 karakter.
            </span>
          </div>
        </div>
      `;
      return;
    }

    const isFull = presentChars.length === 4;
    summaryText.textContent = isFull
      ? `${resonances.length} Resonansi Elemental sedang AKTIF!`
      : `${resonances.length} Resonansi terdeteksi (Akan aktif sepenuhnya saat party terisi 4 karakter).`;

    resonances.forEach(res => {
      const card = document.createElement('div');
      card.className = `col-12 col-md-6`;

      const statusBadge = res.is_active
        ? `<span class="badge bg-success text-white resonance-badge-status"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>`
        : `<span class="badge bg-warning text-dark resonance-badge-status"><i class="bi bi-clock-history me-1"></i>Siap Aktif (Perlu 4 Karakter)</span>`;

      card.innerHTML = `
        <div class="resonance-card ${res.is_active ? 'active' : ''}" style="--res-color: ${res.color};">
          <div class="resonance-card-header">
            <div class="resonance-title" style="color: ${res.color};">
              <i class="bi ${res.icon}"></i> ${res.name}
              <span class="badge rounded-pill bg-dark border border-secondary text-secondary ms-1" style="font-size: 0.65rem;">
                ${res.badge}
              </span>
            </div>
            <div>${statusBadge}</div>
          </div>
          <p class="resonance-desc">${res.desc}</p>
        </div>
      `;
      cardsContainer.appendChild(card);
    });
  }

  // ─── CHARACTER CATALOG ─────────────────────────────────
  function renderCharacterCatalog() {
    const grid = document.getElementById('characterGrid');
    const emptyState = document.getElementById('catalogEmptyState');
    grid.innerHTML = '';

    const selectedIds = new Set(partyState.slots.filter(Boolean).map(c => c.id));

    const filtered = window.allCharacters.filter(c => {
      if (filterState.search && !c.name.toLowerCase().includes(filterState.search)) return false;
      if (filterState.element !== 'all' && c.element.toLowerCase() !== filterState.element.toLowerCase()) return false;
      if (filterState.rarity !== 'all' && parseInt(c.rarity) !== parseInt(filterState.rarity)) return false;
      if (filterState.weapon !== 'all' && c.weapon_type.toLowerCase() !== filterState.weapon.toLowerCase()) return false;
      return true;
    });

    document.getElementById('catalogCountLabel').textContent = `(${filtered.length} dari ${window.allCharacters.length})`;

    if (filtered.length === 0) {
      emptyState.classList.remove('d-none');
      return;
    }
    emptyState.classList.add('d-none');

    filtered.forEach(char => {
      const isSelected = selectedIds.has(char.id);
      const elemColor = char.element_color || (ELEMENT_CONFIG[char.element]?.color ?? '#c8aa6e');
      const elemIcon = ELEMENT_CONFIG[char.element]?.icon ?? 'bi-circle';

      const col = document.createElement('div');
      col.className = 'col';

      col.innerHTML = `
        <div class="char-picker-card ${isSelected ? 'is-selected-in-party' : ''}" style="--elem-color: ${elemColor};" title="${char.name} (${char.element} - ${char.weapon_type})">
          <div class="char-avatar-ring">
            <img src="${char.icon_url || ''}" alt="${char.name}" class="char-avatar-img" loading="lazy">
          </div>
          <div class="char-card-name">${char.name}</div>
          <div class="char-card-badges">
            <span class="char-elem-tag" style="background-color: ${elemColor};">
              <i class="bi ${elemIcon}"></i> ${char.element}
            </span>
            <span class="text-warning" style="font-size: 0.65rem;">★${char.rarity || 5}</span>
          </div>
        </div>
      `;

      col.querySelector('.char-picker-card').addEventListener('click', function () {
        selectCharacter(char);
      });

      grid.appendChild(col);
    });
  }

  // ─── RENDER SAVED PARTIES ──────────────────────────────
  function renderSavedParties() {
    const grid = document.getElementById('savedPartiesGrid');
    const emptyState = document.getElementById('savedPartiesEmptyState');
    const counter = document.getElementById('savedPartiesCounter');

    grid.innerHTML = '';
    counter.textContent = window.savedParties.length;

    if (window.savedParties.length === 0) {
      emptyState.classList.remove('d-none');
      return;
    }
    emptyState.classList.add('d-none');

    window.savedParties.forEach(party => {
      const col = document.createElement('div');
      col.className = 'col-12 col-md-6 col-lg-4';

      let charsHtml = '';
      (party.characters || []).forEach(c => {
        const elemColor = c.element_color || (ELEMENT_CONFIG[c.element]?.color ?? '#c8aa6e');
        const elemIcon = ELEMENT_CONFIG[c.element]?.icon ?? 'bi-circle';
        charsHtml += `
          <div class="saved-char-mini" style="--char-elem-color: ${elemColor};" title="${c.name} (${c.element})">
            <img src="${c.icon_url || ''}" alt="${c.name}">
            <div class="saved-char-elem-icon">
              <i class="bi ${elemIcon}"></i>
            </div>
          </div>
        `;
      });

      let resHtml = '';
      (party.resonances || []).forEach(r => {
        const color = r.color || (ELEMENT_CONFIG[r.element]?.color ?? '#c8aa6e');
        const icon = r.icon || 'bi-lightning-fill';
        resHtml += `
          <span class="saved-res-badge" style="--res-color: ${color};" title="${r.desc}">
            <i class="bi ${icon}"></i> ${r.name}
          </span>
        `;
      });
      if (!resHtml) resHtml = '<span class="small text-muted" style="font-size: 0.72rem;">Tidak ada resonansi aktif</span>';

      const notesHtml = party.notes ? `<div class="saved-party-notes"><i class="bi bi-chat-quote me-1"></i>${escapeHtml(party.notes)}</div>` : '';
      const accountBadge = party.account_name ? `<span class="badge bg-dark border border-secondary text-warning" style="font-size: 0.65rem;"><i class="bi bi-person me-1"></i>${party.account_name}</span>` : '';

      // Benchmark Box if exists
      let benchBoxHtml = '';
      const lb = party.latest_benchmark;
      if (lb) {
        benchBoxHtml = `
          <div class="saved-bench-box" style="--bench-tier-color: ${lb.tier_color};">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="badge" style="background-color: ${lb.tier_color}; color: #000; font-weight: 800; font-size: 0.72rem;">
                Tier ${lb.tier}
              </span>
              <span class="text-gold fw-bold" style="font-size: 0.88rem; font-family: monospace;">
                <i class="bi bi-speedometer2 text-warning me-1"></i>${lb.formatted_dps} DPS
              </span>
            </div>
            <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.7rem;">
              <span>Total: ${lb.formatted_damage}</span>
              <span>Hit Terkuat: ${lb.formatted_max_hit}</span>
            </div>
          </div>
        `;
      }

      // Actions row
      let benchActions = '';
      if (lb) {
        benchActions = `
          <button type="button" class="btn btn-sm btn-outline-success btn-view-bench" data-id="${lb.id}" title="Lihat Laporan Lengkap">
            <i class="bi bi-bar-chart-line-fill me-1"></i>Hasil Uji (${party.benchmarks_count})
          </button>
          <button type="button" class="btn btn-sm btn-outline-warning btn-export-gemini" data-id="${lb.id}" title="Export Prompt untuk Gemini AI">
            <i class="bi bi-cpu-fill me-1"></i>Gemini AI
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary btn-open-scan" data-party-id="${party.id}" title="Tambah / Scan Uji Baru">
            <i class="bi bi-camera-fill"></i>
          </button>
        `;
      } else {
        benchActions = `
          <button type="button" class="btn btn-sm btn-outline-info w-100 btn-open-scan" data-party-id="${party.id}">
            <i class="bi bi-camera-fill me-1"></i>Scan / Input Hasil Uji
          </button>
        `;
      }

      col.innerHTML = `
        <div class="saved-party-card" id="savedPartyCard_${party.id}">
          <div>
            <div class="saved-party-header">
              <div>
                <h6 class="saved-party-title">${escapeHtml(party.name)}</h6>
                <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 0.72rem;">
                  <span><i class="bi bi-clock me-1"></i>${party.created_at}</span>
                  ${accountBadge}
                </div>
              </div>
              <span class="badge bg-dark border border-secondary text-gold" style="font-size: 0.7rem;">
                ${party.characters ? party.characters.length : 0} Karakter
              </span>
            </div>

            <div class="saved-party-chars">
              ${charsHtml}
            </div>

            <div class="saved-party-resonances">
              ${resHtml}
            </div>

            ${benchBoxHtml}
            ${notesHtml}
          </div>

          <div>
            <div class="d-flex align-items-center gap-2 mb-2">
              ${benchActions}
            </div>

            <div class="saved-party-actions">
              <button type="button" class="btn btn-sm btn-outline-warning w-100 btn-load-party" data-id="${party.id}">
                <i class="bi bi-box-arrow-in-up me-1"></i>Muat ke Builder
              </button>
              <button type="button" class="btn btn-sm btn-outline-info btn-edit-party" data-id="${party.id}" title="Edit Party">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete-party" data-id="${party.id}" title="Hapus Party">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          </div>
        </div>
      `;

      col.querySelector('.btn-load-party').addEventListener('click', () => loadPartyToBuilder(party));
      col.querySelector('.btn-edit-party').addEventListener('click', () => startEditParty(party));
      col.querySelector('.btn-delete-party').addEventListener('click', () => confirmDeleteParty(party));

      col.querySelectorAll('.btn-open-scan').forEach(btn => {
        btn.addEventListener('click', () => openScanBenchmarkModal(party));
      });

      col.querySelectorAll('.btn-view-bench').forEach(btn => {
        btn.addEventListener('click', function () {
          const bId = this.getAttribute('data-id');
          openViewBenchmarkModal(bId);
        });
      });

      col.querySelectorAll('.btn-export-gemini').forEach(btn => {
        btn.addEventListener('click', function () {
          const bId = this.getAttribute('data-id');
          openGeminiExportModal(bId);
        });
      });

      grid.appendChild(col);
    });
  }

  // ─── LOAD & EDIT PARTY ─────────────────────────────────
  function loadPartyToBuilder(party) {
    partyState.name = party.name || 'Party';
    partyState.notes = party.notes || '';
    document.getElementById('inputPartyName').value = partyState.name;
    document.getElementById('inputPartyNotes').value = partyState.notes;

    partyState.slots = [null, null, null, null];
    (party.character_ids || []).slice(0, 4).forEach((id, i) => {
      const found = window.allCharacters.find(c => c.id == id);
      if (found) partyState.slots[i] = found;
    });

    renderSlots();
    renderResonances();
    renderCharacterCatalog();
    document.getElementById('partyBuilderSection').scrollIntoView({ behavior: 'smooth' });

    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'info',
      title: `Party "${party.name}" dimuat ke Builder`,
      showConfirmButton: false,
      timer: 2000
    });
  }

  function startEditParty(party) {
    loadPartyToBuilder(party);
    partyState.editingPartyId = party.id;

    const badge = document.getElementById('builderModeBadge');
    badge.className = 'badge rounded-pill bg-warning text-dark fw-bold px-3 py-2';
    badge.innerHTML = `<i class="bi bi-pencil-square me-1"></i>Mengedit: "${escapeHtml(party.name)}"`;

    document.getElementById('btnCancelEdit').classList.remove('d-none');
    document.getElementById('btnSaveText').textContent = 'Perbarui Party';
    document.getElementById('btnSaveParty').className = 'btn btn-warning btn-sm fw-bold';
  }

  function resetToNewParty() {
    partyState.editingPartyId = null;
    partyState.name = 'Party Baru';
    partyState.notes = '';
    document.getElementById('inputPartyName').value = 'Party Baru';
    document.getElementById('inputPartyNotes').value = '';

    const badge = document.getElementById('builderModeBadge');
    badge.className = 'badge rounded-pill bg-gold text-dark fw-bold px-3 py-2';
    badge.innerHTML = `<i class="bi bi-hammer me-1"></i>Mode: Buat Party Baru`;

    document.getElementById('btnCancelEdit').classList.add('d-none');
    document.getElementById('btnSaveText').textContent = 'Simpan Party';
    document.getElementById('btnSaveParty').className = 'btn btn-genshin btn-genshin-sm';

    clearAllSlots();
  }

  // ─── SAVE OR UPDATE VIA AJAX ───────────────────────────
  function saveOrUpdateParty() {
    const name = partyState.name || document.getElementById('inputPartyName').value.trim();
    if (!name) {
      Swal.fire('Nama Party Kosong', 'Mohon masukkan nama party terlebih dahulu!', 'warning');
      return;
    }

    const selectedCharacters = partyState.slots.filter(Boolean);
    if (selectedCharacters.length === 0) {
      Swal.fire('Karakter Belum Dipilih', 'Pilih minimal 1 karakter sebelum menyimpan!', 'warning');
      return;
    }

    const charIds = selectedCharacters.map(c => c.id);
    const notes = partyState.notes || document.getElementById('inputPartyNotes').value.trim();
    const accountId = partyState.accountId || document.getElementById('selectGameAccount').value || null;

    const isEdit = partyState.editingPartyId !== null;
    const url = isEdit ? `{{ url('party/update') }}/${partyState.editingPartyId}` : `{{ route('party.save') }}`;
    const method = isEdit ? 'PUT' : 'POST';

    const saveBtn = document.getElementById('btnSaveParty');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span>Menyimpan...`;

    fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        name: name,
        character_ids: charIds,
        notes: notes,
        game_account_id: accountId
      })
    })
    .then(res => res.json())
    .then(data => {
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;

      if (data.success) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: data.message || 'Party berhasil disimpan!',
          showConfirmButton: false,
          timer: 2500
        });

        if (isEdit) {
          const idx = window.savedParties.findIndex(p => p.id === partyState.editingPartyId);
          if (idx !== -1) window.savedParties[idx] = data.party;
          resetToNewParty();
        } else {
          window.savedParties.unshift(data.party);
        }

        renderSavedParties();
      } else {
        Swal.fire('Gagal Menyimpan', data.message || 'Terjadi kesalahan.', 'error');
      }
    })
    .catch(err => {
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;
      console.error(err);
      Swal.fire('Kesalahan Sistem', 'Gagal menghubungi server.', 'error');
    });
  }

  function confirmDeleteParty(party) {
    Swal.fire({
      title: `Hapus Party "${party.name}"?`,
      text: 'Komposisi party yang dihapus tidak dapat dikembalikan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch(`{{ url('party/saved') }}/${party.id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            window.savedParties = window.savedParties.filter(p => p.id !== party.id);
            renderSavedParties();
            if (partyState.editingPartyId === party.id) resetToNewParty();

            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: data.message || 'Party berhasil dihapus!',
              showConfirmButton: false,
              timer: 2000
            });
          } else {
            Swal.fire('Error', data.message || 'Gagal menghapus party.', 'error');
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'Gagal menghubungi server.', 'error');
        });
      }
    });
  }

  function fetchSavedPartiesList() {
    fetch('{{ route("party.list-ajax") }}', { headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(data => {
      if (data.success && data.parties) {
        window.savedParties = data.parties;
        renderSavedParties();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Daftar party diperbarui!',
          showConfirmButton: false,
          timer: 1500
        });
      }
    })
    .catch(err => console.error(err));
  }

  function fetchCharactersForAccount(accountId) {
    const url = accountId
      ? `{{ route('party.characters-ajax') }}?game_account_id=${accountId}`
      : `{{ route('party.characters-ajax') }}`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(data => {
      if (data.success && data.characters) {
        window.allCharacters = data.characters;
        partyState.slots.forEach((c, idx) => {
          if (c) {
            const updated = window.allCharacters.find(item => item.id === c.id);
            if (updated) partyState.slots[idx] = updated;
          }
        });
        renderSlots();
        renderCharacterCatalog();
      }
    })
    .catch(err => console.error(err));
  }

  // =========================================================================
  // BENCHMARK SCAN & MODAL MANAGEMENT
  // =========================================================================
  function bindBenchmarkEvents() {
    const dropzone = document.getElementById('benchmarkDropzone');
    const fileInput = document.getElementById('benchmarkImageInput');
    const selectBtn = document.getElementById('btnSelectBenchmarkImage');

    selectBtn.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('click', (e) => {
      if (e.target !== selectBtn) fileInput.click();
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.add('border-warning');
      });
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-warning');
      });
    });

    dropzone.addEventListener('drop', (e) => {
      if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        handleBenchmarkFileUpload(e.dataTransfer.files[0]);
      }
    });

    fileInput.addEventListener('change', (e) => {
      if (e.target.files && e.target.files[0]) {
        handleBenchmarkFileUpload(e.target.files[0]);
      }
    });

    // Add rotation row
    document.getElementById('btnAddRotationRow').addEventListener('click', () => {
      addRotationRowToForm();
    });

    // Save benchmark button
    document.getElementById('btnSaveBenchmarkData').addEventListener('click', saveBenchmarkData);

    // Copy Gemini Prompt button
    document.getElementById('btnCopyPromptToClipboard').addEventListener('click', () => {
      const textarea = document.getElementById('geminiPromptTextarea');
      textarea.select();
      navigator.clipboard.writeText(textarea.value).then(() => {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Prompt Gemini AI berhasil disalin!',
          showConfirmButton: false,
          timer: 2000
        });
      });
    });

    // View Modal actions
    document.getElementById('btnExportGeminiFromView').addEventListener('click', () => {
      if (activeBenchmark) {
        bootstrap.Modal.getInstance(document.getElementById('modalViewBenchmark')).hide();
        openGeminiExportModal(activeBenchmark.id);
      }
    });

    document.getElementById('btnDeleteCurrentBenchmark').addEventListener('click', () => {
      if (activeBenchmark) {
        confirmDeleteBenchmark(activeBenchmark.id);
      }
    });
  }

  function openScanBenchmarkModal(party) {
    activeScanPartyId = party.id;
    document.getElementById('benchPartyId').value = party.id;
    document.getElementById('scanTargetPartyName').textContent = party.name;

    // Reset fields
    document.getElementById('formBenchmarkData').reset();
    document.getElementById('tbodyBenchRotations').innerHTML = '';
    document.getElementById('benchCharDmgInputs').innerHTML = '';
    document.getElementById('benchElemDmgInputs').innerHTML = '';
    document.getElementById('benchCharStatsCards').innerHTML = '';

    // Initialize 4 party characters into dmg inputs
    initBenchmarkFormDefaults(party);

    const modal = new bootstrap.Modal(document.getElementById('modalScanBenchmark'));
    modal.show();
  }

  function initBenchmarkFormDefaults(party) {
    const charsContainer = document.getElementById('benchCharDmgInputs');
    charsContainer.innerHTML = '';
    (party.characters || []).forEach((c, idx) => {
      charsContainer.innerHTML += `
        <div class="row g-2 align-items-center mb-1">
          <div class="col-4">
            <input type="text" class="form-control form-control-sm genshin-input bench-char-name" value="${c.name}" placeholder="Nama">
          </div>
          <div class="col-3">
            <input type="text" class="form-control form-control-sm genshin-input bench-char-elem" value="${c.element}" placeholder="Elemen">
          </div>
          <div class="col-3">
            <input type="number" class="form-control form-control-sm genshin-input bench-char-dmg" value="0" placeholder="Damage">
          </div>
          <div class="col-2">
            <div class="input-group input-group-sm">
              <input type="number" class="form-control genshin-input bench-char-pct" value="0">
              <span class="input-group-text genshin-input-group-text">%</span>
            </div>
          </div>
        </div>
      `;
    });

    // Init Element Dmg Inputs (8 Elements)
    const elemContainer = document.getElementById('benchElemDmgInputs');
    elemContainer.innerHTML = '';
    ['pyro', 'hydro', 'electro', 'cryo', 'anemo', 'geo', 'dendro', 'physical'].forEach(elem => {
      elemContainer.innerHTML += `
        <div class="col-6">
          <div class="input-group input-group-sm">
            <span class="input-group-text genshin-input-group-text text-capitalize" style="width: 70px;">${elem}</span>
            <input type="number" class="form-control genshin-input bench-elem-val" data-elem="${elem}" value="0">
            <span class="input-group-text genshin-input-group-text">%</span>
          </div>
        </div>
      `;
    });

    // Add 7 rotation rows default
    const tbody = document.getElementById('tbodyBenchRotations');
    tbody.innerHTML = '';
    for (let i = 1; i <= 7; i++) {
      addRotationRowToForm(i, 0, 0, 0);
    }
  }

  function addRotationRowToForm(num = null, dps = 0, dmg = 0, time = 0) {
    const tbody = document.getElementById('tbodyBenchRotations');
    const rowCount = tbody.querySelectorAll('tr').length + 1;
    const rotNum = num || rowCount;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="number" class="form-control form-control-sm genshin-input text-center rot-num" value="${rotNum}"></td>
      <td><input type="number" class="form-control form-control-sm genshin-input rot-dps" value="${dps}" placeholder="DPS"></td>
      <td><input type="number" class="form-control form-control-sm genshin-input rot-dmg" value="${dmg}" placeholder="Total DMG"></td>
      <td><input type="number" step="0.01" class="form-control form-control-sm genshin-input rot-time" value="${time}" placeholder="Detik"></td>
      <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger btn-del-rot"><i class="bi bi-x"></i></button></td>
    `;
    tr.querySelector('.btn-del-rot').addEventListener('click', () => tr.remove());
    tbody.appendChild(tr);
  }

  function handleBenchmarkFileUpload(file) {
    const loading = document.getElementById('scanLoadingOverlay');
    loading.classList.remove('d-none');

    const formData = new FormData();
    formData.append('image', file);

    fetch('{{ route("party.scan-benchmark") }}', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      loading.classList.add('d-none');
      if (data.success && data.data) {
        populateBenchmarkFormData(data.data, data.screenshot_path);
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Screenshot berhasil diekstrak via Gemini AI!',
          showConfirmButton: false,
          timer: 3000
        });
      } else {
        Swal.fire('Ekstraksi Gagal', data.message || 'Gagal mengekstrak data gambar.', 'error');
      }
    })
    .catch(err => {
      loading.classList.add('d-none');
      console.error(err);
      Swal.fire('Error Sistem', 'Terjadi kesalahan saat memproses gambar.', 'error');
    });
  }

  function populateBenchmarkFormData(d, screenshotPath) {
    document.getElementById('benchScreenshotPath').value = screenshotPath || '';
    document.getElementById('benchPresetName').value = d.preset_name || 'Spiral Abyss Lantai 12';
    document.getElementById('benchDps').value = d.dps || 0;
    document.getElementById('benchTotalTime').value = d.total_time || 0;
    document.getElementById('benchTotalDmg').value = d.total_damage || 0;
    document.getElementById('benchMaxHit').value = d.max_hit || 0;

    if (d.target_dummy) {
      document.getElementById('benchDummyName').value = d.target_dummy.name || 'SSR Teddy';
      document.getElementById('benchDummyLevel').value = d.target_dummy.level || 100;
    }
    document.getElementById('benchGuid').value = d.stage_guid || '';
    document.getElementById('benchUid').value = d.uid || '';

    // Populate Character Dmg Distribution
    const charsContainer = document.getElementById('benchCharDmgInputs');
    charsContainer.innerHTML = '';
    (d.character_dmg_distribution || []).forEach(c => {
      charsContainer.innerHTML += `
        <div class="row g-2 align-items-center mb-1">
          <div class="col-4">
            <input type="text" class="form-control form-control-sm genshin-input bench-char-name" value="${escapeHtml(c.name)}" placeholder="Nama">
          </div>
          <div class="col-3">
            <input type="text" class="form-control form-control-sm genshin-input bench-char-elem" value="${c.element || ''}" placeholder="Elemen">
          </div>
          <div class="col-3">
            <input type="number" class="form-control form-control-sm genshin-input bench-char-dmg" value="${c.damage || 0}" placeholder="Damage">
          </div>
          <div class="col-2">
            <div class="input-group input-group-sm">
              <input type="number" class="form-control genshin-input bench-char-pct" value="${c.percentage || 0}">
              <span class="input-group-text genshin-input-group-text">%</span>
            </div>
          </div>
        </div>
      `;
    });

    // Populate Elemental Dmg
    if (d.elemental_dmg_distribution) {
      document.querySelectorAll('.bench-elem-val').forEach(input => {
        const elemKey = input.getAttribute('data-elem');
        if (d.elemental_dmg_distribution[elemKey] !== undefined) {
          input.value = d.elemental_dmg_distribution[elemKey];
        }
      });
    }

    // Populate Rotations
    const tbody = document.getElementById('tbodyBenchRotations');
    tbody.innerHTML = '';
    (d.rotations || []).forEach(r => {
      addRotationRowToForm(r.rotation, r.dps, r.damage, r.time);
    });

    // Populate Character Stats
    const statsContainer = document.getElementById('benchCharStatsCards');
    statsContainer.innerHTML = '';
    (d.character_stats || []).forEach((st, idx) => {
      statsContainer.innerHTML += `
        <div class="col-12 col-md-6">
          <div class="p-2 rounded bg-dark border border-secondary">
            <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-secondary">
              <strong class="text-warning">${st.name} (${st.element})</strong>
              <span class="badge bg-secondary">Lv. ${st.level || 90}</span>
            </div>
            <div class="row g-1 small">
              <div class="col-6">HP: <input type="number" class="form-control form-control-sm genshin-input cs-hp" data-idx="${idx}" value="${st.hp || 0}"></div>
              <div class="col-6">Total ATK: <input type="number" class="form-control form-control-sm genshin-input cs-tatk" data-idx="${idx}" value="${st.total_atk || 0}"></div>
              <div class="col-6">CRIT Rate: <input type="number" step="0.1" class="form-control form-control-sm genshin-input cs-cr" data-idx="${idx}" value="${st.crit_rate || 0}"></div>
              <div class="col-6">CRIT DMG: <input type="number" step="0.1" class="form-control form-control-sm genshin-input cs-cd" data-idx="${idx}" value="${st.crit_dmg || 0}"></div>
              <div class="col-6">ER%: <input type="number" step="0.1" class="form-control form-control-sm genshin-input cs-er" data-idx="${idx}" value="${st.energy_recharge || 0}"></div>
              <div class="col-6">EM: <input type="number" class="form-control form-control-sm genshin-input cs-em" data-idx="${idx}" value="${st.elemental_mastery || 0}"></div>
            </div>
          </div>
        </div>
      `;
    });
    // Cache stats for submission
    window._lastExtractedStats = d.character_stats || [];
  }

  function saveBenchmarkData() {
    const partyId = document.getElementById('benchPartyId').value;
    const dps = document.getElementById('benchDps').value;
    const totalTime = document.getElementById('benchTotalTime').value;
    const totalDmg = document.getElementById('benchTotalDmg').value;
    const maxHit = document.getElementById('benchMaxHit').value;

    if (!dps || !totalTime || !totalDmg) {
      Swal.fire('Data Tidak Lengkap', 'Mohon isi setidaknya nilai DPS, Waktu, dan Total Damage.', 'warning');
      return;
    }

    // Build character dmg distribution
    const charDmg = [];
    document.querySelectorAll('#benchCharDmgInputs .row').forEach(row => {
      charDmg.push({
        name: row.querySelector('.bench-char-name').value,
        element: row.querySelector('.bench-char-elem').value,
        damage: parseInt(row.querySelector('.bench-char-dmg').value) || 0,
        percentage: parseInt(row.querySelector('.bench-char-pct').value) || 0
      });
    });

    // Build elemental dmg distribution
    const elemDmg = {};
    document.querySelectorAll('.bench-elem-val').forEach(input => {
      elemDmg[input.getAttribute('data-elem')] = parseInt(input.value) || 0;
    });

    // Build rotations
    const rotations = [];
    document.querySelectorAll('#tbodyBenchRotations tr').forEach(tr => {
      rotations.push({
        rotation: parseInt(tr.querySelector('.rot-num').value) || 1,
        dps: parseInt(tr.querySelector('.rot-dps').value) || 0,
        damage: parseInt(tr.querySelector('.rot-dmg').value) || 0,
        time: parseFloat(tr.querySelector('.rot-time').value) || 0
      });
    });

    // Sync stats from inputs
    const charStats = window._lastExtractedStats || [];
    charStats.forEach((st, idx) => {
      const hpIn = document.querySelector(`.cs-hp[data-idx="${idx}"]`);
      if (hpIn) st.hp = parseInt(hpIn.value) || st.hp;
      const atkIn = document.querySelector(`.cs-tatk[data-idx="${idx}"]`);
      if (atkIn) st.total_atk = parseInt(atkIn.value) || st.total_atk;
      const crIn = document.querySelector(`.cs-cr[data-idx="${idx}"]`);
      if (crIn) st.crit_rate = parseFloat(crIn.value) || st.crit_rate;
      const cdIn = document.querySelector(`.cs-cd[data-idx="${idx}"]`);
      if (cdIn) st.crit_dmg = parseFloat(cdIn.value) || st.crit_dmg;
      const erIn = document.querySelector(`.cs-er[data-idx="${idx}"]`);
      if (erIn) st.energy_recharge = parseFloat(erIn.value) || st.energy_recharge;
      const emIn = document.querySelector(`.cs-em[data-idx="${idx}"]`);
      if (emIn) st.elemental_mastery = parseInt(emIn.value) || st.elemental_mastery;
    });

    const payload = {
      saved_party_id: partyId,
      preset_name: document.getElementById('benchPresetName').value,
      dps: dps,
      total_time: totalTime,
      total_damage: totalDmg,
      max_hit: maxHit,
      target_dummy: {
        name: document.getElementById('benchDummyName').value,
        level: parseInt(document.getElementById('benchDummyLevel').value) || 100,
        elemental_res: { pyro: 10, hydro: 10, electro: 10, cryo: 10, anemo: 10, geo: 10, dendro: 10, physical: 10 }
      },
      character_dmg_distribution: charDmg,
      elemental_dmg_distribution: elemDmg,
      rotations: rotations,
      character_stats: charStats,
      stage_guid: document.getElementById('benchGuid').value,
      uid: document.getElementById('benchUid').value,
      screenshot_path: document.getElementById('benchScreenshotPath').value
    };

    fetch('{{ route("party.benchmarks.store") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalScanBenchmark')).hide();

        // Update party in window.savedParties
        const idx = window.savedParties.findIndex(p => p.id == partyId);
        if (idx !== -1 && data.party) {
          window.savedParties[idx] = data.party;
          renderSavedParties();
        }

        Swal.fire({
          icon: 'success',
          title: 'Hasil Uji Tersimpan!',
          text: `Benchmark berhasil ditautkan ke party. Tier Evaluasi: ${data.benchmark.tier} (${data.benchmark.tier_label})`,
          confirmButtonText: 'Lihat Laporan Lengkap',
          showCancelButton: true,
          cancelButtonText: 'Tutup'
        }).then((result) => {
          if (result.isConfirmed) {
            openViewBenchmarkModal(data.benchmark.id);
          }
        });
      } else {
        Swal.fire('Gagal Menyimpan', data.message || 'Terjadi kesalahan.', 'error');
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire('Error', 'Gagal menghubungi server.', 'error');
    });
  }

  // ─── VIEW BENCHMARK MODAL ──────────────────────────────
  function openViewBenchmarkModal(benchmarkId) {
    fetch(`{{ url('party/benchmarks') }}/${benchmarkId}`, {
      headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success && data.benchmark) {
        activeBenchmark = data.benchmark;
        renderViewBenchmarkDashboard(data.benchmark);
        const modal = new bootstrap.Modal(document.getElementById('modalViewBenchmark'));
        modal.show();
      }
    })
    .catch(err => console.error(err));
  }

  function renderViewBenchmarkDashboard(b) {
    document.getElementById('viewBenchPresetBadge').textContent = b.preset_name || 'Spiral Abyss Lantai 12';
    document.getElementById('viewBenchDps').textContent = b.formatted_dps;
    document.getElementById('viewBenchTime').textContent = `${b.total_time} d`;
    document.getElementById('viewBenchTotalDmg').textContent = b.formatted_damage;
    document.getElementById('viewBenchMaxHit').textContent = b.formatted_max_hit;

    // Diagnostics
    const ev = b.evaluation || {};
    const tierBadge = document.getElementById('viewBenchTierBadge');
    tierBadge.textContent = `Tier ${b.tier} (${b.tier_label})`;
    tierBadge.style.backgroundColor = b.tier_color;
    tierBadge.style.color = '#000';

    document.getElementById('viewBenchArchetypeLabel').textContent = ev.archetype || 'Balanced Tim';
    document.getElementById('viewBenchDominantElemLabel').textContent = ev.dominant_element || 'Multi';
    document.getElementById('viewBenchDate').textContent = b.created_at;

    // Positives & Warnings
    const posDiv = document.getElementById('viewBenchPositives');
    posDiv.innerHTML = '<strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Keunggulan Tim:</strong><ul class="mb-0 ps-3">';
    (ev.positives || ['DPS tinggi dan stabil.']).forEach(p => posDiv.innerHTML += `<li>${p}</li>`);
    posDiv.innerHTML += '</ul>';

    const warnDiv = document.getElementById('viewBenchWarnings');
    warnDiv.innerHTML = '<strong class="text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i>Peringatan & Saran:</strong><ul class="mb-0 ps-3">';
    const allNotices = (ev.warnings || []).concat(ev.recommendations || []);
    if (allNotices.length === 0) allNotices.push('Tidak ditemukan bottleneck rotasi.');
    allNotices.forEach(w => warnDiv.innerHTML += `<li>${w}</li>`);
    warnDiv.innerHTML += '</ul>';

    // Target Dummy
    const dummy = b.target_dummy || {};
    document.getElementById('viewBenchDummyLabel').textContent = `${dummy.name || 'SSR Teddy'} Lv. ${dummy.level || 100}`;
    const resBadges = document.getElementById('viewBenchResBadges');
    resBadges.innerHTML = '';
    const resMap = dummy.elemental_res || { pyro: 10, hydro: 10, electro: 10, cryo: 10, anemo: 10, geo: 10, dendro: 10, physical: 10 };
    for (const [eName, rVal] of Object.entries(resMap)) {
      const cfg = ELEMENT_CONFIG[ucfirst(eName)] || { color: '#c8aa6e' };
      resBadges.innerHTML += `
        <span class="badge border border-secondary" style="font-size: 0.62rem; color: ${cfg.color}; background: #161a29;">
          ${ucfirst(eName)} ${rVal}%
        </span>
      `;
    }

    // Character DMG Bars
    const charBars = document.getElementById('viewBenchCharDmgBars');
    charBars.innerHTML = '';
    (b.character_dmg_distribution || []).forEach(cd => {
      const cfg = ELEMENT_CONFIG[cd.element] || { color: '#c8aa6e' };
      charBars.innerHTML += `
        <div>
          <div class="d-flex justify-content-between small mb-1">
            <span class="fw-bold" style="color: ${cfg.color};">${cd.name}</span>
            <span class="text-muted" style="font-family: monospace;">${numberFormat(cd.damage)} (${cd.percentage}%)</span>
          </div>
          <div class="progress" style="height: 8px; background: rgba(255,255,255,0.06);">
            <div class="progress-bar" style="width: ${cd.percentage}%; background-color: ${cfg.color};"></div>
          </div>
        </div>
      `;
    });

    // Element DMG Summary
    const elemSum = document.getElementById('viewBenchElemDmgSummary');
    elemSum.innerHTML = '';
    for (const [elem, pct] of Object.entries(b.elemental_dmg_distribution || {})) {
      if (pct > 0) {
        const cfg = ELEMENT_CONFIG[ucfirst(elem)] || { color: '#c8aa6e' };
        elemSum.innerHTML += `
          <span class="badge" style="background-color: color-mix(in srgb, ${cfg.color} 25%, transparent); border: 1px solid ${cfg.color}; color: #fff; font-size: 0.72rem;">
            ${ucfirst(elem)} ${pct}%
          </span>
        `;
      }
    }

    // Rotations Table
    const rotList = document.getElementById('viewBenchRotationsList');
    rotList.innerHTML = '';
    (b.rotations || []).forEach(r => {
      rotList.innerHTML += `
        <tr class="border-bottom border-secondary">
          <td class="text-warning fw-bold" style="width: 25px;">#${r.rotation}</td>
          <td>DPS: <strong class="text-white">${numberFormat(r.dps)}</strong></td>
          <td class="text-muted">${numberFormat(r.damage)} DMG</td>
          <td class="text-end text-gold">${r.time} d</td>
        </tr>
      `;
    });

    // 4 Character Snapshot Stats
    const statsRow = document.getElementById('viewBenchCharStatsRow');
    statsRow.innerHTML = '';
    (b.character_stats || []).forEach(st => {
      const cfg = ELEMENT_CONFIG[st.element] || { color: '#c8aa6e' };
      statsRow.innerHTML += `
        <div class="col-6 col-md-3">
          <div class="bench-char-stat-card" style="border-top: 2px solid ${cfg.color};">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong style="color: ${cfg.color}; font-size: 0.85rem;">${st.name}</strong>
              <span class="badge bg-secondary" style="font-size: 0.62rem;">Lv. ${st.level || 90}</span>
            </div>
            <div class="stat-row"><span class="text-muted">HP:</span><span>${numberFormat(st.hp)}</span></div>
            <div class="stat-row"><span class="text-muted">Total ATK:</span><span class="text-warning">${st.total_atk || 0}</span></div>
            <div class="stat-row"><span class="text-muted">CRIT Rate:</span><span>${st.crit_rate}%</span></div>
            <div class="stat-row"><span class="text-muted">CRIT DMG:</span><span class="text-gold">${st.crit_dmg}%</span></div>
            <div class="stat-row"><span class="text-muted">ER%:</span><span class="text-info">${st.energy_recharge}%</span></div>
            <div class="stat-row"><span class="text-muted">EM:</span><span>${st.elemental_mastery || 0}</span></div>
          </div>
        </div>
      `;
    });

    document.getElementById('viewBenchGuid').textContent = b.stage_guid ? `GUID Stage: ${b.stage_guid}` : '';
    document.getElementById('viewBenchUid').textContent = b.uid ? `UID: ${b.uid}` : '';
  }

  function confirmDeleteBenchmark(benchmarkId) {
    Swal.fire({
      title: 'Hapus Hasil Pengujian Ini?',
      text: 'Data benchmark tidak dapat dikembalikan setelah dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then(res => {
      if (res.isConfirmed) {
        fetch(`{{ url('party/benchmarks') }}/${benchmarkId}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalViewBenchmark')).hide();
            fetchSavedPartiesList();
            Swal.fire('Terhapus', data.message || 'Benchmark berhasil dihapus.', 'success');
          }
        });
      }
    });
  }

  // ─── GEMINI PROMPT EXPORT MODAL ────────────────────────
  function openGeminiExportModal(benchmarkId) {
    fetch(`{{ url('party/benchmarks') }}/${benchmarkId}/export-prompt`, {
      headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success && data.prompt) {
        document.getElementById('geminiPromptTextarea').value = data.prompt;
        document.getElementById('btnDownloadMdLink').href = `{{ url('party/benchmarks') }}/${benchmarkId}/download-md`;
        document.getElementById('btnDownloadJsonLink').href = `{{ url('party/benchmarks') }}/${benchmarkId}/download-json`;

        const modal = new bootstrap.Modal(document.getElementById('modalGeminiExport'));
        modal.show();
      }
    })
    .catch(err => console.error(err));
  }

  // ─── UTILITIES ─────────────────────────────────────────
  function colorMixWithAlpha(hexColor, alpha) {
    let hex = hexColor.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const r = parseInt(hex.substring(0, 2), 16) || 200;
    const g = parseInt(hex.substring(2, 4), 16) || 170;
    const b = parseInt(hex.substring(4, 6), 16) || 110;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function numberFormat(val) {
    if (val === null || val === undefined) return '0';
    return Number(val).toLocaleString('id-ID');
  }
</script>
@endpush
@endsection
