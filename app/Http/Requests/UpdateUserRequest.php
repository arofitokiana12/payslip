<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Admins peuvent modifier les users, ou l'user peut modifier son propre profil
        $user = $this->user();
        $targetUserId = $this->route('user');

        return $user?->isAdmin() || $user?->id === $targetUserId;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'user_name' => ['nullable', 'string', 'max:255', Rule::unique('users', 'user_name')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'integer', 'exists:roles,role_id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,company_id'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'user_name.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role_id.exists' => 'Le rôle sélectionné n\'existe pas.',
            'company_id.exists' => 'L\'entreprise sélectionnée n\'existe pas.',
        ];
    }

    /**
     * Préparer les données pour validation
     */
    protected function prepareForValidation(): void
    {
        // Les non-admins ne peuvent pas changer leur rôle ou entreprise
        if ($this->user() && !$this->user()->isAdmin()) {
            $this->merge([
                'role_id' => $this->user()->role_id,
                'company_id' => $this->user()->company_id,
            ]);
        }
    }
}
