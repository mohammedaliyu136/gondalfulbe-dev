<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingEvent extends Model
{
    protected $table = 'training_events';

    protected $fillable = [
        'event_id', 'title', 'event_date', 'location', 'center',
        'facilitators', 'topics_covered', 'notes', 'created_by',
    ];

    protected $casts = [
        'event_date'  => 'date',
        'facilitators' => 'array',
    ];

    public function attendees()  { return $this->hasMany(TrainingAttendee::class, 'event_id'); }
    public function materials()  { return $this->hasMany(TrainingMaterial::class, 'event_id'); }

    public function getAttendeesCountAttribute(): int { return $this->attendees()->count(); }

    public static function generateEventId(): string
    {
        $count = static::count() + 1;
        return 'EVT-' . date('Y') . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
