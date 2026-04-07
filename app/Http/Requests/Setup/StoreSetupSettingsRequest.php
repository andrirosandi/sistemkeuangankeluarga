<?php

namespace App\Http\Requests\Setup;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetupSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => 'required|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'currency.required' => 'Mata uang wajib diisi.',
        ];
    }
}
