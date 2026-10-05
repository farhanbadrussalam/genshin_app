@extends('layout.main')

@section('content')
@php $title = 'Aturan Scoring per Karakter'; @endphp
@include('layout.header')

<div class="scoring-page-container">

  {{-- Header --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.45rem;">
        <i class="bi bi-gear-fill me-2"></i>Aturan Scoring per Karakter
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.84rem; margin: 0;">
        Atur bobot sub-stat untuk menghitung skor artifact tiap karakter
      </p>
    </div>

    {{-- Account Switcher --}}
    @if($accounts->isNotEmpty())
    <form method="GET" action="{{ route('artifact-scoring.rules') }}" id="accountForm">
      <div class="input-group input-group-sm">
        <span class="input-group-text genshin-input-group-text">
          <i class="bi bi-controller text-gold"></i>
        </span>
        <select name="account_id" class="form-select genshin-select" onchange="document.getElementById('accountForm').submit()" style="min-width: 220px;">
          @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" {{ ($activeAccount?->id === $acc->id) ? 'selected' : '' }}>
              {{ $acc->nickname }} (UID: {{ $acc->uid }})
            </option>
          @endforeach
        </select>
      </div>
    </form>
    @endif
  </div>

  {{-- Back to Scoring --}}
  <div class="mb-3">
    <a href="{{ route('artifact-scoring.index', ['account_id' => $activeAccount?->id]) }}" class="back-nav-link">
      <i class="bi bi-arrow-left me-1"></i>Kembali ke Artifact Scoring
    </a>
  </div>

  @if($characters->isEmpty())
    <div class="empty-state text-center py-5">
      <div style="font-size: 3rem; opacity: 0.3;">🎮</div>
      <p class="text-muted mt-2">Tidak ada karakter di inventori akun ini.</p>
    </div>
  @else
    <div class="row g-3 animate-fade-in-up">
      @foreach($characters as $char)
        @php
          $rule = $char->scoringRule;
          $hasRule = $rule !== null;
          $roleColors = [
            'dps' => '#ef4444', 'sub_dps' => '#f97316',
            'support' => '#8b5cf6', 'healer' => '#22c55e',
          ];
          $roleColor = $roleColors[$rule?->role ?? 'dps'] ?? '#e5a029';
          $roleName  = ['dps'=>'DPS','sub_dps'=>'Sub-DPS','support'=>'Support','healer'=>'Healer'][$rule?->role ?? ''] ?? '—';
        @endphp
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="rule-char-card">
            <div class="d-flex align-items-center gap-3 mb-3">
              {{-- Avatar --}}
              <div class="char-avatar-sm">
                @if($char->icon_url)
                  <img src="{{ $char->icon_url }}" alt="{{ $char->name }}" class="char-avatar-img"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;">
                    <i class="bi bi-person-fill text-gold"></i>
                  </div>
                @else
                  <i class="bi bi-person-fill text-gold"></i>
                @endif
              </div>
              <div class="flex-grow-1 min-w-0">
                <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  {{ $char->name }}
                </div>
                <div class="d-flex align-items-center gap-2 mt-0.5">
                  <span style="font-size: 0.72rem; color: {{ $char->element_color }}; font-weight: 600;">{{ $char->element }}</span>
                  @if($hasRule)
                    <span class="role-chip" style="border-color: {{ $roleColor }}44; color: {{ $roleColor }};">
                      {{ $roleName }}
                    </span>
                  @endif
                </div>
              </div>
              {{-- Status --}}
              @if($hasRule)
                <div style="color: #22c55e; font-size: 0.8rem;" title="Aturan sudah diset">
                  <i class="bi bi-check-circle-fill"></i>
                </div>
              @else
                <div style="color: #64748b; font-size: 0.8rem;" title="Belum ada aturan">
                  <i class="bi bi-dash-circle"></i>
                </div>
              @endif
            </div>

            {{-- Stat weights preview (jika ada rule) --}}
            @if($hasRule)
              @php
                $weights = $rule->getWeightsArray();
                $topStats = collect($weights)->filter(fn($v) => $v > 0)->sortByDesc(fn($v) => $v)->take(3);
              @endphp
              <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach($topStats as $stat => $w)
                  <span class="stat-mini-chip">
                    {{ ['crit_rate'=>'CR','crit_dmg'=>'CD','atk_pct'=>'ATK%','hp_pct'=>'HP%','def_pct'=>'DEF%','em'=>'EM','er'=>'ER','flat_atk'=>'fATK','flat_hp'=>'fHP','flat_def'=>'fDEF'][$stat] ?? $stat }}
                    ×{{ number_format($w, 1) }}
                  </span>
                @endforeach
              </div>

              @if($rule->build_note)
                <div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                  <i class="bi bi-pencil-fill me-1"></i>{{ $rule->build_note }}
                </div>
              @endif
            @else
              <div style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                <i class="bi bi-exclamation-circle me-1"></i>Belum ada aturan scoring
              </div>
            @endif

            <a href="{{ route('artifact-scoring.rule', $char->id) }}" class="btn-genshin btn-genshin-sm w-100">
              <i class="bi bi-{{ $hasRule ? 'pencil' : 'plus-circle' }}-fill me-1"></i>
              {{ $hasRule ? 'Edit Aturan' : 'Buat Aturan' }}
            </a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/artifact-scoring.css') }}">
@endpush

@endsection
