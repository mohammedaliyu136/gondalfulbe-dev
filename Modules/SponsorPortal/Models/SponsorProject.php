<?php

namespace Modules\SponsorPortal\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vender;
use Modules\Cooperatives\Models\Cooperative;

class SponsorProject extends Model
{
    protected $table = 'sponsor_projects';

    protected $fillable = [
        'project_code', 'sponsor_id', 'title', 'description',
        'start_date', 'end_date', 'budget', 'status',
        'objectives', 'focus_areas', 'created_by',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'budget'      => 'decimal:2',
        'objectives'  => 'array',
        'focus_areas' => 'array',
    ];

    const STATUSES    = ['Draft', 'Active', 'Paused', 'Completed'];
    const FOCUS_AREAS = ['milk', 'extension', 'oss', 'logistics'];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public function farmers()
    {
        return $this->belongsToMany(Vender::class, 'project_farmers', 'project_id', 'farmer_id')
                    ->withPivot('enrolled_date');
    }

    public function cooperatives()
    {
        return $this->belongsToMany(Cooperative::class, 'project_cooperatives', 'project_id', 'cooperative_id')
                    ->withPivot('enrolled_date');
    }

    public static function generateCode(): string
    {
        $count = static::count() + 1;
        return 'PROJ-' . date('Y') . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
