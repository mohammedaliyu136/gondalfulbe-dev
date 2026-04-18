<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingMaterial extends Model
{
    protected $table    = 'training_materials';
    protected $fillable = ['event_id', 'material_name', 'quantity_distributed'];

    protected $casts = ['quantity_distributed' => 'decimal:2'];

    public function event() { return $this->belongsTo(TrainingEvent::class, 'event_id'); }
}
