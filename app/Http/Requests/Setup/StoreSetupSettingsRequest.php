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
            'timezone' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'currency.required' => 'Mata uang wajib diisi.',
            'timezone.required' => 'Timezone wajib dipilih.',
        ];
    }
}
