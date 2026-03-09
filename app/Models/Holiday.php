<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';
    protected $primaryKey = 'holiday_id';

    protected $fillable = [
        'name',
        'date',
        'year',
        'recurring'
    ];

    protected $casts = [
        'date' => 'date',
        'recurring' => 'boolean'
    ];
}
