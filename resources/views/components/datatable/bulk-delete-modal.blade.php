@props(['title' => 'Hapus Terpilih', 'message' => 'Anda yakin menghapus data terpilih? Tindakan ini tidak dapat dibatalkan.', 'route' => '', 'id' => 'modal-bulk-delete', 'confirmId' => 'btn-bulk-delete-confirm'])

<div class="modal modal-blur fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.707 2.983h16.845a1.989 1.989 0 0 0 1.708 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.415 0z" /><path d="M12 9v4" /><path d="M12 17h.01" /></svg>
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
