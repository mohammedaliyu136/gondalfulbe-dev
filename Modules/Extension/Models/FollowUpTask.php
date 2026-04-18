<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vender;

class FollowUpTask extends Model
{
    protected $table = 'follow_up_tasks';

    protected $fillable = [
        'visit_id', 'agent_id', 'farmer_id', 'due_date', 'note', 'status', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function visit()
    {
        return $this->belongsTo(FieldVisit::class, 'visit_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function farmer()
    {
        return $this->belongsTo(Vender::class, 'farmer_id');
    }
}
