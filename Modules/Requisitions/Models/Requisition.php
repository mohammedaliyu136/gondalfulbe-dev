<?php

namespace Modules\Requisitions\Models;

use App\Models\FinancialAuditLog;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Requisition extends Model
{
    protected static function boot(): void
    {
        parent::boot();
        static::created(fn($m)  => FinancialAuditLog::record('created', $m));
        static::updated(fn($m)  => FinancialAuditLog::record('updated', $m));
        static::deleted(fn($m)  => FinancialAuditLog::record('deleted', $m));
    }

    protected $table = 'gondal_requisitions';

    protected $fillable = [
        'requisition_ref', 'request_date', 'requester_id', 'center',
        'title', 'description', 'total_estimated_cost', 'priority',
        'status', 'approved_amount', 'payment_reference',
        'rejection_reason', 'current_approver_level', 'created_by',
    ];

    protected $casts = [
        'request_date'        => 'date',
        'total_estimated_cost' => 'decimal:2',
        'approved_amount'     => 'decimal:2',
    ];

    const PRIORITIES = ['Low', 'Medium', 'High', 'Emergency'];
    const STATUSES   = [
        'pending', 'supervisor_approved', 'manager_approved',
        'approved', 'rejected', 'paid', 'completed',
    ];
    const MCCS = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

    public function requester() { return $this->belongsTo(User::class, 'requester_id'); }
    public function items()     { return $this->hasMany(RequisitionItem::class, 'requisition_id'); }
    public function approvals() { return $this->hasMany(RequisitionApproval::class, 'requisition_id')->orderBy('acted_at'); }

    public function getAmountTierAttribute(): string
    {
        $cost = (float) $this->total_estimated_cost;
        if ($cost < 50000)  return 'finance';
        if ($cost <= 200000) return 'component_lead';
        return 'executive_director';
    }

    public static function generateRef(): string
    {
        $year  = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'REQ-' . $year . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'Low'       => 'bg-secondary',
            'Medium'    => 'bg-info',
            'High'      => 'bg-warning text-dark',
            'Emergency' => 'bg-danger',
            default     => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'             => 'bg-secondary',
            'supervisor_approved' => 'bg-info',
            'manager_approved'    => 'bg-primary',
            'approved'            => 'bg-success',
            'rejected'            => 'bg-danger',
            'paid'                => 'bg-success',
            'completed'           => 'bg-success',
            default               => 'bg-secondary',
        };
    }
}
