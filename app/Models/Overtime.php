<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $table = 'overtime';
    protected $primaryKey = 'overtime_id';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'date',
        'hour'
    ];

    protected $casts = [
        'date' => 'date',
        'hour' => 'integer'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
