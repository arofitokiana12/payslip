<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    // --------------- TABLE / KEY ---------------
    protected $table = 'companies';
    protected $primaryKey = 'company_id';

    // --------------- MASS-ASSIGNMENT ---------------
    protected $fillable = [
        'company_name',
        'date_creation',
        'nif',
        'stat',
        'rcs',
        'logo',
        'adress',
        'email',
        'active',
    ];

    protected $casts = [
        'active'          => 'boolean',
        'date_creation'   => 'date',
    ];

    // --------------- ROUTE MODEL BINDING ---------------

    public function getRouteKeyName(): string
    {
        return 'company_id';
    }

    // --------------- RELATIONS ---------------
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'company_id', 'company_id');
    }

    /** Une entreprise a plusieurs postes */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'company_id', 'company_id');
    }
}
