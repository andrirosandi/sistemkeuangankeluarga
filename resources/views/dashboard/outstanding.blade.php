{{-- Outstanding Board HTML fragment — returned by /api/dashboard/outstanding --}}
@if($data['totalCount'] === 0)
    <div class="text-center text-secondary py-3">
        <i class="ti ti-circle-check" style="font-size:24px"></i>
        <div class="mt-1">Tidak ada outstanding</div>
    </div>
@else
    {{-- Total Outstanding --}}
    <div class="d-flex align-items-center gap-3 mb-3">
        <span class="avatar avatar-lg bg-orange-lt text-orange rounded">
            <i class="ti ti-hourglass" style="font-size:24px"></i>
        </span>
        <div>
            <div class="h2 mb-0 fw-bold">@uang($data['totalAmount'])</div>
            <div class="text-secondary">{{ $data['totalCount'] }} pengajuan outstanding</div>
        </div>
    </div>

    {{-- Breakdown cards --}}
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="border rounded p-2 text-center">
                <div class="text-yellow fw-bold">{{ $data['requestedCount'] }}</div>
                <div class="text-secondary" style="font-size:0.7rem">Menunggu Approval</div>
                <div style="font-size:0.7rem">@uang($data['requestedAmount'])</div>
            </div>
        </div>
        <div class="col-4">
            <div class="border rounded p-2 text-center">
                <div class="text-blue fw-bold">{{ $data['approvedDraftCount'] }}</div>
                <div class="text-secondary" style="font-size:0.7rem">Belum Direalisasikan</div>
                <div style="font-size:0.7rem">@uang($data['approvedDraftAmount'])</div>
            </div>
        </div>
        <div class="col-4">
            <div class="border rounded p-2 text-center">
                <div class="text-purple fw-bold">{{ $data['partialCount'] }}</div>
                <div class="text-secondary" style="font-size:0.7rem">Parsial</div>
                <div style="font-size:0.7rem">@uang($data['partialAmount'])</div>
            </div>
        </div>
    </div>

    {{-- Aging indicator --}}
    <div class="small">
        <div class="fw-medium mb-1">Aging Outstanding:</div>
        <div class="d-flex gap-2">
            <span class="badge bg-green-lt text-green">
                <i class="ti ti-clock me-1"></i>≤ 3 hari: {{ $data['aging']['fresh'] }}
            </span>
            <span class="badge bg-yellow-lt text-yellow">
                <i class="ti ti-clock me-1"></i>3-7 hari: {{ $data['aging']['medium'] }}
            </span>
            <span class="badge bg-red-lt text-red">
                <i class="ti ti-alert-triangle me-1"></i>> 7 hari: {{ $data['aging']['old'] }}
            </span>
        </div>
    </div>
@endif
