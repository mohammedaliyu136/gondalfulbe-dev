<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlipFarmerBatch extends Model
{
    protected static function boot(): void
    {
        parent::boot();
        static::created(fn($m)  => FinancialAuditLog::record('created', $m));
        static::updated(fn($m)  => FinancialAuditLog::record('updated', $m));
        static::deleted(fn($m)  => FinancialAuditLog::record('deleted', $m));
    }

    protected $fillable = [
        'batch_id',
        'status',
        'created_by',
    ];  
    
    public static $statues = [
        'Pending',
        'Approved',
        'Not Approved',
        'Initialized',
        'Paid',
        'Canceled',
        'Reversed'
    ];
    
    public function items()
    {
        return $this->hasMany('App\Models\PaySlipFarmerBatchItem', 'id', 'batch_id')->get();
    }
}

?>