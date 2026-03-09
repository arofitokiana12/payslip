<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    use HasFactory;

    protected $table = 'tax_brackets';
    protected $primaryKey = 'bracket_id';

    protected $fillable = [
        'min_salary',
        'max_salary',
        'tax_rate',
        'fixed_amount',
        'order',
        'active'
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'active' => 'boolean'
    ];
}
