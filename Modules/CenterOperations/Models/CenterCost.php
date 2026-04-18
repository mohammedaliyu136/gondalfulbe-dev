<?php

namespace Modules\CenterOperations\Models;

use App\Models\FinancialAuditLog;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CenterCost extends Model
{
    protected static function boot(): void
    {
        parent::boot();
        static::created(fn($m)  => FinancialAuditLog::record('created', $m));
        static::updated(fn($m)  => FinancialAuditLog::record('updated', $m));
        static::deleted(fn($m)  => FinancialAuditLog::record('deleted', $m));
    }

    protected $table = 'center_costs';

    protected $fillable = [
        'cost_entry_id', 'mcc', 'category', 'amount', 'description',
        'receipt_path', 'period_start', 'period_end', 'status',
        'submitted_by', 'submitted_at', 'approved_by', 'approved_at',
        'rejected_by', 'rejected_at', 'rejection_reason',
        'paid_by', 'paid_at', 'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'period_start' => 'date',
        'period_end'   => 'date',
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'paid_at'      => 'datetime',
    ];

    const CATEGORIES = [
        'Labour', 'Cleaning Supplies', 'Maintenance & Repairs',
        'Utilities', 'Rent', 'Miscellaneous',
    ];
    const STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'paid'];
    const MCCS     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

    public function submitter()   { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver()    { return $this->belongsTo(User::class, 'approved_by'); }
    public function rejector()    { return $this->belongsTo(User::class, 'rejected_by'); }
    public function paidBy()      { return $this->belongsTo(User::class, 'paid_by'); }

    public function isEditable(): bool { return $this->status === 'draft'; }

    public static function generateCostEntryId(string $mcc): string
    {
        $prefix = 'CC-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $mcc), 0, 4));
        $count  = static::where('mcc', $mcc)->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-secondary',
            'submitted' => 'bg-warning text-dark',
            'approved'  => 'bg-info',
            'rejected'  => 'bg-danger',
            'paid'      => 'bg-success',
            default     => 'bg-secondary',
        };
    }
}
