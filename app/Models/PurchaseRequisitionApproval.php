<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseRequisitionApproval extends Model
{
    use HasFactory;
    protected $fillable = ['purchase_requisition_id', 'user_id', 'stage_level', 'stage_name', 'status', 'approved_by'];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }
}

