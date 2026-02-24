<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Admins et super_admins peuvent créer des utilisateurs
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'user_name' => ['nullable', 'string', 'max:255', 'unique:users,user_name'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
            'company_id' => ['required', 'integer', 'exists:companies,company_id'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'user_name.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role_id.required' => 'Le rôle est obligatoire.',
            'role_id.exists' => 'Le rôle sélectionné n\'existe pas.',
            'company_id.required' => 'L\'entreprise est obligatoire.',
            'company_id.exists' => 'L\'entreprise sélectionnée n\'existe pas.',
        ];
    }

    /**
     * Préparer les données pour validation
     */
    protected function prepareForValidation(): void
    {
        // Si l'utilisateur connecté n'est pas super_admin,
        // forcer la company_id à celle de l'utilisateur
        if ($this->user() && !$this->user()->isSuperAdmin()) {
            $this->merge([
                'company_id' => $this->user()->company_id,
            ]);
        }
    }
}
