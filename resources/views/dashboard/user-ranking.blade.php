{{-- User Ranking HTML fragment — returned by /api/dashboard/user-ranking --}}
@if(empty($ranking))
    <div class="text-center text-secondary py-3">
        <i class="ti ti-users" style="font-size:24px"></i>
        <div class="mt-1">Belum ada data pengguna</div>
    </div>
@else
    <div class="list-group list-group-flush">
        @foreach($ranking as $i => $user)
        <div class="list-group-item px-0">
            <div class="d-flex align-items-center gap-3">
                {{-- Rank badge --}}
                <span class="avatar avatar-sm rounded {{ $i === 0 ? 'bg-red-lt text-red' : ($i === count($ranking) - 1 ? 'bg-green-lt text-green' : 'bg-secondary-lt text-secondary') }}" style="font-size:0.8rem; font-weight:700">
                    #{{ $i + 1 }}
                </span>

                <div class="flex-fill">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-medium">{{ $user['name'] }}</span>
                        <span class="fw-bold text-red">@uang($user['totalOut'])</span>
                    </div>
                    {{-- Progress bar --}}
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-red" style="width: {{ $maxOut > 0 ? ($user['totalOut'] / $maxOut * 100) : 0 }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-secondary" style="font-size:0.7rem">
                            <i class="ti ti-trending-up text-green"></i> Masuk: @uang($user['totalIn'])
                        </span>
                        <span class="text-secondary" style="font-size:0.7rem">
                            Net: <span class="{{ $user['net'] >= 0 ? 'text-green' : 'text-red' }}">@uang($user['net'])</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
