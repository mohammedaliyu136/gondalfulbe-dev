<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionPaymentBatch extends Model
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
        return $this->belongsTo(PurchaseRequisition::class, 'pr_payment_batch_id');
    }
    
    public function paySlips()
    {
        return $this->belongsToMany(PurchaseRequisition::class, 'purchase_requisition_payment_batch_items')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    public function totalNetPayble()
    {
        return $this->paySlips->sum(function($pr) {
            return $pr->pivot->amount;
        });
    }
}

?>