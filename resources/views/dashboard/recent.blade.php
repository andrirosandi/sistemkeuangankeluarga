{{-- Recent Transactions HTML fragment — returned by /api/dashboard/recent --}}
@if(empty($data))
    <div class="text-center text-secondary py-3">
        <i class="ti ti-database-off" style="font-size:24px"></i>
        <div class="mt-1">Belum ada transaksi</div>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-vcenter mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Kategori</th>
                    <th class="text-end">Jumlah</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $trx)
                <tr>
                    <td class="text-secondary" style="font-size:0.8rem">
                        {{ \Carbon\Carbon::parse($trx['date'])->translatedFormat('d M Y') }}
                    </td>
                    <td>{{ Str::limit($trx['description'], 35) }}</td>
                    <td>
                        <span class="badge" style="background: {{ $trx['color'] }}20; color: {{ $trx['color'] }}; font-size:0.7rem">
                            {{ $trx['category'] }}
                        </span>
                    </td>
                    <td class="text-end fw-bold {{ $trx['type'] === 'in' ? 'text-green' : 'text-red' }}">
                        {{ $trx['type'] === 'in' ? '+' : '-' }}{{ number_format($trx['amount'], 0, ',', '.') }}
                    </td>
                    <td class="text-secondary" style="font-size:0.8rem">{{ $trx['creator'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
