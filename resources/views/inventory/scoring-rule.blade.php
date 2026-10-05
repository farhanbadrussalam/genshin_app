@extends('layout.main')

@section('content')
@php $title = 'Aturan Scoring Artifact'; @endphp
@include('layout.header')

<div class="scoring-page-container">

  {{-- Back --}}
  <div class="mb-3">
    <a href="{{ route('artifact-scoring.rules') }}" class="back-nav-link">
      <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Karakter
    </a>
  </div>

  {{-- Header --}}
  <div class="d-flex align-items-center gap-3 mb-4 animate-fade-in-up">
    <div class="char-icon-frame">
      @if($character->icon_url)
        <img src="{{ $character->icon_url }}" alt="{{ $character->name }}" class="char-icon-img">
      @else
        <i class="bi bi-person-fill text-gold fs-3"></i>
      @endif
    </div>
    <div>
      <h1 class="font-display text-gold mb-0" style="font-size: 1.4rem; letter-spacing: 0.06em;">
        {{ $character->name }}
      </h1>
      <div class="d-flex align-items-center gap-2 mt-1">
        <span style="color: {{ $character->element_color }}; font-weight: 700; font-size: 0.82rem;">
          {{ $character->element }}
        </span>
        <span class="text-muted" style="font-size: 0.78rem;">•</span>
        <span class="text-muted" style="font-size: 0.78rem;">{{ $character->weapon_type }}</span>
        <span class="text-gold" style="font-size: 0.78rem;">{{ str_repeat('★', $character->rarity) }}</span>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert-genshin-success mb-3 animate-fade-in-up">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
  @endif

  <div class="row g-4">

    {{-- Form Aturan Scoring --}}
    <div class="col-12 col-lg-7">
      <div class="scoring-rule-card animate-fade-in-up">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="text-gold mb-0" style="font-size: 0.95rem;">
            <i class="bi bi-sliders me-2"></i>Bobot Sub-Stat
          </h5>
          {{-- Preset Archetype --}}
          <div class="d-flex align-items-center gap-2">
            <select id="archetypeSelect" class="form-select form-select-sm genshin-select" style="min-width: 200px; font-size: 0.78rem;">
              <option value="">— Pilih Template —</option>
              @foreach($archetypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
            <button class="btn-genshin btn-genshin-sm" id="btnLoadPreset">
              <i class="bi bi-magic me-1"></i>Muat
            </button>
          </div>
        </div>

        <form method="POST" action="{{ route('artifact-scoring.rule.save', $character->id) }}">
          @csrf

          {{-- Bobot Sub-Stats Sliders --}}
          @php
            $statFields = [
              'w_crit_rate' => ['label' => 'CRIT Rate', 'color' => '#ffd700', 'icon' => '⚡'],
              'w_crit_dmg'  => ['label' => 'CRIT DMG',  'color' => '#ef4444', 'icon' => '💥'],
              'w_atk_pct'   => ['label' => 'ATK%',      'color' => '#f97316', 'icon' => '⚔️'],
              'w_hp_pct'    => ['label' => 'HP%',       'color' => '#22c55e', 'icon' => '❤️'],
              'w_def_pct'   => ['label' => 'DEF%',      'color' => '#3b82f6', 'icon' => '🛡️'],
              'w_em'        => ['label' => 'Elem. Mastery', 'color' => '#10b981', 'icon' => '🌿'],
              'w_er'        => ['label' => 'Energy Recharge','color' => '#8b5cf6', 'icon' => '⚡'],
              'w_flat_atk'  => ['label' => 'ATK (Flat)','color' => '#94a3b8', 'icon' => '➕'],
              'w_flat_hp'   => ['label' => 'HP (Flat)', 'color' => '#94a3b8', 'icon' => '➕'],
              'w_flat_def'  => ['label' => 'DEF (Flat)','color' => '#94a3b8', 'icon' => '➕'],
            ];
          @endphp

          <div class="d-flex flex-column gap-3 mb-4">
            @foreach($statFields as $field => $cfg)
              @php $val = old($field, $rule->$field ?? 0); @endphp
              <div class="stat-weight-row">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="stat-label" for="{{ $field }}">
                    <span>{{ $cfg['icon'] }}</span>
                    <span>{{ $cfg['label'] }}</span>
                  </label>
                  <span class="weight-value-display" id="{{ $field }}_display" style="color: {{ $cfg['color'] }};">
                    {{ number_format($val, 2) }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="weight-label-min">0</span>
                  <input type="range" class="weight-slider" id="{{ $field }}" name="{{ $field }}"
                    min="0" max="1" step="0.05" value="{{ $val }}"
                    data-display="{{ $field }}_display"
                    style="--slider-color: {{ $cfg['color'] }};">
                  <span class="weight-label-max">1.0</span>
                </div>
                <div class="weight-bar-visual">
                  <div class="weight-bar-fill" id="{{ $field }}_bar" style="width: {{ $val * 100 }}%; background: {{ $cfg['color'] }};"></div>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Role & Catatan --}}
          <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6">
              <label class="form-label text-muted" style="font-size: 0.8rem;">Role</label>
              <select name="role" class="form-select genshin-select form-select-sm">
                @foreach(['dps' => 'DPS (Main Carry)', 'sub_dps' => 'Sub-DPS', 'support' => 'Support', 'healer' => 'Healer/Tank'] as $rv => $rl)
                  <option value="{{ $rv }}" {{ (old('role', $rule->role ?? 'dps') === $rv) ? 'selected' : '' }}>
                    {{ $rl }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label text-muted" style="font-size: 0.8rem;">Catatan Build</label>
              <input type="text" name="build_note" class="form-control genshin-input form-control-sm"
                value="{{ old('build_note', $rule->build_note) }}"
                placeholder="Contoh: Full Crit → Emblem" maxlength="255">
            </div>
          </div>

          <button class="btn-genshin w-100" type="submit">
            <i class="bi bi-floppy-fill me-2"></i>Simpan Aturan Scoring
          </button>
        </form>
      </div>
    </div>

    {{-- Preview & Panduan --}}
    <div class="col-12 col-lg-5">
      {{-- Panduan --}}
      <div class="scoring-guide-card animate-fade-in-up mb-3">
        <h6 class="text-gold mb-3" style="font-size: 0.88rem;">
          <i class="bi bi-info-circle-fill me-2"></i>Cara Membaca Bobot
        </h6>
        <div class="d-flex flex-column gap-2" style="font-size: 0.78rem; color: var(--text-secondary);">
          <div><span class="text-white fw-bold">1.0</span> — Stat ini sangat penting (CRIT pada DPS)</div>
          <div><span class="text-white fw-bold">0.75</span> — Stat penting tapi tidak utama</div>
          <div><span class="text-white fw-bold">0.5</span> — Berguna sebagai secondary</div>
          <div><span class="text-white fw-bold">0.25</span> — Sedikit berguna</div>
          <div><span class="text-white fw-bold">0.0</span> — Tidak relevan (abaikan)</div>
        </div>
      </div>

      {{-- Rating Scale --}}
      <div class="scoring-guide-card animate-fade-in-up mb-3">
        <h6 class="text-gold mb-3" style="font-size: 0.88rem;">
          <i class="bi bi-award-fill me-2"></i>Skala Rating
        </h6>
        @php
          $ratings = [
            'SS' => ['min' => 55, 'color' => '#ffd700', 'desc' => 'Sempurna — godroll'],
            'S'  => ['min' => 45, 'color' => '#e5a029', 'desc' => 'Sangat Bagus'],
            'A'  => ['min' => 35, 'color' => '#a855f7', 'desc' => 'Bagus'],
            'B'  => ['min' => 25, 'color' => '#3b82f6', 'desc' => 'Cukup'],
            'C'  => ['min' => 15, 'color' => '#10b981', 'desc' => 'Kurang'],
            'D'  => ['min' => 0,  'color' => '#64748b', 'desc' => 'Jelek — perlu ganti'],
          ];
        @endphp
        <div class="d-flex flex-column gap-1">
          @foreach($ratings as $r => $info)
            <div class="d-flex align-items-center gap-2">
              <span class="mini-rating-badge" style="background: {{ $info['color'] }}22; border-color: {{ $info['color'] }}55; color: {{ $info['color'] }};">
                {{ $r }}
              </span>
              <span style="font-size: 0.76rem; color: var(--text-secondary);">
                ≥ {{ $info['min'] }} — {{ $info['desc'] }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Artifact terpasang ke karakter ini --}}
      @php
        $equipped = \App\Models\InventoryArtifact::with('artifactSet')
          ->where('equipped_character_id', $character->id)
          ->get();
      @endphp
      @if($equipped->isNotEmpty())
        <div class="scoring-guide-card animate-fade-in-up">
          <h6 class="text-gold mb-3" style="font-size: 0.88rem;">
            <i class="bi bi-gem me-2"></i>Artifact Terpasang
          </h6>
          <div class="d-flex flex-column gap-2">
            @foreach($equipped as $eq)
              @php
                $slotIcons = ['flower'=>'🌸','plume'=>'🪶','sands'=>'⏳','goblet'=>'🏆','circlet'=>'👑'];
                $rColors = ['SS'=>'#ffd700','S'=>'#e5a029','A'=>'#a855f7','B'=>'#3b82f6','C'=>'#10b981','D'=>'#64748b'];
                $rc = $rColors[$eq->score_rating ?? ''] ?? '#64748b';
              @endphp
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <span>{{ $slotIcons[$eq->slot_key] ?? '💎' }}</span>
                  <div>
                    <div style="font-size: 0.76rem; color: var(--text-primary);">{{ $eq->artifactSet?->name }}</div>
                    <div style="font-size: 0.68rem; color: var(--text-muted);">Lv.{{ $eq->level }} • {{ strtoupper($eq->main_stat_key) }}</div>
                  </div>
                </div>
                @if($eq->score_rating)
                  <span class="mini-rating-badge" style="background: {{ $rc }}22; border-color: {{ $rc }}55; color: {{ $rc }};">
                    {{ $eq->score_rating }}
                  </span>
                @else
                  <span class="mini-rating-badge" style="background: rgba(100,116,139,0.15); border-color: rgba(100,116,139,0.3); color: #64748b;">?</span>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/artifact-scoring.css') }}">
@endpush

@push('scripts')
<script>
// Slider live update
document.querySelectorAll('.weight-slider').forEach(slider => {
  const displayId = slider.dataset.display;
  const barId     = slider.id + '_bar';

  const update = () => {
    const val = parseFloat(slider.value);
    const el  = document.getElementById(displayId);
    const bar = document.getElementById(barId);
    if (el)  el.textContent  = val.toFixed(2);
    if (bar) bar.style.width = (val * 100) + '%';
  };

  slider.addEventListener('input', update);
  update();
});

// Load preset archetype via AJAX
document.getElementById('btnLoadPreset')?.addEventListener('click', async () => {
  const archetype = document.getElementById('archetypeSelect').value;
  if (!archetype) return;

  const res = await fetch(`/artifact-scoring/preset?archetype=${archetype}`);
  const data = await res.json();

  Object.entries(data.weights || {}).forEach(([key, val]) => {
    const fieldName = 'w_' + key;
    const slider = document.getElementById(fieldName);
    if (slider) {
      slider.value = val;
      slider.dispatchEvent(new Event('input'));
    }
  });
});
</script>
@endpush

@endsection
