<?php

namespace Modules\Cooperatives\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vender;

class Cooperative extends Model
{
    protected $fillable = [
        'code',
        'name',
        'location',
        'leader_name',
        'leader_phone',
        'leader_user_id',
        'site_location',
        'formation_date',
        'average_daily_supply',
        'status',
        'created_by',
    ];

    protected $casts = [
        'formation_date'      => 'date',
        'average_daily_supply' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function farmers()
    {
        return $this->hasMany(Vender::class, 'cooperative_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Auto-generate a cooperative code based on its location and ID.
     * e.g. COOP-YOL-0023
     */
    public static function generateCode(int $id, ?string $location): string
    {
        $loc = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $location ?? 'GEN'), 0, 3));
        $loc = $loc ?: 'GEN';
        return 'COOP-' . $loc . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Convenient accessor so views can call $cooperative->farmer_count.
     */
    public function getFarmerCountAttribute(): int
    {
        return $this->farmers()->count();
    }
}
