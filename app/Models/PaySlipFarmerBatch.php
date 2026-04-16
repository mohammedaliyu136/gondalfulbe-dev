<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlipFarmerBatch extends Model
{
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