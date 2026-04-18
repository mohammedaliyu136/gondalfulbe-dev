<?php

namespace Modules\MilkCollection\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vender;
use App\Models\User;

class MilkCollection extends Model
{
    protected $table = 'milk_collections';

    protected $fillable = [
        'collection_id', 'date', 'time', 'mcc', 'farmer_id',
        'quantity_litres', 'quality_grade', 'temperature_celsius',
        'rejection_reason', 'collection_batch_id', 'recorded_by',
        'notes', 'photo_path', 'created_by',
    ];

    protected $casts = [
        'date'               => 'date',
        'quantity_litres'    => 'decimal:2',
        'temperature_celsius' => 'decimal:2',
    ];

    const MCCS   = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
    const GRADES = ['A' => 'Premium', 'B' => 'Standard', 'C' => 'Rejected'];

    public function farmer()
    {
        return $this->belongsTo(Vender::class, 'farmer_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function generateCollectionId(string $mcc): string
    {
        $prefix = 'MC-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $mcc), 0, 4));
        $count  = static::where('mcc', $mcc)->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    public function getGradeLabelAttribute(): string
    {
        return self::GRADES[$this->quality_grade] ?? $this->quality_grade;
    }

    public function getGradeBadgeClassAttribute(): string
    {
        return match ($this->quality_grade) {
            'A'     => 'bg-success',
            'B'     => 'bg-warning text-dark',
            'C'     => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
