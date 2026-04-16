<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmField extends Model
{
    protected $fillable = [
        'workspace_id', 'field_name', 'size', 'crop_type', 'activities', 'latitude', 'longitude',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
    
    public function farmActivities()
    {
        return $this->hasMany(FarmActivity::class, 'farm_field_id');
    }

}
