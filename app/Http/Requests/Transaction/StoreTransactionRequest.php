<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules untuk store dan update realisasi (identik).
     */
    public function rules(): array
    {
        return [
            'category_id'         => 'required|exists:categories,id',
            'transaction_date'    => 'required|date',
            'description'         => 'required|string|max:255',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'         => 'Kategori wajib dipilih.',
            'transaction_date.required'    => 'Tanggal transaksi wajib diisi.',
            'description.required'         => 'Deskripsi wajib diisi.',
            'items.required'               => 'Detail item wajib diisi minimal 1.',
            'items.*.description.required' => 'Deskripsi item wajib diisi.',
            'items.*.amount.required'      => 'Jumlah item wajib diisi.',
        ];
    }
}
