<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiderTrip extends Model
{
    use HasFactory;

    protected $table = 'rider_trips';

    protected $fillable = [
        'rider_id',
        'trip_date',
        'trip_count',
        'amount_per_trip',
        'total_amount',
        'status',
        'validated_by'
    ];

    protected $casts = [
        'trip_date' => 'date',
        'amount_per_trip' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
    
        public static $statues = [
        'Draft',
        'Pending',
        'Not Paid',
        'Paid',
        'Canceled',
        'Reversed'
    ];

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }
}
