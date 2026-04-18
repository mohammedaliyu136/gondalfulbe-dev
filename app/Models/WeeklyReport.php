<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyReport extends Model
{
    protected $table = 'weekly_reports';

    protected $fillable = ['filename', 'path', 'week_start', 'week_end', 'created_by'];

    protected $casts = [
        'week_start' => 'date',
        'week_end'   => 'date',
    ];
}
