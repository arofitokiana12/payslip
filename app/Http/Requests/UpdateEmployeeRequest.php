<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // On récupère l'ID de l'employé en cours de modification
        // pour exclure son propre matricule lors de la vérification unique
        $employeeId = $this->route('employee');

        return [
            'first_name'        => ['nullable', 'string', 'max:255'],
            'last_name'         => ['nullable', 'string', 'max:255'],
            'matricule'         => ['nullable', 'string', 'max:50', Rule::unique('employees', 'matricule')->ignore($employeeId, 'employee_id')],
            'position_id'       => ['nullable', 'integer', 'exists:positions,position_id'],
            'hire_date'         => ['nullable', 'date'],
            'contract_type'     => ['nullable', 'in:CDI,CDD,stage,freelance,other'],
            'contract_end_date' => ['nullable', 'date'],
            'status'            => ['nullable', 'in:active,inactive,on_leave,suspended'],
            'active'            => ['nullable', 'boolean'],
            'base_salary'       => ['nullable', 'numeric', 'min:0'],
            'company_id'        => ['nullable', 'integer', 'exists:companies,company_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'matricule.unique'      => 'Ce matricule existe déjà sur un autre employé.',
            'position_id.exists'    => 'Le poste n\'existe pas.',
            'company_id.exists'     => 'L\'entreprise n\'existe pas.',
            'contract_type.in'      => 'Type de contrat invalide.',
            'status.in'             => 'Statut invalide.',
            'base_salary.min'       => 'Le salaire doit être positif.',
        ];
    }
}
