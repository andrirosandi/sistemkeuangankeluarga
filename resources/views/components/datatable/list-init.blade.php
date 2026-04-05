@props(['valueNames' => '[]', 'perPage' => 20, 'listVar' => 'dataList'])

@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
@endpush
@endonce

<script>
(function () {
    var tableSelector = window.initSmartTableSelection({
        batchBarId: 'batch-action-bar',
        countId: 'selected-count'
    });
    window.tableSelector = tableSelector;

    var list = new List('table-default', {
        valueNames: {!! $valueNames !!},
        page: {{ $perPage }},
        pagination: { innerWindow: 2, outerWindow: 1 }
    });

    list.on('updated', function (l) {
        var pw = document.getElementById('pagination-wrapper');
        if (l.items.length > 0) {
            pw.classList.remove('d-none');
            var s = document.getElementById('pagination-info-start');
            if (s) s.innerText = l.i;
            var e = document.getElementById('pagination-info-end');
            if (e) e.innerText = Math.min(l.i + l.page - 1, l.items.length);
            var t = document.getElementById('pagination-info-total');
            if (t) t.innerText = l.items.length;
        } else {
            pw.classList.add('d-none');
        }
        tableSelector.syncCheckboxes();
    });

    document.getElementById('page-count-input')?.addEventListener('change', function(e) {
        list.show(1, parseInt(e.target.value) || {{ $perPage }});
    });

    window.{{ $listVar }} = list;
})();
</script>
