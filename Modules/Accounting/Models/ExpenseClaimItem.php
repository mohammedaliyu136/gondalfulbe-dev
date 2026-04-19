<?php

namespace Modules\Accounting\Models;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Model;

class ExpenseClaimItem extends Model
{
    protected $fillable = [
        'expense_claim_id', 'date', 'description',
        'chart_account_id', 'amount', 'receipt_path',
    ];

    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];

    public function claim()
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_account_id');
    }
}
