<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;

class VisitTopic extends Model
{
    protected $table    = 'visit_topics';
    protected $fillable = ['visit_id', 'topic'];

    public function visit() { return $this->belongsTo(FieldVisit::class, 'visit_id'); }
}
