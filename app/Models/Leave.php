<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory;

    protected $table = 'leaves';
    protected $primaryKey = 'leave_id';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Attribut calculé : durée en jours
    protected $appends = ['duration_days'];

    public function getDurationDaysAttribute()
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        return $start->diffInDays($end) + 1;
    }

    // Relation avec Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Scopes utiles
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('start_date', $year);
    }
}
