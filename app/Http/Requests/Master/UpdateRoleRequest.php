<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name'          => 'required|string|max:255|unique:roles,name,' . $roleId,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
            'visibility'    => 'nullable|array',
            'visibility.*'  => 'integer|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique'   => 'Nama role ini sudah digunakan.',
        ];
    }
}
