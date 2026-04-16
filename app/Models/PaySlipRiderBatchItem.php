<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlipRiderBatchItem extends Model
{
    protected $fillable = [
        'batch_id',
        'amount',
        'trip_ids',
        'rider_id',
        'status',
        'created_by',
    ];   
    
    public function batch()
    {
        return $this->belongsTo('App\Models\PaySlipRiderBatch', 'batch_id');
    }
    
    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id');
    }
}