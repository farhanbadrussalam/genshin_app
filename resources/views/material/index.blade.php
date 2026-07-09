@extends('layout.main')

@section('content')
@include('layout.header')
<div class="d-flex justify-content-center align-items-start">
    <div class="row w-100 justify-content-center mt-3">
        <div class="col-sm-12 col-md-6 col-lg-6 mb-2 p-0">
            <div class="position-fixed bottom-0 end-0 m-3 shadow z-1">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahMaterial">Create</button>
            </div>
            <div class="accordion accordion-flush shadow" id="accordionFlushExample">
                @foreach($dataFamily as $value)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="flush-headingOne{{$value->id}}">
                    <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne{{$value->id}}" aria-expanded="false" aria-controls="flush-collapseOne{{$value->id}}">
                        {{$value->name}} ({{ count($value->material) }})
                    </button>
                    </h2>
                    <div id="flush-collapseOne{{$value->id}}" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body p-1 py-2">
                            <ol class="list-group">
                                @foreach($value->material as $material)
                                <?php
                                    $color = "";
                                    switch ($material->rarity) {
                                        case 1:
                                            $color = '#d7d7d7';
                                            break;
                                        case 2:
                                            $color = '#cdffe7';
                                            break;
                                        case 3:
                                            $color = '#aaecff';
                                            break;
                                        case 4:
                                            $color = '#eccdf5';
                                            break;
                                        case 5:
                                            $color = '#ffe4c4';
                                            break;
                                    }
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-start px-2" style="background-color: {{ $color }};">
                                    <div class="text-center col-2">
                                        <div><img src="{{ $material->images }}" style="width: 3rem;" alt="" class="img-fluid"></div>
                                        <small style="font-size: 10px;">@for($a=0;$a<$material->rarity;$a++)<i class="bi bi-star-fill text-warning"></i>@endfor</small>
                                    </div>
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">{{ $material->name }} <span class="me-1" role="button" onclick="editfunction(this)" data-info="{{ $material }}"><i class="bi bi-pencil-square"></i></span></div>
                                        <figure>
                                            <blockquote>
                                                <small>
                                                @foreach(json_decode($material->daysofweek) as $days)
                                                {{ $days }} 
                                                @endforeach
                                                </small>
                                            </blockquote>
                                            @foreach(json_decode($material->source) as $source)
                                            <figcaption class="blockquote-footer text-muted lh-2" style="font-size: 9px;">
                                            {{ $source }}
                                            </figcaption>
                                            @endforeach
                                        </figure>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $material->amount }}</span>
                                </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal" tabindex="-1" id="tambahMaterial">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
        <form action="{{ route('material.store') }}" method="POST">
            @csrf
            <div class="modal-body">
              <div class="row">
                  <div class="col-sm-12">
                      <div class="input-group mb-1">
                          <div class="form-floating">
                              <input type="text" class="form-control" id="nameMaterial" name="nameMaterial" placeholder="Name material">
                              <input type="hidden" id="dataMaterial" name="dataMaterial">
                              <label for="nameMaterial">Name material</label>
                          </div>
                          <button class="btn btn-info" onclick="searchMaterial(this)"><i class="bi bi-search"></i></button>
                      </div>
                  </div>
                  <div id="resultMaterial" style="display: none;">
                      <div class="col-sm-12 text-center my-1">
                          <img src="" alt="" id="imageMaterial" class="img-thumbnail">
                      </div>
                      <div class="col-sm-12">
                          <div class="form-floating mb-1">
                              <select class="form-select" id="family_id" name="family_id">
                                  @foreach($dataFamily as $value)
                                  <option value="{{ $value->id }}">{{ $value->name }}</option>
                                  @endforeach
                              </select>
                              <label for="family_id">Select family material</label>
                          </div>
                      </div>
                      <div class="col-sm-12">
                          <div class="form-floating mb-1">
                              <input type="number" class="form-control" id="rarity" name="rarity" placeholder="Name material" readonly>
                              <label for="rarity">Rarity</label>
                          </div>
                      </div>
                      <div class="col-sm-12">
                          <div class="form-floating mb-1">
                              <input type="number" class="form-control" id="amount" name="amount" placeholder="Name material" value="0">
                              <label for="amount">Amount</label>
                          </div>
                      </div>
                  </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="btnSave" style="display: none;">Save</button>
            </div>
        </form>
    </div>
  </div>
</div>

<div class="modal" tabindex="-1" id="editMaterial">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
        <form action="#" method="POST" id="formMaterialEdit">
            @csrf
            @method('PUT')
            <div class="modal-body">
              <div class="row">
                    <div class="col-sm-12">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="nameMaterialEdit" name="nameMaterial" placeholder="Name material" readonly>
                            <input type="hidden" class="form-control" id="material_idEdit" name="material_id">
                            <label for="nameMaterialEdit">Name material</label>
                        </div>
                    </div>
                    <div class="col-sm-12 text-center my-1">
                        <img src="" alt="" id="imageMaterialEdit" class="img-thumbnail">
                    </div>
                    <div class="col-sm-12">
                        <div class="form-floating mb-1">
                            <select class="form-select" id="family_idEdit" name="family_id">
                                @foreach($dataFamily as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </select>
                            <label for="family_idEdit">Select family material</label>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-floating mb-1">
                            <input type="number" class="form-control" id="rarityEdit" name="rarity" placeholder="Name material" readonly>
                            <label for="rarityEdit">Rarity</label>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-floating mb-1">
                            <input type="number" class="form-control" id="amountEdit" name="amount" placeholder="Name material" value="0">
                            <label for="amountEdit">Amount</label>
                        </div>
                    </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="btnEdit">Save</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
    function searchMaterial(obj){
        const material_name = $('#nameMaterial').val();
        $.ajax({
            url: `${URL_API_GENSHIN}materials`,
            method: 'GET',
            data: {
                query: material_name,
                matchCategories: true,
                verboseCategories: true,
                resultLanguage: "Indonesia"
            },
            beforeSend: function( xhr ) {
                $(obj).attr("disabled", true);
                $(obj).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`)
            },
            success: function (result) {
                console.log(result);
                $('#dataMaterial').val(JSON.stringify(result));
                $(obj).attr("disabled", false);
                $(obj).html(`<i class="bi bi-search"></i>`);

                $('#nameMaterial').val(result.name);
                $('#rarity').val(result.rarity);
                $('#imageMaterial').attr('src', result.images.fandom ? result.images.fandom : result.images.redirect);

                $('#resultMaterial').show();
                $('#btnSave').show();
            }
        }).fail(function() {
            swal.fire({
                title: "Data tidak ditemukan",
                icon: "warning"
            });
            $(obj).attr("disabled", false);
            $(obj).html(`<i class="bi bi-search"></i>`);
            $('#resultMaterial').hide();
            $('#btnSave').hide();
        })
    }

    function editfunction(obj) {
        let data = $(obj).data('info');

        $('#nameMaterialEdit').val(data.name);
        $('#formMaterialEdit').attr('action', "{{ url('material') }}/"+data.id);
        $('#material_idEdit').val(data.id);
        $('#family_idEdit').val(data.familie_id);
        $('#amountEdit').val(data.amount);
        $('#rarityEdit').val(data.rarity);
        $('#imageMaterialEdit').attr('src', data.images);
        $('#editMaterial').modal('show');
    }
</script>

@endsection
