<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i:s',
        'check_out' => 'datetime:H:i:s'
    ];

    // Relation avec Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
