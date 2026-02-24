<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // --------------- MASS-ASSIGNMENT ---------------
    protected $fillable = [
        'name',
        'email',
        'user_name',
        'password',
        'role_id',
        'company_id',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    // --------------- RELATIONS ---------------

    /** Un utilisateur appartient à un rôle */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /** Un utilisateur appartient à une entreprise */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    // --------------- RBAC METHODS ---------------

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        if (is_array($roles)) {
            return in_array(strtolower($this->role->name), array_map('strtolower', $roles));
        }

        return strtolower($this->role->name) === strtolower($roles);
    }

    /**
     * Vérifie si l'utilisateur est super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Vérifie si l'utilisateur est admin (super_admin ou admin)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'admin']);
    }

    /**
     * Vérifie si l'utilisateur peut gérer les employés
     */
    public function canManageEmployees(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'manager', 'hr']);
    }

    /**
     * Vérifie si l'utilisateur peut gérer la paie
     */
    public function canManagePayroll(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'accountant']);
    }

    /**
     * Vérifie si l'utilisateur peut voir les rapports
     */
    public function canViewReports(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'manager', 'accountant']);
    }

    /**
     * Vérifie si l'utilisateur appartient à la même entreprise
     */
    public function isSameCompany(int $companyId): bool
    {
        return $this->company_id === $companyId;
    }

    // --------------- ACCESSORS ---------------

    /**
     * Retourne le nom du rôle
     */
    public function getRoleNameAttribute(): ?string
    {
        return $this->role?->name;
    }
}
