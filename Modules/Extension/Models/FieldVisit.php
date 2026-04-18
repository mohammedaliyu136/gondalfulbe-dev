<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;

class FieldVisit extends Model
{
    protected $table = 'field_visits';

    protected $fillable = [
        'visit_id', 'agent_id', 'visit_date', 'center', 'community',
        'notes', 'follow_up_required', 'follow_up_date', 'follow_up_note', 'created_by',
    ];

    protected $casts = [
        'visit_date'          => 'date',
        'follow_up_date'      => 'date',
        'follow_up_required'  => 'boolean',
    ];

    const TOPICS = ['Animal Health', 'Feeding', 'Hygiene', 'Breeding', 'Record Keeping', 'Other'];

    public function agent()    { return $this->belongsTo(ExtensionAgent::class, 'agent_id'); }
    public function farmers()  { return $this->hasMany(VisitFarmer::class, 'visit_id'); }
    public function topics()   { return $this->hasMany(VisitTopic::class, 'visit_id'); }
    public function photos()   { return $this->hasMany(VisitPhoto::class, 'visit_id'); }

    public function getFarmersCountAttribute(): int { return $this->farmers()->count(); }

    public static function generateVisitId(): string
    {
        $count = static::count() + 1;
        return 'VIS-' . date('Y') . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
