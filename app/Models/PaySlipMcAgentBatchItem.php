<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlipMcAgentBatchItem extends Model
{
    protected $fillable = [
        'batch_id',
        'amount',
        'agent_id',
        'status',
        'created_by',
    ];   
    
    public function batch()
    {
        return $this->belongsTo('App\Models\PaySlipMcAgentBatch', 'batch_id');
    }
    
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}