<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MutationExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return collect($this->transactions);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Ref',
            'Oleh',
            'Kategori',
            'Deskripsi',
            'Kas Masuk',
            'Kas Keluar'
        ];
    }

    public function map($trx): array
    {
        return [
            Carbon::parse($trx->transaction_date)->format('d/m/Y'),
            $trx->trans_code == 1 ? 'IN-'.str_pad($trx->id, 4, '0', STR_PAD_LEFT) : 'OUT-'.str_pad($trx->id, 4, '0', STR_PAD_LEFT),
            $trx->creator->name ?? '-',
            $trx->category->name ?? '-',
            $trx->description,
            $trx->trans_code == 1 ? $trx->amount : '',
            $trx->trans_code == 2 ? $trx->amount : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
