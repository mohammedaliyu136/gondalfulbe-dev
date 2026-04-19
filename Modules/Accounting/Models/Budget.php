<?php

namespace Modules\Accounting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'budget_id', 'name', 'fiscal_year', 'start_date', 'end_date',
        'status', 'description', 'created_by',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    const STATUSES = ['draft' => 'Draft', 'active' => 'Active', 'closed' => 'Closed'];

    public function lines()
    {
        return $this->hasMany(BudgetLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalBudgeted(): float
    {
        return (float) $this->lines()->selectRaw(
            'jan+feb+mar+apr+may+jun+jul+aug+sep+oct+nov+dec as total'
        )->get()->sum('total');
    }

    public static function generateId(): string
    {
        $count = static::count() + 1;
        return 'BDG-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'closed' => 'bg-secondary',
            default  => 'bg-warning text-dark',
        };
    }
}
