@extends('layout.main')

@section('content')
@include('layout.header')
<div class="d-flex justify-content-center align-items-start">
    <div class="row w-100 justify-content-center mt-3">
        <div class="col-sm-12 col-md-6 col-lg-6 mb-2">
            <div class="position-fixed bottom-0 end-0 m-3 shadow z-1">
                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#tambahTask">Create</button>
            </div>
            <ol class="list-group">
                @foreach($dataTask as $key => $task)
                <li class="list-group-item mb-1 shadow">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">{{ $task->nama_task }}</div>
                        </div>
                        <div class="ms-2 me-auto">{{$task->prioritas}}</div>
                        <div>
                            {{$task->jenis}}
                        </div>
                    </div>
                    <div class=" mt-1">
                        @foreach($task->sub_task as $sub_task)
                        <?php
                            $dimiliki = $sub_task->material->amount;
                            if(isset($sub_task->material->hasilCraft)){
                                $dimiliki += $sub_task->material->hasilCraft;
                            }
                            $dibutuhkan = $sub_task->amount;
                        ?>
                        <div class="mb-2 border rounded p-1">
                            <div class="d-flex justify-content-between align-items-start align-items-center">
                                <img src="{{$sub_task->material->images}}" role="button" alt="" class="img-thumbnail" style="width: 50px;" data-subtask="{{$sub_task}}" onclick="showMaterial(this)">
                                <div class="flex-fill ps-2 text-truncate">
                                    <div class="">{{$sub_task->material->name}}</div>
                                    <small class="text-muted">
                                        @foreach(json_decode($sub_task->material->daysofweek) as $days)
                                        {{ $days }} 
                                        @endforeach
                                    </small>
                                    <div><small>{{ isset(json_decode($sub_task->material->source)[0]) ? json_decode($sub_task->material->source)[0] : '' }}</small></div>
                                </div>
                                <small class="badge @if($dimiliki >= $dibutuhkan) text-bg-success @else text-bg-secondary @endif text-white">{{$dimiliki}} - {{ $sub_task->material->amount }}/{{$sub_task->amount}}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ url('taskComplete/'.$task->id) }}" onclick="return confirm('Upgrade {{ $task->nama_task }} ?')" class="btn btn-primary btn-sm @if(!$task->statusUpgrade) disabled @endif" >Upgrade</a>
                        <form action="{{ url('task/'.$task->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm ms-1" type="submit" onclick="return confirm('hapus {{ $task->nama_task }} ?')"><i class="bi bi-trash-fill"></i></button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>

<div class="modal" id="tambahTask" tabindex="-1" aria-labelledby="tambahTask" aria-modal="true" role="dialog">
    <form action="{{ route('task.store') }}" method="POST">
        @csrf
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-floating mb-1">
                                <select class="form-select" id="jenis_task" name="jenis_task"
                                    onchange="jenisTaskChange(this)">
                                    <option value="stat">Stat</option>
                                    <option value="talent">Talent</option>
                                    <option value="weapon">Weapon</option>
                                </select>
                                <label for="jenis_task">Select Jenis Task</label>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="input-group mb-1">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nameTask" name="nameTask"
                                        placeholder="Name material">
                                    <input type="hidden" id="dataMaterial" name="dataMaterial">
                                    <label for="nameTask">Name task</label>
                                </div>
                                <button class="btn btn-info" onclick="search(this)"><i
                                        class="bi bi-search"></i></button>
                            </div>
                        </div>
                        <div id="resultTask" style="display: none;">
                            <div class="col-sm-12 text-center my-1">
                                <img src="" alt="" style="width: 5rem;" id="imageTask" class="img-thumbnail">
                                <input type="hidden" id="urlImage" name="urlImage">
                            </div>
                            <div class="col-sm-12">
                                <div class="form-floating mb-1">
                                    <input type="number" class="form-control" id="prioritas" name="prioritas"
                                        placeholder="Prioritas">
                                    <label for="prioritas">Prioritas</label>
                                </div>
                            </div>
                            <hr>
                            <div class="col-sm-12 text-center">
                                <select class="form-select" style="width: 100%;" data-control="select2"
                                    id="material_name" name="material_name">
                                    <option value="">-- Pilih Material --</option>
                                    @foreach($dataMaterial as $material)
                                    <option value="{{ $material->id }}">{{ $material->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-success my-2" type="button" data-type="tambah"
                                    onclick="btnMaterial(this)">Tambah</button>
                            </div>
                            <div id="material_list" class="border rounded border-success border-opacity-75 p-2"
                                style="display: none;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSave" style="display: none;">Save</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal" tabindex="-1" id="modalDetailMaterial">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="titleModal">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="materialCrafting">
            
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" tabindex="-1" id="modalRangeCraft" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
        <form action="{{ route('craftingBuild') }}" method="post">
            @csrf
            <div class="modal-body">
              <label for="formRangeCraft" class="form-label">Craft material <span id="titleCraftModal"></span></label>
              <input type="hidden" class="w-100" id="formidmaterial" name="formidmaterial">
              <input type="range" class="w-100" min="0" step="1" id="formRangeCraft" name="formRangeCraft">
              <p>
                  Hasil Craft : <output id="valueCraft"></output>
              </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="$('#modalDetailMaterial').modal('show')">Close</button>
              <button class="btn btn-primary" type="submit">Ok</button>
            </div>
        </form>
    </div>
  </div>
</div>

<div class="modal" tabindex="-1" id="modalEditMaterial" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
        <form action="{{ route('editMaterial') }}" method="post">
            @csrf
            <div class="modal-body">
              <label for="formAmountMaterial" class="form-label">Tambah material <span id="titleMaterialModal"></span></label>
              <input type="hidden" class="w-100" id="formidUpdatematerial" name="formidUpdatematerial">
              <input type="number" class="form-control" id="formAmountMaterial" name="formAmountMaterial" value="0">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="$('#modalDetailMaterial').modal('show')">Close</button>
              <button class="btn btn-primary" type="submit">Ok</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
    $(document).ready(function () {
        window.jenisTask = 'characters';
        $('#material_name').select2({
            dropdownParent: $('#tambahTask')
        });
        window.materialNo = 0;
    })

    function btnMaterial(obj) {
        const type = $(obj).data('type');
        if (type == "tambah") {
            let name = $('#material_name').val();
            if (name != "") {
                let content = '';
                let text = $( "#material_name option:selected" ).text();
                content = `
                    <div class="col-sm-12 mb-1" id="material_${window.materialNo}">
                        <div class="input-group">
                            <input type="text" class="form-control" id="namaMaterial_${window.materialNo}" placeholder="Nama material" readonly>
                            <input type="hidden" name="namaMaterial[]" value="${name}" >
                            <button class="btn btn-danger" data-nomer="${window.materialNo}" onclick="btnRemoveMaterial(this)"><i class="bi bi-trash"></i></button>
                            <input type="number" class="form-control" id="amount" name="amount[]" placeholder="amount">
                        </div>
                    </div>
                `;
                $('#material_name').val("").trigger('change');
                $('#material_list').append(content);
                $('#material_list').show();

                $(`#namaMaterial_${window.materialNo}`).val(text);
                window.materialNo++;
            }
        }
    }

    function btnRemoveMaterial(obj) {
        const nomer = $(obj).data('nomer');
        $('#material_' + nomer).remove();
    }
    function jenisTaskChange(obj) {
        let jenis = $(obj).val();

        window.jenisTask = jenis == 'weapon' ? 'weapons' : 'characters';
    }

    function search(obj) {
        const task_name = $('#nameTask').val();
        $.ajax({
            url: `${URL_API_GENSHIN}${window.jenisTask}`,
            method: 'GET',
            data: {
                query: task_name,
                matchCategories: true,
                verboseCategories: true,
                resultLanguage: "Indonesia"
            },
            beforeSend: function (xhr) {
                $(obj).attr("disabled", true);
                $(obj).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`)
            },
            success: function (result) {
                if (result.length > 1) {
                    result = result[0];
                }
                $(obj).attr("disabled", false);
                $(obj).html(`<i class="bi bi-search"></i>`);

                $('#nameTask').val(result.name);
                $('#imageTask').attr('src', result.images.icon);
                $('#urlImage').val(result.images.icon);

                $('#resultTask').show();
                $('#btnSave').show();
            }
        }).fail(function () {
            swal.fire({
                title: "Data tidak ditemukan",
                icon: "warning"
            });
            $(obj).attr("disabled", false);
            $(obj).html(`<i class="bi bi-search"></i>`);
            $('#resultTask').hide();
            $('#btnSave').hide();
        })
    }

    function showMaterial(obj) {
        const data = $(obj).data('subtask');
        let content = '';
        if(data.material.sumberCraft){
            for (const material of data.material.sumberCraft) {
                console.log(material);
                content += `
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <img src="${material.images}" role="button" alt="" class="img-thumbnail" style="width: 30px;height: 30px;">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">${material.name}</div>
                        </div>
                        <button class="btn btn-light btn-sm p-0 px-1 me-1" data-idmaterial="${material.id}" data-name="${material.name}" data-amount="${material.amount}" onclick="editMaterial(this)">Tambah</button>
                        <button class="btn btn-warning btn-sm p-0 px-1" data-idmaterial="${material.id}" data-name="${material.name}" data-amount="${material.amount}" onclick="craftMaterial(this)">Craft</button>
                        <span class="mx-2">${material.amount}</span>
                    </div>
                `;
            }

        }
        content += `
            <div class="d-flex justify-content-between align-items-start mb-1">
                <img src="${data.material.images}" role="button" alt="" class="img-thumbnail" style="width: 30px;height: 30px;">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">${data.material.name}</div>
                </div>
                <button class="btn btn-light btn-sm p-0 px-1 me-1" data-idmaterial="${data.material.id}" data-name="${data.material.name}" data-amount="${data.material.amount}" onclick="editMaterial(this)">Tambah</button>
                <span class="mx-2">${data.material.amount}</span>
            </div>
        `;

        $('#materialCrafting').html(content);
        $('#titleModal').html(data.material.name);
        $('#modalDetailMaterial').modal('show');
    }

    function craftMaterial(obj) {
        const id = $(obj).data('idmaterial');
        const amount = parseInt($(obj).data('amount')/3);
        const name = $(obj).data('name');

        $('#titleCraftModal').html(name);
        $('#formidmaterial').val(id);

        $('#formRangeCraft').attr('max', amount);
        $('#formRangeCraft').on('input', (event) => {
            $('#valueCraft').html(event.target.value);
        })
        $('#valueCraft').html($('#formRangeCraft').val());
        $('#modalRangeCraft').modal('show');
        $('#modalDetailMaterial').modal('hide');
    }

    function editMaterial(obj) {
        const id = $(obj).data('idmaterial');
        const amount = $(obj).data('amount');
        const name = $(obj).data('name');

        $('#titleMaterialModal').html(name);
        $('#formidUpdatematerial').val(id);

        $('#modalEditMaterial').modal('show');
        $('#modalDetailMaterial').modal('hide');
    }
</script>
@endsection