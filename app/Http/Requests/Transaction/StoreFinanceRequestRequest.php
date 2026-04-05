<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules untuk store dan update pengajuan keuangan (identik).
     */
    public function rules(): array
    {
        return [
            'category_id'         => 'required|exists:categories,id',
            'request_date'        => 'required|date',
            'priority'            => 'required|in:low,normal,high',
            'description'         => 'required|string|max:255',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount'      => 'required|numeric|min:0',
            'media_ids'           => 'nullable|array',
            'media_ids.*'         => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'         => 'Kategori wajib dipilih.',
            'request_date.required'        => 'Tanggal pengajuan wajib diisi.',
            'priority.required'            => 'Prioritas wajib dipilih.',
            'description.required'         => 'Deskripsi wajib diisi.',
            'items.required'               => 'Detail item wajib diisi minimal 1.',
            'items.*.description.required' => 'Deskripsi item wajib diisi.',
            'items.*.amount.required'      => 'Jumlah item wajib diisi.',
        ];
    }
}
