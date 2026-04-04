@props(['perPage' => 20, 'searchLabel' => 'Cari data'])

<div class="card-body border-bottom py-3">
    <div class="d-flex">
        <div class="text-secondary">
            Tampilkan
            <div class="mx-2 d-inline-block">
                <input type="text" class="form-control form-control-sm" value="{{ $perPage }}" size="3" aria-label="In page count" id="page-count-input">
            </div>
            data
        </div>
        <div class="ms-auto text-secondary">
            Pencarian:
            <div class="ms-2 d-inline-block">
                <input type="search" class="search form-control form-control-sm" aria-label="{{ $searchLabel }}">
            </div>
        </div>
    </div>
</div>
