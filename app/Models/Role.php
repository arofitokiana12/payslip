<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // --------------- TABLE / KEY ---------------
    protected $table = 'roles';
    protected $primaryKey = 'role_id';

    // --------------- MASS-ASSIGNMENT ---------------
    protected $fillable = [
        'name',
        'description',
    ];

    // --------------- ROUTE MODEL BINDING ---------------
    public function getRouteKeyName(): string
    {
        return 'role_id';
    }

    // --------------- RELATIONS ---------------

    /** Un rôle peut être attribué à plusieurs utilisateurs */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    // --------------- HELPER METHODS ---------------

    /**
     * Vérifie si le rôle a un nom spécifique
     */
    public function hasName(string $roleName): bool
    {
        return strtolower($this->name) === strtolower($roleName);
    }

    /**
     * Rôles système prédéfinis
     */
    public static function getSystemRoles(): array
    {
        return [
            'super_admin' => 'Super Administrateur',
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'hr' => 'Ressources Humaines',
            'accountant' => 'Comptable',
            'employee' => 'Employé',
        ];
    }
}
