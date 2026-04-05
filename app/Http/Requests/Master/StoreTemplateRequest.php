<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules untuk store dan update template (identik).
     */
    public function rules(): array
    {
        return [
            'description'          => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,id',
            'trans_code'           => 'required|in:1,2',
            'details'              => 'required|array|min:1',
            'details.*.description' => 'required|string|max:255',
            'details.*.amount'     => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required'          => 'Deskripsi template wajib diisi.',
            'category_id.required'          => 'Kategori wajib dipilih.',
            'details.required'              => 'Detail item wajib diisi minimal 1.',
            'details.*.description.required' => 'Deskripsi item wajib diisi.',
            'details.*.amount.required'     => 'Jumlah item wajib diisi.',
        ];
    }
}
