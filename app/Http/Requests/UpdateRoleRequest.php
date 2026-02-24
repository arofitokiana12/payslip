<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => ['nullable', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId, 'role_id')],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ce nom de rôle existe déjà.',
        ];
    }
}
