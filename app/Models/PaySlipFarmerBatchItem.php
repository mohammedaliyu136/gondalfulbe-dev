<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlipFarmerBatchItem extends Model
{
    protected $fillable = [
        'batch_id',
        'status',
        'created_by',
    ];   
    
    public function batch()
    {
        return $this->hasOne('App\Models\PaySlipFarmerBatch', 'id', 'batch_id')->first();
    }
    
    public function vender()
    {
        return $this->belongsTo(Vender::class, 'vender_id');
    }
}