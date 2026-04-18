<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ExtensionAgent extends Model
{
    protected $table = 'extension_agents';

    protected $fillable = [
        'agent_code', 'user_id', 'name', 'phone',
        'assigned_communities', 'assigned_centers', 'join_date',
        'supervisor_id', 'status', 'created_by',
    ];

    protected $casts = [
        'assigned_communities' => 'array',
        'assigned_centers'     => 'array',
        'join_date'            => 'date',
    ];

    public function user()       { return $this->belongsTo(User::class, 'user_id'); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function visits()     { return $this->hasMany(FieldVisit::class, 'agent_id'); }

    public function getVisitsThisWeekAttribute(): int
    {
        return $this->visits()
            ->whereBetween('visit_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
    }

    public function isBelowTarget(): bool
    {
        return $this->visits_this_week < 2;
    }

    public static function generateAgentCode(): string
    {
        $count = static::count() + 1;
        return 'EA-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
