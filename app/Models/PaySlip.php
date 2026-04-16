<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlip extends Model
{
    protected $fillable = [
        'employee_id',
        'net_payble',
        'basic_salary',
        'salary_month',
        'status',
        'allowance',
        'commission',
        'loan',
        'saturation_deduction',
        'txn_ref',
        'pay_slip_hr_batches_id',
        'txn_status',
        'txn_description',
        'other_payment',
        'overtime',
        'created_by',
    ];

    public static function employee($id)
    {
        return Employee::find($id);
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }
    
    public function parentBatch()
    {
        return $this->belongsTo(PaySlipHrBatch::class, 'parent_batch_id');
    }

}
