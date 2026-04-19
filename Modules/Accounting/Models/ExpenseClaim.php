<?php

namespace Modules\Accounting\Models;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ExpenseClaim extends Model
{
    protected $fillable = [
        'claim_id', 'employee_id', 'claim_date', 'title', 'description',
        'total_amount', 'status', 'approved_by', 'approved_at',
        'paid_by', 'paid_at', 'rejection_reason', 'created_by',
    ];

    protected $casts = [
        'claim_date'  => 'date',
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
        'total_amount'=> 'decimal:2',
    ];

    const STATUSES = [
        'draft'     => ['label' => 'Draft',     'class' => 'bg-secondary'],
        'submitted' => ['label' => 'Submitted',  'class' => 'bg-warning text-dark'],
        'approved'  => ['label' => 'Approved',   'class' => 'bg-info'],
        'rejected'  => ['label' => 'Rejected',   'class' => 'bg-danger'],
        'paid'      => ['label' => 'Paid',       'class' => 'bg-success'],
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(ExpenseClaimItem::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public static function generateId(): string
    {
        $count = static::count() + 1;
        return 'EXP-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
