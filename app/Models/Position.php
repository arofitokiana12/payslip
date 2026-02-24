<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    // --------------- TABLE / KEY ---------------
    protected $table = 'positions';
    protected $primaryKey = 'position_id';

    // --------------- MASS-ASSIGNMENT ---------------
    protected $fillable = [
        'position_name',
        'company_id',
    ];

    // --------------- ROUTE MODEL BINDING ---------------

    /** Laravel utilise cette clé pour résoudre {position} dans les routes */
    public function getRouteKeyName(): string
    {
        return 'position_id';
    }

    // --------------- RELATIONS ---------------

    /** Un poste appartient à une entreprise */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /** Un poste peut avoir plusieurs employés */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id', 'position_id');
    }
}
