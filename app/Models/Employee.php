<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    // --------------- TABLE / KEY ---------------
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';

    // --------------- MASS-ASSIGNMENT ---------------
    protected $fillable = [
        'first_name',
        'last_name',
        'matricule',
        'position_id',
        'hire_date',
        'contract_type',
        'contract_end_date',
        'status',
        'active',
        'base_salary',
        'company_id',
    ];

    // --------------- CASTS ---------------
    protected $casts = [
        'active'            => 'boolean',
        'hire_date'         => 'date',
        'contract_end_date' => 'date',
        'base_salary'       => 'float',
    ];

    // --------------- RELATIONS ---------------

    /** Un employé appartient à une entreprise */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /** Un employé a un poste */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }

    /** Un employé a plusieurs présences */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'employee_id');
    }

    /** Un employé a plusieurs congés */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'employee_id', 'employee_id');
    }

    /** Un employé a plusieurs absences */
    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class, 'employee_id', 'employee_id');
    }

    /** Un employé a plusieurs heures supplémentaires */
    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class, 'employee_id', 'employee_id');
    }

    /** Un employé a plusieurs fiches de paie */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'employee_id', 'employee_id');
    }

    // --------------- ROUTE MODEL BINDING ---------------

    /** Laravel utilise cette clé pour résoudre {employee} dans les routes */
    public function getRouteKeyName(): string
    {
        return 'employee_id';
    }

    // --------------- ACCESSORS ---------------

    /** Retourne le nom complet : "Prénom Nom" */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
