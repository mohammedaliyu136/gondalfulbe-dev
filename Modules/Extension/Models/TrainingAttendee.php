<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingAttendee extends Model
{
    protected $table    = 'training_attendees';
    protected $fillable = ['event_id', 'farmer_id', 'farmer_name'];

    public function event() { return $this->belongsTo(TrainingEvent::class, 'event_id'); }
}
