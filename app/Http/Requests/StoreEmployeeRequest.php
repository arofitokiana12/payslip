<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Tout utilisateur authentifié peut créer un employé.
     * Ajuste selon tes rôles si nécessaire.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'matricule'         => ['required', 'string', 'max:50', 'unique:employees,matricule'],
            'position_id'       => ['nullable', 'integer', 'exists:positions,position_id'],
            'hire_date'         => ['nullable', 'date'],
            'contract_type'     => ['nullable', 'in:CDI,CDD,stage,freelance,other'],
            'contract_end_date' => ['nullable', 'date'],
            'status'            => ['nullable', 'in:active,inactive,on_leave,suspended'],
            'active'            => ['nullable', 'boolean'],
            'base_salary'       => ['nullable', 'numeric', 'min:0'],
            'company_id'        => ['required', 'integer', 'exists:companies,company_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'       => 'Le prénom est obligatoire.',
            'last_name.required'        => 'Le nom est obligatoire.',
            'matricule.required'        => 'Le matricule est obligatoire.',
            'matricule.unique'          => 'Ce matricule existe déjà.',
            'company_id.required'       => 'L\'entreprise est obligatoire.',
            'company_id.exists'         => 'L\'entreprise n\'existe pas.',
            'position_id.exists'        => 'Le poste n\'existe pas.',
            'contract_type.in'          => 'Type de contrat invalide (CDI, CDD, stage, freelance, other).',
            'status.in'                 => 'Statut invalide (active, inactive, on_leave, suspended).',
            'base_salary.min'           => 'Le salaire doit être positif.',
        ];
    }
}
