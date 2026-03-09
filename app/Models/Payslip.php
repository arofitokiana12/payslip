<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $table = 'payslips';
    protected $primaryKey = 'payslip_id';

    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'gross_salary',
        'net_salary',
        'total_deductions',
        'total_earnings',
        'status'
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_earnings' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(PayslipItem::class, 'payslip_id', 'payslip_id');
    }

    public function earnings()
    {
        return $this->items()->where('item_type', 'earning');
    }

    public function deductions()
    {
        return $this->items()->where('item_type', 'deduction');
    }

    public function taxes()
    {
        return $this->items()->where('item_type', 'tax');
    }
}
