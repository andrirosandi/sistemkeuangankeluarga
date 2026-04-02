@props(['total' => 0, 'perPage' => 20])

<div class="card-footer d-flex align-items-center border-top">
    <p class="m-0 text-secondary" id="pagination-wrapper">
        <span class="d-none d-sm-inline">Menampilkan</span>
        <span id="pagination-info-start">1</span>-<span id="pagination-info-end">{{ min($perPage, $total) }}</span>
        dari <span id="pagination-info-total">{{ $total }}</span>
        <span class="d-none d-sm-inline">data</span>
    </p>
    <div class="pagination m-0 ms-auto"></div>
</div>
