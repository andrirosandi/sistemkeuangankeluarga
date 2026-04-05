<?php

namespace App\Http\Requests\Setup;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetupMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validasi SMTP bersifat conditional: hanya berlaku jika user tidak memilih 'skip'.
     */
    public function rules(): array
    {
        if ($this->boolean('skip')) {
            return [];
        }

        return [
            'mail_host'       => 'required|string',
            'mail_port'       => 'required|numeric',
            'mail_username'   => 'required|string',
            'mail_password'   => 'required|string',
            'mail_encryption' => 'required|in:ssl,tls',
            'mail_from'       => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'mail_host.required'       => 'SMTP Host wajib diisi.',
            'mail_port.required'       => 'SMTP Port wajib diisi.',
            'mail_username.required'   => 'SMTP Username wajib diisi.',
            'mail_password.required'   => 'SMTP Password wajib diisi.',
            'mail_encryption.required' => 'Jenis enkripsi wajib dipilih.',
            'mail_from.required'       => 'Email pengirim wajib diisi.',
            'mail_from.email'          => 'Format email pengirim tidak valid.',
        ];
    }
}
