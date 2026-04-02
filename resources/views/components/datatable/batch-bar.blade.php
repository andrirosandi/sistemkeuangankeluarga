@props(['targetModal' => '#modal-bulk-delete'])

<div id="batch-action-bar" class="bg-primary-lt px-3 py-2 border-top d-none">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span class="text-primary fw-bold"><span id="selected-count">0</span> Data Terpilih</span>
            <button type="button" class="btn btn-sm btn-link text-secondary p-0" id="btn-cancel-all">Batalkan Pilihan</button>
        </div>
        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="{{ $targetModal }}">Hapus</button>
    </div>
</div>
