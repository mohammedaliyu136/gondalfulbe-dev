<?php

namespace Modules\SponsorPortal\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAgent extends Model
{
    protected $table = 'project_agents';

    protected $fillable = [
        'project_id',
        'agent_id',
        'enrolled_date',
    ];

    protected $casts = [
        'enrolled_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(SponsorProject::class, 'project_id');
    }
}
