{{-- Activity HTML fragment — returned by /api/dashboard/activity --}}
<table class="table table-sm table-vcenter mb-0">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th class="text-end text-green">Masuk</th>
            <th class="text-end text-red">Keluar</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $day)
        <tr>
            <td>{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('D, d M') }}</td>
            <td class="text-end">
                @if($day['debit'] > 0)
                    <span class="text-green">+{{ number_format($day['debit'], 0, ',', '.') }}</span>
                @else
                    <span class="text-secondary">-</span>
                @endif
            </td>
            <td class="text-end">
                @if($day['credit'] > 0)
                    <span class="text-red">-{{ number_format($day['credit'], 0, ',', '.') }}</span>
                @else
                    <span class="text-secondary">-</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center text-secondary py-3">Tidak ada aktivitas 7 hari terakhir</td>
        </tr>
        @endforelse
    </tbody>
</table>
