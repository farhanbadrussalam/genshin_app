@extends('layout.main')

@section('content')
    @php $title = 'Task'; @endphp
    @include('layout.header')

    <div class="page-container" style="padding-top: 1.25rem; padding-bottom: 5rem;">

        <div class="page-header">
            <i class="bi bi-list-task"></i> Task
        </div>

        {{-- Search Bar --}}
        <div class="search-filter-box mb-3">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="filterTaskInput"
                    placeholder="Cari nama task / jenis / material...">
                <button class="btn btn-secondary text-muted" type="button" id="clearTaskFilter"
                    style="display: none;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        {{-- Task List --}}
        <div id="taskListContainer">
        @forelse($dataTask as $index => $task)
            <div class="task-card animate-fade-in-up" style="animation-delay: {{ $index * 0.05 }}s;">

                {{-- Task Header --}}
                <div class="task-header">
                    <span class="task-title">{{ $task->nama_task }}</span>
                    <div class="task-meta">
                        <span class="badge-jenis">{{ $task->jenis }}</span>
                        <span class="badge-priority"
                            style="
          background: {{ $task->prioritas <= 3 ? 'rgba(76,175,80,0.15)' : ($task->prioritas <= 7 ? 'rgba(255,152,0,0.15)' : 'rgba(244,67,54,0.15)') }};
          color: {{ $task->prioritas <= 3 ? '#81c784' : ($task->prioritas <= 7 ? '#ffb74d' : '#e57373') }};
          border: 1px solid {{ $task->prioritas <= 3 ? 'rgba(76,175,80,0.3)' : ($task->prioritas <= 7 ? 'rgba(255,152,0,0.3)' : 'rgba(244,67,54,0.3)') }};
        ">
                            P{{ $task->prioritas }}
                        </span>
                    </div>
                </div>

                {{-- Sub Tasks --}}
                @foreach ($task->sub_task as $sub_task)
                    @php
                        $dimiliki = $sub_task->material->amount;
                        if (isset($sub_task->material->hasilCraft)) {
                            $dimiliki += $sub_task->material->hasilCraft;
                        }
                        $dibutuhkan = $sub_task->amount;
                        $isOk = $dimiliki >= $dibutuhkan;
                    @endphp
                    <div class="task-sub-item" data-subtask="{{ $sub_task }}" onclick="showMaterial(this)">
                        <img src="{{ $sub_task->material->images }}" alt="{{ $sub_task->material->name }}"
                            class="task-sub-img" loading="lazy">
                        <div class="sub-info">
                            <div class="sub-name">{{ $sub_task->material->name }}</div>
                            @if(isset($sub_task->material->rarity) && $sub_task->material->rarity > 0)
                                <div class="rarity-stars">{{ str_repeat('★', $sub_task->material->rarity) }}</div>
                            @endif
                            <div class="sub-days">
                                @foreach (json_decode($sub_task->material->daysofweek) as $days)
                                    {{ $days }}
                                @endforeach
                            </div>
                            @if (isset(json_decode($sub_task->material->source)[0]))
                                <div class="sub-source">{{ json_decode($sub_task->material->source)[0] }}</div>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <span class="{{ $isOk ? 'status-ok' : 'status-need' }}"
                                data-needed="{{ $dibutuhkan }}"
                                data-craft="{{ isset($sub_task->material->hasilCraft) ? $sub_task->material->hasilCraft : 0 }}">
                                {{ $dimiliki }}/{{ $sub_task->amount }}
                            </span>
                            <button type="button" class="btn btn-secondary btn-sm py-0 px-2 ms-1"
                                style="height: 26px; font-size: 0.8rem; border-radius: var(--radius-sm);"
                                data-material-id="{{ $sub_task->material->id }}"
                                onclick="quickChangeSubtaskMaterial(event, this, -1)"
                                title="Kurang 1 {{ $sub_task->material->name }}">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm py-0 px-2"
                                style="height: 26px; font-size: 0.8rem; border-radius: var(--radius-sm);"
                                data-material-id="{{ $sub_task->material->id }}"
                                onclick="quickChangeSubtaskMaterial(event, this, 1)"
                                title="Tambah 1 {{ $sub_task->material->name }}">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                @endforeach

                {{-- Actions --}}
                <div class="task-actions">
                    <button type="button" class="btn btn-warning btn-sm"
                        onclick="editTaskModal(this)"
                        data-task="{{ json_encode($task) }}"
                        title="Edit Task">
                        <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <a href="{{ url('taskComplete/' . $task->id) }}"
                        onclick="return confirm('Upgrade {{ $task->nama_task }} ?')"
                        class="btn btn-primary btn-sm {{ !$task->statusUpgrade ? 'disabled' : '' }}">
                        <i class="bi bi-arrow-up-circle-fill me-1"></i>Upgrade
                    </a>
                    <form action="{{ url('task/' . $task->id) }}" method="post" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit"
                            onclick="return confirm('Hapus {{ $task->nama_task }} ?')">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <div class="text-center py-5 animate-fade-in-up" style="color: var(--text-muted);">
                <i class="bi bi-list-task" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-2 mb-0" style="font-size: 0.85rem;">Belum ada task. Tambahkan task baru!</p>
            </div>
        @endforelse
        </div>

    </div>

    {{-- FAB --}}
    <div class="fab-container">
        <button type="button" class="btn-fab fab-pulse" data-bs-toggle="modal" data-bs-target="#tambahTask"
            title="Tambah Task">
            <i class="bi bi-plus-lg"></i>
        </button>
    </div>

    {{-- Modal Tambah Task --}}
    <div class="modal fade" id="tambahTask" tabindex="-1" aria-labelledby="tambahTaskTitle" aria-modal="true"
        role="dialog">
        <form action="{{ route('task.store') }}" method="POST">
            @csrf
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahTaskTitle">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Task
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-floating mb-2">
                            <select class="form-select" id="jenis_task" name="jenis_task" onchange="jenisTaskChange(this)">
                                <option value="stat">Stat</option>
                                <option value="talent">Talent</option>
                                <option value="weapon">Weapon</option>
                            </select>
                            <label for="jenis_task">Jenis Task</label>
                        </div>

                        <div class="input-group mb-3">
                            <div class="form-floating flex-grow-1">
                                <input type="text" class="form-control" id="nameTask" name="nameTask">
                                <input type="hidden" id="dataMaterial" name="dataMaterial">
                                <label for="nameTask">Nama Karakter / Senjata</label>
                            </div>
                            <button class="btn btn-info" type="button" onclick="search(this)" title="Cari">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                        <div id="resultTask" style="display: none;" class="animate-fade-in-up">
                            <div class="result-preview mb-3">
                                <img src="" alt="" style="width: 80px;" id="imageTask">
                                <input type="hidden" id="urlImage" name="urlImage">
                            </div>

                            <div class="form-floating mb-2">
                                <input type="number" class="form-control" id="prioritas" name="prioritas"
                                    placeholder="Prioritas" min="1">
                                <label for="prioritas">Prioritas (1 = tertinggi)</label>
                            </div>

                            <hr class="genshin-divider">

                            <div class="mb-2">
                                <select class="form-select" style="width: 100%;" data-control="select2"
                                    id="material_name" name="material_name">
                                    <option value="">-- Pilih Material --</option>
                                    @foreach ($dataMaterial as $material)
                                        <option value="{{ $material->id }}">{{ $material->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-success btn-sm mt-2 w-100" type="button" data-type="tambah"
                                    onclick="btnMaterial(this)">
                                    <i class="bi bi-plus me-1"></i>Tambah Material
                                </button>
                            </div>

                            <div id="material_list" class="border border-success border-opacity-25 rounded p-2"
                                style="display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4" id="btnSave" style="display: none;">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Modal Detail Material --}}
    <div class="modal fade" tabindex="-1" id="modalDetailMaterial">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-gold" id="titleModal">Detail Material</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="materialCrafting"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Craft Range --}}
    <div class="modal fade" tabindex="-1" id="modalRangeCraft" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('craftingBuild') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title text-gold">Craft Material</h5>
                    </div>
                    <div class="modal-body">
                        <p style="color: var(--text-secondary); font-size: 0.85rem;">
                            Craft material: <strong class="text-gold" id="titleCraftModal"></strong>
                        </p>
                        <input type="hidden" id="formidmaterial" name="formidmaterial">
                        <label class="form-label">Jumlah Craft</label>
                        <input type="range" class="w-100 mb-2" min="0" step="1" id="formRangeCraft"
                            name="formRangeCraft">
                        <p style="color: var(--text-secondary); font-size: 0.85rem;">
                            Hasil Craft: <strong class="text-gold" id="valueCraft">0</strong>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            onclick="$('#modalDetailMaterial').modal('show')">Batal</button>
                        <button class="btn btn-primary" type="submit">Craft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Jumlah Material --}}
    <div class="modal fade" tabindex="-1" id="modalEditMaterial" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('editMaterial') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title text-gold">Tambah Material</h5>
                    </div>
                    <div class="modal-body">
                        <p style="color: var(--text-secondary); font-size: 0.85rem;">
                            Material: <strong class="text-gold" id="titleMaterialModal"></strong>
                        </p>
                        <input type="hidden" id="formidUpdatematerial" name="formidUpdatematerial">
                        <div class="form-floating">
                            <input type="number" class="form-control" id="formAmountMaterial" name="formAmountMaterial"
                                placeholder="Jumlah" value="0" min="0">
                            <label for="formAmountMaterial">Jumlah Ditambahkan</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            onclick="$('#modalDetailMaterial').modal('show')">Batal</button>
                        <button class="btn btn-primary" type="submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Task --}}
    <div class="modal fade" id="modalEditTask" tabindex="-1" aria-labelledby="modalEditTaskTitle">
        <form action="#" method="POST" id="formEditTask">
            @csrf
            @method('PUT')
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditTaskTitle">
                            <i class="bi bi-pencil-square me-2"></i>Edit Task
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-floating mb-2">
                            <select class="form-select" id="jenis_taskEdit" name="jenis_taskEdit">
                                <option value="stat">Stat</option>
                                <option value="talent">Talent</option>
                                <option value="weapon">Weapon</option>
                            </select>
                            <label for="jenis_taskEdit">Jenis Task</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="nameTaskEdit" name="nameTaskEdit" required>
                            <input type="hidden" id="urlImageEdit" name="urlImageEdit">
                            <label for="nameTaskEdit">Nama Task</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input type="number" class="form-control" id="prioritasEdit" name="prioritasEdit" placeholder="Prioritas" min="1" required>
                            <label for="prioritasEdit">Prioritas (1 = tertinggi)</label>
                        </div>

                        <hr class="genshin-divider">

                        <div class="mb-2">
                            <label class="form-label text-gold font-display" style="font-size: 0.85rem;">Daftar Material Target</label>
                            <select class="form-select" style="width: 100%;" data-control="select2" id="material_nameEdit">
                                <option value="">-- Pilih Material Baru --</option>
                                @foreach ($dataMaterial as $material)
                                    <option value="{{ $material->id }}">{{ $material->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-success btn-sm mt-2 w-100" type="button" onclick="btnMaterialEditAdd()">
                                <i class="bi bi-plus me-1"></i>Tambah Material Ke List
                            </button>
                        </div>

                        <div id="material_listEdit" class="border border-success border-opacity-25 rounded p-2 mt-2">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        window.editMaterialNo = 0;

        $(document).ready(function() {
            window.jenisTask = 'characters';
            $('#material_name').select2({
                dropdownParent: $('#tambahTask')
            });
            $('#material_nameEdit').select2({
                dropdownParent: $('#modalEditTask')
            });
            window.materialNo = 0;
        });

        function editTaskModal(btn) {
            const data = $(btn).data('task');
            $('#formEditTask').attr('action', "{{ url('task') }}/" + data.id);
            $('#jenis_taskEdit').val(data.jenis);
            $('#nameTaskEdit').val(data.nama_task);
            $('#urlImageEdit').val(data.images);
            $('#prioritasEdit').val(data.prioritas);

            $('#material_listEdit').empty();
            window.editMaterialNo = 0;

            if (data.sub_task && data.sub_task.length > 0) {
                data.sub_task.forEach(sub => {
                    let matName = sub.material ? sub.material.name : ('Material ID ' + sub.material_id);
                    addEditMaterialRow(sub.material_id, matName, sub.amount);
                });
            }

            $('#modalEditTask').modal('show');
        }

        function addEditMaterialRow(matId, matName, amount) {
            let content = `
                <div class="mb-1" id="edit_material_row_${window.editMaterialNo}">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm"
                            value="${matName}" readonly style="border-radius: var(--radius-sm) 0 0 var(--radius-sm) !important;">
                        <input type="hidden" name="namaMaterial[]" value="${matId}">
                        <input type="number" class="form-control form-control-sm"
                            name="amount[]" value="${amount}" placeholder="Jumlah Target" required style="width: 70px;">
                        <button class="btn btn-danger btn-sm" type="button"
                            onclick="$('#edit_material_row_${window.editMaterialNo}').remove()"
                            style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0 !important;" title="Hapus Material">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#material_listEdit').append(content);
            window.editMaterialNo++;
        }

        function btnMaterialEditAdd() {
            let id = $('#material_nameEdit').val();
            if (id !== "") {
                let text = $("#material_nameEdit option:selected").text();
                addEditMaterialRow(id, text, 1);
                $('#material_nameEdit').val("").trigger('change');
            }
        }

        function btnMaterial(obj) {
            const type = $(obj).data('type');
            if (type === "tambah") {
                let name = $('#material_name').val();
                if (name !== "") {
                    let text = $("#material_name option:selected").text();
                    let content = `
                    <div class="mb-1" id="material_${window.materialNo}">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm"
                                id="namaMaterial_${window.materialNo}"
                                placeholder="Nama material" readonly style="border-radius: var(--radius-sm) 0 0 var(--radius-sm) !important;">
                            <input type="hidden" name="namaMaterial[]" value="${name}">
                            <input type="number" class="form-control form-control-sm"
                                name="amount[]" placeholder="Jumlah">
                            <button class="btn btn-danger btn-sm" type="button"
                                data-nomer="${window.materialNo}"
                                onclick="btnRemoveMaterial(this)" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0 !important;">
                                <i class="bi bi-trash"></i>
                            </button>
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
            window.jenisTask = jenis === 'weapon' ? 'weapons' : 'characters';
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
                beforeSend: function(xhr) {
                    $(obj).attr("disabled", true);
                    $(obj).html(
                        `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`
                    );
                },
                success: function(result) {
                    if (Array.isArray(result) && result.length > 1) {
                        result = result[0];
                    }
                    $(obj).attr("disabled", false);
                    $(obj).html(`<i class="bi bi-search"></i>`);

                    console.log(result.images);
                    $('#nameTask').val(result.name);

                    let imageCandidates = [];
                    if (result.images) {
                        const priorityKeys = [
                            'hoyowiki_icon',
                            'icon',
                            'fandom',
                            'redirect',
                            'cover1',
                            'cover2',
                            'card',
                            'portrait',
                            'hoyolab-avatar',
                            'mihoyo_icon',
                            'mihoyo_sideIcon',
                            'mihoyo_card'
                        ];

                        priorityKeys.forEach(key => {
                            if (result.images[key] && typeof result.images[key] === 'string' &&
                                (result.images[key].startsWith('http://') || result.images[key].startsWith('https://'))) {
                                if (!imageCandidates.includes(result.images[key])) {
                                    imageCandidates.push(result.images[key]);
                                }
                            }
                        });

                        Object.values(result.images).forEach(url => {
                            if (typeof url === 'string' && (url.startsWith('http://') || url.startsWith('https://'))) {
                                if (!imageCandidates.includes(url)) {
                                    imageCandidates.push(url);
                                }
                            }
                        });

                        if (result.images.filename_icon) {
                            const yattaUrl = `https://gi.yatta.moe/assets/UI/${result.images.filename_icon}.png`;
                            if (!imageCandidates.includes(yattaUrl)) {
                                imageCandidates.push(yattaUrl);
                            }
                        }

                        const safeCandidates = imageCandidates.filter(url => !url.includes('upload-os-bbs.mihoyo.com'));
                        if (safeCandidates.length > 0) {
                            imageCandidates = safeCandidates.concat(imageCandidates.filter(url => url.includes('upload-os-bbs.mihoyo.com')));
                        }
                    }

                    window.taskImageCandidates = imageCandidates;
                    let selectedUrl = window.taskImageCandidates.length > 0 ? window.taskImageCandidates.shift() : '';

                    const imgElem = $('#imageTask');
                    imgElem.off('error').on('error', function() {
                        if (window.taskImageCandidates && window.taskImageCandidates.length > 0) {
                            let nextUrl = window.taskImageCandidates.shift();
                            $(this).attr('src', nextUrl);
                            $('#urlImage').val(nextUrl);
                        } else {
                            $(this).attr('src', '');
                            $('#urlImage').val('');
                        }
                    });

                    imgElem.attr('src', selectedUrl);
                    $('#urlImage').val(selectedUrl);

                    $('#resultTask').show();
                    $('#btnSave').show();
                }
            }).fail(function() {
                Swal.fire({
                    title: "Data tidak ditemukan",
                    icon: "warning"
                });
                $(obj).attr("disabled", false);
                $(obj).html(`<i class="bi bi-search"></i>`);
                $('#resultTask').hide();
                $('#btnSave').hide();
            });
        }

        function showMaterial(obj) {
            const data = $(obj).data('subtask');
            let content = '';
            window.needTaskReload = false;

            if (data.material.sumberCraft) {
                for (const material of data.material.sumberCraft) {
                    let stars = material.rarity ? '★'.repeat(material.rarity) : '';
                    content += `
                    <div class="task-sub-item mb-2 align-items-center flex-wrap" id="detail_mat_${material.id}">
                        <img src="${material.images}" alt="" class="task-sub-img">
                        <div class="sub-info me-auto">
                            <div class="sub-name">${material.name}</div>
                            ${stars ? `<div class="rarity-stars">${stars}</div>` : ''}
                            <div class="sub-days"><span class="owned-amount">${material.amount}</span> dimiliki</div>
                        </div>
                        <div class="d-flex align-items-center gap-1 mt-1 mt-sm-0">
                            <input type="number" class="form-control form-control-sm add-amount-input" value="1" min="1" style="width: 55px; text-align: center;">
                            <button class="btn btn-secondary btn-sm px-2"
                                data-idmaterial="${material.id}"
                                onclick="quickAddMaterial(this, -1)" title="Kurangi Jumlah">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <button class="btn btn-success btn-sm px-2"
                                data-idmaterial="${material.id}"
                                onclick="quickAddMaterial(this, 1)" title="Tambah Jumlah">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button class="btn btn-warning btn-sm px-2"
                                data-idmaterial="${material.id}"
                                data-name="${material.name}"
                                data-amount="${material.amount}"
                                onclick="craftMaterial(this)" title="Craft Material">
                                <i class="bi bi-hammer"></i>
                            </button>
                        </div>
                    </div>
                `;
                }
            }

            let mainStars = data.material.rarity ? '★'.repeat(data.material.rarity) : '';
            content += `
            <div class="task-sub-item align-items-center flex-wrap" id="detail_mat_${data.material.id}">
                <img src="${data.material.images}" alt="" class="task-sub-img">
                <div class="sub-info me-auto">
                    <div class="sub-name">${data.material.name}</div>
                    ${mainStars ? `<div class="rarity-stars">${mainStars}</div>` : ''}
                    <div class="sub-days"><span class="owned-amount">${data.material.amount}</span> dimiliki</div>
                </div>
                <div class="d-flex align-items-center gap-1 mt-1 mt-sm-0">
                    <input type="number" class="form-control form-control-sm add-amount-input" value="1" min="1" style="width: 55px; text-align: center;">
                    <button class="btn btn-secondary btn-sm px-2"
                        data-idmaterial="${data.material.id}"
                        onclick="quickAddMaterial(this, -1)" title="Kurangi Jumlah">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button class="btn btn-success btn-sm px-2"
                        data-idmaterial="${data.material.id}"
                        onclick="quickAddMaterial(this, 1)" title="Tambah Jumlah">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
            `;

            $('#materialCrafting').html(content);
            $('#titleModal').html(data.material.name);
            $('#modalDetailMaterial').modal('show');
        }

        function quickAddMaterial(btn, sign = 1) {
            const $btn = $(btn);
            const id = $btn.data('idmaterial');
            const $row = $btn.closest('.task-sub-item');
            const $input = $row.find('.add-amount-input');
            const rawAmount = parseInt($input.val()) || 1;
            const amount = rawAmount * sign;
            const iconClass = sign > 0 ? 'bi-plus-lg' : 'bi-dash-lg';

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;" role="status"></span>');

            $.ajax({
                url: "{{ route('editMaterial') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    formidUpdatematerial: id,
                    formAmountMaterial: amount
                },
                success: function(res) {
                    $btn.prop('disabled', false).html(`<i class="bi ${iconClass}"></i>`);
                    if (res && res.success) {
                        $row.find('.owned-amount').text(res.new_amount);
                        window.needTaskReload = true;
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(`<i class="bi ${iconClass}"></i>`);
                    Swal.fire({
                        title: "Gagal memperbarui jumlah material",
                        icon: "error"
                    });
                }
            });
        }

        function quickChangeSubtaskMaterial(e, btn, delta) {
            e.stopPropagation();
            const $btn = $(btn);
            const materialId = $btn.data('material-id');
            const $subItem = $btn.closest('.task-sub-item');
            const $badge = $subItem.find('.status-need, .status-ok');
            const $taskCard = $btn.closest('.task-card');
            const needed = parseInt($badge.data('needed')) || 0;
            const craft = parseInt($badge.data('craft')) || 0;
            const iconClass = delta > 0 ? 'bi-plus-lg' : 'bi-dash-lg';

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;" role="status"></span>');

            $.ajax({
                url: "{{ route('editMaterial') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    formidUpdatematerial: materialId,
                    formAmountMaterial: delta
                },
                success: function(res) {
                    $btn.prop('disabled', false).html(`<i class="bi ${iconClass}"></i>`);
                    if (res && res.success) {
                        let totalDimiliki = res.new_amount + craft;
                        $badge.text(`${totalDimiliki}/${needed}`);

                        if (totalDimiliki >= needed) {
                            $badge.removeClass('status-need').addClass('status-ok');
                        } else {
                            $badge.removeClass('status-ok').addClass('status-need');
                        }

                        let allOk = true;
                        $taskCard.find('.task-sub-item').each(function() {
                            let $b = $(this).find('.status-need, .status-ok');
                            if ($b.hasClass('status-need')) {
                                allOk = false;
                            }
                        });

                        const $upgradeBtn = $taskCard.find('.task-actions .btn-primary');
                        if (allOk) {
                            $upgradeBtn.removeClass('disabled');
                        } else {
                            $upgradeBtn.addClass('disabled');
                        }
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(`<i class="bi ${iconClass}"></i>`);
                }
            });
        }

        function craftMaterial(obj) {
            const id = $(obj).data('idmaterial');
            const amount = parseInt($(obj).data('amount') / 3);
            const name = $(obj).data('name');

            $('#titleCraftModal').html(name);
            $('#formidmaterial').val(id);
            $('#formRangeCraft').attr('max', amount);
            $('#formRangeCraft').on('input', (event) => {
                $('#valueCraft').html(event.target.value);
            });
            $('#valueCraft').html($('#formRangeCraft').val());
            $('#modalRangeCraft').modal('show');
            $('#modalDetailMaterial').modal('hide');
        }

        function editMaterial(obj) {
            const id = $(obj).data('idmaterial');
            const name = $(obj).data('name');

            $('#titleMaterialModal').html(name);
            $('#formidUpdatematerial').val(id);
            $('#modalEditMaterial').modal('show');
            $('#modalDetailMaterial').modal('hide');
        }

        $(document).ready(function() {
            $('#filterTaskInput').on('input', function() {
                let q = $(this).val().toLowerCase().trim();
                $('#clearTaskFilter').toggle(q.length > 0);

                let matchCount = 0;
                $('#taskListContainer .task-card').each(function() {
                    let title = $(this).find('.task-title').text().toLowerCase();
                    let jenis = $(this).find('.badge-jenis').text().toLowerCase();
                    let subNames = $(this).find('.sub-name').map(function() {
                        return $(this).text().toLowerCase();
                    }).get().join(' ');

                    if (title.includes(q) || jenis.includes(q) || subNames.includes(q)) {
                        $(this).show();
                        matchCount++;
                    } else {
                        $(this).hide();
                    }
                });

                if (matchCount === 0 && q.length > 0) {
                    if ($('#noTaskFilterMatch').length === 0) {
                        $('#taskListContainer').append(`
                            <div id="noTaskFilterMatch" class="text-center py-5" style="color: var(--text-muted);">
                                <i class="bi bi-search" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0" style="font-size: 0.85rem;">Tidak ada task yang cocok dengan "${q}"</p>
                            </div>
                        `);
                    } else {
                        $('#noTaskFilterMatch').show().find('p').text(`Tidak ada task yang cocok dengan "${q}"`);
                    }
                } else {
                    $('#noTaskFilterMatch').hide();
                }
            });

            $('#clearTaskFilter').on('click', function() {
                $('#filterTaskInput').val('').trigger('input').focus();
            });

            $('#modalDetailMaterial').on('hidden.bs.modal', function() {
                if (window.needTaskReload) {
                    window.location.reload();
                }
            });
        });
    </script>

@endsection
