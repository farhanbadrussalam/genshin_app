@extends('layout.main')

@section('content')
    @php $title = 'Material'; @endphp
    @include('layout.header')

    <div class="page-container" style="padding-top: 1.25rem; padding-bottom: 5rem;">

        <div class="page-header">
            <i class="bi bi-gem"></i> Material
        </div>

        {{-- Accordion --}}
        <div class="genshin-accordion accordion accordion-flush" id="accordionMaterial">
            @foreach ($dataFamily as $index => $value)
                <div class="accordion-item animate-fade-in-up" style="animation-delay: {{ $index * 0.05 }}s;">
                    <h2 class="accordion-header" id="heading-{{ $value->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $value->id }}" aria-expanded="false"
                            aria-controls="collapse-{{ $value->id }}">
                            <i class="bi bi-folder2 me-2" style="color: var(--color-gold); font-size: 0.9rem;"></i>
                            {{ $value->name }}
                            <span class="material-count-badge ms-auto me-2">{{ count($value->material) }}</span>
                        </button>
                    </h2>
                    <div id="collapse-{{ $value->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading-{{ $value->id }}" data-bs-parent="#accordionMaterial">
                        <div class="accordion-body">
                            @forelse($value->material as $material)
                                @php
                                    $rarityClass = 'rarity-bg-' . $material->rarity;
                                    $rarityBorder = 'rarity-border-' . $material->rarity;
                                    $stars = str_repeat('★', $material->rarity);
                                @endphp
                                <div class="material-item {{ $rarityClass }} {{ $rarityBorder }}">
                                    <img src="{{ $material->images }}" alt="{{ $material->name }}" class="material-img"
                                        loading="lazy">
                                    <div class="material-info">
                                        <div class="material-name">
                                            {{ $material->name }}
                                            <button type="button" class="btn btn-sm p-0 ms-1"
                                                style="color: var(--text-muted); line-height: 1; vertical-align: middle;"
                                                onclick="editfunction(this)" data-info="{{ $material }}"
                                                title="Edit">
                                                <i class="bi bi-pencil-square" style="font-size: 0.75rem;"></i>
                                            </button>
                                        </div>
                                        <div class="rarity-stars">{{ $stars }}</div>
                                        <div class="material-days">
                                            @foreach (json_decode($material->daysofweek) as $days)
                                                {{ $days }}
                                            @endforeach
                                        </div>
                                        @foreach (json_decode($material->source) as $source)
                                            <div class="material-source">{{ $source }}</div>
                                        @endforeach
                                    </div>
                                    <span class="amount-badge">{{ $material->amount }}</span>
                                </div>
                            @empty
                                <p class="text-center py-2 mb-0" style="color: var(--text-muted); font-size: 0.82rem;">
                                    Belum ada material di kategori ini
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    {{-- FAB --}}
    <div class="fab-container">
        <button type="button" class="btn-fab fab-pulse" data-bs-toggle="modal" data-bs-target="#tambahMaterial"
            title="Tambah Material">
            <i class="bi bi-plus-lg"></i>
        </button>
    </div>

    {{-- Modal Tambah Material --}}
    <div class="modal fade" tabindex="-1" id="tambahMaterial" aria-labelledby="tambahMaterialTitle">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('material.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahMaterialTitle">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Material
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Search --}}
                        <div class="input-group mb-3">
                            <div class="form-floating flex-grow-1">
                                <input type="text" class="form-control" id="nameMaterial" name="nameMaterial"
                                    placeholder="Nama material">
                                <input type="hidden" id="dataMaterial" name="dataMaterial">
                                <label for="nameMaterial">Cari nama material</label>
                            </div>
                            <button class="btn btn-info" type="button" onclick="searchMaterial(this)" title="Cari">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                        {{-- Result Preview --}}
                        <div id="resultMaterial" style="display: none;" class="animate-fade-in-up">
                            <div class="result-preview mb-3">
                                <img src="" alt="" id="imageMaterial" class="mb-2">
                            </div>

                            <div class="form-floating mb-2">
                                <select class="form-select" id="family_id" name="family_id">
                                    @foreach ($dataFamily as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                <label for="family_id">Kategori Family</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="number" class="form-control" id="rarity" name="rarity"
                                    placeholder="Rarity" readonly>
                                <label for="rarity">Rarity</label>
                            </div>

                            <div class="form-floating">
                                <input type="number" class="form-control" id="amount" name="amount"
                                    placeholder="Jumlah" value="0" min="0">
                                <label for="amount">Jumlah Dimiliki</label>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4" id="btnSave" style="display: none;">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Material --}}
    <div class="modal fade" tabindex="-1" id="editMaterial" aria-labelledby="editMaterialTitle">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="#" method="POST" id="formMaterialEdit">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMaterialTitle">
                            <i class="bi bi-pencil me-2"></i>Edit Material
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="result-preview mb-3">
                            <img src="" alt="" id="imageMaterialEdit" class="mb-1">
                        </div>

                        <input type="hidden" id="material_idEdit" name="material_id">

                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="nameMaterialEdit" name="nameMaterial"
                                placeholder="Nama material" readonly>
                            <label for="nameMaterialEdit">Nama Material</label>
                        </div>

                        <div class="form-floating mb-2">
                            <select class="form-select" id="family_idEdit" name="family_id">
                                @foreach ($dataFamily as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </select>
                            <label for="family_idEdit">Kategori Family</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input type="number" class="form-control" id="rarityEdit" name="rarity"
                                placeholder="Rarity" readonly>
                            <label for="rarityEdit">Rarity</label>
                        </div>

                        <div class="form-floating">
                            <input type="number" class="form-control" id="amountEdit" name="amount"
                                placeholder="Jumlah" value="0" min="0">
                            <label for="amountEdit">Jumlah Dimiliki</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4" id="btnEdit">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function searchMaterial(obj) {
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
                    $('#dataMaterial').val(JSON.stringify(result));
                    $(obj).attr("disabled", false);
                    $(obj).html(`<i class="bi bi-search"></i>`);

                    $('#nameMaterial').val(result.name);
                    $('#rarity').val(result.rarity);
                    let imageCandidates = [];
                    if (result.images) {
                        const priorityKeys = ['fandom', 'redirect', 'hoyowiki_icon', 'icon', 'mihoyo_icon', 'nameicon'];

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
                            const mihoyoEquipUrl = `https://upload-os-bbs.mihoyo.com/game_record/genshin/equip/${result.images.filename_icon}.png`;
                            if (!imageCandidates.includes(mihoyoEquipUrl)) {
                                imageCandidates.push(mihoyoEquipUrl);
                            }
                        }

                        const safeCandidates = imageCandidates.filter(url => !url.includes('upload-os-bbs.mihoyo.com'));
                        if (safeCandidates.length > 0) {
                            imageCandidates = safeCandidates.concat(imageCandidates.filter(url => url.includes('upload-os-bbs.mihoyo.com')));
                        }
                    }

                    window.materialImageCandidates = imageCandidates;
                    let selectedUrl = window.materialImageCandidates.length > 0 ? window.materialImageCandidates.shift() : '';

                    const imgElem = $('#imageMaterial');
                    imgElem.off('error').on('error', function() {
                        if (window.materialImageCandidates && window.materialImageCandidates.length > 0) {
                            let nextUrl = window.materialImageCandidates.shift();
                            $(this).attr('src', nextUrl);
                        } else {
                            $(this).attr('src', '');
                        }
                    });

                    imgElem.attr('src', selectedUrl);

                    $('#resultMaterial').show();
                    $('#btnSave').show();
                }
            }).fail(function() {
                Swal.fire({
                    title: "Data tidak ditemukan",
                    icon: "warning"
                });
                $(obj).attr("disabled", false);
                $(obj).html(`<i class="bi bi-search"></i>`);
                $('#resultMaterial').hide();
                $('#btnSave').hide();
            });
        }

        function editfunction(obj) {
            let data = $(obj).data('info');

            $('#nameMaterialEdit').val(data.name);
            $('#formMaterialEdit').attr('action', "{{ url('material') }}/" + data.id);
            $('#material_idEdit').val(data.id);
            $('#family_idEdit').val(data.familie_id);
            $('#amountEdit').val(data.amount);
            $('#rarityEdit').val(data.rarity);
            $('#imageMaterialEdit').attr('src', data.images);
            $('#editMaterial').modal('show');
        }
    </script>
@endsection
