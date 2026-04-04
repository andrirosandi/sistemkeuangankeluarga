{{-- Approval Stats HTML fragment — returned by /api/dashboard/approval-stats --}}
<div class="row g-3">
    {{-- Reviewed this month --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-green-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-check text-green"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Disetujui</div>
                <div class="fw-bold text-green">{{ $data['approvedCount'] }}</div>
                <div class="text-secondary text-truncate" style="font-size:0.7rem">@uang($data['approvedAmount'])</div>
            </div>
        </div>
    </div>

    {{-- Rejected --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-red-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-x text-red"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Ditolak</div>
                <div class="fw-bold text-red">{{ $data['rejectedCount'] }}</div>
            </div>
        </div>
    </div>

    {{-- Pending --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-yellow-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-clock text-yellow"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Menunggu</div>
                <div class="fw-bold text-yellow">{{ $data['pendingCount'] }}</div>
                <div class="text-secondary text-truncate" style="font-size:0.7rem">@uang($data['pendingAmount'])</div>
            </div>
        </div>
    </div>

    {{-- Avg Response --}}
    <div class="col-6 col-md-3">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-blue-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-hourglass text-blue"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Rata-rata Respon</div>
                <div class="fw-bold text-blue">
                    @if($data['avgResponseHours'] !== null)
                        @if($data['avgResponseHours'] < 24)
                            {{ $data['avgResponseHours'] }} jam
                        @else
                            {{ round($data['avgResponseHours'] / 24, 1) }} hari
                        @endif
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Overdue alert --}}
@if($data['overdueCount'] > 0)
<div class="mt-3 p-3 rounded" style="background: var(--tblr-danger-lt, #f8d7da)">
    <div class="d-flex align-items-center gap-2">
        <i class="ti ti-alert-circle text-red" style="font-size:20px"></i>
        <div>
            <div class="fw-bold text-red">{{ $data['overdueCount'] }} pengajuan menunggu lebih dari 3 hari!</div>
            <div class="text-secondary" style="font-size:0.75rem">Segera proses untuk menghindari keterlambatan</div>
        </div>
    </div>
</div>
@endif
