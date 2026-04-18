<?php

namespace Modules\Extension\Models;

use Illuminate\Database\Eloquent\Model;

class VisitFarmer extends Model
{
    protected $table    = 'visit_farmers';
    protected $fillable = ['visit_id', 'farmer_id', 'farmer_name'];

    public function visit() { return $this->belongsTo(FieldVisit::class, 'visit_id'); }
}
