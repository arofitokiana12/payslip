<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipItem extends Model
{
    use HasFactory;

    protected $table = 'payslip_items';
    protected $primaryKey = 'payslip_item_id';
    public $timestamps = false;

    protected $fillable = [
        'payslip_id',
        'item_type',
        'item_name',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class, 'payslip_id', 'payslip_id');
    }
}
