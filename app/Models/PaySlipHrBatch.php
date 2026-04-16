<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlipHrBatch extends Model
{
    protected $fillable = [
        'batch_id',
        'status',
        'created_by',
    ];  
    
    public static $statues = [
        'Pending',
        'Approved',
        'Approved',
        'Initialized',
        'Paid',
        'Canceled'
    ];
    
    public function batch()
    {
        return $this->belongsTo(PaySlip::class, 'batch_id')->get();
    }
    
    public function paySlips()
    {
        return $this->hasMany(PaySlip::class, 'pay_slip_hr_batch_id', 'id');
    }

    public function totalNetPayble()
    {
        return $this->paySlips()->sum('net_payble');
    }
}

?>