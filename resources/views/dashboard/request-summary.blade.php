{{-- Request Summary HTML fragment — returned by /api/dashboard/request-summary --}}
<div class="row g-3">
    {{-- Total Pengajuan --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-primary-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-file-text text-primary"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Pengajuan</div>
                <div class="fw-bold">{{ $data['totalCount'] }}</div>
                <div class="text-secondary text-truncate" style="font-size:0.7rem">@uang($data['totalAmount'])</div>
            </div>
        </div>
    </div>

    {{-- Approved --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-green-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-circle-check text-green"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Disetujui</div>
                <div class="fw-bold text-green">{{ $data['byStatus']['approved']['count'] }}</div>
                <div class="text-secondary text-truncate" style="font-size:0.7rem">@uang($data['byStatus']['approved']['amount'])</div>
            </div>
        </div>
    </div>

    {{-- Pending / Requested --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-yellow-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-clock text-yellow"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Menunggu</div>
                <div class="fw-bold text-yellow">{{ $data['byStatus']['requested']['count'] }}</div>
                <div class="text-secondary text-truncate" style="font-size:0.7rem">@uang($data['byStatus']['requested']['amount'])</div>
            </div>
        </div>
    </div>

    {{-- Rejected --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-red-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-x text-red"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Ditolak</div>
                <div class="fw-bold text-red">{{ $data['byStatus']['rejected']['count'] }}</div>
                <div class="text-secondary text-truncate" style="font-size:0.7rem">@uang($data['byStatus']['rejected']['amount'])</div>
            </div>
        </div>
    </div>
</div>

{{-- Outstanding highlight --}}
@if($data['outstanding'] > 0)
<div class="mt-3 p-3 rounded" style="background: var(--tblr-warning-lt, #fff3cd)">
    <div class="d-flex align-items-center gap-2">
        <i class="ti ti-alert-triangle text-yellow" style="font-size:20px"></i>
        <div>
            <div class="fw-bold">Outstanding: @uang($data['outstanding'])</div>
            <div class="text-secondary" style="font-size:0.75rem">Total pengajuan yang belum sepenuhnya terealisasi</div>
        </div>
    </div>
</div>
@endif
