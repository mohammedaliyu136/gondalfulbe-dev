<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlipMcAgentBatch extends Model
{
    protected $fillable = [
        'batch_id',
        'status',
        'start_date',
        'end_date',
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
        return $this->hasMany('App\Models\PaySlipMcAgentBatchItem', 'id', 'batch_id')->get();
    }
}

?>