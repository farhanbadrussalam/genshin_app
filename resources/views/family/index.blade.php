@extends('layout.main')

@section('content')
@include('layout.header')
<div class="d-flex justify-content-center align-items-start">
    <div class="row w-100 justify-content-center mt-3">
        <div class="col-sm-12 col-md-6 col-lg-6 mb-2">
            <div class="position-fixed bottom-0 end-0 m-3 shadow z-1">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahFamily">Create</button>
            </div>
            <ol class="list-group list-group-numbered">
              @foreach($dataFamily as $value)
              <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                  <div class="fw-bold">{{$value->name}}</div>
                </div>
                <button class="btn btn-warning btn-sm p-1 me-1" onclick="editfunction(this)" data-info="{{ $value }}"><i class="bi bi-pencil-square"></i></button>
                <form action="{{ url('family/'.$value->id) }}" method="post">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-sm p-1" type="submit" onclick="return confirm('hapus {{ $value->name }} ?')"><i class="bi bi-trash-fill"></i></button>
                </form>
              </li>
              @endforeach
            </ol>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal" tabindex="-1" id="tambahFamily">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('family.store') }}" method="post">
        @csrf
        <div class="modal-body">
          <div class="row">
              <div class="col-sm-12">
                  <div class="form-floating mb-1">
                      <input type="text" class="form-control" id="nameFamily" name="nameFamily" placeholder="Name family">
                      <label for="nameFamily">Name</label>
                  </div>
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" tabindex="-1" id="editFamily">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <form action="#" method="post" id="formFamilyEdit">
        @csrf
        @method("PUT")
        <div class="modal-body">
          <div class="row">
              <div class="col-sm-12">
                  <div class="form-floating mb-1">
                      <input type="hidden" class="form-control" id="family_id" name="family_id">
                      <input type="text" class="form-control" id="nameFamilyEdit" name="nameFamily" placeholder="Name family">
                      <label for="nameFamilyEdit">Name</label>
                  </div>
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
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

  function removefunction(obj) {
    let data = $(obj).data('info');
    Swal.fire({
      title: `Hapus ${data.name} ?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "{{ url('family') }}/"+data.id,
          method: "DELETE",
          dataType: "JSON",
          data: {
            "_token" : "{{ csrf_token() }}"
          },
          success: (result) => {
            console.log(result);
          }
        })
      }
    })
  }
</script>