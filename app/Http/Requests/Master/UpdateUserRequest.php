<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'  => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $userId,
            'role'  => 'required|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email ini sudah digunakan.',
            'role.required'  => 'Role wajib dipilih.',
        ];
    }
}
