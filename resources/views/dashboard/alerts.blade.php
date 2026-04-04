{{-- Alerts HTML fragment — returned by /api/dashboard/alerts --}}
@if(empty($data))
    <div class="text-center text-secondary py-3">
        <i class="ti ti-check" style="font-size:24px"></i>
        <div class="mt-1">Tidak ada pengajuan pending</div>
    </div>
@else
    <div class="list-group list-group-flush">
        @foreach($data as $alert)
        <a href="{{ $alert['type'] === 'in' ? route('in.request.show', $alert['id']) : route('out.request.show', $alert['id']) }}"
           class="list-group-item list-group-item-action d-flex align-items-center gap-3">
            <span class="{{ $alert['type'] === 'in' ? 'bg-green-lt text-green' : 'bg-red-lt text-red' }} rounded p-2">
                <i class="ti ti-{{ $alert['type'] === 'in' ? 'arrow-down-left' : 'arrow-up-right' }}"></i>
            </span>
            <div class="flex-fill">
                <div class="fw-medium">{{ Str::limit($alert['description'], 40) }}</div>
                <div class="text-secondary" style="font-size:0.75rem">
                    {{ $alert['creator'] }} &middot; {{ $alert['created_at'] }}
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold">Rp {{ number_format($alert['amount'], 0, ',', '.') }}</div>
                <span class="badge bg-yellow-lt text-yellow" style="font-size:0.65rem">Pending</span>
            </div>
        </a>
        @endforeach
    </div>
@endif
