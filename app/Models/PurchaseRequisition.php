<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseRequisition extends Model
{
    use HasFactory;
    
    public const PAYMENT_STATUS_PENDING = 1;
    public const PAYMENT_STATUS_UNPAID = 2;
    public const PAYMENT_STATUS_PARTIALLY_PAID = 3;
    public const PAYMENT_STATUS_PAID = 4;
    public const PAYMENT_STATUS_FAILED = 5;

    protected $fillable = [ 
        'pr_id',
        'title',
        'comment',
        'requested_by', 
        'department_id',
        'priority',
        'status', 
        'payment_status', 
        'current_approval_phase', 
        'created_by',
        'txn_status',
        'txn_ref',
        'txn_description'
        ];

    public function approvals()
    {
        return $this->hasMany(PurchaseRequisitionApproval::class);
    }
    
    // app/Models/PurchaseRequisition.php

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class)->where('status', 1);
    }
    

    
    public function totalEstimatedCost()
    {
        return $this->items()->selectRaw('SUM(quantity * estimated_cost) as total')->value('total') ?? 0;
    }

    
    public function totalApprovedCost()
    {
        return $this->items()->selectRaw('SUM(approved_quantity * approved_cost) as total')->value('total') ?? 0;
    }
    
    public function PrDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function paymentBatches()
    {
        return $this->belongsToMany(PurchaseRequisitionPaymentBatch::class, 'purchase_requisition_payment_batch_items')
                    ->withPivot('amount')
                    ->withTimestamps();
    }
    
    // New Relationship for Service Providers
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }



    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_UNPAID => 'Unpaid',
            self::PAYMENT_STATUS_PARTIALLY_PAID => 'Partially Paid',
            self::PAYMENT_STATUS_PAID => 'Paid',
        ];
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::paymentStatuses()[$this->payment_status] ?? 'Unknown';
    }
    

    
}
