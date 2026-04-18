<?php

namespace Modules\Requisitions\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $table = 'gondal_requisition_items';

    protected $fillable = [
        'requisition_id', 'item_name', 'quantity', 'unit', 'estimated_cost', 'purpose',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'estimated_cost' => 'decimal:2',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'requisition_id');
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->estimated_cost;
    }
}
