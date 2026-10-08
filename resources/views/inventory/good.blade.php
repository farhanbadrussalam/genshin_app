@extends('layout.main')

@section('content')
@php $title = 'Export & Import Format GOOD'; @endphp
@include('layout.header')

<style>
  .good-card {
    background: rgba(22, 27, 46, 0.85);
    border: 1px solid rgba(228, 196, 133, 0.2);
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(10px);
    transition: all 0.25s ease;
  }
  .good-card:hover {
    border-color: rgba(228, 196, 133, 0.35);
  }
  .good-nav-tab {
    background: transparent;
    border: 1px solid rgba(228, 196, 133, 0.25);
    color: var(--text-secondary);
    border-radius: 10px;
    padding: 0.65rem 1.4rem;
    font-weight: 600;
    font-size: 0.92rem;
    transition: all 0.2s ease;
  }
  .good-nav-tab.active {
    background: linear-gradient(135deg, rgba(228, 196, 133, 0.25), rgba(212, 163, 89, 0.15)) !important;
    border-color: var(--genshin-gold) !important;
    color: var(--genshin-gold) !important;
    box-shadow: 0 0 15px rgba(228, 196, 133, 0.2);
  }
  .mode-option-card {
    cursor: pointer;
    border: 2px solid rgba(255, 255, 255, 0.08);
    background: rgba(15, 18, 32, 0.6);
    border-radius: 12px;
    padding: 1rem;
    transition: all 0.2s ease;
  }
  .mode-option-card:hover {
    border-color: rgba(228, 196, 133, 0.4);
    background: rgba(20, 25, 45, 0.8);
  }
  .mode-option-card.selected {
    border-color: var(--genshin-gold);
    background: rgba(228, 196, 133, 0.08);
    box-shadow: 0 0 16px rgba(228, 196, 133, 0.15);
  }
  .drop-zone {
    border: 2px dashed rgba(228, 196, 133, 0.35);
    border-radius: 12px;
    padding: 2.2rem 1.5rem;
    text-align: center;
    background: rgba(12, 15, 28, 0.5);
    transition: all 0.2s ease;
    cursor: pointer;
  }
  .drop-zone:hover, .drop-zone.dragover {
    border-color: var(--genshin-gold);
    background: rgba(228, 196, 133, 0.06);
  }
  .json-preview-box {
    background: #0b0e17;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1rem;
    color: #38bdf8;
    font-family: 'Fira Code', 'Courier New', monospace;
    font-size: 0.82rem;
    max-height: 420px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
  }
  .stat-pill {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.6rem 0.9rem;
  }
</style>

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 4rem;">

  {{-- Flash Notifications --}}
  @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 border-0 text-white" style="background: rgba(16, 185, 129, 0.2); border-left: 4px solid #10b981 !important;">
      <i class="bi bi-check-circle-fill fs-5 text-success"></i>
      <div>{{ session('success') }}</div>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 border-0 text-white" style="background: rgba(239, 68, 68, 0.2); border-left: 4px solid #ef4444 !important;">
      <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
      <div>{{ session('error') }}</div>
    </div>
  @endif

  {{-- Result Banner jika baru selesai Import --}}
  @if(session('good_import_result'))
    @php $res = session('good_import_result'); @endphp
    <div class="good-card p-4 mb-4 border-success animate-fade-in-up" style="border-left: 5px solid #10b981;">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
          <h4 class="font-display text-white mb-1">
            <i class="bi bi-file-earmark-check-fill text-success me-2"></i>Laporan Hasil Impor Berkas GOOD
          </h4>
          <span class="badge {{ $res['mode'] === 'replace' ? 'bg-danger' : 'bg-primary' }} text-uppercase">
            Mode: {{ $res['mode'] === 'replace' ? 'Replace (Ganti Seluruh Data)' : 'Merge (Perbarui & Tambah)' }}
          </span>
        </div>
        <a href="{{ route('inventory.dashboard', ['account_id' => $activeAccount?->id]) }}" class="btn-genshin btn-genshin-sm">
          <i class="bi bi-speedometer2 me-1"></i>Buka Dashboard Inventori
        </a>
      </div>

      <div class="row g-3">
        {{-- Karakter --}}
        <div class="col-6 col-md-3">
          <div class="stat-pill">
            <div class="text-muted small mb-1"><i class="bi bi-people-fill text-gold me-1"></i>Karakter</div>
            <div class="d-flex align-items-baseline gap-2">
              <span class="fs-5 fw-bold text-white">+{{ $res['stats']['characters']['imported'] }}</span>
              <span class="small text-info">Update: {{ $res['stats']['characters']['updated'] }}</span>
            </div>
            @if($res['stats']['characters']['skipped'] > 0)
              <div class="small text-warning">Dilewati: {{ $res['stats']['characters']['skipped'] }}</div>
            @endif
          </div>
        </div>

        {{-- Senjata --}}
        <div class="col-6 col-md-3">
          <div class="stat-pill">
            <div class="text-muted small mb-1"><i class="bi bi-shield-shaded text-gold me-1"></i>Senjata</div>
            <div class="d-flex align-items-baseline gap-2">
              <span class="fs-5 fw-bold text-white">+{{ $res['stats']['weapons']['imported'] }}</span>
              <span class="small text-info">Update: {{ $res['stats']['weapons']['updated'] }}</span>
            </div>
            @if($res['stats']['weapons']['skipped'] > 0)
              <div class="small text-warning">Dilewati: {{ $res['stats']['weapons']['skipped'] }}</div>
            @endif
          </div>
        </div>

        {{-- Artefak --}}
        <div class="col-6 col-md-3">
          <div class="stat-pill">
            <div class="text-muted small mb-1"><i class="bi bi-gem text-gold me-1"></i>Artefak (Scored)</div>
            <div class="d-flex align-items-baseline gap-2">
              <span class="fs-5 fw-bold text-white">+{{ $res['stats']['artifacts']['imported'] }}</span>
              <span class="small text-info">Update: {{ $res['stats']['artifacts']['updated'] }}</span>
            </div>
            @if($res['stats']['artifacts']['skipped'] > 0)
              <div class="small text-warning">Dilewati: {{ $res['stats']['artifacts']['skipped'] }}</div>
            @endif
          </div>
        </div>

        {{-- Material --}}
        <div class="col-6 col-md-3">
          <div class="stat-pill">
            <div class="text-muted small mb-1"><i class="bi bi-backpack text-gold me-1"></i>Material</div>
            <div class="d-flex align-items-baseline gap-2">
              <span class="fs-5 fw-bold text-white">+{{ $res['stats']['materials']['imported'] }}</span>
              <span class="small text-info">Update: {{ $res['stats']['materials']['updated'] }}</span>
            </div>
          </div>
        </div>
      </div>

      @if(!empty($res['warnings']))
        <div class="mt-3 p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25);">
          <div class="small fw-bold text-warning mb-1">
            <i class="bi bi-info-circle me-1"></i>Catatan Peringatan Master Data:
          </div>
          <ul class="mb-0 small text-light ps-3">
            @foreach($res['warnings'] as $warn)
              <li>{{ $warn }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  @endif

  {{-- Page Header & Account Selector --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <div class="d-flex align-items-center gap-2">
        <h1 class="font-display text-gold mb-1" style="font-size: 1.45rem; letter-spacing: 0.08em;">
          <i class="bi bi-arrow-left-right me-2"></i>Export / Import Format GOOD
        </h1>
        <span class="badge bg-dark border border-warning text-gold">Standard GOOD v2</span>
      </div>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0;">
        <strong>Genshin Open Object Description (GOOD)</strong> — Format pertukaran data standar komunitas untuk Genshin Optimizer, Seelie, Akasha, dan scanner lainnya.
      </p>
    </div>

    {{-- Account Switcher --}}
    @if($accounts->isNotEmpty())
      <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('inventory.good.index') }}" id="accountSelectForm">
          <div class="input-group input-group-sm">
            <span class="input-group-text genshin-input-group-text">
              <i class="bi bi-controller text-gold"></i>
            </span>
            <select name="account_id" class="form-select genshin-select" onchange="this.form.submit()" style="min-width: 200px;">
              @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ ($activeAccount?->id === $acc->id) ? 'selected' : '' }}>
                  {{ $acc->nickname }} (UID: {{ $acc->uid }})
                </option>
              @endforeach
            </select>
          </div>
        </form>

        <a href="{{ route('inventory.dashboard', ['account_id' => $activeAccount?->id]) }}" class="btn-genshin btn-genshin-sm">
          <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
      </div>
    @endif
  </div>

  @if(!$activeAccount)
    <div class="empty-state text-center py-5 good-card">
      <div style="font-size: 3rem; opacity: 0.5;">🎮</div>
      <h4 class="font-display text-gold mt-2">Belum Ada Akun Game</h4>
      <p class="text-muted">Tambahkan akun game Genshin Impact terlebih dahulu untuk menggunakan fitur Export / Import GOOD.</p>
      <a href="{{ route('game-accounts.index') }}" class="btn-genshin btn-genshin-sm">
        <i class="bi bi-plus-lg me-1"></i>Kelola Akun Game
      </a>
    </div>
  @else

    {{-- Tabs Export / Import --}}
    <ul class="nav nav-pills gap-2 mb-4" id="goodTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link good-nav-tab active" id="export-tab" data-bs-toggle="pill" data-bs-target="#export-content" type="button" role="tab">
          <i class="bi bi-download me-2"></i>Ekspor ke Format GOOD
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link good-nav-tab" id="import-tab" data-bs-toggle="pill" data-bs-target="#import-content" type="button" role="tab">
          <i class="bi bi-upload me-2"></i>Impor dari Berkas GOOD
        </button>
      </li>
    </ul>

    <div class="tab-content" id="goodTabContent">

      {{-- ==================== TAB 1: EXPORT ==================== --}}
      <div class="tab-pane fade show active" id="export-content" role="tabpanel">
        <div class="row g-4">
          
          <div class="col-12 col-lg-7">
            <div class="good-card p-4 h-100">
              <h4 class="font-display text-gold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-down-fill text-gold"></i>Ekspor Data Inventori Akun
              </h4>
              <p class="text-muted small mb-4">
                Pilih komponen yang ingin kamu ekspor. Berkas JSON yang dihasilkan kompatibel 100% dan bisa langsung diunggah ke <strong>Genshin Optimizer</strong> atau platform kalkulator Genshin lainnya.
              </p>

              <form action="{{ route('inventory.good.export') }}" method="POST" id="exportForm">
                @csrf
                <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

                {{-- Checklist Komponen --}}
                <div class="mb-4">
                  <label class="form-label text-gold small text-uppercase fw-bold mb-3">Komponen yang Disertakan:</label>
                  
                  <div class="row g-3">
                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_characters" id="expChar" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="expChar">
                          <i class="bi bi-people-fill text-gold me-2"></i>Koleksi Karakter
                          <div class="small text-muted ps-4">Level, Talent, Ascension, Constellation ({{ $accountStats['characters'] }} item)</div>
                        </label>
                      </div>
                    </div>

                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_weapons" id="expWeap" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="expWeap">
                          <i class="bi bi-shield-shaded text-gold me-2"></i>Koleksi Senjata
                          <div class="small text-muted ps-4">Level, Refinement, Ascension, Equips ({{ $accountStats['weapons'] }} item)</div>
                        </label>
                      </div>
                    </div>

                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_artifacts" id="expArt" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="expArt">
                          <i class="bi bi-gem text-gold me-2"></i>Koleksi Artefak
                          <div class="small text-muted ps-4">Set, Slot, Main Stat, Substats ({{ $accountStats['artifacts'] }} item)</div>
                        </label>
                      </div>
                    </div>

                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_materials" id="expMat" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="expMat">
                          <i class="bi bi-backpack text-gold me-2"></i>Material / Mora
                          <div class="small text-muted ps-4">Jumlah bahan & consumable ({{ $accountStats['materials'] }} item)</div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap gap-2 pt-2 border-top border-secondary">
                  <button type="submit" class="btn btn-warning fw-bold px-4 py-2 d-inline-flex align-items-center gap-2 text-dark shadow">
                    <i class="bi bi-download fs-5"></i>
                    <span>Unduh Berkas GOOD (.json)</span>
                  </button>

                  <button type="button" class="btn-genshin" id="btnPreviewExport">
                    <i class="bi bi-code-square me-1 text-gold"></i>Lihat Preview / Salin JSON
                  </button>
                </div>
              </form>
            </div>
          </div>

          {{-- Kolom Info Kompatibilitas --}}
          <div class="col-12 col-lg-5">
            <div class="good-card p-4 h-100">
              <h5 class="font-display text-gold mb-3">
                <i class="bi bi-patch-check-fill text-gold me-2"></i>Kompatibilitas Format GOOD
              </h5>
              
              <div class="d-flex flex-column gap-3 small text-light">
                <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                  <div class="fw-bold text-gold mb-1">Genshin Optimizer</div>
                  <p class="text-muted mb-0">Kamu bisa langsung mengimpor berkas ini ke Genshin Optimizer melalui menu <em>Settings → Database → Import GOOD</em>.</p>
                </div>

                <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                  <div class="fw-bold text-gold mb-1">SEELIE.me & Aspirine Calculator</div>
                  <p class="text-muted mb-0">Dapat dipakai untuk sinkronisasi target ascend karakter, material inventory, dan progres farming harian kamu.</p>
                </div>

                <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                  <div class="fw-bold text-gold mb-1">Scanner Komunitas (Inventory Kamera)</div>
                  <p class="text-muted mb-0">Format output dari Inventory Kamera atau OCR Scanner dapat langsung diimpor ke aplikasi ini di Tab Impor.</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- ==================== TAB 2: IMPORT ==================== --}}
      <div class="tab-pane fade" id="import-content" role="tabpanel">
        <div class="good-card p-4">
          <h4 class="font-display text-gold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-cloud-arrow-up-fill text-gold"></i>Impor Berkas GOOD ke Akun: <span class="text-white">{{ $activeAccount->nickname }}</span>
          </h4>
          <p class="text-muted small mb-4">
            Unggah file <code>.json</code> hasil export dari Genshin Optimizer, Inventory Kamera, atau tempelkan langsung string JSON. Sistem akan secara otomatis memetakan karakter, senjata, dan roll sub-stat artefak serta memberikan penilaian skor (auto-scoring).
          </p>

          <form action="{{ route('inventory.good.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
            @csrf
            <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

            {{-- 1. Pilihan Mode Impor --}}
            <div class="mb-4">
              <label class="form-label text-gold small text-uppercase fw-bold mb-2">1. Pilih Mode Impor:</label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mode-option-card selected" id="cardModeMerge" onclick="selectImportMode('merge')">
                    <div class="d-flex align-items-start gap-3">
                      <input class="form-check-input mt-1" type="radio" name="mode" id="modeMerge" value="merge" checked>
                      <div>
                        <div class="fw-bold text-white mb-1">
                          <i class="bi bi-union text-info me-1"></i>Gabungkan & Perbarui (Merge) <span class="badge bg-success small ms-1">Disarankan</span>
                        </div>
                        <div class="small text-muted">
                          Memperbarui level, talent, dan artefak yang cocok. Item baru akan ditambahkan tanpa menghapus item yang sudah ada di inventori kamu.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="mode-option-card" id="cardModeReplace" onclick="selectImportMode('replace')">
                    <div class="d-flex align-items-start gap-3">
                      <input class="form-check-input mt-1" type="radio" name="mode" id="modeReplace" value="replace">
                      <div>
                        <div class="fw-bold text-danger mb-1">
                          <i class="bi bi-arrow-repeat text-danger me-1"></i>Gantikan Seluruh Data (Replace)
                        </div>
                        <div class="small text-muted">
                          Menghapus seluruh karakter, senjata, dan artefak lama akun ini lalu menggantikannya murni dengan data dari berkas yang diimpor.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- 2. Pilihan Komponen yang Diimpor --}}
            <div class="mb-4">
              <label class="form-label text-gold small text-uppercase fw-bold mb-2">2. Komponen yang Ingin Diimpor:</label>
              <div class="d-flex flex-wrap gap-4 p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="import_characters" id="impChar" value="1" checked>
                  <label class="form-check-label text-white small fw-medium" for="impChar">
                    <i class="bi bi-people-fill text-gold me-1"></i>Karakter
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="import_weapons" id="impWeap" value="1" checked>
                  <label class="form-check-label text-white small fw-medium" for="impWeap">
                    <i class="bi bi-shield-shaded text-gold me-1"></i>Senjata
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="import_artifacts" id="impArt" value="1" checked>
                  <label class="form-check-label text-white small fw-medium" for="impArt">
                    <i class="bi bi-gem text-gold me-1"></i>Artefak (Auto-Scored)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="import_materials" id="impMat" value="1" checked>
                  <label class="form-check-label text-white small fw-medium" for="impMat">
                    <i class="bi bi-backpack text-gold me-1"></i>Material
                  </label>
                </div>
              </div>
            </div>

            {{-- 3. Sumber Berkas JSON (File Upload / Paste) --}}
            <div class="mb-4">
              <label class="form-label text-gold small text-uppercase fw-bold mb-2">3. Sumber Berkas GOOD JSON:</label>
              
              <ul class="nav nav-tabs border-secondary mb-3" id="inputTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active text-light bg-transparent border-0 border-bottom border-warning pb-2" id="file-input-tab" data-bs-toggle="tab" data-bs-target="#tab-file-input" type="button" role="tab">
                    <i class="bi bi-file-earmark-arrow-up me-1 text-gold"></i>Unggah Berkas File (.json)
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link text-light bg-transparent border-0 pb-2" id="paste-input-tab" data-bs-toggle="tab" data-bs-target="#tab-paste-input" type="button" role="tab">
                    <i class="bi bi-clipboard-data me-1 text-gold"></i>Tempel (Paste) Kode JSON
                  </button>
                </li>
              </ul>

              <div class="tab-content" id="inputTabContent">
                {{-- Subtab File --}}
                <div class="tab-pane fade show active" id="tab-file-input" role="tabpanel">
                  <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <i class="bi bi-cloud-arrow-up text-gold fs-1 d-block mb-2"></i>
                    <h6 class="text-white mb-1">Klik untuk memilih file atau seret & lepas berkas ke sini</h6>
                    <p class="text-muted small mb-2">Mendukung file ekstensi <code>.json</code> format GOOD (Max 15MB)</p>
                    <div id="selectedFileName" class="badge bg-warning text-dark px-3 py-2 d-none">
                      <i class="bi bi-file-earmark-code me-1"></i><span></span>
                    </div>
                    <input type="file" name="good_file" id="fileInput" class="d-none" accept=".json,application/json">
                  </div>
                </div>

                {{-- Subtab Paste --}}
                <div class="tab-pane fade" id="tab-paste-input" role="tabpanel">
                  <textarea name="good_json_raw" id="goodJsonRaw" rows="8" class="form-control" style="background: #0d111e; border-color: rgba(228, 196, 133, 0.3); color: #38bdf8; font-family: monospace; font-size: 0.85rem;" placeholder='Contoh: { "format": "GOOD", "version": 2, "characters": [...], "weapons": [...], "artifacts": [...] }'></textarea>
                  <div class="small text-muted mt-1">Salin isi berkas JSON dan tempelkan di kotak teks di atas.</div>
                </div>
              </div>
            </div>

            {{-- Submit Button --}}
            <div class="d-flex justify-content-end pt-3 border-top border-secondary">
              <button type="submit" class="btn btn-warning fw-bold px-4 py-2 d-inline-flex align-items-center gap-2 text-dark shadow" id="btnSubmitImport" onclick="return confirmImportSubmit()">
                <i class="bi bi-check2-circle fs-5"></i>
                <span>Mulai Proses Impor GOOD</span>
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>

  @endif

</div>

{{-- Modal Preview & Salin JSON --}}
<div class="modal fade" id="modalPreviewExport" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content good-card">
      <div class="modal-header border-secondary">
        <h5 class="modal-title font-display text-gold">
          <i class="bi bi-code-square me-2"></i>Preview Format GOOD JSON
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="small text-muted" id="previewSummaryText">Memuat data...</span>
          <button type="button" class="btn btn-sm btn-outline-warning" id="btnCopyJson">
            <i class="bi bi-clipboard me-1"></i><span id="copyBtnText">Salin ke Clipboard</span>
          </button>
        </div>
        <div class="json-preview-box" id="previewJsonCode">Memproses ekspor inventori...</div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-warning btn-sm fw-bold text-dark" onclick="document.getElementById('exportForm').submit()">
          <i class="bi bi-download me-1"></i>Unduh File .json
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // Mode selection styling
  function selectImportMode(mode) {
    document.getElementById('mode' + (mode === 'merge' ? 'Merge' : 'Replace')).checked = true;
    document.getElementById('cardModeMerge').classList.toggle('selected', mode === 'merge');
    document.getElementById('cardModeReplace').classList.toggle('selected', mode === 'replace');
  }

  // Confirm submit jika mode replace
  function confirmImportSubmit() {
    const isReplace = document.getElementById('modeReplace').checked;
    if (isReplace) {
      return confirm("PERINGATAN: Mode Replace akan MENGHAPUS seluruh karakter, senjata, dan artefak lama akun ini dan menggantinya dengan isi berkas GOOD. Apakah kamu yakin ingin melanjutkan?");
    }
    return true;
  }

  // File dropzone behavior
  const fileInput = document.getElementById('fileInput');
  const dropZone  = document.getElementById('dropZone');
  const fileBadge = document.getElementById('selectedFileName');

  if (fileInput && dropZone) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        fileBadge.querySelector('span').textContent = this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(1) + ' KB)';
        fileBadge.classList.remove('d-none');
      }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
      }, false);
    });

    dropZone.addEventListener('drop', (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files && files[0]) {
        fileInput.files = files;
        fileBadge.querySelector('span').textContent = files[0].name + ' (' + (files[0].size / 1024).toFixed(1) + ' KB)';
        fileBadge.classList.remove('d-none');
      }
    });
  }

  // AJAX Preview Export JSON
  const btnPreview = document.getElementById('btnPreviewExport');
  if (btnPreview) {
    btnPreview.addEventListener('click', function() {
      const modalEl = new bootstrap.Modal(document.getElementById('modalPreviewExport'));
      modalEl.show();

      const form = document.getElementById('exportForm');
      const formData = new FormData(form);

      document.getElementById('previewJsonCode').textContent = "Memuat data ekspor inventori...";
      document.getElementById('previewSummaryText').textContent = "Memproses...";

      fetch("{{ route('inventory.good.export-json') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          const pretty = JSON.stringify(res.data, null, 2);
          document.getElementById('previewJsonCode').textContent = pretty;
          document.getElementById('previewSummaryText').textContent = 
            `Siap diekspor: ${res.summary.characters} Karakter, ${res.summary.weapons} Senjata, ${res.summary.artifacts} Artefak, ${res.summary.materials} Material`;
        } else {
          document.getElementById('previewJsonCode').textContent = "Gagal memuat: " + (res.message || 'Terjadi kesalahan');
        }
      })
      .catch(err => {
        document.getElementById('previewJsonCode').textContent = "Gagal memuat JSON: " + err.message;
      });
    });
  }

  // Copy to clipboard
  const btnCopy = document.getElementById('btnCopyJson');
  if (btnCopy) {
    btnCopy.addEventListener('click', function() {
      const code = document.getElementById('previewJsonCode').textContent;
      navigator.clipboard.writeText(code).then(() => {
        const textSpan = document.getElementById('copyBtnText');
        textSpan.textContent = "Berhasil Disalin!";
        setTimeout(() => {
          textSpan.textContent = "Salin ke Clipboard";
        }, 2000);
      });
    });
  }
</script>
@endsection
