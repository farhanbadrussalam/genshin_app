@extends('layout.main')

@section('content')
@php $title = 'Game Accounts'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 2rem; padding-bottom: 4rem;">

  {{-- Flash Message --}}
  @if(session('success'))
    <div class="alert-toast success" id="alertToast">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
  @endif

  {{-- Page Header --}}
  <div class="d-flex align-items-center justify-content-between mb-4 animate-fade-in-up">
    <div>
      <h1 class="font-display text-gold mb-0" style="font-size: 1.3rem; letter-spacing: 0.08em;">
        <i class="bi bi-controller me-2"></i>Game Accounts
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.82rem; margin-top: 0.25rem;">
        Kelola akun game yang ingin kamu tracking inventorinya
      </p>
    </div>
    <button class="btn-genshin btn-genshin-sm" data-bs-toggle="modal" data-bs-target="#modalAddAccount">
      <i class="bi bi-plus-lg me-1"></i>Tambah Akun
    </button>
  </div>

  {{-- Account Cards --}}
  @if($accounts->isEmpty())
    <div class="empty-state animate-fade-in-up">
      <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">🎮</div>
      <p style="color: var(--text-secondary); font-size: 0.9rem;">Belum ada akun game yang ditambahkan.</p>
      <button class="btn-genshin btn-genshin-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalAddAccount">
        <i class="bi bi-plus-lg me-1"></i>Tambah Akun Pertama
      </button>
    </div>
  @else
    <div class="row g-3">
      @foreach($accounts as $account)
        <div class="col-12 col-sm-6 col-lg-4 animate-fade-in-up" style="animation-delay: {{ $loop->index * 0.06 }}s;">
          <div class="account-card">
            {{-- Game Badge --}}
            <div class="account-game-badge game-{{ Str::slug($account->game, '_') }}">
              @if($account->game === 'genshin_impact')
                <span>✦</span>
              @elseif($account->game === 'honkai_star_rail')
                <span>★</span>
              @else
                <span>⬡</span>
              @endif
              {{ $account->game_label }}
            </div>

            {{-- Account Info --}}
            <div class="account-info mt-3">
              <h3 class="account-nickname">{{ $account->nickname }}</h3>
              <div class="account-meta">
                <span class="meta-item">
                  <i class="bi bi-hash"></i> UID: <strong>{{ $account->uid }}</strong>
                </span>
                <span class="meta-item">
                  <i class="bi bi-globe2"></i> {{ $account->server_label }}
                </span>
                @if($account->last_synced_at)
                  <span class="meta-item">
                    <i class="bi bi-arrow-repeat"></i>
                    Sync: {{ $account->last_synced_at->diffForHumans() }}
                  </span>
                @else
                  <span class="meta-item text-muted">
                    <i class="bi bi-arrow-repeat"></i> Belum pernah sync
                  </span>
                @endif
              </div>
              @if($account->notes)
                <p class="account-notes">{{ $account->notes }}</p>
              @endif
            </div>

            {{-- Actions --}}
            <div class="account-actions">
              <a href="{{ route('inventory.characters.index', ['account_id' => $account->id]) }}" class="btn-action btn-action-primary"
                 title="Lihat Inventori Karakter">
                <i class="bi bi-box-seam"></i>
                <span>Inventori</span>
              </a>
              <button class="btn-action btn-action-secondary"
                      title="Edit Akun"
                      onclick="openEditModal({{ $account->id }}, '{{ addslashes($account->nickname) }}', '{{ $account->server }}', '{{ addslashes($account->notes ?? '') }}')">
                <i class="bi bi-pencil"></i>
                <span>Edit</span>
              </button>
              <button class="btn-action btn-action-danger"
                      title="Hapus Akun"
                      onclick="confirmDelete({{ $account->id }}, '{{ addslashes($account->nickname) }}')">
                <i class="bi bi-trash3"></i>
                <span>Hapus</span>
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>

{{-- ===== MODAL: TAMBAH AKUN ===== --}}
<div class="modal fade" id="modalAddAccount" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-genshin">
      <div class="modal-header modal-genshin-header">
        <h5 class="modal-title" id="modalAddLabel">
          <i class="bi bi-plus-circle me-2 text-gold"></i>Tambah Akun Game
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('game-accounts.store') }}" method="POST" id="formAddAccount">
        @csrf
        <div class="modal-body">
          {{-- Game --}}
          <div class="form-group-genshin mb-3">
            <label class="form-label-genshin">Game</label>
            <select name="game" id="selectGame" class="form-select-genshin" required>
              @foreach($games as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>

          {{-- UID --}}
          <div class="form-group-genshin mb-3">
            <label class="form-label-genshin">UID Player</label>
            <input type="text" name="uid" class="form-input-genshin"
                   placeholder="Contoh: 812345678"
                   maxlength="20" required
                   pattern="[0-9]+" title="UID hanya boleh berisi angka">
            <small class="form-hint">UID bisa dilihat di bagian profil dalam game</small>
          </div>

          {{-- Nickname --}}
          <div class="form-group-genshin mb-3">
            <label class="form-label-genshin">Nickname / Nama Akun</label>
            <input type="text" name="nickname" class="form-input-genshin"
                   placeholder="Contoh: AkunUtama" maxlength="100" required>
          </div>

          {{-- Server --}}
          <div class="form-group-genshin mb-3">
            <label class="form-label-genshin">Server</label>
            <select name="server" class="form-select-genshin" required>
              @foreach($servers as $value => $label)
                <option value="{{ $value }}" {{ $value === 'asia' ? 'selected' : '' }}>
                  {{ $label }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Notes --}}
          <div class="form-group-genshin mb-0">
            <label class="form-label-genshin">Catatan <span class="text-muted">(opsional)</span></label>
            <textarea name="notes" class="form-input-genshin" rows="2"
                      placeholder="Contoh: akun f2p, fokus Pyro..."></textarea>
          </div>
        </div>
        <div class="modal-footer modal-genshin-footer">
          <button type="button" class="btn-genshin btn-genshin-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin">
            <i class="bi bi-plus-lg me-1"></i>Tambah Akun
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ===== MODAL: EDIT AKUN ===== --}}
<div class="modal fade" id="modalEditAccount" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-genshin">
      <div class="modal-header modal-genshin-header">
        <h5 class="modal-title">
          <i class="bi bi-pencil me-2 text-gold"></i>Edit Akun Game
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="" method="POST" id="formEditAccount">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group-genshin mb-3">
            <label class="form-label-genshin">Nickname / Nama Akun</label>
            <input type="text" name="nickname" id="editNickname" class="form-input-genshin"
                   maxlength="100" required>
          </div>
          <div class="form-group-genshin mb-3">
            <label class="form-label-genshin">Server</label>
            <select name="server" id="editServer" class="form-select-genshin" required>
              @foreach($servers as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group-genshin mb-0">
            <label class="form-label-genshin">Catatan <span class="text-muted">(opsional)</span></label>
            <textarea name="notes" id="editNotes" class="form-input-genshin" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer modal-genshin-footer">
          <button type="button" class="btn-genshin btn-genshin-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-genshin">
            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Hidden form for delete --}}
<form id="formDelete" action="" method="POST" style="display:none;">
  @csrf
  @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
  // Edit modal
  function openEditModal(id, nickname, server, notes) {
    document.getElementById('editNickname').value = nickname;
    document.getElementById('editServer').value   = server;
    document.getElementById('editNotes').value    = notes;
    document.getElementById('formEditAccount').action = '/game-accounts/' + id;
    new bootstrap.Modal(document.getElementById('modalEditAccount')).show();
  }

  // Confirm delete
  function confirmDelete(id, nickname) {
    Swal.fire({
      title: 'Hapus Akun?',
      html: `Akun <strong>${nickname}</strong> akan dihapus permanen.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      background: 'var(--card-bg)',
      color: 'var(--text-primary)',
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.getElementById('formDelete');
        form.action = '/game-accounts/' + id;
        form.submit();
      }
    });
  }

  // Auto hide toast
  const toast = document.getElementById('alertToast');
  if (toast) {
    setTimeout(() => toast.classList.add('hide'), 3500);
  }
</script>
@endpush
