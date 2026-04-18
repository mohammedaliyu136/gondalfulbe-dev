<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;

class VisitPhoto extends Model
{
    protected $table    = 'visit_photos';
    protected $fillable = ['visit_id', 'photo_path', 'caption'];

    public function visit() { return $this->belongsTo(FieldVisit::class, 'visit_id'); }
}
