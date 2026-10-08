@extends('layout.main')

@section('content')
@include('layout.header')

<div class="container-fluid px-3 px-md-4 py-4" style="max-width: 1400px;">

  {{-- ===== PAGE TITLE & GLOBAL ACTIONS ===== --}}
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2">
        <h1 class="h3 fw-bold text-white mb-0" style="font-family: 'Cinzel', serif;">
          <i class="bi bi-moon-stars-fill text-info me-2"></i>Daily Check-in & Resin Alert
        </h1>
        <span class="badge rounded-pill bg-info text-dark fw-bold px-2 py-1 fs-xs">HoYoLAB Live</span>
      </div>
      <p class="text-secondary small mb-0 mt-1">
        Pantau Original Resin, estimasi pemulihan real-time, ekspedisi, dan jalankan otomatisasi Daily Check-in berhadiah.
      </p>
    </div>

    {{-- Controls --}}
    <div class="d-flex flex-wrap align-items-center gap-2">
      <button class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" id="btnRequestNotify" onclick="requestNotificationPermission()">
        <i class="bi bi-bell-fill"></i>
        <span id="notifyStatusText">Notifikasi Browser</span>
      </button>

      <button class="btn btn-sm btn-outline-secondary text-light d-flex align-items-center gap-1" id="btnSoundToggle" onclick="toggleSoundAlert()">
        <i class="bi bi-volume-up-fill" id="soundIcon"></i>
        <span id="soundText">Suara: Aktif</span>
      </button>

      <button class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1" onclick="testNotificationAlert()">
        <i class="bi bi-lightning-charge-fill"></i> Tes Alert
      </button>

      @if($selectedAccount && $selectedAccount->hasHoyoLabCookies())
        <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" id="btnRefreshLive" onclick="refreshLiveData()">
          <i class="bi bi-arrow-repeat" id="refreshIcon"></i>
          <span>Refresh Data</span>
        </button>
      @endif
    </div>
  </div>

  {{-- ===== ACCOUNT SELECTOR ===== --}}
  <div class="card glass-card border-secondary border-opacity-25 mb-4">
    <div class="card-body p-3">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <span class="text-gold fw-semibold small text-uppercase letter-spacing-1">
            <i class="bi bi-person-circle me-1"></i>Pilih Akun:
          </span>
          @forelse($accounts as $acc)
            <a href="{{ route('daily-resin.index', ['account_id' => $acc->id]) }}" 
               class="account-badge-btn text-decoration-none px-3 py-2 rounded-3 d-flex align-items-center gap-2 {{ ($selectedAccount && $selectedAccount->id === $acc->id) ? 'active' : '' }}">
              <span class="status-indicator {{ $acc->hasHoyoLabCookies() ? 'bg-success' : 'bg-warning' }}"></span>
              <span class="fw-bold fs-sm">{{ $acc->nickname }}</span>
              <span class="text-secondary fs-xs">({{ $acc->uid }})</span>
              @if($acc->hasHoyoLabCookies())
                <i class="bi bi-check-circle-fill text-success fs-xs" title="Cookie HoYoLAB Terpasang"></i>
              @else
                <i class="bi bi-exclamation-triangle-fill text-warning fs-xs" title="Belum ada Cookie HoYoLAB"></i>
              @endif
            </a>
          @empty
            <div class="text-secondary small">Belum ada akun game terdaftar.</div>
          @endforelse
        </div>

        <div class="d-flex align-items-center gap-2 text-secondary small">
          @if($selectedAccount)
            <span>Server: <strong class="text-white">{{ $selectedAccount->server_label }}</strong></span>
            <span>?</span>
            <span id="lastSyncedDisplay">
              @if($selectedAccount->last_resin_synced_at)
                Terakhir Diperbarui: <strong class="text-gold">{{ $selectedAccount->last_resin_synced_at->format('H:i:s') }}</strong>
              @else
                Belum Pernah Sync
              @endif
            </span>
          @endif
        </div>
      </div>
    </div>
  </div>

  @if($fetchError)
    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      <div>{{ $fetchError }}</div>
    </div>
  @endif

  @if(!$selectedAccount)
    <div class="text-center py-5 glass-card rounded-4">
      <i class="bi bi-controller text-secondary fs-1 mb-3 d-block"></i>
      <h4 class="text-white">Tidak ada Akun Game</h4>
      <p class="text-secondary">Silakan tambahkan akun game Genshin Impact terlebih dahulu di halaman Akun Game.</p>
      <a href="{{ route('game-accounts.index') }}" class="btn btn-outline-warning">
        <i class="bi bi-plus-circle me-1"></i>Kelola Akun Game
      </a>
    </div>
  @elseif(!$selectedAccount->hasHoyoLabCookies())
    <div class="glass-card rounded-4 p-4 text-center border-warning border-opacity-25 mb-4">
      <div class="d-inline-flex p-3 rounded-circle bg-warning bg-opacity-10 text-warning mb-3">
        <i class="bi bi-key-fill fs-2"></i>
      </div>
      <h4 class="text-white fw-bold">Cookie HoYoLAB Belum Diatur</h4>
      <p class="text-secondary mx-auto" style="max-width: 650px;">
        Fitur <strong>Real-Time Resin Tracker</strong> dan <strong>Auto Daily Check-in</strong> memerlukan token cookie 
        <code>ltuid_v2</code> dan <code>ltoken_v2</code> dari akun HoYoLAB Anda untuk berkomunikasi dengan API game.
      </p>
      <div class="d-flex justify-content-center gap-2 mt-3">
        <a href="{{ route('game-accounts.index') }}" class="btn btn-warning fw-bold text-dark px-4">
          <i class="bi bi-box-arrow-in-right me-1"></i>Masukkan Cookie di Halaman Akun
        </a>
      </div>
    </div>
  @else

    {{-- ===== DUA HERO CARD UTAMA: RESIN VITAL & DAILY CHECK-IN ===== --}}
    <div class="row g-4 mb-4">

      {{-- 1. RESIN HERO CARD --}}
      <div class="col-12 col-xl-7">
        <div class="card glass-card h-100 resin-hero-card position-relative overflow-hidden">
          <div class="resin-glow-bg"></div>
          <div class="card-body p-4 position-relative z-1">

            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <div class="resin-icon-wrapper">
                  <span class="resin-crystal-symbol">?</span>
                </div>
                <div>
                  <h3 class="h5 fw-bold text-white mb-0">Original Resin</h3>
                  <span class="fs-xs text-secondary">Maksimum: 200 Resin</span>
                </div>
              </div>

              <div id="resinStatusBadge">
                @php
                  $curResin = $notes['current_resin'] ?? ($selectedAccount->last_known_resin ?? 0);
                  $maxResin = $notes['max_resin'] ?? 200;
                  $pct = $maxResin > 0 ? min(100, round(($curResin / $maxResin) * 100)) : 0;
                  $threshold = $selectedAccount->resin_alert_threshold ?? 160;
                @endphp

                @if($curResin >= $maxResin)
                  <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-2 fs-xs fw-bold animate-pulse">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i>RESIN PENUH!
                  </span>
                @elseif($curResin >= $threshold)
                  <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3 py-2 fs-xs fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>MENDEKATI BATAS
                  </span>
                @else
                  <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 fs-xs fw-bold">
                    <i class="bi bi-shield-check me-1"></i>AMAN
                  </span>
                @endif
              </div>
            </div>

            <div class="row align-items-center my-3">
              <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
                <div class="d-flex align-items-baseline gap-2 justify-content-center justify-content-sm-start">
                  <span class="display-3 fw-bold text-white resin-numeric-val" id="valCurrentResin">
                    {{ $curResin }}
                  </span>
                  <span class="fs-3 text-secondary">/ <span id="valMaxResin">{{ $maxResin }}</span></span>
                </div>
                <div class="text-secondary small mt-1">
                  Ambang batas alert: <strong class="text-gold" id="valThresholdDisplay">{{ $threshold }} Resin</strong>
                </div>
              </div>

              <div class="col-sm-6 text-center text-sm-end">
                <div class="p-3 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 d-inline-block text-start w-100" style="max-width: 280px;">
                  <div class="text-secondary fs-xs text-uppercase mb-1">Estimasi Penuh:</div>
                  <div class="h5 fw-bold text-info mb-0" id="timerFullResin">
                    @if(isset($notes['remaining_resin_recovery_seconds']) && $notes['remaining_resin_recovery_seconds'] > 0)
                      Menghitung...
                    @elseif($curResin >= $maxResin)
                      Sudah Penuh (Cap)
                    @else
                      -
                    @endif
                  </div>
                  <div class="fs-xs text-muted mt-1" id="exactFullTimeText">-</div>
                </div>
              </div>
            </div>

            <div class="progress resin-progress mb-4" style="height: 14px; background: rgba(255,255,255,0.06); border-radius: 10px;">
              <div class="progress-bar resin-bar {{ $curResin >= $maxResin ? 'bg-danger' : ($curResin >= $threshold ? 'bg-warning' : 'bg-info') }}" 
                   id="barResinProgress"
                   role="progressbar" 
                   style="width: {{ $pct }}%; transition: width 0.8s ease;"
                   aria-valuenow="{{ $curResin }}" 
                   aria-valuemin="0" 
                   aria-valuemax="{{ $maxResin }}">
              </div>
            </div>

            <div class="row g-2 pt-2 border-top border-secondary border-opacity-10 text-center">
              <div class="col-4">
                <div class="fs-xs text-secondary">Regenerasi</div>
                <div class="fw-bold text-white small">1 Resin / 8 Mnt</div>
              </div>
              <div class="col-4">
                <div class="fs-xs text-secondary">Total Harian</div>
                <div class="fw-bold text-white small">180 Resin / 24j</div>
              </div>
              <div class="col-4">
                <div class="fs-xs text-secondary">Batas Alert</div>
                <div class="fw-bold text-warning small" id="metaThresholdVal">{{ $threshold }} / 200</div>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- 2. DAILY CHECK-IN HERO CARD --}}
      <div class="col-12 col-xl-5">
        <div class="card glass-card h-100 checkin-hero-card">
          <div class="card-body p-4 d-flex flex-column justify-content-between">

            <div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                  <div class="checkin-icon-wrapper">
                    <i class="bi bi-calendar-check-fill text-gold"></i>
                  </div>
                  <div>
                    <h3 class="h5 fw-bold text-white mb-0">Daily Check-in</h3>
                    <span class="fs-xs text-secondary">Klaim hadiah gratis setiap hari</span>
                  </div>
                </div>

                <div id="checkinStatusBadge">
                  @php
                    $isSignedIn = $checkinStatus['is_signed_in'] ?? false;
                    if (!$isSignedIn && $selectedAccount->last_checkin_at && $selectedAccount->last_checkin_at->isToday() && $selectedAccount->last_checkin_status !== 'failed') {
                      $isSignedIn = true;
                    }
                  @endphp

                  @if($isSignedIn)
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 fs-xs fw-bold">
                      <i class="bi bi-check-lg me-1"></i>SUDAH CHECK-IN
                    </span>
                  @else
                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3 py-2 fs-xs fw-bold">
                      <i class="bi bi-clock-history me-1"></i>BELUM CHECK-IN
                    </span>
                  @endif
                </div>
              </div>

              <div class="p-3 rounded-3 bg-surface-2 border border-secondary border-opacity-25 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="fs-xs text-secondary d-block">Total Check-in Bulan Ini:</span>
                    <span class="h4 fw-bold text-gold mb-0" id="valClaimedDays">
                      {{ $checkinStatus['total_claimed_days'] ?? '-' }}
                    </span>
                    <span class="text-secondary small"> / 31 Hari</span>
                  </div>
                  <div class="text-end">
                    <span class="fs-xs text-secondary d-block">Status Otomatis:</span>
                    <span class="badge {{ $selectedAccount->auto_checkin_enabled ? 'bg-success' : 'bg-secondary' }} bg-opacity-25 text-white border border-secondary border-opacity-25">
                      {{ $selectedAccount->auto_checkin_enabled ? 'Auto Check-in Aktif' : 'Nonaktif' }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button class="btn btn-gold w-100 py-3 fw-bold fs-6 d-flex align-items-center justify-content-center gap-2 shadow-sm"
                        id="btnClaimCheckin"
                        onclick="claimDailyCheckinNow()"
                        {{ $isSignedIn ? 'disabled' : '' }}>
                  <i class="bi bi-gift-fill" id="claimIcon"></i>
                  <span id="claimBtnText">{{ $isSignedIn ? 'Sudah Diklaim Hari Ini ?' : '? Klaim Hadiah Hari Ini Sekarang' }}</span>
                </button>
              </div>

              <div class="small text-secondary text-center" id="checkinMessageDisplay">
                @if($selectedAccount->last_checkin_message)
                  <i class="bi bi-info-circle me-1"></i>{{ $selectedAccount->last_checkin_message }}
                @endif
              </div>
            </div>

            <div class="pt-3 border-top border-secondary border-opacity-10 mt-3">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fs-xs text-secondary">Hadiah Terakhir:</span>
                <span class="fs-xs text-light" id="lastRewardPreview">
                  @if(!empty($checkinStatus['recent_claims']))
                    @php $lastR = $checkinStatus['recent_claims'][0]; @endphp
                    <span class="text-gold fw-semibold">{{ $lastR['name'] }}</span> x{{ $lastR['amount'] }}
                  @else
                    -
                  @endif
                </span>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>

    {{-- ===== VITALS GRID: REALM CURRENCY, COMMISSIONS, BOSSES, TRANSFORMER ===== --}}
    <div class="row g-3 mb-4">

      {{-- 1. Realm Currency --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 p-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="fs-xs text-secondary text-uppercase fw-semibold">Realm Currency</span>
            <i class="bi bi-coin text-warning fs-5"></i>
          </div>
          <div class="d-flex align-items-baseline gap-1 mb-2">
            <span class="h4 fw-bold text-white mb-0" id="valRealmCurr">
              {{ $notes['current_realm_currency'] ?? '-' }}
            </span>
            <span class="text-secondary small">/ {{ $notes['max_realm_currency'] ?? 2400 }}</span>
          </div>
          @php
            $rCur = $notes['current_realm_currency'] ?? 0;
            $rMax = $notes['max_realm_currency'] ?? 2400;
            $rPct = $rMax > 0 ? min(100, round(($rCur / $rMax) * 100)) : 0;
          @endphp
          <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.06);">
            <div class="progress-bar bg-warning" id="barRealmProgress" style="width: {{ $rPct }}%"></div>
          </div>
          <span class="fs-xs text-secondary" id="timerRealmFull">
            @if(isset($notes['remaining_realm_currency_recovery_seconds']) && $notes['remaining_realm_currency_recovery_seconds'] > 0)
              Penuh dlm: {{ floor($notes['remaining_realm_currency_recovery_seconds'] / 3600) }}j {{ floor(($notes['remaining_realm_currency_recovery_seconds'] % 3600) / 60) }}m
            @else
              Penuh / N/A
            @endif
          </span>
        </div>
      </div>

      {{-- 2. Daily Commissions --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 p-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="fs-xs text-secondary text-uppercase fw-semibold">Daily Commissions</span>
            <i class="bi bi-stars text-gold fs-5"></i>
          </div>
          <div class="d-flex align-items-baseline gap-1 mb-2">
            <span class="h4 fw-bold text-white mb-0" id="valCommissions">
              {{ $notes['completed_commissions'] ?? '-' }}
            </span>
            <span class="text-secondary small">/ {{ $notes['max_commissions'] ?? 4 }} Selesai</span>
          </div>
          <div class="d-flex gap-1 mb-2">
            @php $comp = $notes['completed_commissions'] ?? 0; @endphp
            @for($i = 1; $i <= 4; $i++)
              <div class="flex-fill rounded-1 py-1 text-center {{ $i <= $comp ? 'bg-gold text-dark' : 'bg-dark text-secondary' }} fs-xs fw-bold">
                <i class="bi {{ $i <= $comp ? 'bi-check-lg' : 'bi-dash' }}"></i>
              </div>
            @endfor
          </div>
          <div class="d-flex justify-content-between align-items-center fs-xs">
            <span class="text-secondary">Bonus Katheryne:</span>
            <span class="fw-bold {{ (!empty($notes['claimed_commission_reward'])) ? 'text-success' : 'text-warning' }}">
              {{ (!empty($notes['claimed_commission_reward'])) ? 'Sudah Diambil ?' : 'Belum Diambil' }}
            </span>
          </div>
        </div>
      </div>

      {{-- 3. Weekly Boss Resin Discounts --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 p-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="fs-xs text-secondary text-uppercase fw-semibold">Diskon Boss Mingguan</span>
            <i class="bi bi-shield-shaded text-danger fs-5"></i>
          </div>
          <div class="d-flex align-items-baseline gap-1 mb-2">
            <span class="h4 fw-bold text-white mb-0" id="valBossDiscounts">
              {{ $notes['remaining_resin_discounts'] ?? '-' }}
            </span>
            <span class="text-secondary small">/ {{ $notes['max_resin_discounts'] ?? 3 }} Tersisa (30 Resin)</span>
          </div>
          <div class="d-flex gap-1 mb-2">
            @php $bLeft = $notes['remaining_resin_discounts'] ?? 0; @endphp
            @for($i = 1; $i <= 3; $i++)
              <div class="flex-fill rounded-1 py-1 text-center {{ $i <= $bLeft ? 'bg-danger text-white' : 'bg-dark text-secondary' }} fs-xs fw-bold">
                30
              </div>
            @endfor
          </div>
          <span class="fs-xs text-secondary">Reset setiap hari Senin 04:00</span>
        </div>
      </div>

      {{-- 4. Parametric Transformer --}}
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 p-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="fs-xs text-secondary text-uppercase fw-semibold">Parametric Transformer</span>
            <i class="bi bi-box2-fill text-info fs-5"></i>
          </div>
          @php $tReady = !empty($notes['transformer_reached']); @endphp
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge {{ $tReady ? 'bg-success' : 'bg-secondary' }} px-2 py-1 fs-xs fw-bold">
              {{ $tReady ? 'Siap Digunakan!' : 'Dalam Cooldown' }}
            </span>
          </div>
          <div class="fs-xs text-secondary mt-auto">
            @if(isset($notes['transformer_recovery_time_seconds']) && $notes['transformer_recovery_time_seconds'] > 0)
              Cooldown: {{ floor($notes['transformer_recovery_time_seconds'] / 86400) }}h {{ floor(($notes['transformer_recovery_time_seconds'] % 86400) / 3600) }}j
            @else
              Bisa langsung digunakan di inventori.
            @endif
          </div>
        </div>
      </div>

    </div>

    {{-- ===== EKSPEDISI KARAKTER ===== --}}
    <div class="card glass-card border-secondary border-opacity-25 mb-4">
      <div class="card-header bg-transparent border-secondary border-opacity-10 py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-compass-fill text-gold fs-5"></i>
          <h4 class="h6 fw-bold text-white mb-0">Ekspedisi Karakter (Expeditions)</h4>
        </div>
        <span class="badge bg-dark text-secondary border border-secondary border-opacity-25">
          {{ count($notes['expeditions'] ?? []) }} / 5 Berjalan
        </span>
      </div>
      <div class="card-body p-3">
        <div class="row g-3" id="expeditionsContainer">
          @forelse($notes['expeditions'] ?? [] as $idx => $exp)
            @php 
              $isFin = ($exp['status'] ?? '') === 'Finished' || ($exp['remaining_time_seconds'] ?? 0) <= 0;
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-xl">
              <div class="p-3 rounded-3 bg-surface-2 border {{ $isFin ? 'border-success' : 'border-secondary border-opacity-25' }} text-center h-100 d-flex flex-column align-items-center justify-content-between">
                <div class="expedition-avatar-wrapper mb-2 position-relative">
                  @if(!empty($exp['character_icon']))
                    <img src="{{ $exp['character_icon'] }}" alt="Karakter" class="rounded-circle border border-gold" style="width: 52px; height: 52px; object-fit: cover;">
                  @else
                    <div class="rounded-circle bg-dark border border-secondary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                      <i class="bi bi-person text-secondary fs-4"></i>
                    </div>
                  @endif
                  @if($isFin)
                    <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success p-1">
                      <i class="bi bi-check fs-xs"></i>
                    </span>
                  @endif
                </div>

                <div class="mb-2">
                  <span class="badge {{ $isFin ? 'bg-success text-white' : 'bg-warning bg-opacity-25 text-warning' }} fs-xs">
                    {{ $isFin ? 'Selesai ?' : 'Sedang Menjelajah' }}
                  </span>
                </div>

                <div class="fs-xs fw-semibold text-secondary exp-countdown-timer" 
                     data-seconds="{{ $exp['remaining_time_seconds'] ?? 0 }}" 
                     id="expTimer{{ $idx }}">
                  @if($isFin)
                    Siap Diklaim
                  @else
                    {{ floor(($exp['remaining_time_seconds'] ?? 0) / 3600) }}j {{ floor((($exp['remaining_time_seconds'] ?? 0) % 3600) / 60) }}m
                  @endif
                </div>
              </div>
            </div>
          @empty
            <div class="col-12 text-center py-3 text-secondary small">
              Tidak ada data ekspedisi aktif.
            </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- ===== PENGATURAN RESIN ALERT & RIWAYAT LOG ===== --}}
    <div class="row g-4">

      {{-- Form Pengaturan Alert & Auto Checkin --}}
      <div class="col-12 col-lg-5">
        <div class="card glass-card h-100">
          <div class="card-header bg-transparent border-secondary border-opacity-10 py-3">
            <h4 class="h6 fw-bold text-white mb-0">
              <i class="bi bi-sliders me-2 text-gold"></i>Pengaturan Alert & Otomatisasi
            </h4>
          </div>
          <div class="card-body p-4">
            <form id="formSettings" onsubmit="saveAccountSettings(event)">
              @csrf

              {{-- Toggle Auto Checkin --}}
              <div class="form-check form-switch form-switch-lg mb-4 d-flex justify-content-between align-items-center ps-0">
                <div>
                  <label class="form-check-label text-white fw-semibold mb-0" for="switchAutoCheckin">
                    Auto Daily Check-in
                  </label>
                  <div class="fs-xs text-secondary">
                    Klaim reward harian otomatis via Scheduler setiap hari.
                  </div>
                </div>
                <input class="form-check-input ms-3" type="checkbox" id="switchAutoCheckin" 
                       {{ $selectedAccount->auto_checkin_enabled ? 'checked' : '' }}>
              </div>

              <hr class="border-secondary border-opacity-25 my-3">

              {{-- Toggle Resin Alert --}}
              <div class="form-check form-switch form-switch-lg mb-4 d-flex justify-content-between align-items-center ps-0">
                <div>
                  <label class="form-check-label text-white fw-semibold mb-0" for="switchResinAlert">
                    Notifikasi Resin Alert
                  </label>
                  <div class="fs-xs text-secondary">
                    Munculkan peringatan browser & log saat Resin mencapai batas.
                  </div>
                </div>
                <input class="form-check-input ms-3" type="checkbox" id="switchResinAlert" 
                       {{ $selectedAccount->resin_alert_enabled ? 'checked' : '' }}>
              </div>

              {{-- Threshold Slider & Input --}}
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label text-white fw-semibold mb-0" for="rangeThreshold">
                    Batas Resin (Threshold)
                  </label>
                  <span class="badge bg-gold text-dark fw-bold fs-sm" id="badgeThresholdVal">
                    {{ $selectedAccount->resin_alert_threshold ?? 160 }} Resin
                  </span>
                </div>
                <input type="range" class="form-range" min="80" max="200" step="5" 
                       id="rangeThreshold" 
                       value="{{ $selectedAccount->resin_alert_threshold ?? 160 }}" 
                       oninput="document.getElementById('badgeThresholdVal').innerText = this.value + ' Resin'">
                <div class="d-flex justify-content-between fs-xs text-secondary mt-1">
                  <span>80</span>
                  <span>140</span>
                  <span>160</span>
                  <span>180</span>
                  <span>200 (Cap)</span>
                </div>
              </div>

              <button type="submit" class="btn btn-outline-gold w-100 fw-bold d-flex align-items-center justify-content-center gap-2" id="btnSaveSettings">
                <i class="bi bi-save2-fill"></i>
                <span>Simpan Pengaturan Akun</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      {{-- Tab Riwayat --}}
      <div class="col-12 col-lg-7">
        <div class="card glass-card h-100">
          <div class="card-header bg-transparent border-secondary border-opacity-10 py-2 d-flex justify-content-between align-items-center">
            <ul class="nav nav-pills card-header-pills" id="historyTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active py-1 px-3 fs-sm" id="alerts-tab" data-bs-toggle="pill" data-bs-target="#tab-alerts" type="button" role="tab">
                  <i class="bi bi-bell-fill me-1 text-warning"></i>Riwayat Alert Resin
                  @if($unreadAlertsCount > 0)
                    <span class="badge bg-danger rounded-pill ms-1" id="badgeUnreadCount">{{ $unreadAlertsCount }}</span>
                  @endif
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-3 fs-sm" id="checkins-tab" data-bs-toggle="pill" data-bs-target="#tab-checkins" type="button" role="tab">
                  <i class="bi bi-calendar-check me-1 text-gold"></i>Log Daily Check-in
                </button>
              </li>
            </ul>

            <button class="btn btn-xs btn-outline-secondary text-secondary" onclick="markAllAlertsRead()" title="Tandai semua alert sudah dibaca">
              <i class="bi bi-check2-all"></i> Tandai Dibaca
            </button>
          </div>

          <div class="card-body p-0">
            <div class="tab-content" id="historyTabsContent">

              {{-- Tab 1: Alert Resin --}}
              <div class="tab-pane fade show active p-3" id="tab-alerts" role="tabpanel">
                <div class="table-responsive" style="max-height: 380px;">
                  <table class="table table-dark table-hover table-sm align-middle mb-0 fs-sm" id="tableAlerts">
                    <thead>
                      <tr class="text-secondary border-secondary border-opacity-25 fs-xs">
                        <th>Waktu</th>
                        <th>Resin</th>
                        <th>Pesan / Status</th>
                      </tr>
                    </thead>
                    <tbody id="alertsTbody">
                      @forelse($resinAlerts as $alt)
                        <tr class="{{ !$alt->is_read ? 'table-warning bg-opacity-10' : '' }}">
                          <td class="text-nowrap text-secondary fs-xs">
                            {{ $alt->notified_at ? $alt->notified_at->format('d M H:i') : $alt->created_at->format('d M H:i') }}
                          </td>
                          <td>
                            <span class="badge {{ $alt->resin_amount >= $alt->max_resin ? 'bg-danger' : 'bg-warning text-dark' }} fw-bold">
                              {{ $alt->resin_amount }}/{{ $alt->max_resin }}
                            </span>
                          </td>
                          <td class="text-light small">
                            {{ $alt->message }}
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="3" class="text-center py-4 text-secondary small">
                            Belum ada riwayat peringatan Resin.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>

              {{-- Tab 2: Log Daily Checkin --}}
              <div class="tab-pane fade p-3" id="tab-checkins" role="tabpanel">
                <div class="table-responsive" style="max-height: 380px;">
                  <table class="table table-dark table-hover table-sm align-middle mb-0 fs-sm">
                    <thead>
                      <tr class="text-secondary border-secondary border-opacity-25 fs-xs">
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Hadiah</th>
                        <th>Tipe</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($checkinLogs as $log)
                        <tr>
                          <td class="text-nowrap text-secondary fs-xs">
                            {{ $log->checked_at->format('d M H:i') }}
                          </td>
                          <td>
                            @if($log->status === 'success')
                              <span class="badge bg-success bg-opacity-25 text-success">Sukses</span>
                            @elseif($log->status === 'already_claimed')
                              <span class="badge bg-secondary bg-opacity-25 text-light">Sudah Diklaim</span>
                            @else
                              <span class="badge bg-danger bg-opacity-25 text-danger">Gagal</span>
                            @endif
                          </td>
                          <td>
                            @if($log->reward_name)
                              <div class="d-flex align-items-center gap-1">
                                @if($log->reward_icon)
                                  <img src="{{ $log->reward_icon }}" style="width: 22px; height: 22px;">
                                @endif
                                <span class="text-gold">{{ $log->reward_name }}</span>
                                <span class="text-secondary">x{{ $log->reward_amount }}</span>
                              </div>
                            @else
                              <span class="text-secondary small">{{ $log->message ?? '-' }}</span>
                            @endif
                          </td>
                          <td>
                            <span class="badge {{ $log->is_auto ? 'bg-info bg-opacity-10 text-info' : 'bg-dark text-secondary' }} fs-xs">
                              {{ $log->is_auto ? 'Auto (Cron)' : 'Manual' }}
                            </span>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="4" class="text-center py-4 text-secondary small">
                            Belum ada log aktivitas check-in.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>

  @endif

</div>

@endsection

@push('styles')
<style>
  .glass-card {
    background: rgba(19, 23, 42, 0.75);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(200, 170, 110, 0.15);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    transition: transform 0.2s ease, border-color 0.2s ease;
  }
  .glass-card:hover {
    border-color: rgba(200, 170, 110, 0.3);
  }

  .account-badge-btn {
    background: rgba(26, 31, 53, 0.6);
    border: 1px solid rgba(200, 170, 110, 0.15);
    color: #e8e0d0;
    transition: all 0.2s ease;
  }
  .account-badge-btn:hover {
    background: rgba(200, 170, 110, 0.1);
    border-color: rgba(200, 170, 110, 0.35);
    color: #ffffff;
  }
  .account-badge-btn.active {
    background: rgba(200, 170, 110, 0.18);
    border-color: #c8aa6e;
    box-shadow: 0 0 15px rgba(200, 170, 110, 0.25);
    color: #ffffff;
  }

  .status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
  }

  .resin-hero-card {
    background: linear-gradient(135deg, rgba(19, 23, 42, 0.9) 0%, rgba(13, 15, 26, 0.95) 100%);
    border: 1px solid rgba(78, 205, 196, 0.2);
  }
  .resin-glow-bg {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(78, 205, 196, 0.15) 0%, rgba(78, 205, 196, 0) 70%);
    pointer-events: none;
    border-radius: 50%;
  }
  .resin-icon-wrapper {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(78, 205, 196, 0.15);
    border: 1px solid rgba(78, 205, 196, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4ecdc4;
    font-size: 20px;
    box-shadow: 0 0 12px rgba(78, 205, 196, 0.2);
  }
  .resin-numeric-val {
    font-family: 'Cinzel', serif;
    letter-spacing: -1px;
    text-shadow: 0 0 20px rgba(78, 205, 196, 0.3);
  }
  .resin-bar {
    background: linear-gradient(90deg, #4ecdc4, #4a90d9);
    box-shadow: 0 0 12px rgba(78, 205, 196, 0.5);
  }

  .checkin-hero-card {
    background: linear-gradient(135deg, rgba(19, 23, 42, 0.9) 0%, rgba(26, 31, 53, 0.8) 100%);
    border: 1px solid rgba(200, 170, 110, 0.25);
  }
  .checkin-icon-wrapper {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(200, 170, 110, 0.15);
    border: 1px solid rgba(200, 170, 110, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 0 12px rgba(200, 170, 110, 0.2);
  }

  @keyframes pulseAlert {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
  }
  .animate-pulse {
    animation: pulseAlert 1.5s infinite ease-in-out;
  }

  .btn-gold {
    background: linear-gradient(135deg, #e8d5a3 0%, #c8aa6e 50%, #8f7a4b 100%);
    color: #0d0f1a;
    border: none;
    transition: all 0.25s ease;
  }
  .btn-gold:hover:not(:disabled) {
    background: linear-gradient(135deg, #ffffff 0%, #e8d5a3 50%, #c8aa6e 100%);
    box-shadow: 0 0 20px rgba(200, 170, 110, 0.5);
    transform: translateY(-2px);
    color: #0d0f1a;
  }
  .btn-gold:disabled {
    background: #2a2f45;
    color: #64748b;
    cursor: not-allowed;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  .btn-outline-gold {
    border: 1px solid #c8aa6e;
    color: #c8aa6e;
    background: transparent;
    transition: all 0.2s ease;
  }
  .btn-outline-gold:hover {
    background: #c8aa6e;
    color: #0d0f1a;
  }

  .fs-xs { font-size: 0.75rem; }
  .fs-sm { font-size: 0.875rem; }
  .text-gold { color: #c8aa6e !important; }
  .bg-gold { background-color: #c8aa6e !important; }
</style>
@endpush

@push('scripts')
<script>
  const CURRENT_ACCOUNT_ID = {{ $selectedAccount ? $selectedAccount->id : 'null' }};
  const CSRF_TOKEN = '{{ csrf_token() }}';

  let remainingResinSecs = {{ $notes['remaining_resin_recovery_seconds'] ?? 0 }};
  let currentResin = {{ $notes['current_resin'] ?? ($selectedAccount->last_known_resin ?? 0) }};
  let maxResin = {{ $notes['max_resin'] ?? 200 }};
  let soundAlertEnabled = localStorage.getItem('resin_sound_alert') !== 'false';

  updateSoundUI();
  updateNotificationButtonUI();

  setInterval(function() {
    if (remainingResinSecs > 0 && currentResin < maxResin) {
      remainingResinSecs--;
      renderResinTimer(remainingResinSecs);
    } else if (currentResin >= maxResin) {
      var el = document.getElementById('timerFullResin');
      if (el) el.innerText = 'Sudah Penuh (Cap)';
      var tEl = document.getElementById('exactFullTimeText');
      if (tEl) tEl.innerText = '-';
    }

    document.querySelectorAll('.exp-countdown-timer').forEach(function(el) {
      var secs = parseInt(el.getAttribute('data-seconds') || 0);
      if (secs > 0) {
        secs--;
        el.setAttribute('data-seconds', secs);
        var h = Math.floor(secs / 3600);
        var m = Math.floor((secs % 3600) / 60);
        var s = secs % 60;
        el.innerText = h + 'j ' + m + 'm ' + s + 's';
      } else if (secs === 0 && !el.innerText.includes('Siap Diklaim')) {
        el.innerText = 'Siap Diklaim ?';
        el.classList.add('text-success');
      }
    });
  }, 1000);

  if (remainingResinSecs > 0) {
    renderResinTimer(remainingResinSecs);
  }

  function renderResinTimer(secs) {
    var el = document.getElementById('timerFullResin');
    var exactEl = document.getElementById('exactFullTimeText');
    if (!el) return;

    if (secs <= 0) {
      el.innerText = 'Sudah Penuh';
      if (exactEl) exactEl.innerText = 'Sekarang';
      return;
    }

    var hours = Math.floor(secs / 3600);
    var minutes = Math.floor((secs % 3600) / 60);
    var seconds = secs % 60;
    el.innerText = hours + 'j ' + minutes + 'm ' + seconds + 's';

    if (exactEl) {
      var targetDate = new Date(Date.now() + secs * 1000);
      var isToday = targetDate.toDateString() === new Date().toDateString();
      var timeStr = targetDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      exactEl.innerText = (isToday ? 'Hari ini' : 'Besok') + ' ' + timeStr;
    }
  }

  function playAlertChime() {
    if (!soundAlertEnabled) return;
    try {
      var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      var now = audioCtx.currentTime;

      var osc1 = audioCtx.createOscillator();
      var gain1 = audioCtx.createGain();
      osc1.type = 'sine';
      osc1.frequency.setValueAtTime(880, now);
      gain1.gain.setValueAtTime(0.3, now);
      gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
      osc1.connect(gain1);
      gain1.connect(audioCtx.destination);
      osc1.start(now);
      osc1.stop(now + 0.5);

      var osc2 = audioCtx.createOscillator();
      var gain2 = audioCtx.createGain();
      osc2.type = 'triangle';
      osc2.frequency.setValueAtTime(1320, now + 0.15);
      gain2.gain.setValueAtTime(0.3, now + 0.15);
      gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
      osc2.connect(gain2);
      gain2.connect(audioCtx.destination);
      osc2.start(now + 0.15);
      osc2.stop(now + 0.8);
    } catch (e) {
      console.warn('Audio blocked or not supported:', e);
    }
  }

  function toggleSoundAlert() {
    soundAlertEnabled = !soundAlertEnabled;
    localStorage.setItem('resin_sound_alert', soundAlertEnabled);
    updateSoundUI();
    if (soundAlertEnabled) playAlertChime();
  }

  function updateSoundUI() {
    var icon = document.getElementById('soundIcon');
    var text = document.getElementById('soundText');
    if (!icon || !text) return;
    if (soundAlertEnabled) {
      icon.className = 'bi bi-volume-up-fill text-info';
      text.innerText = 'Suara: Aktif';
    } else {
      icon.className = 'bi bi-volume-mute-fill text-secondary';
      text.innerText = 'Suara: Hening';
    }
  }

  function requestNotificationPermission() {
    if (!("Notification" in window)) {
      Swal.fire({
        title: 'Notifikasi Tidak Didukung',
        text: 'Browser Anda tidak mendukung Web Desktop Notification.',
        icon: 'info',
        background: '#13172a',
        color: '#e8e0d0',
      });
      return;
    }

    Notification.requestPermission().then(function(permission) {
      updateNotificationButtonUI();
      if (permission === 'granted') {
        sendWebNotification('Notifikasi Diaktifkan!', 'Genshin Tracker akan memberi tahu jika Resin kamu mendekati batas!');
        playAlertChime();
      }
    });
  }

  function updateNotificationButtonUI() {
    var btnText = document.getElementById('notifyStatusText');
    var btn = document.getElementById('btnRequestNotify');
    if (!btnText || !btn) return;

    if (!("Notification" in window)) {
      btnText.innerText = 'Notif: N/A';
      return;
    }
    if (Notification.permission === 'granted') {
      btnText.innerText = 'Notif: Aktif ?';
      btn.classList.replace('btn-outline-info', 'btn-info');
      btn.classList.add('text-dark');
    } else {
      btnText.innerText = 'Izinkan Notifikasi';
      btn.classList.replace('btn-info', 'btn-outline-info');
      btn.classList.remove('text-dark');
    }
  }

  function sendWebNotification(title, body) {
    if ("Notification" in window && Notification.permission === "granted") {
      new Notification(title, {
        body: body,
        icon: 'https://upload-static.hoyoverse.com/event/2021/02/25/01ba12730bd86c8858c1e2d86c7d150d_5665148762126820826.png',
        silent: true
      });
    }
  }

  function testNotificationAlert() {
    playAlertChime();
    sendWebNotification('?? Peringatan Resin Penuh (Tes)', 'Original Resin Anda sudah mencapai batas 160/200! Segera klaim dan gunakan!');

    if (CURRENT_ACCOUNT_ID) {
      $.ajax({
        url: '/daily-resin/alerts/' + CURRENT_ACCOUNT_ID + '/test',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
        success: function() {
          Swal.fire({
            title: 'Tes Alert Berhasil!',
            text: 'Simulasi notifikasi telah dicatat dan audio chime dibunyikan.',
            icon: 'success',
            background: '#13172a',
            color: '#e8e0d0',
            confirmButtonColor: '#c8aa6e',
          }).then(function() { refreshLiveData(); });
        }
      });
    } else {
      Swal.fire({
        title: 'Tes Audio Berhasil!',
        text: 'Audio chime berbunyi dengan baik.',
        icon: 'success',
        background: '#13172a',
        color: '#e8e0d0',
      });
    }
  }

  function claimDailyCheckinNow() {
    if (!CURRENT_ACCOUNT_ID) return;

    var btn = document.getElementById('btnClaimCheckin');
    var claimIcon = document.getElementById('claimIcon');
    var claimText = document.getElementById('claimBtnText');

    btn.disabled = true;
    claimIcon.className = 'spinner-border spinner-border-sm';
    claimText.innerText = 'Menghubungi HoYoLAB...';

    $.ajax({
      url: '/daily-resin/checkin/' + CURRENT_ACCOUNT_ID,
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
      success: function(resp) {
        claimIcon.className = 'bi bi-check-circle-fill text-success';
        claimText.innerText = 'Sudah Diklaim Hari Ini ?';

        var bEl = document.getElementById('checkinStatusBadge');
        if (bEl) {
          bEl.innerHTML = '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 fs-xs fw-bold"><i class="bi bi-check-lg me-1"></i>SUDAH CHECK-IN</span>';
        }

        if (resp.claimed_count) {
          var cEl = document.getElementById('valClaimedDays');
          if (cEl) cEl.innerText = resp.claimed_count;
        }

        if (resp.reward) {
          playAlertChime();
          Swal.fire({
            title: 'Check-in Berhasil! ??',
            html: '<div class="py-2 text-center"><img src="' + resp.reward.icon + '" style="width: 64px; height: 64px;" class="mb-2"><h5 class="text-gold fw-bold">' + resp.reward.name + ' x' + resp.reward.amount + '</h5><p class="text-secondary small mb-0">' + resp.message + '</p></div>',
            icon: 'success',
            background: '#13172a',
            color: '#e8e0d0',
            confirmButtonColor: '#c8aa6e',
          });
        } else {
          Swal.fire({
            title: 'Info Check-in',
            text: resp.message,
            icon: 'info',
            background: '#13172a',
            color: '#e8e0d0',
            confirmButtonColor: '#c8aa6e',
          });
        }

        refreshLiveData();
      },
      error: function(xhr) {
        btn.disabled = false;
        claimIcon.className = 'bi bi-gift-fill';
        claimText.innerText = '? Klaim Hadiah Hari Ini Sekarang';

        var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat check-in.';
        Swal.fire({
          title: 'Gagal Check-in',
          text: err,
          icon: 'error',
          background: '#13172a',
          color: '#e8e0d0',
          confirmButtonColor: '#ef4444',
        });
      }
    });
  }

  function refreshLiveData() {
    if (!CURRENT_ACCOUNT_ID) return;

    var refreshIcon = document.getElementById('refreshIcon');
    if (refreshIcon) refreshIcon.classList.add('spin-animation');

    $.ajax({
      url: '/daily-resin/data/' + CURRENT_ACCOUNT_ID,
      type: 'GET',
      success: function(resp) {
        if (refreshIcon) refreshIcon.classList.remove('spin-animation');
        if (!resp.success) return;

        var n = resp.notes;
        currentResin = n.current_resin;
        maxResin = n.max_resin;
        remainingResinSecs = n.remaining_resin_recovery_seconds;

        var valCurEl = document.getElementById('valCurrentResin');
        if (valCurEl) valCurEl.innerText = currentResin;
        var valMaxEl = document.getElementById('valMaxResin');
        if (valMaxEl) valMaxEl.innerText = maxResin;

        var pct = Math.min(100, Math.round((currentResin / maxResin) * 100));
        var bar = document.getElementById('barResinProgress');
        if (bar) {
          bar.style.width = pct + '%';
          bar.className = 'progress-bar resin-bar ' + (currentResin >= maxResin ? 'bg-danger' : (currentResin >= 160 ? 'bg-warning' : 'bg-info'));
        }

        if (document.getElementById('valRealmCurr')) {
          document.getElementById('valRealmCurr').innerText = n.current_realm_currency;
          var rPct = Math.min(100, Math.round((n.current_realm_currency / n.max_realm_currency) * 100));
          var rBar = document.getElementById('barRealmProgress');
          if (rBar) rBar.style.width = rPct + '%';
        }

        if (document.getElementById('valCommissions')) {
          document.getElementById('valCommissions').innerText = n.completed_commissions;
        }

        var syncEl = document.getElementById('lastSyncedDisplay');
        if (syncEl) {
          syncEl.innerHTML = 'Terakhir Diperbarui: <strong class="text-gold">' + resp.synced_at + '</strong>';
        }

        if (resp.alert_triggered) {
          playAlertChime();
          sendWebNotification('?? Perhatian: Original Resin Mencapai Batas!', 'Resin akun Anda saat ini: ' + currentResin + '/' + maxResin);
        }
      },
      error: function() {
        if (refreshIcon) refreshIcon.classList.remove('spin-animation');
      }
    });
  }

  function saveAccountSettings(e) {
    e.preventDefault();
    if (!CURRENT_ACCOUNT_ID) return;

    var btn = document.getElementById('btnSaveSettings');
    btn.disabled = true;

    var data = {
      auto_checkin_enabled: document.getElementById('switchAutoCheckin').checked ? 1 : 0,
      resin_alert_enabled: document.getElementById('switchResinAlert').checked ? 1 : 0,
      resin_alert_threshold: parseInt(document.getElementById('rangeThreshold').value),
    };

    $.ajax({
      url: '/daily-resin/settings/' + CURRENT_ACCOUNT_ID,
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
      data: data,
      success: function() {
        btn.disabled = false;
        var thEl = document.getElementById('valThresholdDisplay');
        if (thEl) thEl.innerText = data.resin_alert_threshold + ' Resin';
        var metaEl = document.getElementById('metaThresholdVal');
        if (metaEl) metaEl.innerText = data.resin_alert_threshold + ' / 200';

        Swal.fire({
          title: 'Tersimpan!',
          text: 'Pengaturan Auto Check-in & Resin Alert berhasil disimpan.',
          icon: 'success',
          timer: 1800,
          showConfirmButton: false,
          background: '#13172a',
          color: '#e8e0d0',
        });
      },
      error: function(xhr) {
        btn.disabled = false;
        var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error saat menyimpan.';
        Swal.fire({
          title: 'Gagal Menyimpan',
          text: err,
          icon: 'error',
          background: '#13172a',
          color: '#e8e0d0',
        });
      }
    });
  }

  function markAllAlertsRead() {
    if (!CURRENT_ACCOUNT_ID) return;

    $.ajax({
      url: '/daily-resin/alerts/' + CURRENT_ACCOUNT_ID + '/mark-read',
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
      success: function() {
        var badge = document.getElementById('badgeUnreadCount');
        if (badge) badge.remove();
        document.querySelectorAll('#tableAlerts tr.table-warning').forEach(function(tr) {
          tr.classList.remove('table-warning');
        });
      }
    });
  }
</script>
<style>
  @keyframes spin { 100% { transform: rotate(360deg); } }
  .spin-animation { animation: spin 1s linear infinite; display: inline-block; }
</style>
@endpush
