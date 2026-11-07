@extends('layout.main')

@section('content')
@php $title = 'Pengaturan AI & Kuota Limit'; @endphp
@include('layout.header')

<div class="page-container ai-settings-page" style="padding-top: 1.5rem; padding-bottom: 5rem;">

  {{-- Page Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <h1 class="font-display mb-0" style="font-size: 1.5rem; letter-spacing: 0.08em; color: #fbbf24 !important;">
          <i class="bi bi-cpu me-2 text-warning"></i>Pengaturan AI Model & Pemantauan Kuota
        </h1>
        <span class="badge {{ $stats['status_health'] === 'safe' ? 'bg-success' : ($stats['status_health'] === 'warning' ? 'bg-warning text-dark' : 'bg-danger') }} px-2 py-1" id="badgeGlobalHealth">
          <i class="bi bi-shield-check me-1"></i>{{ $stats['status_health'] === 'safe' ? 'Status: Kuota Aman' : ($stats['status_health'] === 'warning' ? 'Status: Waspada' : 'Status: Mendekati Limit') }}
        </span>
      </div>
      <p style="color: #94a3b8; font-size: 0.88rem; margin-bottom: 0;">
        Kelola model Gemini AI yang aktif, pantau limit kuota harian & per-menit secara real-time, dan tentukan strategi auto-fallback.
      </p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-sm btn-outline-warning" id="btnRefreshQuota" style="border-color: rgba(251, 191, 36, 0.4); color: #fbbf24;">
        <i class="bi bi-arrow-clockwise me-1"></i>Refresh Kuota
      </button>
      <a href="{{ route('party.index') }}" class="btn btn-sm btn-outline-secondary" style="color: #cbd5e1; border-color: rgba(255, 255, 255, 0.2);">
        <i class="bi bi-shield-shaded me-1"></i>Kembali ke Party Builder
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success border-success bg-dark text-success alert-dismissible fade show mb-4 animate-fade-in-up" role="alert" style="border-color: #22c55e !important; background-color: #0c1a14 !important; color: #4ade80 !important;">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- 1. REAL-TIME RATE LIMIT & QUOTA DASHBOARD --}}
  <div class="card genshin-panel-card mb-4 border-gold-glow animate-fade-in-up">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom-genshin py-3">
      <div class="d-flex align-items-center gap-2">
        <div class="status-orb" style="width: 12px; height: 12px; border-radius: 50%; background: {{ $stats['status_health'] === 'safe' ? '#22c55e' : ($stats['status_health'] === 'warning' ? '#eab308' : '#ef4444') }}; box-shadow: 0 0 10px {{ $stats['status_health'] === 'safe' ? '#22c55e' : '#ef4444' }};"></div>
        <h5 class="font-display mb-0" style="font-size: 1.1rem; color: #fbbf24;">
          <i class="bi bi-speedometer2 me-1"></i>Live Quota & Rate Limit Tracker
        </h5>
        <span class="badge bg-dark border border-secondary text-warning small ms-1" style="background-color: #0a0d17 !important; border-color: rgba(251, 191, 36, 0.3) !important;">
          Model Aktif: <strong id="lblActiveModelMeta" class="text-white">{{ $stats['active_model_meta']['name'] }}</strong>
        </span>
      </div>

      <div class="small d-flex align-items-center gap-2" style="font-size: 0.8rem; color: #94a3b8;">
        <i class="bi bi-clock-history text-warning"></i>
        <span>Reset Kuota Harian Google:</span>
        <strong class="text-white" id="lblResetCountdown">{{ $stats['next_reset_in'] }} lagi</strong>
        <span style="color: #64748b;">(00:00 UTC / 07:00 WIB)</span>
      </div>
    </div>

    <div class="card-body p-3 p-md-4">
      <div class="row g-3">
        {{-- Metric 1: Requests Per Day (RPD) --}}
        <div class="col-12 col-md-4">
          <div class="stat-metric-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="stat-metric-title">PERMINTAAN HARI INI (RPD)</span>
                <h4 class="font-display mb-0 mt-1" id="txtRpdUsed" style="color: #fbbf24;">
                  {{ number_format($stats['rpd']['used'], 0, ',', '.') }}
                  <span class="fw-normal" style="font-size: 0.85rem; color: #94a3b8;">/ {{ number_format($stats['rpd']['limit'], 0, ',', '.') }}</span>
                </h4>
              </div>
              <span class="badge bg-dark border border-secondary text-warning" id="badgeRpdPercent" style="background-color: #0a0d17 !important;">
                {{ $stats['rpd']['percent'] }}%
              </span>
            </div>

            <div class="progress mb-2" style="height: 7px; background: rgba(255, 255, 255, 0.1);">
              <div class="progress-bar {{ $stats['rpd']['percent'] > 85 ? 'bg-danger' : ($stats['rpd']['percent'] > 60 ? 'bg-warning' : 'bg-success') }}" 
                   id="barRpd" role="progressbar" style="width: {{ min(100, $stats['rpd']['percent']) }}%;"></div>
            </div>
            <div class="d-flex justify-content-between small" style="font-size: 0.74rem; color: #94a3b8;">
              <span>Sisa kuota: <strong class="text-white" id="txtRpdRemaining">{{ max(0, $stats['rpd']['limit'] - $stats['rpd']['used']) }} request</strong></span>
              <span style="color: #64748b;">Total semua model: {{ $stats['rpd']['all_models_used'] }}</span>
            </div>
          </div>
        </div>

        {{-- Metric 2: Requests Per Minute (RPM) --}}
        <div class="col-12 col-md-4">
          <div class="stat-metric-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="stat-metric-title">BEBAN MENIT INI (RPM)</span>
                <h4 class="font-display mb-0 mt-1" id="txtRpmUsed" style="color: #38bdf8;">
                  {{ $stats['rpm']['used'] }}
                  <span class="fw-normal" style="font-size: 0.85rem; color: #94a3b8;">/ {{ $stats['rpm']['limit'] }} RPM</span>
                </h4>
              </div>
              <span class="badge bg-dark border border-secondary text-info" id="badgeRpmPercent" style="background-color: #0a0d17 !important;">
                {{ $stats['rpm']['percent'] }}%
              </span>
            </div>

            <div class="progress mb-2" style="height: 7px; background: rgba(255, 255, 255, 0.1);">
              <div class="progress-bar {{ $stats['rpm']['percent'] > 80 ? 'bg-danger' : ($stats['rpm']['percent'] > 50 ? 'bg-warning' : 'bg-info') }}" 
                   id="barRpm" role="progressbar" style="width: {{ min(100, $stats['rpm']['percent']) }}%;"></div>
            </div>
            <div class="d-flex justify-content-between small" style="font-size: 0.74rem; color: #94a3b8;">
              <span>Batas kecepatan: <strong class="text-white">{{ $stats['rpm']['limit'] }} req/menit</strong></span>
              <span id="txtRpmStatus">
                @if($stats['rpm']['used'] >= $stats['rpm']['limit'])
                  <span class="text-danger fw-bold"><i class="bi bi-pause-circle me-1"></i>Cooldown</span>
                @else
                  <span class="text-success"><i class="bi bi-lightning-charge me-1"></i>Optimal</span>
                @endif
              </span>
            </div>
          </div>
        </div>

        {{-- Metric 3: Token Usage Today --}}
        <div class="col-12 col-md-4">
          <div class="stat-metric-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="stat-metric-title">TOTAL TOKEN HARI INI</span>
                <h4 class="font-display mb-0 mt-1" id="txtTokensUsed" style="color: #4ade80;">
                  {{ number_format($stats['tokens']['used_today'], 0, ',', '.') }}
                  <span class="fw-normal" style="font-size: 0.8rem; color: #94a3b8;">Tokens</span>
                </h4>
              </div>
              <span class="badge bg-dark border border-secondary text-success" style="background-color: #0a0d17 !important; border-color: rgba(74, 222, 128, 0.3) !important;">
                <i class="bi bi-activity me-1"></i>Active
              </span>
            </div>

            <div class="progress mb-2" style="height: 7px; background: rgba(255, 255, 255, 0.1);">
              <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
            </div>
            <div class="d-flex justify-content-between small" style="font-size: 0.74rem; color: #94a3b8;">
              <span>Batas TPM: <strong class="text-white">{{ number_format($stats['tokens']['limit_per_min'], 0, ',', '.') }} tok/menit</strong></span>
              <span>Errors: <strong class="{{ $stats['error_count_today'] > 0 ? 'text-danger' : 'text-white' }}">{{ $stats['error_count_today'] }}</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 2. AI MODEL CONFIGURATION FORM --}}
  <form action="{{ route('settings.ai.update') }}" method="POST" id="formAiSettings">
    @csrf

    <div class="card genshin-panel-card mb-4 border-gold-glow animate-fade-in-up">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom-genshin py-3">
        <div>
          <h5 class="font-display mb-0" style="font-size: 1.15rem; color: #fbbf24;">
            <i class="bi bi-sliders me-2 text-warning"></i>Konfigurasi Model AI & Perilaku Eksekusi
          </h5>
          <div class="helper-text mt-1">
            Pengaturan ini langsung diterapkan ke seluruh fitur AI di aplikasi (Smart Team Builder, Theorycrafter Chat, Komparasi Benchmark, dan Vision Scanner).
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-outline-info" id="btnTestConnection" style="border-color: rgba(56, 189, 248, 0.4); color: #38bdf8;">
            <i class="bi bi-broadcast me-1"></i>Tes Ping Model
          </button>
          <button type="submit" class="btn btn-genshin btn-genshin-sm" id="btnSaveConfig">
            <i class="bi bi-check-circle-fill me-1"></i>Simpan Pengaturan
          </button>
        </div>
      </div>

      <div class="card-body p-3 p-md-4">
        {{-- Section A: Pilihan Model Utama (Interactive Cards) --}}
        <div class="mb-4">
          <label class="form-label small fw-bold mb-2" style="color: #fbbf24;">
            <i class="bi bi-stars me-1 text-warning"></i>1. Pilih Model AI Utama (Default untuk Seluruh Fitur)
          </label>
          <div class="row g-3">
            @foreach($models as $key => $meta)
              @php
                $isActive = ($config['active_model'] === $key);
                $isPro = str_contains($key, 'pro');
              @endphp
              <div class="col-12 col-md-6 col-lg-3">
                <label class="model-choice-card {{ $isActive ? 'selected' : '' }} p-3 d-block h-100" for="model_{{ $loop->index }}" style="cursor: pointer;">
                  <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div class="form-check m-0">
                      <input class="form-check-input radio-model-choice" type="radio" name="active_model" id="model_{{ $loop->index }}" value="{{ $key }}" {{ $isActive ? 'checked' : '' }}>
                      <span class="fw-bold text-white small ms-1" style="font-size: 0.9rem;">{{ $meta['name'] }}</span>
                    </div>
                    <span class="badge {{ $isPro ? 'bg-primary' : 'bg-warning text-dark' }}" style="font-size: 0.65rem;">
                      {{ $meta['badge'] }}
                    </span>
                  </div>

                  <p class="model-choice-desc mb-3" style="min-height: 48px;">
                    {{ $meta['description'] }}
                  </p>

                  <div class="model-meta-footer pt-2 d-flex justify-content-between small">
                    <span><i class="bi bi-calendar-check me-1 text-warning"></i>Limit: <strong class="text-white">{{ number_format($meta['rpd'], 0, ',', '.') }} RPD</strong></span>
                    <span><i class="bi bi-speedometer2 me-1 text-info"></i>Max: <strong class="text-white">{{ $meta['rpm'] }} RPM</strong></span>
                  </div>
                </label>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Section B: Fallback Strategy & Parameters --}}
        <div class="row g-3 mb-4 pt-3 border-top-genshin">
          {{-- Model Cadangan --}}
          <div class="col-12 col-md-4">
            <label class="form-label small fw-bold mb-1" style="color: #fbbf24;">
              <i class="bi bi-arrow-repeat me-1"></i>2. Model Cadangan (Fallback)
            </label>
            <select name="fallback_model" id="selectFallbackModel" class="form-select form-select-sm genshin-select">
              @foreach($models as $key => $meta)
                <option value="{{ $key }}" {{ $config['fallback_model'] === $key ? 'selected' : '' }}>
                  {{ $meta['name'] }} ({{ $meta['badge'] }})
                </option>
              @endforeach
            </select>
            <div class="helper-text mt-1">
              Digunakan jika model utama mengalami rate limit (HTTP 429) atau server Google sibuk (HTTP 503).
            </div>
          </div>

          {{-- Auto Fallback Switch --}}
          <div class="col-12 col-md-4">
            <label class="form-label small fw-bold mb-1" style="color: #fbbf24;">
              <i class="bi bi-shield-check me-1"></i>3. Fitur Auto-Fallback
            </label>
            <div class="stat-metric-card p-3">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" name="enable_auto_fallback" id="switchAutoFallback" value="1" {{ !empty($config['enable_auto_fallback']) ? 'checked' : '' }} style="cursor: pointer;">
                <label class="form-check-label small text-white fw-bold" for="switchAutoFallback" style="cursor: pointer; font-size: 0.82rem;">
                  Aktifkan Auto-Fallback Otomatis
                </label>
              </div>
              <div class="helper-text mt-1">
                Mencegah error pada UI dengan otomatis mencoba model cadangan bila terjadi limit.
              </div>
            </div>
          </div>

          {{-- Tier Kuota Akun --}}
          <div class="col-12 col-md-4">
            <label class="form-label small fw-bold mb-1" style="color: #fbbf24;">
              <i class="bi bi-credit-card me-1"></i>4. Jenis Kuota Akun API
            </label>
            <select name="rate_limit_tier" class="form-select form-select-sm genshin-select">
              <option value="free" {{ ($config['rate_limit_tier'] ?? 'free') === 'free' ? 'selected' : '' }}>
                Free Tier (Default Google AI Studio — 15 RPM / 1.500 RPD)
              </option>
              <option value="pay_as_you_go" {{ ($config['rate_limit_tier'] ?? '') === 'pay_as_you_go' ? 'selected' : '' }}>
                Pay-as-you-go (Akun Berbayar Google Cloud — Tanpa Batas Ketat)
              </option>
            </select>
            <div class="helper-text mt-1">
              Menentukan batas ambang kalkulator limit pada dashboard di atas.
            </div>
          </div>
        </div>

        {{-- Section C: Advanced Parameters (Temperature & Max Output Tokens) --}}
        <div class="row g-3 pt-3 border-top-genshin">
          <div class="col-12 col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label small fw-bold mb-0" style="color: #fbbf24;">
                <i class="bi bi-thermometer-half me-1"></i>Kreativitas Jawaban (Temperature)
              </label>
              <span class="badge bg-dark border border-secondary text-warning" id="lblTempVal" style="background-color: #0a0d17 !important;">{{ $config['temperature'] }}</span>
            </div>
            <input type="range" class="form-range" name="temperature" id="inputTemperature" min="0.0" max="1.0" step="0.05" value="{{ $config['temperature'] }}">
            <div class="d-flex justify-content-between helper-text">
              <span>0.0 (Presisi & Konsisten)</span>
              <span style="color: #fbbf24;">0.25 (Ideal Theorycrafter)</span>
              <span>1.0 (Sangat Kreatif)</span>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label small fw-bold mb-0" style="color: #fbbf24;">
                <i class="bi bi-body-text me-1"></i>Batas Panjang Output (Max Tokens)
              </label>
              <span class="badge bg-dark border border-secondary text-info" id="lblTokensVal" style="background-color: #0a0d17 !important;">{{ $config['max_tokens'] }} Tokens</span>
            </div>
            <input type="range" class="form-range" name="max_tokens" id="inputMaxTokens" min="2048" max="16384" step="1024" value="{{ $config['max_tokens'] }}">
            <div class="d-flex justify-content-between helper-text">
              <span>2.048 (Ringkas)</span>
              <span style="color: #38bdf8;">8.192 (Direkomendasikan untuk Smart Team)</span>
              <span>16.384 (Ekstra Panjang)</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </form>

  {{-- 3. RIWAYAT PEMAKAIAN AI TERAKHIR --}}
  <div class="card genshin-panel-card animate-fade-in-up">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom-genshin py-3">
      <h5 class="font-display mb-0" style="font-size: 1.1rem; color: #fbbf24;">
        <i class="bi bi-journal-text me-2 text-warning"></i>Riwayat Pemanggilan AI Terakhir
      </h5>
      <span class="helper-text">
        10 aktivitas pemanggilan terbaru di aplikasi Anda
      </span>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-genshin table-hover table-sm align-middle mb-0" style="font-size: 0.82rem;">
          <thead>
            <tr>
              <th class="ps-3 py-2">Waktu</th>
              <th>Fitur Pemanggil</th>
              <th>Model Digunakan</th>
              <th>Total Token</th>
              <th>Durasi Eksekusi</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="tbodyRecentLogs">
            @forelse($recentLogs as $log)
              <tr>
                <td class="ps-3" style="color: #94a3b8;">{{ $log['time_ago'] }}</td>
                <td class="fw-bold text-white">{{ $log['feature'] }}</td>
                <td>
                  <span class="badge bg-dark border border-secondary text-info" style="background-color: #0c101d !important;">
                    {{ $log['model'] }}
                  </span>
                </td>
                <td style="color: #fbbf24; font-weight: 600;">{{ number_format($log['total_tokens'], 0, ',', '.') }} tok</td>
                <td class="text-white">{{ $log['duration_sec'] }} detik</td>
                <td>
                  @if($log['status'] === 'success')
                    <span class="badge bg-success bg-opacity-25 border border-success text-success" style="border-color: #22c55e !important;"><i class="bi bi-check-circle me-1"></i>Sukses</span>
                  @elseif($log['status'] === 'fallback')
                    <span class="badge bg-warning bg-opacity-25 border border-warning text-warning" style="border-color: #eab308 !important;"><i class="bi bi-arrow-repeat me-1"></i>Fallback</span>
                  @else
                    <span class="badge bg-danger bg-opacity-25 border border-danger text-danger" style="border-color: #ef4444 !important;"><i class="bi bi-x-circle me-1"></i>Gagal</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4" style="color: #64748b;">
                  <i class="bi bi-info-circle me-1"></i>Belum ada aktivitas pemanggilan Gemini AI yang tercatat.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@push('styles')
<style>
  :root {
    --bg-page: #0b0e17;
    --bg-panel: #131726;
    --bg-panel-dark: #0c0f1a;
    --border-genshin: rgba(200, 170, 110, 0.25);
    --border-gold-glow: rgba(251, 191, 36, 0.45);
    --color-gold: #c8aa6e;
    --color-gold-bright: #fbbf24;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
  }

  /* Solid Dark Layout Wrapper */
  .ai-settings-page {
    color: var(--text-primary);
  }

  /* Genshin Dark Panel Cards */
  .genshin-panel-card {
    background-color: #131726 !important;
    border: 1px solid rgba(200, 170, 110, 0.25) !important;
    border-radius: 14px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
    color: #f8fafc !important;
    overflow: hidden;
  }

  .genshin-panel-card .card-header {
    background-color: rgba(12, 15, 26, 0.95) !important;
    border-bottom: 1px solid rgba(200, 170, 110, 0.2) !important;
    color: #f8fafc !important;
  }

  .genshin-panel-card .card-body {
    background-color: #131726 !important;
    color: #f8fafc !important;
  }

  .border-bottom-genshin {
    border-bottom: 1px solid rgba(200, 170, 110, 0.2) !important;
  }

  .border-top-genshin {
    border-top: 1px solid rgba(200, 170, 110, 0.2) !important;
  }

  .border-gold-glow {
    border-color: rgba(200, 170, 110, 0.4) !important;
    box-shadow: 0 0 25px rgba(200, 170, 110, 0.08) !important;
  }

  /* Stat Metric Cards (RPD, RPM, TPM) */
  .stat-metric-card {
    background-color: #0c0f1a !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 10px;
    padding: 1.1rem;
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3);
  }

  .stat-metric-title {
    color: #94a3b8 !important;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }

  /* Model Choice Radio Cards */
  .model-choice-card {
    background-color: #0c0f1a !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 12px;
    transition: all 0.22s ease-in-out;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  }

  .model-choice-card:hover {
    border-color: rgba(200, 170, 110, 0.6) !important;
    background-color: rgba(26, 32, 53, 0.8) !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
  }

  .model-choice-card.selected {
    border: 2px solid #fbbf24 !important;
    background: linear-gradient(180deg, rgba(200, 170, 110, 0.16) 0%, rgba(15, 18, 30, 0.98) 100%) !important;
    box-shadow: 0 0 18px rgba(251, 191, 36, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
  }

  .model-choice-desc {
    color: #cbd5e1 !important;
    font-size: 0.75rem;
    line-height: 1.5;
  }

  .model-meta-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    color: #94a3b8 !important;
  }

  /* Form Elements & Inputs */
  .genshin-select, .genshin-input {
    background-color: #0c0f1a !important;
    border: 1px solid rgba(200, 170, 110, 0.25) !important;
    color: #f8fafc !important;
    border-radius: 8px;
  }

  .genshin-select:focus, .genshin-input:focus {
    border-color: #fbbf24 !important;
    box-shadow: 0 0 0 0.25rem rgba(200, 170, 110, 0.2) !important;
    background-color: #111524 !important;
    color: #ffffff !important;
  }

  .helper-text {
    color: #94a3b8 !important;
    font-size: 0.74rem;
  }

  /* Range Sliders */
  .form-range {
    height: 1.5rem;
  }
  .form-range::-webkit-slider-runnable-track {
    background-color: rgba(255, 255, 255, 0.15);
    height: 6px;
    border-radius: 3px;
  }
  .form-range::-webkit-slider-thumb {
    background: #fbbf24;
    box-shadow: 0 0 8px rgba(251, 191, 36, 0.6);
    margin-top: -5px;
  }

  /* Table Dark Styling */
  .table-genshin {
    --bs-table-bg: transparent;
    --bs-table-color: #e2e8f0;
    --bs-table-hover-bg: rgba(255, 255, 255, 0.04);
    --bs-table-hover-color: #ffffff;
    border-color: rgba(255, 255, 255, 0.08) !important;
  }

  .table-genshin thead th {
    background-color: rgba(0, 0, 0, 0.35) !important;
    color: #fbbf24 !important;
    border-bottom: 1px solid rgba(200, 170, 110, 0.25) !important;
    font-weight: 600;
  }

  .table-genshin tbody tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }

  /* Radio & Switch */
  .form-check-input:checked {
    background-color: #fbbf24;
    border-color: #fbbf24;
  }

  /* Buttons */
  .btn-genshin {
    background: linear-gradient(135deg, #e8a946 0%, #c97e1b 100%) !important;
    color: #0b0e17 !important;
    font-weight: 700 !important;
    border: 1px solid #ffd37a !important;
    box-shadow: 0 3px 12px rgba(232, 169, 70, 0.3) !important;
    transition: all 0.2s ease;
  }
  .btn-genshin:hover {
    background: linear-gradient(135deg, #f0ba5d 0%, #db8c22 100%) !important;
    box-shadow: 0 4px 16px rgba(232, 169, 70, 0.5) !important;
    color: #000 !important;
    transform: translateY(-1px);
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';

    // 1. Interactive Model Radio Card Selection
    document.querySelectorAll('.radio-model-choice').forEach(radio => {
      radio.addEventListener('change', function () {
        document.querySelectorAll('.model-choice-card').forEach(c => c.classList.remove('selected'));
        if (this.checked) {
          this.closest('.model-choice-card').classList.add('selected');
        }
      });
    });

    // 2. Temperature & Max Tokens Sliders Live Label
    const tempInput = document.getElementById('inputTemperature');
    const tempLbl = document.getElementById('lblTempVal');
    if (tempInput && tempLbl) {
      tempInput.addEventListener('input', (e) => tempLbl.textContent = e.target.value);
    }

    const tokensInput = document.getElementById('inputMaxTokens');
    const tokensLbl = document.getElementById('lblTokensVal');
    if (tokensInput && tokensLbl) {
      tokensInput.addEventListener('input', (e) => tokensLbl.textContent = `${Number(e.target.value).toLocaleString('id-ID')} Tokens`);
    }

    // 3. Refresh Quota Data via AJAX
    const btnRefresh = document.getElementById('btnRefreshQuota');
    if (btnRefresh) {
      btnRefresh.addEventListener('click', fetchQuotaStats);
    }

    // 4. Test Ping Connection to Active Model
    const btnTest = document.getElementById('btnTestConnection');
    if (btnTest) {
      btnTest.addEventListener('click', function () {
        const selectedModel = document.querySelector('input[name="active_model"]:checked')?.value || 'gemini-2.5-flash';
        btnTest.disabled = true;
        btnTest.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Testing ${selectedModel}...`;

        fetch('{{ route("settings.ai.test-connection") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ model: selectedModel })
        })
        .then(res => res.json())
        .then(data => {
          btnTest.disabled = false;
          btnTest.innerHTML = `<i class="bi bi-broadcast me-1"></i>Tes Ping Model`;

          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Koneksi Berhasil!',
              html: `
                <div class="text-start small">
                  <div><strong>Model:</strong> ${escapeHtml(data.model)}</div>
                  <div><strong>Latensi:</strong> ${data.latency_ms} ms</div>
                  <div><strong>Respons:</strong> "${escapeHtml(data.reply)}"</div>
                </div>
              `
            });
            fetchQuotaStats();
          } else {
            Swal.fire('Koneksi Gagal', data.message || 'Gagal menghubungi server Google Gemini.', 'error');
          }
        })
        .catch(err => {
          btnTest.disabled = false;
          btnTest.innerHTML = `<i class="bi bi-broadcast me-1"></i>Tes Ping Model`;
          Swal.fire('Error', 'Gagal menghubungi backend sistem.', 'error');
        });
      });
    }

    function fetchQuotaStats() {
      if (btnRefresh) {
        btnRefresh.disabled = true;
        btnRefresh.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Memuat...`;
      }

      fetch('{{ route("settings.ai.quota-ajax") }}', {
        headers: { 'Accept': 'application/json' }
      })
      .then(res => res.json())
      .then(data => {
        if (btnRefresh) {
          btnRefresh.disabled = false;
          btnRefresh.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i>Refresh Kuota`;
        }
        if (data.success && data.stats) {
          updateQuotaUI(data.stats);
        }
      })
      .catch(err => {
        if (btnRefresh) {
          btnRefresh.disabled = false;
          btnRefresh.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i>Refresh Kuota`;
        }
        console.error(err);
      });
    }

    function updateQuotaUI(stats) {
      document.getElementById('lblActiveModelMeta').textContent = stats.active_model_meta?.name || stats.config?.active_model;
      document.getElementById('lblResetCountdown').textContent = `${stats.next_reset_in} lagi`;

      // RPD
      document.getElementById('txtRpdUsed').innerHTML = `${numberFormat(stats.rpd.used)} <span class="fw-normal" style="font-size: 0.85rem; color: #94a3b8;">/ ${numberFormat(stats.rpd.limit)}</span>`;
      document.getElementById('badgeRpdPercent').textContent = `${stats.rpd.percent}%`;
      const barRpd = document.getElementById('barRpd');
      barRpd.style.width = `${Math.min(100, stats.rpd.percent)}%`;
      barRpd.className = `progress-bar ${stats.rpd.percent > 85 ? 'bg-danger' : ($stats['rpd']['percent'] > 60 ? 'bg-warning' : 'bg-success')}`;
      document.getElementById('txtRpdRemaining').textContent = `${Math.max(0, stats.rpd.limit - stats.rpd.used)} request`;

      // RPM
      document.getElementById('txtRpmUsed').innerHTML = `${stats.rpm.used} <span class="fw-normal" style="font-size: 0.85rem; color: #94a3b8;">/ ${stats.rpm.limit} RPM</span>`;
      document.getElementById('badgeRpmPercent').textContent = `${stats.rpm.percent}%`;
      const barRpm = document.getElementById('barRpm');
      barRpm.style.width = `${Math.min(100, stats.rpm.percent)}%`;

      // Tokens
      document.getElementById('txtTokensUsed').innerHTML = `${numberFormat(stats.tokens.used_today)} <span class="fw-normal" style="font-size: 0.8rem; color: #94a3b8;">Tokens</span>`;

      // Health Badge
      const badgeHealth = document.getElementById('badgeGlobalHealth');
      if (badgeHealth) {
        if (stats.status_health === 'safe') {
          badgeHealth.className = 'badge bg-success px-2 py-1';
          badgeHealth.innerHTML = `<i class="bi bi-shield-check me-1"></i>Status: Kuota Aman`;
        } else if (stats.status_health === 'warning') {
          badgeHealth.className = 'badge bg-warning text-dark px-2 py-1';
          badgeHealth.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Status: Waspada`;
        } else {
          badgeHealth.className = 'badge bg-danger px-2 py-1';
          badgeHealth.innerHTML = `<i class="bi bi-shield-x me-1"></i>Status: Mendekati Limit`;
        }
      }
    }

    function numberFormat(val) {
      if (val === null || val === undefined) return '0';
      return Number(val).toLocaleString('id-ID');
    }

    function escapeHtml(text) {
      if (!text) return '';
      return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
  });
</script>
@endpush
@endsection
