<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_name'        => 'required|string|max:255',
            'logo_media_id'   => 'nullable|integer',
            'favicon_media_id' => 'nullable|integer',
            'timezone'        => 'required|string',
            'currency'        => 'required|string|max:10',
            'mail_host'       => 'nullable|string',
            'mail_port'       => 'nullable|numeric',
            'mail_username'   => 'nullable|string',
            'mail_password'   => 'nullable|string',
            'mail_encryption' => 'nullable|in:ssl,tls',
            'mail_from'       => 'nullable|email',
        ];
    }

    public function messages(): array
    {
        return [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'timezone.required' => 'Timezone wajib dipilih.',
            'currency.required' => 'Mata uang wajib diisi.',
            'mail_from.email'   => 'Format email pengirim tidak valid.',
        ];
    }
}
