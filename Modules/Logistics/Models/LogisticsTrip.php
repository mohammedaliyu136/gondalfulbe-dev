<?php

namespace Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Rider;

class LogisticsTrip extends Model
{
    protected $table = 'logistics_trips';

    protected $fillable = [
        'trip_id', 'trip_date', 'mcc_source', 'destination', 'rider_id',
        'vehicle_registration', 'departure_time', 'arrival_time',
        'litres_transported', 'collection_batch_id', 'fuel_cost',
        'other_expenses', 'other_expenses_description', 'status',
        'delivery_note_path', 'cost_per_litre', 'created_by',
    ];

    protected $casts = [
        'trip_date'          => 'date',
        'litres_transported' => 'decimal:2',
        'fuel_cost'          => 'decimal:2',
        'other_expenses'     => 'decimal:2',
        'cost_per_litre'     => 'decimal:4',
    ];

    const STATUSES = ['Scheduled', 'In Transit', 'Completed', 'Delayed'];
    const MCCS     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id');
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->fuel_cost + (float) $this->other_expenses;
    }

    public function computeCostPerLitre(): float
    {
        $litres = (float) $this->litres_transported;
        return $litres > 0 ? $this->total_cost / $litres : 0;
    }

    public static function generateTripId(string $mcc): string
    {
        $prefix = 'TRIP-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $mcc), 0, 4));
        $count  = static::where('mcc_source', $mcc)->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Scheduled'  => 'bg-info',
            'In Transit' => 'bg-warning text-dark',
            'Completed'  => 'bg-success',
            'Delayed'    => 'bg-danger',
            default      => 'bg-secondary',
        };
    }
}
