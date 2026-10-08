@extends('layout.main')

@section('content')
@php $title = 'Master Musuh'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 1.5rem; padding-bottom: 4rem;">

  {{-- Flash Message --}}
  @if(session('success'))
    <div class="alert-toast success" id="alertToast">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert-toast error" id="alertToast">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    </div>
  @endif

  {{-- Page Header & Stats --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-1" style="font-size: 1.35rem; letter-spacing: 0.08em;">
        <i class="bi bi-crosshair me-2"></i>Master Musuh & Hasil Drop
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.84rem; margin-bottom: 0;">
        Katalog musuh Teyvat, hasil drop material, serta analisis kelemahan & rekomendasi taktik party
      </p>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="char-stat-badge">
        <span class="label">Total</span>
        <span class="val">{{ $stats['total'] }}</span>
      </div>
      <div class="char-stat-badge text-danger" style="border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.08);">
        <span class="label" style="color: #fca5a5;">Weekly</span>
        <span class="val" style="color: #ef4444;">{{ $stats['weekly'] }}</span>
      </div>
      <div class="char-stat-badge text-warning" style="border-color: rgba(234, 179, 8, 0.4); background: rgba(234, 179, 8, 0.08);">
        <span class="label" style="color: #fde047;">Boss</span>
        <span class="val" style="color: #eab308;">{{ $stats['boss'] }}</span>
      </div>
      <div class="char-stat-badge text-info" style="border-color: rgba(14, 165, 233, 0.4); background: rgba(14, 165, 233, 0.08);">
        <span class="label" style="color: #7dd3fc;">Elite</span>
        <span class="val" style="color: #0ea5e9;">{{ $stats['elite'] }}</span>
      </div>
      <div class="char-stat-badge text-secondary" style="border-color: rgba(148, 163, 184, 0.3); background: rgba(148, 163, 184, 0.05);">
        <span class="label">Common</span>
        <span class="val">{{ $stats['common'] }}</span>
      </div>
      <button type="button" class="btn-genshin btn-genshin-sm ms-2" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(249, 115, 22, 0.15)); border-color: rgba(234, 179, 8, 0.5);" data-bs-toggle="modal" data-bs-target="#modalSyncMasterEnemies">
        <i class="bi bi-cloud-arrow-down-fill me-1 text-warning"></i>Sync Database Musuh
      </button>
      <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal" data-bs-target="#modalAddEnemy">
        <i class="bi bi-plus-lg me-1"></i>Tambah Manual
      </button>
    </div>
  </div>

  {{-- Filter & Search Bar --}}
  <div class="filter-panel mb-4 animate-fade-in-up">
    <form method="GET" action="{{ route('enemy.index') }}" id="filterForm">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text genshin-input-group-text"><i class="bi bi-search text-gold"></i></span>
            <input type="text" name="search" class="form-control genshin-input" 
                   placeholder="Cari nama musuh, famili..." 
                   value="{{ $filters['search'] ?? '' }}"
                   onchange="this.form.submit()">
          </div>
        </div>

        <div class="col-6 col-sm-4 col-md-2">
          <select name="category" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
            <option value="all">Semua Kategori</option>
            @foreach($categories as $cat)
              <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>
                {{ $cat }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-sm-4 col-md-2">
          <select name="element" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
            <option value="all">Semua Elemen</option>
            @foreach($elements as $el)
              <option value="{{ $el }}" {{ ($filters['element'] ?? '') === $el ? 'selected' : '' }}>
                {{ $el }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-sm-4 col-md-2">
          <select name="region" class="form-select form-select-sm genshin-select" onchange="this.form.submit()">
            <option value="all">Semua Region</option>
            @foreach($regions as $reg)
              <option value="{{ $reg }}" {{ ($filters['region'] ?? '') === $reg ? 'selected' : '' }}>
                {{ $reg }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-md-2 d-flex gap-2">
          @if(!empty(array_filter($filters ?? [])))
            <a href="{{ route('enemy.index') }}" class="btn btn-sm btn-outline-secondary w-100" style="border-radius: 8px; font-size: 0.8rem;">
              <i class="bi bi-x-circle me-1"></i>Reset
            </a>
          @endif
        </div>
      </div>
    </form>
  </div>

  {{-- Category Quick Pills --}}
  <div class="d-flex flex-wrap gap-2 mb-4 animate-fade-in-up">
    <a href="{{ route('enemy.index', array_merge($filters ?? [], ['category' => 'all'])) }}"
       class="element-badge-pill {{ empty($filters['category']) || $filters['category'] === 'all' ? 'active' : '' }}"
       style="--pill-color: var(--accent-gold);">
      ✦ Semua ({{ $stats['total'] }})
    </a>
    <a href="{{ route('enemy.index', array_merge($filters ?? [], ['category' => 'Weekly Bosses'])) }}"
       class="element-badge-pill {{ ($filters['category'] ?? '') === 'Weekly Bosses' ? 'active' : '' }}"
       style="--pill-color: #ef4444;">
      ☠️ Weekly Bosses ({{ $stats['weekly'] }})
    </a>
    <a href="{{ route('enemy.index', array_merge($filters ?? [], ['category' => 'Normal Bosses'])) }}"
       class="element-badge-pill {{ ($filters['category'] ?? '') === 'Normal Bosses' ? 'active' : '' }}"
       style="--pill-color: #eab308;">
      👑 Normal Bosses ({{ $stats['boss'] }})
    </a>
    <a href="{{ route('enemy.index', array_merge($filters ?? [], ['category' => 'Elite Enemies'])) }}"
       class="element-badge-pill {{ ($filters['category'] ?? '') === 'Elite Enemies' ? 'active' : '' }}"
       style="--pill-color: #0ea5e9;">
      ⚔️ Elite Enemies ({{ $stats['elite'] }})
    </a>
    <a href="{{ route('enemy.index', array_merge($filters ?? [], ['category' => 'Common Enemies'])) }}"
       class="element-badge-pill {{ ($filters['category'] ?? '') === 'Common Enemies' ? 'active' : '' }}"
       style="--pill-color: #94a3b8;">
      👾 Common Enemies ({{ $stats['common'] }})
    </a>
  </div>

  {{-- Enemy Grid --}}
  @if($enemies->isEmpty())
    <div class="empty-state animate-fade-in-up text-center py-5">
      <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;">✦</div>
      <p style="color: var(--text-secondary); font-size: 0.9rem;">Tidak ada musuh yang cocok dengan filter yang dipilih.</p>
      <a href="{{ route('enemy.index') }}" class="btn-genshin btn-genshin-sm mt-2">
        <i class="bi bi-arrow-repeat me-1"></i>Reset Filter
      </a>
    </div>
  @else
    <div class="row g-3">
      @foreach($enemies as $enemy)
        @php
          $elementColor = $enemy->element_color;
          $categoryBadgeStyle = match($enemy->category) {
              'Weekly Bosses' => 'background: linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(185, 28, 28, 0.4)); border: 1px solid rgba(239, 68, 68, 0.6); color: #fca5a5;',
              'Normal Bosses' => 'background: linear-gradient(135deg, rgba(234, 179, 8, 0.2), rgba(202, 138, 4, 0.3)); border: 1px solid rgba(234, 179, 8, 0.6); color: #fef08a;',
              'Elite Enemies' => 'background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(2, 132, 199, 0.25)); border: 1px solid rgba(14, 165, 233, 0.5); color: #bae6fd;',
              default         => 'background: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.3); color: #cbd5e1;',
          };
        @endphp
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 animate-fade-in-up" style="animation-delay: {{ ($loop->index % 12) * 0.03 }}s;">
          <div class="enemy-card">
            {{-- Category Badge --}}
            <span class="enemy-category-badge" style="{{ $categoryBadgeStyle }}">
              {{ $enemy->category }}
            </span>

            {{-- Action Buttons Top Right --}}
            <div class="enemy-actions">
              <button class="char-action-btn edit btn-edit-enemy" 
                      title="Edit Musuh"
                      data-enemy="{{ json_encode($enemy) }}">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <form action="{{ route('enemy.destroy', $enemy) }}" method="POST" class="d-inline form-delete">
                @csrf
                @method('DELETE')
                <button type="button" class="char-action-btn delete btn-delete-enemy" data-name="{{ $enemy->name }}" title="Hapus Musuh">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </form>
            </div>

            {{-- Enemy Avatar Box --}}
            <div class="enemy-avatar-wrapper text-center my-2">
              <div class="enemy-avatar-ring" style="--element-glow: {{ $elementColor }};">
                @if($enemy->icon_url)
                  <img src="{{ $enemy->icon_url }}" 
                       alt="{{ $enemy->name }}" 
                       class="enemy-avatar"
                       loading="lazy"
                       referrerpolicy="no-referrer"
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class="enemy-avatar-placeholder" style="display: none; background: {{ $elementColor }}20;">
                    <i class="bi bi-crosshair text-gold fs-2"></i>
                  </div>
                @else
                  <div class="enemy-avatar-placeholder" style="background: {{ $elementColor }}20;">
                    <i class="bi bi-crosshair text-gold fs-2"></i>
                  </div>
                @endif
              </div>
            </div>

            {{-- Enemy Info --}}
            <div class="enemy-body">
              <h3 class="enemy-name" title="{{ $enemy->name }}">{{ $enemy->name }}</h3>
              
              <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
                @if($enemy->family)
                  <span class="badge-mini bg-dark border border-secondary text-light">{{ $enemy->family }}</span>
                @endif
                @if($enemy->region)
                  <span class="badge-mini bg-dark border border-secondary text-info"><i class="bi bi-geo-alt-fill me-1"></i>{{ $enemy->region }}</span>
                @endif
              </div>

              {{-- Elements Tag --}}
              <div class="d-flex flex-wrap gap-1 mb-3">
                @if(!empty($enemy->elements) && is_array($enemy->elements))
                  @foreach($enemy->elements as $elm)
                    <span class="badge-mini-element" data-element="{{ strtolower($elm) }}">
                      {{ $elm }}
                    </span>
                  @endforeach
                @else
                  <span class="badge-mini-element text-muted">Physical / None</span>
                @endif
              </div>

              {{-- Drops Strip Preview --}}
              <div class="enemy-drops-section mb-3">
                <div class="section-micro-title d-flex justify-content-between align-items-center mb-1">
                  <span><i class="bi bi-gem me-1 text-gold"></i>Hasil Drop:</span>
                  <small class="text-muted">{{ $enemy->drops->count() }} item</small>
                </div>
                <div class="d-flex flex-wrap gap-1 enemy-drops-strip">
                  @forelse($enemy->drops->take(4) as $drop)
                    <span class="drop-chip" style="border-color: {{ $drop->rarity_color }};" title="{{ $drop->name }} (★{{ $drop->rarity }})">
                      <span class="star-dot" style="background: {{ $drop->rarity_color }};"></span>
                      {{ Str::limit($drop->name, 14) }}
                    </span>
                  @empty
                    <small class="text-muted" style="font-size: 0.72rem;">Belum ada data drop</small>
                  @endforelse
                  @if($enemy->drops->count() > 4)
                    <span class="drop-chip more">+{{ $enemy->drops->count() - 4 }}</span>
                  @endif
                </div>
              </div>

              {{-- Strategy / Weakness Teaser --}}
              @if(!empty($enemy->weakness_elements) || !empty($enemy->immunities) || !empty($enemy->recommended_mechanics))
                <div class="strategy-teaser-box mb-3">
                  @if(!empty($enemy->weakness_elements))
                    <div class="d-flex align-items-center gap-1 mb-1">
                      <small class="text-success fw-bold" style="font-size: 0.7rem;">Kelemahan:</small>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach(array_slice($enemy->weakness_elements, 0, 3) as $w)
                          <span class="badge-weakness">{{ $w }}</span>
                        @endforeach
                      </div>
                    </div>
                  @endif

                  @if(!empty($enemy->immunities))
                    <div class="d-flex align-items-center gap-1">
                      <small class="text-danger fw-bold" style="font-size: 0.7rem;">Kebal:</small>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($enemy->immunities as $im)
                          <span class="badge-immunity">{{ $im }}</span>
                        @endforeach
                      </div>
                    </div>
                  @endif
                </div>
              @endif

              {{-- Action Buttons --}}
              <div class="mt-auto d-flex flex-column gap-1">
                <a href="{{ route('party.index', ['enemy_id' => $enemy->id]) }}" class="btn-party-enemy text-decoration-none">
                  <i class="bi bi-shield-fill-check me-1"></i>Analisis Party Lawan Ini
                </a>
                <button type="button" class="btn-detail-enemy" 
                        data-id="{{ $enemy->id }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#modalDetailEnemy">
                  <i class="bi bi-info-circle me-1"></i>Detail & Drops
                </button>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
      {{ $enemies->links('pagination::bootstrap-5') }}
    </div>
  @endif

</div>
{{-- ─── MODAL DETAIL MUSUH & TAKTIK ─────────────────────────────────────────── --}}
<div class="modal fade" id="modalDetailEnemy" tabindex="-1" aria-labelledby="modalDetailEnemyLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content genshin-modal">
      <div class="modal-header border-secondary">
        <h5 class="modal-title font-display text-gold" id="modalDetailEnemyLabel">
          <i class="bi bi-crosshair me-2"></i><span id="detailEnemyName">Detail Musuh</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="detailModalBody">
        <div class="text-center py-4" id="detailLoading">
          <div class="spinner-border text-warning" role="status"></div>
          <p class="text-muted mt-2" style="font-size: 0.85rem;">Memuat data musuh...</p>
        </div>

        <div id="detailContent" style="display: none;">
          <div class="d-flex flex-column flex-sm-row align-items-center gap-3 mb-4 p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
            <div class="enemy-avatar-ring modal-avatar" style="--element-glow: var(--accent-gold);">
              <img id="detailEnemyIcon" src="" alt="" class="enemy-avatar">
            </div>
            <div class="flex-grow-1 text-center text-sm-start">
              <h4 class="font-display text-gold mb-1" id="detailTitle"></h4>
              <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-1 mb-2">
                <span class="badge bg-secondary" id="detailCategory"></span>
                <span class="badge bg-dark border border-secondary" id="detailFamily"></span>
                <span class="badge bg-dark border border-secondary text-info" id="detailRegion"></span>
                <span class="badge bg-dark border border-secondary text-warning" id="detailMora"></span>
              </div>
              <p class="text-muted small mb-0" id="detailDescription" style="line-height: 1.4;"></p>
            </div>
          </div>

          <ul class="nav nav-tabs border-secondary mb-3" id="enemyDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active text-light" id="drops-tab" data-bs-toggle="tab" data-bs-target="#drops-pane" type="button" role="tab">
                <i class="bi bi-gem me-1 text-gold"></i>Hasil Drop Item (<span id="dropsCountBadge">0</span>)
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link text-light" id="tactics-tab" data-bs-toggle="tab" data-bs-target="#tactics-pane" type="button" role="tab">
                <i class="bi bi-shield-shaded me-1 text-warning"></i>Analisis Kelemahan & Strategi
              </button>
            </li>
          </ul>

          <div class="tab-content" id="enemyDetailTabContent">
            <div class="tab-pane fade show active" id="drops-pane" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-dark table-hover align-middle" style="background: transparent;">
                  <thead>
                    <tr class="text-gold" style="font-size: 0.8rem; border-color: rgba(255,255,255,0.1);">
                      <th>Item Drop</th>
                      <th>Kelangkaan</th>
                      <th>Min. Level</th>
                      <th>Catatan Sumber</th>
                    </tr>
                  </thead>
                  <tbody id="detailDropsTableBody" style="font-size: 0.85rem;"></tbody>
                </table>
              </div>
            </div>

            <div class="tab-pane fade" id="tactics-pane" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="tactics-card">
                    <h6 class="text-success mb-2"><i class="bi bi-bullseye me-1"></i>Elemen Kelemahan / Counter</h6>
                    <div id="detailWeaknessList" class="d-flex flex-wrap gap-1 mb-3"></div>

                    <h6 class="text-danger mb-2"><i class="bi bi-shield-x me-1"></i>Imunitas (Kebal)</h6>
                    <div id="detailImmunityList" class="d-flex flex-wrap gap-1 mb-3"></div>

                    <h6 class="text-info mb-2"><i class="bi bi-gear-wide-connected me-1"></i>Rekomendasi Mekanik</h6>
                    <div id="detailMechanicsList" class="d-flex flex-wrap gap-1"></div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="tactics-card h-100">
                    <h6 class="text-gold mb-2"><i class="bi bi-lightbulb-fill me-1"></i>Tips & Strategi Menghadapi</h6>
                    <p id="detailTipsStrategy" class="text-secondary small mb-0" style="white-space: pre-line; line-height: 1.5;"></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
{{-- ─── MODAL SYNC DATABASE MUSUH ────────────────────────────────────────── --}}
<div class="modal fade" id="modalSyncMasterEnemies" tabindex="-1" aria-labelledby="modalSyncMasterEnemiesLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content genshin-modal">
      <div class="modal-header border-secondary">
        <h5 class="modal-title font-display text-gold" id="modalSyncMasterEnemiesLabel">
          <i class="bi bi-cloud-arrow-down-fill me-2 text-warning"></i>Sync Database Musuh
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('enemy.sync-all') }}" method="POST" id="formSyncEnemies">
        @csrf
        <div class="modal-body p-4 text-center">
          <div class="mb-3">
            <i class="bi bi-crosshair text-gold" style="font-size: 3rem;"></i>
          </div>
          <h5 class="text-light mb-2">Sinkronisasi Otomatis dari API Genshin</h5>
          <p class="text-secondary small mb-0">
            Sistem akan menarik data musuh (Common, Elite, Boss, Weekly Boss) beserta item drop-nya dari Genshin API dan menyimpannya ke database lokal.
          </p>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm" id="btnSubmitSync">
            <i class="bi bi-arrow-repeat me-1"></i>Mulai Sinkronisasi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ─── MODAL TAMBAH MUSUH MANUAL ─────────────────────────────────────────── --}}
<div class="modal fade" id="modalAddEnemy" tabindex="-1" aria-labelledby="modalAddEnemyLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content genshin-modal">
      <div class="modal-header border-secondary">
        <h5 class="modal-title font-display text-gold" id="modalAddEnemyLabel">
          <i class="bi bi-plus-circle me-2"></i>Tambah Musuh Manual
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('enemy.store') }}" method="POST">
        @csrf
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small text-gold">Nama Musuh <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control genshin-input" required placeholder="Contoh: Ruin Guard">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-gold">Kategori <span class="text-danger">*</span></label>
              <select name="category" class="form-select genshin-select" required>
                @foreach($categories as $cat)
                  <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small text-gold">Famili / Faksi</label>
              <input type="text" name="family" class="form-control genshin-input" placeholder="Contoh: Automatons, Fatui">
            </div>
            <div class="col-md-4">
              <label class="form-label small text-gold">Region</label>
              <select name="region" class="form-select genshin-select">
                @foreach($regions as $reg)
                  <option value="{{ $reg }}">{{ $reg }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small text-gold">Mora Gained</label>
              <input type="number" name="mora_gained" class="form-control genshin-input" placeholder="200" value="0">
            </div>
            <div class="col-md-12">
              <label class="form-label small text-gold">URL Ikon / Gambar</label>
              <input type="url" name="icon_url" class="form-control genshin-input" placeholder="https://genshin.jmp.blue/enemies/...">
            </div>
            <div class="col-md-12">
              <label class="form-label small text-gold">Elemen (Centang yang sesuai)</label>
              <div class="d-flex flex-wrap gap-3">
                @foreach($elements as $el)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="elements[]" value="{{ $el }}" id="elAdd_{{ $el }}">
                    <label class="form-check-label text-light small" for="elAdd_{{ $el }}">{{ $el }}</label>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="col-md-12">
              <label class="form-label small text-gold">Deskripsi Lore</label>
              <textarea name="description" class="form-control genshin-input" rows="2" placeholder="Deskripsi latar belakang musuh..."></textarea>
            </div>
            <div class="col-md-12">
              <label class="form-label small text-gold">Tips & Taktik Pertarungan</label>
              <textarea name="tips_strategy" class="form-control genshin-input" rows="2" placeholder="Panduan strategi cara mengalahkannya..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">Simpan Musuh</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ─── MODAL EDIT MUSUH ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalEditEnemy" tabindex="-1" aria-labelledby="modalEditEnemyLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content genshin-modal">
      <div class="modal-header border-secondary">
        <h5 class="modal-title font-display text-gold" id="modalEditEnemyLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Data Musuh
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST" id="formEditEnemy">
        @csrf
        @method('PUT')
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small text-gold">Nama Musuh <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editName" class="form-control genshin-input" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-gold">Slug Identitas <span class="text-danger">*</span></label>
              <input type="text" name="slug" id="editSlug" class="form-control genshin-input" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-gold">Kategori <span class="text-danger">*</span></label>
              <select name="category" id="editCategory" class="form-select genshin-select" required>
                @foreach($categories as $cat)
                  <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small text-gold">Famili</label>
              <input type="text" name="family" id="editFamily" class="form-control genshin-input">
            </div>
            <div class="col-md-3">
              <label class="form-label small text-gold">Region</label>
              <select name="region" id="editRegion" class="form-select genshin-select">
                @foreach($regions as $reg)
                  <option value="{{ $reg }}">{{ $reg }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label small text-gold">URL Ikon / Gambar</label>
              <input type="url" name="icon_url" id="editIconUrl" class="form-control genshin-input">
            </div>
            <div class="col-md-12">
              <label class="form-label small text-gold">Tips Strategi</label>
              <textarea name="tips_strategy" id="editTipsStrategy" class="form-control genshin-input" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin btn-genshin-sm">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
@push('styles')
<style>
/* ─── Styles Scoped Master Musuh ─────────────────────────────────────── */
.enemy-card {
  background: var(--bg-card, #161926);
  border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
  border-radius: 12px;
  padding: 1rem;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.enemy-card:hover {
  transform: translateY(-3px);
  border-color: rgba(234, 179, 8, 0.4);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
}

.enemy-category-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  font-size: 0.68rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  letter-spacing: 0.03em;
}

.enemy-actions {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  gap: 0.35rem;
  z-index: 2;
}

.enemy-avatar-ring {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  margin: 0 auto;
  padding: 4px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.3) 100%);
  border: 2px solid var(--element-glow, #eab308);
  box-shadow: 0 0 14px var(--element-glow, #eab308) 33;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.enemy-avatar {
  width: 100%;
  height: 100%;
  object-fit: contain;
  transition: transform 0.2s ease;
}

.enemy-card:hover .enemy-avatar {
  transform: scale(1.08);
}

.enemy-avatar-placeholder {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.enemy-body {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.enemy-name {
  font-family: var(--font-display, inherit);
  font-size: 1rem;
  color: var(--text-primary, #f3f4f6);
  font-weight: 700;
  margin-bottom: 0.4rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.badge-mini {
  font-size: 0.68rem;
  padding: 0.2rem 0.45rem;
  border-radius: 4px;
}

.badge-mini-element {
  font-size: 0.68rem;
  font-weight: 600;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #f1f5f9;
}

.badge-mini-element[data-element="pyro"] { border-color: rgba(239, 68, 68, 0.5); color: #fca5a5; }
.badge-mini-element[data-element="hydro"] { border-color: rgba(6, 182, 212, 0.5); color: #67e8f9; }
.badge-mini-element[data-element="anemo"] { border-color: rgba(16, 185, 129, 0.5); color: #6ee7b7; }
.badge-mini-element[data-element="electro"] { border-color: rgba(168, 85, 247, 0.5); color: #d8b4fe; }
.badge-mini-element[data-element="dendro"] { border-color: rgba(132, 204, 22, 0.5); color: #bef264; }
.badge-mini-element[data-element="cryo"] { border-color: rgba(56, 189, 248, 0.5); color: #7dd3fc; }
.badge-mini-element[data-element="geo"] { border-color: rgba(234, 179, 8, 0.5); color: #fde047; }

.section-micro-title {
  font-size: 0.72rem;
  color: var(--text-secondary, #94a3b8);
  font-weight: 600;
}

.enemy-drops-strip {
  min-height: 28px;
}

.drop-chip {
  font-size: 0.7rem;
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid #94a3b8;
  color: #e2e8f0;
  padding: 0.15rem 0.45rem;
  border-radius: 5px;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  max-width: 100%;
}

.drop-chip .star-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.drop-chip.more {
  border-color: rgba(255, 255, 255, 0.2);
  color: #94a3b8;
}

.strategy-teaser-box {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 6px;
  padding: 0.45rem 0.6rem;
}

.badge-weakness {
  font-size: 0.65rem;
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.4);
  color: #86efac;
  padding: 0.1rem 0.35rem;
  border-radius: 4px;
}

.badge-immunity {
  font-size: 0.65rem;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.4);
  color: #fca5a5;
  padding: 0.1rem 0.35rem;
  border-radius: 4px;
}

.btn-detail-enemy {
  background: linear-gradient(135deg, rgba(234, 179, 8, 0.12), rgba(202, 138, 4, 0.18));
  border: 1px solid rgba(234, 179, 8, 0.4);
  color: var(--accent-gold, #eab308);
  font-size: 0.78rem;
  font-weight: 600;
  padding: 0.45rem 0.75rem;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.btn-party-enemy {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(234, 179, 8, 0.25));
  border: 1px solid rgba(234, 179, 8, 0.6);
  color: #fef08a;
  font-size: 0.76rem;
  font-weight: 600;
  padding: 0.35rem 0.6rem;
  border-radius: 8px;
  text-align: center;
  transition: all 0.2s ease;
  display: block;
}
.btn-party-enemy:hover {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.35), rgba(234, 179, 8, 0.45));
  border-color: #facc15;
  color: #fff;
}
.btn-detail-enemy:hover {
  background: linear-gradient(135deg, rgba(234, 179, 8, 0.25), rgba(202, 138, 4, 0.35));
  border-color: rgba(234, 179, 8, 0.8);
  color: #fff;
}

.modal-avatar {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
}

.tactics-card {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  padding: 1rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ─── Modal Detail Musuh (AJAX) ──────────────────────────────────────────────
  const modalDetail = document.getElementById('modalDetailEnemy');
  if (modalDetail) {
    modalDetail.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const enemyId = button.getAttribute('data-id');

      const loadingDiv = document.getElementById('detailLoading');
      const contentDiv = document.getElementById('detailContent');
      loadingDiv.style.display = 'block';
      contentDiv.style.display = 'none';

      fetch(`/enemy/${enemyId}`)
        .then(res => res.json())
        .then(data => {
          if (!data.success) return;
          const e = data.data;

          document.getElementById('detailEnemyName').textContent = e.name;
          document.getElementById('detailTitle').textContent = e.name;
          document.getElementById('detailCategory').textContent = e.category || '-';
          document.getElementById('detailFamily').textContent = e.family || 'Famili Tidak Diketahui';
          document.getElementById('detailRegion').textContent = (e.region || 'Global');
          document.getElementById('detailMora').textContent = (e.mora_gained ? `${e.mora_gained} Mora` : '0 Mora');
          document.getElementById('detailDescription').textContent = e.description || 'Tidak ada deskripsi latar belakang.';

          const iconImg = document.getElementById('detailEnemyIcon');
          if (e.icon_url) {
            iconImg.src = e.icon_url;
            iconImg.style.display = 'block';
          } else {
            iconImg.style.display = 'none';
          }

          // Drops Table
          const dropsBody = document.getElementById('detailDropsTableBody');
          dropsBody.innerHTML = '';
          const drops = e.drops || [];
          document.getElementById('dropsCountBadge').textContent = drops.length;

          if (drops.length === 0) {
            dropsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada item drop terdaftar.</td></tr>';
          } else {
            drops.forEach(d => {
              const stars = '★'.repeat(d.rarity || 1);
              const color = d.rarity_color || '#94a3b8';
              dropsBody.innerHTML += `
                <tr>
                  <td class="fw-bold" style="color: ${color};">
                    ${d.name}
                  </td>
                  <td>
                    <span style="color: ${color}; font-weight: 700;">${stars}</span>
                  </td>
                  <td>${d.minimum_level ? `Lv. ${d.minimum_level}+` : 'Semua Level'}</td>
                  <td class="text-muted small">${d.source_note || '-'}</td>
                </tr>
              `;
            });
          }

          // Kelemahan (Weakness)
          const weaknessDiv = document.getElementById('detailWeaknessList');
          weaknessDiv.innerHTML = '';
          if (e.weakness_elements && e.weakness_elements.length > 0) {
            e.weakness_elements.forEach(w => {
              weaknessDiv.innerHTML += `<span class="badge bg-success bg-opacity-25 border border-success text-success-emphasis me-1">${w}</span>`;
            });
          } else {
            weaknessDiv.innerHTML = '<span class="text-muted small">Tidak ada data kelemahan khusus.</span>';
          }

          // Imunitas
          const immunityDiv = document.getElementById('detailImmunityList');
          immunityDiv.innerHTML = '';
          if (e.immunities && e.immunities.length > 0) {
            e.immunities.forEach(im => {
              immunityDiv.innerHTML += `<span class="badge bg-danger bg-opacity-25 border border-danger text-danger-emphasis me-1">${im}</span>`;
            });
          } else {
            immunityDiv.innerHTML = '<span class="text-muted small">Tidak memiliki imunitas absolut.</span>';
          }

          // Rekomendasi Mekanik
          const mechDiv = document.getElementById('detailMechanicsList');
          mechDiv.innerHTML = '';
          if (e.recommended_mechanics && e.recommended_mechanics.length > 0) {
            e.recommended_mechanics.forEach(m => {
              mechDiv.innerHTML += `<span class="badge bg-info bg-opacity-25 border border-info text-info me-1">${m}</span>`;
            });
          } else {
            mechDiv.innerHTML = '<span class="text-muted small">Tidak ada syarat mekanik spesifik.</span>';
          }

          // Tips Strategi
          document.getElementById('detailTipsStrategy').textContent = e.tips_strategy || 'Belum ada catatan taktik pertarungan khusus untuk musuh ini.';

          loadingDiv.style.display = 'none';
          contentDiv.style.display = 'block';
        })
        .catch(err => {
          loadingDiv.innerHTML = '<p class="text-danger">Gagal memuat detail musuh.</p>';
        });
    });
  }

  // ─── Modal Edit Musuh ───────────────────────────────────────────────────────
  document.querySelectorAll('.btn-edit-enemy').forEach(btn => {
    btn.addEventListener('click', function () {
      const e = JSON.parse(this.getAttribute('data-enemy'));
      document.getElementById('formEditEnemy').action = `/enemy/${e.id}`;
      document.getElementById('editName').value = e.name || '';
      document.getElementById('editSlug').value = e.slug || '';
      document.getElementById('editCategory').value = e.category || 'Common Enemies';
      document.getElementById('editFamily').value = e.family || '';
      document.getElementById('editRegion').value = e.region || 'Global';
      document.getElementById('editIconUrl').value = e.icon_url || '';
      document.getElementById('editTipsStrategy').value = e.tips_strategy || '';

      const modal = new bootstrap.Modal(document.getElementById('modalEditEnemy'));
      modal.show();
    });
  });

  // ─── Delete Confirmation (SweetAlert) ───────────────────────────────────────
  document.querySelectorAll('.btn-delete-enemy').forEach(btn => {
    btn.addEventListener('click', function () {
      const form = this.closest('form');
      const name = this.getAttribute('data-name');

      Swal.fire({
        title: 'Hapus Musuh?',
        text: `Apakah Anda yakin ingin menghapus "${name}" beserta seluruh data drop-nya?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });

  // ─── Loading State pada Form Sync ───────────────────────────────────────────
  const formSync = document.getElementById('formSyncEnemies');
  if (formSync) {
    formSync.addEventListener('submit', function () {
      const btn = document.getElementById('btnSubmitSync');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mensinkronisasi API...';
    });
  }
});
</script>
@endpush