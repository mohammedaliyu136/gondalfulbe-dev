<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FarmActivity extends Model
{
protected $fillable = ['farm_field_id', 'activity_date', 'activity_type', 'description', 'worker', 'cost', 'image'];

    public function farmField()
    {
        return $this->belongsTo(FarmField::class);
    }
}
