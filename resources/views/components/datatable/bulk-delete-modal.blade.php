@props(['title' => 'Hapus Terpilih', 'message' => 'Anda yakin menghapus data terpilih? Tindakan ini tidak dapat dibatalkan.', 'route' => '', 'id' => 'modal-bulk-delete', 'confirmId' => 'btn-bulk-delete-confirm'])

<div class="modal modal-blur fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-alert-triangle mb-2 text-danger icon-lg"></i>
                <h3>{{ $title }}</h3>
                <div class="text-secondary">{{ $message }}</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col"><button type="button" class="btn btn-danger w-100" id="{{ $confirmId }}" data-bulk-route="{{ $route }}">Hapus Semua</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="btn-bulk-delete-confirm"]').forEach(function(btn) {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', function() {
            var route = btn.dataset.bulkRoute;
            if (!route) return;
            var ids = window.tableSelector ? window.tableSelector.getSelectedIds() : [];
            if (!ids.length) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = route;
            var csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            ids.forEach(function(id) {
                var input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>
@endpush
@endonce
