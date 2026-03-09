<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialContribution extends Model
{
    use HasFactory;

    protected $table = 'social_contributions';
    protected $primaryKey = 'contribution_id';

    protected $fillable = [
        'name',
        'code',
        'employee_rate',
        'employer_rate',
        'ceiling',
        'active',
        'description'
    ];

    protected $casts = [
        'employee_rate' => 'decimal:2',
        'employer_rate' => 'decimal:2',
        'ceiling' => 'decimal:2',
        'active' => 'boolean'
    ];
}
