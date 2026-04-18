<?php

namespace Modules\SponsorPortal\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCenter extends Model
{
    protected $table = 'project_centers';

    protected $fillable = [
        'project_id',
        'mcc_name',
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
