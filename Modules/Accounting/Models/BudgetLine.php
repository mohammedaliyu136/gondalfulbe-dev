<?php

namespace Modules\Accounting\Models;

use App\Models\ChartOfAccount;
use App\Models\TransactionLines;
use Illuminate\Database\Eloquent\Model;

class BudgetLine extends Model
{
    protected $fillable = [
        'budget_id', 'chart_account_id', 'description',
        'jan','feb','mar','apr','may','jun',
        'jul','aug','sep','oct','nov','dec',
    ];

    private static array $monthMap = [
        1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'may',6=>'jun',
        7=>'jul',8=>'aug',9=>'sep',10=>'oct',11=>'nov',12=>'dec',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_account_id');
    }

    public function annualBudget(): float
    {
        $sum = 0;
        foreach (self::$monthMap as $col) { $sum += (float) $this->{$col}; }
        return $sum;
    }

    /** Total actual spend posted to this chart account within the budget period. */
    public function actualSpend(): float
    {
        $budget = $this->budget;
        return (float) TransactionLines::where('account_id', $this->chart_account_id)
            ->whereBetween('date', [$budget->start_date->toDateString(), $budget->end_date->toDateString()])
            ->sum('debit');
    }

    public function variance(): float
    {
        return $this->annualBudget() - $this->actualSpend();
    }
}
