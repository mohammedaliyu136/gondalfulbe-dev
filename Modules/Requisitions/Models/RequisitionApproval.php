<?php

namespace Modules\Requisitions\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RequisitionApproval extends Model
{
    protected $table = 'gondal_requisition_approvals';

    protected $fillable = [
        'requisition_id', 'actor_id', 'action', 'level', 'comments', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function actor()       { return $this->belongsTo(User::class, 'actor_id'); }
    public function requisition() { return $this->belongsTo(Requisition::class, 'requisition_id'); }
}
