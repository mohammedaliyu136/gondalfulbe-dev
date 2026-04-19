<?php

namespace Modules\Accounting\Models;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Reconciliation extends Model
{
    protected $fillable = [
        'reconciliation_id', 'bank_account_id', 'statement_date',
        'opening_balance', 'closing_balance', 'status',
        'reconciled_at', 'reconciled_by', 'created_by',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'reconciled_at'  => 'datetime',
        'opening_balance'=> 'decimal:2',
        'closing_balance'=> 'decimal:2',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function items()
    {
        return $this->hasMany(ReconciliationItem::class);
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function isOpen(): bool { return $this->status === 'open'; }

    public function totalMatched(): float
    {
        return (float) $this->items()->where('is_matched', true)->sum('amount');
    }

    public function totalUnmatched(): float
    {
        return (float) $this->items()->where('is_matched', false)->sum('amount');
    }

    public static function generateId(): string
    {
        $count = static::count() + 1;
        return 'REC-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
