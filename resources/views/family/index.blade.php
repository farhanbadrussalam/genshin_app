@extends('layout.main')

@section('content')
@php $title = 'Family Material'; @endphp
@include('layout.header')

<div class="page-container" style="padding-top: 1.25rem; padding-bottom: 5rem;">

  <div class="page-header">
    <i class="bi bi-collection-fill"></i> Family Material
  </div>

  <ol class="list-unstyled" id="familyList">
    @foreach($dataFamily as $index => $value)
    <li class="genshin-list-item animate-fade-in-up" style="animation-delay: {{ $index * 0.04 }}s;">
      <span class="item-number">{{ $index + 1 }}</span>
      <span class="item-name">{{ $value->name }}</span>
      <div class="d-flex gap-1 flex-shrink-0">
        <button class="btn btn-warning btn-sm" onclick="editfunction(this)" data-info="{{ $value }}" title="Edit">
          <i class="bi bi-pencil-square"></i>
        </button>
        <form action="{{ url('family/'.$value->id) }}" method="post" style="margin:0;">
          @csrf
          @method('DELETE')
          <button class="btn btn-danger btn-sm" type="submit"
            onclick="return confirm('Hapus {{ $value->name }} ?')" title="Hapus">
            <i class="bi bi-trash-fill"></i>
          </button>
        </form>
      </div>
    </li>
    @endforeach

    @if($dataFamily->isEmpty())
    <li class="text-center py-5" style="color: var(--text-muted);">
      <i class="bi bi-collection" style="font-size: 2.5rem; opacity:0.3;"></i>
      <p class="mt-2 mb-0" style="font-size: 0.85rem;">Belum ada family material</p>
    </li>
    @endif
  </ol>

</div>

{{-- FAB --}}
<div class="fab-container">
  <button type="button" class="btn-fab fab-pulse" data-bs-toggle="modal" data-bs-target="#tambahFamily" title="Tambah Family">
    <i class="bi bi-plus-lg"></i>
  </button>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" tabindex="-1" id="tambahFamily" aria-labelledby="tambahFamilyTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('family.store') }}" method="post">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="tambahFamilyTitle">
            <i class="bi bi-plus-circle me-2"></i>Tambah Family
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="form-floating">
            <input type="text" class="form-control" id="nameFamily" name="nameFamily"
              placeholder="Nama family" required>
            <label for="nameFamily">Nama Family</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" tabindex="-1" id="editFamily" aria-labelledby="editFamilyTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="#" method="post" id="formFamilyEdit">
        @csrf
        @method("PUT")
        <div class="modal-header">
          <h5 class="modal-title" id="editFamilyTitle">
            <i class="bi bi-pencil me-2"></i>Edit Family
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="family_id" name="family_id">
          <div class="form-floating">
            <input type="text" class="form-control" id="nameFamilyEdit" name="nameFamily"
              placeholder="Nama family" required>
            <label for="nameFamilyEdit">Nama Family</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

<script>
  function editfunction(obj) {
    let data = $(obj).data('info');
    $('#nameFamilyEdit').val(data.name);
    $('#formFamilyEdit').attr('action', "{{ url('family') }}/"+data.id);
    $('#family_id').val(data.id);
    $('#editFamily').modal('show');
  }
</script>