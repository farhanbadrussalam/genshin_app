@extends('layout.main')

@section('content')
@php $title = 'Export & Import Data (GOOD & Gemini AI Notebook)'; @endphp
@include('layout.header')
<style>
  .good-card {
    background: rgba(18, 24, 38, 0.95);
    border: 1px solid rgba(228, 196, 133, 0.2);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
  }
  .good-card:hover {
    border-color: rgba(228, 196, 133, 0.4);
  }
  .good-nav-tab {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
    border-radius: 8px;
    padding: 0.65rem 1.25rem;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.25s ease;
  }
  .good-nav-tab:hover {
    background: rgba(228, 196, 133, 0.15);
    color: var(--genshin-gold);
    border-color: rgba(228, 196, 133, 0.3);
  }
  .good-nav-tab.active {
    background: linear-gradient(135deg, #e4c485 0%, #b89758 100%) !important;
    color: #0b0e17 !important;
    border-color: #e4c485 !important;
    box-shadow: 0 4px 15px rgba(228, 196, 133, 0.35);
  }
  .good-nav-tab.tab-gemini.active {
    background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%) !important;
    color: #0b0e17 !important;
    border-color: #38bdf8 !important;
    box-shadow: 0 4px 15px rgba(56, 189, 248, 0.35);
  }
  .format-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 6px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
  .json-preview-box {
    background: #090d16;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1rem;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 0.8rem;
    color: #38bdf8;
    max-height: 420px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-word;
  }
  .drop-zone {
    border: 2px dashed rgba(228, 196, 133, 0.35);
    border-radius: 10px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    background: rgba(255, 255, 255, 0.02);
    transition: all 0.3s ease;
    cursor: pointer;
  }
  .drop-zone:hover, .drop-zone.dragover {
    border-color: var(--genshin-gold);
    background: rgba(228, 196, 133, 0.08);
  }
  .mode-option-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.25s ease;
  }
  .mode-option-card:hover {
    border-color: rgba(228, 196, 133, 0.3);
  }
  .mode-option-card.selected {
    border-color: var(--genshin-gold);
    background: rgba(228, 196, 133, 0.12);
  }
</style>

<div class="container-fluid py-4 px-lg-5">

  {{-- Alert Hasil Impor --}}
  @if(session('good_import_result'))
    @php $res = session('good_import_result'); @endphp
    <div class="good-card p-4 mb-4 border-success animate-fade-in-up" style="border-left: 5px solid #10b981;">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <h5 class="text-success mb-1 fw-bold">
            <i class="bi bi-file-earmark-check-fill text-success me-2"></i>Laporan Hasil Impor Berkas GOOD
          </h5>
          <span class="small text-muted">Mode Impor: <strong>{{ strtoupper($res['mode'] ?? 'MERGE') }}</strong></span>
        </div>
        <span class="badge bg-success-subtle text-success border border-success px-3 py-2">Berhasil Diproses</span>
      </div>

      <div class="row g-3 text-center mb-3">
        <div class="col-6 col-md-3">
          <div class="p-2 rounded" style="background: rgba(255,255,255,0.03);">
            <div class="text-gold fw-bold fs-4">{{ $res['stats']['characters']['imported'] + $res['stats']['characters']['updated'] }}</div>
            <div class="small text-muted">Karakter ({{ $res['stats']['characters']['imported'] }} Baru, {{ $res['stats']['characters']['updated'] }} Update)</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-2 rounded" style="background: rgba(255,255,255,0.03);">
            <div class="text-gold fw-bold fs-4">{{ $res['stats']['weapons']['imported'] + $res['stats']['weapons']['updated'] }}</div>
            <div class="small text-muted">Senjata ({{ $res['stats']['weapons']['imported'] }} Baru, {{ $res['stats']['weapons']['updated'] }} Update)</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-2 rounded" style="background: rgba(255,255,255,0.03);">
            <div class="text-gold fw-bold fs-4">{{ $res['stats']['artifacts']['imported'] + $res['stats']['artifacts']['updated'] }}</div>
            <div class="small text-muted">Artefak ({{ $res['stats']['artifacts']['imported'] }} Baru, {{ $res['stats']['artifacts']['updated'] }} Update)</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-2 rounded" style="background: rgba(255,255,255,0.03);">
            <div class="text-gold fw-bold fs-4">{{ $res['stats']['materials']['imported'] + $res['stats']['materials']['updated'] }}</div>
            <div class="small text-muted">Material Diperbarui</div>
          </div>
        </div>
      </div>

      @if(!empty($res['warnings']))
        <div class="alert alert-warning py-2 px-3 small mb-0" style="background: rgba(234, 179, 8, 0.15); border-color: rgba(234, 179, 8, 0.3);">
          <strong>Catatan / Peringatan:</strong>
          <ul class="mb-0 ps-3">
            @foreach($res['warnings'] as $warn)
              <li>{{ $warn }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  @endif

  {{-- Header Halaman --}}
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <h2 class="font-display text-gold mb-0">
          <i class="bi bi-arrow-left-right me-2"></i>Ekspor & Impor Data Inventori
        </h2>
        <span class="badge bg-dark border border-warning text-gold">GOOD v2</span>
        <span class="badge bg-info text-dark fw-bold">Gemini AI Ready</span>
      </div>
      <p class="text-muted small mb-0">
        Pusat pertukaran data: Dukungan format standar komunitas <strong>GOOD</strong> (Genshin Optimizer, Seelie, Akasha) dan format super detail untuk analisis <strong>Google Gemini & Python Notebook</strong>.
      </p>
    </div>

    {{-- Pemilih Akun Game --}}
    @if($accounts->isNotEmpty())
      <div class="d-flex align-items-center gap-2">
        <label for="account_selector" class="small text-gold fw-medium mb-0 text-nowrap">
          <i class="bi bi-person-badge me-1"></i>Pilih Akun:
        </label>
        <form method="GET" action="{{ route('inventory.good.index') }}" id="accountSelectForm">
          <select name="account_id" id="account_selector" class="form-select form-select-sm" style="background: #121826; border-color: rgba(228,196,133,0.3); color: #fff; min-width: 220px;" onchange="document.getElementById('accountSelectForm').submit()">
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}" {{ $activeAccount && $activeAccount->id === $acc->id ? 'selected' : '' }}>
                {{ $acc->nickname ?: 'Akun #' . $acc->id }} (UID: {{ $acc->uid ?: '-' }})
              </option>
            @endforeach
          </select>
        </form>
      </div>
    @endif
  </div>

  @if(!$activeAccount)
    <div class="empty-state text-center py-5 good-card">
      <i class="bi bi-exclamation-triangle-fill text-warning display-4 mb-3 d-block"></i>
      <h4 class="text-white mb-2">Belum Ada Akun Game yang Terdaftar</h4>
      <p class="text-muted">Tambahkan akun game Genshin Impact terlebih dahulu untuk menggunakan fitur Export / Import.</p>
      <a href="{{ route('game-accounts.index') }}" class="btn-genshin btn-genshin-sm">
        <i class="bi bi-plus-lg me-1"></i>Kelola Akun Game
      </a>
    </div>
  @else

    {{-- Tabs Export / Import --}}
    <ul class="nav nav-pills gap-2 mb-4" id="goodTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link good-nav-tab active" id="export-tab" data-bs-toggle="pill" data-bs-target="#export-content" type="button" role="tab">
          <i class="bi bi-download me-2"></i>Ekspor Format GOOD
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link good-nav-tab tab-gemini" id="gemini-tab" data-bs-toggle="pill" data-bs-target="#gemini-content" type="button" role="tab">
          <i class="bi bi-stars me-2"></i>Ekspor untuk Gemini AI & Notebook
          <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Super Detail</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link good-nav-tab" id="import-tab" data-bs-toggle="pill" data-bs-target="#import-content" type="button" role="tab">
          <i class="bi bi-upload me-2"></i>Impor dari Berkas GOOD
        </button>
      </li>
    </ul>

    <div class="tab-content" id="goodTabContent">

      {{-- ==================== TAB 1: EXPORT GOOD ==================== --}}
      <div class="tab-pane fade show active" id="export-content" role="tabpanel">
        <div class="row g-4">
          
          <div class="col-12 col-lg-7">
            <div class="good-card p-4 h-100">
              <h4 class="font-display text-gold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-down-fill text-gold"></i>Ekspor Data Format GOOD
              </h4>
              <p class="text-muted small mb-4">
                Pilih komponen yang ingin diekspor. Berkas JSON yang dihasilkan kompatibel 100% dan bisa langsung diunggah ke <strong>Genshin Optimizer</strong> atau kalkulator komunitas lainnya.
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

          {{-- Kolom Info Kompatibilitas GOOD --}}
          <div class="col-12 col-lg-5">
            <div class="good-card p-4 h-100">
              <h5 class="font-display text-gold mb-3">
                <i class="bi bi-patch-check-fill text-gold me-2"></i>Kompatibilitas Format GOOD
              </h5>
              
              <ul class="list-unstyled d-flex flex-column gap-3 mb-4 small text-secondary">
                <li class="d-flex align-items-start gap-2">
                  <i class="bi bi-check-circle-fill text-success mt-1"></i>
                  <div>
                    <strong class="text-white">Genshin Optimizer</strong>
                    <p class="text-muted mb-0">Langsung impor berkas ini ke Genshin Optimizer via menu <em>Settings → Database → Import GOOD</em>.</p>
                  </div>
                </li>
                <li class="d-flex align-items-start gap-2">
                  <i class="bi bi-check-circle-fill text-success mt-1"></i>
                  <div>
                    <strong class="text-white">Seelie.me & Akasha.cv</strong>
                    <p class="text-muted mb-0">Format penamaan PascalCase standar menjamin kecocokan identifikasi artefak & karakter.</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>

        </div>
      </div>

      {{-- ==================== TAB 2: GEMINI AI & NOTEBOOK EXPORT ==================== --}}
      <div class="tab-pane fade" id="gemini-content" role="tabpanel">
        <div class="row g-4">

          {{-- Kolom Kiri: Form Ekspor Gemini --}}
          <div class="col-12 col-lg-7">
            <div class="good-card p-4 h-100">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <h4 class="font-display text-info mb-0 d-flex align-items-center gap-2">
                  <i class="bi bi-stars text-info"></i>Ekspor Khusus Gemini AI & Data Notebook
                </h4>
                <span class="badge bg-info-subtle text-info border border-info px-2 py-1">Super Komprehensif</span>
              </div>
              
              <p class="text-muted small mb-4">
                Format data tingkat lanjut yang didesain khusus untuk dianalisis oleh <strong>Google Gemini (Gemini Advanced / AI Studio)</strong>, <strong>Google NotebookLM</strong>, atau dieksplorasi dengan <strong>Python (Pandas)</strong> di Google Colab / Jupyter Notebook. Menyertakan detail build terpasang, 5 slot artefak, perhitungan Crit Value (CV), set bonus aktif, dan senjata cadangan.
              </p>

              <form action="{{ route('inventory.good.gemini-export-json') }}" method="POST" id="geminiForm">
                @csrf
                <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

                {{-- Checklist Komponen --}}
                <div class="mb-4">
                  <label class="form-label text-info small text-uppercase fw-bold mb-3">
                    <i class="bi bi-sliders me-1"></i>Pilih Komponen untuk Analisis:
                  </label>
                  
                  <div class="row g-3">
                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(56, 189, 248, 0.2);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_characters" id="gemChar" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="gemChar">
                          <i class="bi bi-people-fill text-info me-2"></i>Roster & Build Karakter
                          <div class="small text-muted ps-4">Stats, Senjata terpasang, 5 Slot Artefak, Set Bonus, Total CV ({{ $accountStats['characters'] }} karakter)</div>
                        </label>
                      </div>
                    </div>

                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(56, 189, 248, 0.2);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_weapons" id="gemWeap" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="gemWeap">
                          <i class="bi bi-shield-shaded text-info me-2"></i>Seluruh Koleksi Senjata
                          <div class="small text-muted ps-4">Base ATK, Substat, Pasif, Status Pemakai / Tas ({{ $accountStats['weapons'] }} senjata)</div>
                        </label>
                      </div>
                    </div>

                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(56, 189, 248, 0.2);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_artifacts" id="gemArt" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="gemArt">
                          <i class="bi bi-gem text-info me-2"></i>Seluruh Koleksi Artefak & CV
                          <div class="small text-muted ps-4">Kalkulasi Crit Value (CV), Quality Score, Status Pemakai / Tas ({{ $accountStats['artifacts'] }} artefak)</div>
                        </label>
                      </div>
                    </div>

                    <div class="col-sm-6">
                      <div class="form-check p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(56, 189, 248, 0.2);">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="include_materials" id="gemMat" value="1" checked>
                        <label class="form-check-label text-white fw-medium" for="gemMat">
                          <i class="bi bi-backpack text-info me-2"></i>Material & Consumables
                          <div class="small text-muted ps-4">Bahan ascension, buku talent, mora ({{ $accountStats['materials'] }} item)</div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-secondary">
                  <button type="submit" class="btn btn-info fw-bold px-4 py-2 d-inline-flex align-items-center gap-2 text-dark shadow" id="btnDownloadGeminiJson">
                    <i class="bi bi-filetype-json fs-5"></i>
                    <span>Unduh JSON Gemini (.json)</span>
                  </button>

                  <button type="button" class="btn btn-outline-info fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow" id="btnDownloadGeminiMd">
                    <i class="bi bi-file-earmark-markdown fs-5"></i>
                    <span>Unduh Prompt Markdown (.md)</span>
                  </button>

                  <button type="button" class="btn btn-dark border-secondary px-3 py-2 text-white" id="btnPreviewGemini">
                    <i class="bi bi-eye text-warning me-1"></i>Preview / Salin Prompt
                  </button>
                </div>
              </form>
            </div>
          </div>

          {{-- Kolom Kanan: Panduan Penggunaan & Python Starter Code --}}
          <div class="col-12 col-lg-5">
            <div class="good-card p-4 h-100">
              <h5 class="font-display text-info mb-3">
                <i class="bi bi-lightning-charge-fill text-info me-2"></i>Cara Penggunaan dengan Gemini & Notebook
              </h5>

              <div class="d-flex flex-column gap-3 mb-4 small text-secondary">
                <div class="p-3 rounded" style="background: rgba(56, 189, 248, 0.06); border-left: 3px solid #38bdf8;">
                  <strong class="text-white d-block mb-1">
                    <i class="bi bi-chat-left-dots text-info me-1"></i>1. Google Gemini (Web / AI Studio)
                  </strong>
                  <span>Tinggal upload berkas <code>.json</code> atau <code>.md</code> hasil unduhan langsung ke percakapan Gemini. Gemini akan membaca seluruh build karakter dan memberikan saran tim Abyss, rotasi, serta prioritas farming.</span>
                </div>

                <div class="p-3 rounded" style="background: rgba(168, 85, 247, 0.06); border-left: 3px solid #a855f7;">
                  <strong class="text-white d-block mb-1">
                    <i class="bi bi-journal-bookmark text-primary me-1"></i>2. Google NotebookLM
                  </strong>
                  <span>Tambahkan berkas <code>.md</code> sebagai <em>Source</em> di NotebookLM untuk asisten AI pribadi yang menguasai seluruh inventori akun kamu.</span>
                </div>

                <div class="p-3 rounded" style="background: rgba(234, 179, 8, 0.06); border-left: 3px solid #eab308;">
                  <strong class="text-white d-block mb-1">
                    <i class="bi bi-code-square text-warning me-1"></i>3. Google Colab / Jupyter Notebook (Python)
                  </strong>
                  <span>Gunakan cuplikan kode di bawah untuk memuat file JSON ke DataFrame Pandas:</span>
                </div>
              </div>

              {{-- Snippet Python --}}
              <div class="position-relative">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded-top" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-bottom: none;">
                  <span class="small text-info fw-bold font-monospace">Python Pandas Snippet</span>
                  <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" style="font-size: 0.75rem;" id="btnCopyPythonSnippet">
                    <i class="bi bi-clipboard me-1"></i>Salin Kode
                  </button>
                </div>
                <pre class="m-0 p-3 rounded-bottom font-monospace small" id="pythonSnippetCode" style="background: #060913; border: 1px solid rgba(255,255,255,0.1); color: #7dd3fc; max-height: 200px; overflow-y: auto;">import json
import pandas as pd

# Load berkas JSON hasil ekspor
with open('Genshin_Gemini_Notebook.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

# 1. Analisis Karakter & Build Terpasang
df_chars = pd.json_normalize(data['characters'])
print("Total Karakter:", len(df_chars))
display(df_chars[['name', 'element', 'investment.level', 'investment.constellation', 'equipped_weapon.name', 'total_artifact_cv']].head())

# 2. Analisis Top 10 Artefak Crit Value (CV) Tertinggi
df_arts = pd.DataFrame(data['account_overview']['top_crit_artifacts'])
display(df_arts)</pre>
              </div>

            </div>
          </div>

        </div>
      </div>

      {{-- ==================== TAB 3: IMPORT ==================== --}}
      <div class="tab-pane fade" id="import-content" role="tabpanel">
        <div class="good-card p-4">
          <h4 class="font-display text-gold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-cloud-arrow-up-fill text-gold"></i>Impor Berkas GOOD ke Akun: <span class="text-white">{{ $activeAccount->nickname }}</span>
          </h4>
          <p class="text-muted small mb-4">
            Unggah berkas JSON hasil scanner (seperti <em>Inventory Kamera, Akasha Scanner, Mona Uranus, Genshin Optimizer</em>). Seluruh karakter, senjata, artefak, dan material akan disinkronkan secara otomatis.
          </p>

          <form action="{{ route('inventory.good.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
            @csrf
            <input type="hidden" name="game_account_id" value="{{ $activeAccount->id }}">

            {{-- 1. Pilihan Mode Impor --}}
            <div class="mb-4">
              <label class="form-label text-gold small text-uppercase fw-bold mb-2">1. Mode Penggabungan Data:</label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mode-option-card selected" id="cardModeMerge" onclick="selectImportMode('merge')">
                    <div class="d-flex align-items-start gap-3">
                      <input class="form-check-input mt-1" type="radio" name="mode" id="modeMerge" value="merge" checked>
                      <div>
                        <div class="fw-bold text-white mb-1">
                          <i class="bi bi-bezier2 text-gold me-1"></i>Gabungkan Data (Merge & Update)
                        </div>
                        <div class="small text-muted">
                          Rekomendasi aman. Menambahkan item baru dan memperbarui item yang sudah ada tanpa menghapus data inventori lainnya.
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
              
              <ul class="nav nav-tabs border-secondary mb-3" id="importSourceTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active text-white" id="tab-file-btn" data-bs-toggle="tab" data-bs-target="#tab-file-upload" type="button" role="tab">
                    <i class="bi bi-file-earmark-arrow-up me-1 text-gold"></i>Unggah File JSON
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link text-white" id="tab-paste-btn" data-bs-toggle="tab" data-bs-target="#tab-paste-input" type="button" role="tab">
                    <i class="bi bi-clipboard-data me-1 text-gold"></i>Tempel Teks (Paste JSON)
                  </button>
                </li>
              </ul>

              <div class="tab-content" id="importSourceTabContent">
                {{-- Subtab File --}}
                <div class="tab-pane fade show active" id="tab-file-upload" role="tabpanel">
                  <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <i class="bi bi-cloud-arrow-up text-gold display-5 mb-2 d-block"></i>
                    <h6 class="text-white mb-1">Seret & Lepaskan Berkas JSON ke Sini</h6>
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

{{-- Modal Preview & Salin Format GOOD JSON --}}
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

{{-- Modal Preview Gemini AI & Notebook --}}
<div class="modal fade" id="modalPreviewGemini" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content good-card" style="border-color: rgba(56, 189, 248, 0.4);">
      <div class="modal-header border-secondary">
        <div class="d-flex align-items-center gap-2">
          <h5 class="modal-title font-display text-info mb-0">
            <i class="bi bi-stars me-2"></i>Preview Data Gemini AI & Notebook
          </h5>
          <span class="badge bg-info text-dark" id="geminiPreviewFormatBadge">JSON</span>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-info active" id="btnTogglePreviewJson">Format JSON Terstruktur</button>
            <button type="button" class="btn btn-outline-info" id="btnTogglePreviewMd">Format Dokumen Markdown (.md)</button>
          </div>
          <button type="button" class="btn btn-sm btn-info text-dark fw-bold" id="btnCopyGeminiPreview">
            <i class="bi bi-clipboard me-1"></i><span id="geminiCopyBtnText">Salin Seluruh Konten</span>
          </button>
        </div>
        <div class="json-preview-box" id="geminiPreviewCode" style="max-height: 480px; color: #a5f3fc;">Memuat data preview...</div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-info btn-sm fw-bold text-dark" onclick="document.getElementById('geminiForm').submit()">
          <i class="bi bi-download me-1"></i>Unduh Berkas .json
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

  // Validasi Export GOOD Form
  const exportForm = document.getElementById('exportForm');
  if (exportForm) {
    exportForm.addEventListener('submit', function(e) {
      const anyChecked = exportForm.querySelectorAll('input[type="checkbox"]:checked').length > 0;
      if (!anyChecked) {
        e.preventDefault();
        alert('Silakan centang minimal satu komponen (Karakter, Senjata, Artefak, atau Material) untuk diekspor.');
      }
    });
  }

  // Validasi Import Form
  const importForm = document.getElementById('importForm');
  if (importForm) {
    importForm.addEventListener('submit', function(e) {
      const anyChecked = importForm.querySelectorAll('input[type="checkbox"]:checked').length > 0;
      if (!anyChecked) {
        e.preventDefault();
        alert('Silakan centang minimal satu komponen yang ingin diimpor.');
      }
    });
  }

  // AJAX Preview Export GOOD JSON
  const btnPreview = document.getElementById('btnPreviewExport');
  if (btnPreview) {
    btnPreview.addEventListener('click', function() {
      const form = document.getElementById('exportForm');
      const anyChecked = form ? form.querySelectorAll('input[type="checkbox"]:checked').length > 0 : false;
      if (!anyChecked) {
        alert('Silakan centang minimal satu komponen untuk melihat preview.');
        return;
      }

      const modalEl = new bootstrap.Modal(document.getElementById('modalPreviewExport'));
      modalEl.show();

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

  // Copy to clipboard format GOOD
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

  // ================= GEMINI AI & NOTEBOOK HANDLERS =================
  const geminiForm = document.getElementById('geminiForm');
  const btnGeminiJson = document.getElementById('btnDownloadGeminiJson');
  const btnGeminiMd = document.getElementById('btnDownloadGeminiMd');
  const btnPreviewGemini = document.getElementById('btnPreviewGemini');
  let currentGeminiPreviewFormat = 'json';

  // Validasi & Action Handler Form Gemini
  if (geminiForm) {
    geminiForm.addEventListener('submit', function(e) {
      const anyChecked = geminiForm.querySelectorAll('input[type="checkbox"]:checked').length > 0;
      if (!anyChecked) {
        e.preventDefault();
        alert('Silakan centang minimal satu komponen untuk diekspor ke Gemini.');
      }
    });
  }

  // Download Markdown Button
  if (btnGeminiMd) {
    btnGeminiMd.addEventListener('click', function() {
      const anyChecked = geminiForm.querySelectorAll('input[type="checkbox"]:checked').length > 0;
      if (!anyChecked) {
        alert('Silakan centang minimal satu komponen untuk diekspor ke Gemini.');
        return;
      }
      geminiForm.action = "{{ route('inventory.good.gemini-export-markdown') }}";
      geminiForm.submit();
      // Kembalikan action default ke JSON
      geminiForm.action = "{{ route('inventory.good.gemini-export-json') }}";
    });
  }

  // Preview Gemini Button
  if (btnPreviewGemini) {
    btnPreviewGemini.addEventListener('click', function() {
      const anyChecked = geminiForm.querySelectorAll('input[type="checkbox"]:checked').length > 0;
      if (!anyChecked) {
        alert('Silakan centang minimal satu komponen untuk melihat preview.');
        return;
      }
      loadGeminiPreview('json');
      const modalEl = new bootstrap.Modal(document.getElementById('modalPreviewGemini'));
      modalEl.show();
    });
  }

  function loadGeminiPreview(format) {
    currentGeminiPreviewFormat = format;
    const badge = document.getElementById('geminiPreviewFormatBadge');
    badge.textContent = format.toUpperCase();
    badge.className = format === 'json' ? 'badge bg-info text-dark' : 'badge bg-warning text-dark';

    document.getElementById('btnTogglePreviewJson').classList.toggle('active', format === 'json');
    document.getElementById('btnTogglePreviewMd').classList.toggle('active', format === 'markdown');

    const previewCodeEl = document.getElementById('geminiPreviewCode');
    previewCodeEl.textContent = "Memuat data preview " + format.toUpperCase() + "...";

    const formData = new FormData(geminiForm);
    formData.append('format', format);

    fetch("{{ route('inventory.good.gemini-preview') }}", {
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
        if (res.format === 'markdown') {
          previewCodeEl.textContent = res.data;
        } else {
          previewCodeEl.textContent = JSON.stringify(res.data, null, 2);
        }
      } else {
        previewCodeEl.textContent = "Gagal memuat: " + (res.message || 'Terjadi kesalahan');
      }
    })
    .catch(err => {
      previewCodeEl.textContent = "Gagal memuat: " + err.message;
    });
  }

  document.getElementById('btnTogglePreviewJson')?.addEventListener('click', () => loadGeminiPreview('json'));
  document.getElementById('btnTogglePreviewMd')?.addEventListener('click', () => loadGeminiPreview('markdown'));

  // Copy Gemini Preview Text
  document.getElementById('btnCopyGeminiPreview')?.addEventListener('click', function() {
    const text = document.getElementById('geminiPreviewCode').textContent;
    navigator.clipboard.writeText(text).then(() => {
      const span = document.getElementById('geminiCopyBtnText');
      span.textContent = "Berhasil Disalin!";
      setTimeout(() => {
        span.textContent = "Salin Seluruh Konten";
      }, 2000);
    });
  });

  // Copy Python Snippet
  document.getElementById('btnCopyPythonSnippet')?.addEventListener('click', function() {
    const code = document.getElementById('pythonSnippetCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
      this.innerHTML = '<i class="bi bi-check2 me-1"></i>Tersalin!';
      setTimeout(() => {
        this.innerHTML = '<i class="bi bi-clipboard me-1"></i>Salin Kode';
      }, 2000);
    });
  });
</script>
@endsection
