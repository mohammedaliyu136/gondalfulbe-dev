<?php

namespace App\Services;

use App\Models\TransactionLines;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;

class AccountingService
{
    /**
     * Post a double-entry to TransactionLines.
     *
     * @param int    $debitAccountId   Chart-of-account ID to debit
     * @param int    $creditAccountId  Chart-of-account ID to credit
     * @param float  $amount
     * @param string $reference        e.g. 'Payroll', 'Center Cost', 'OSS Sale'
     * @param int    $referenceId      Primary record ID
     * @param int    $referenceSubId   Sub-record ID (use 0 if none)
     * @param string $date             Y-m-d
     */
    public static function post(
        int $debitAccountId,
        int $creditAccountId,
        float $amount,
        string $reference,
        int $referenceId,
        int $referenceSubId,
        string $date
    ): void {
        if ($amount <= 0 || !$debitAccountId || !$creditAccountId) {
            return;
        }

        $createdBy = Auth::check() ? Auth::user()->creatorId() : 1;

        // Debit entry
        static::upsertLine([
            'account_id'      => $debitAccountId,
            'reference'       => $reference,
            'reference_id'    => $referenceId,
            'reference_sub_id'=> $referenceSubId,
            'date'            => $date,
            'debit'           => $amount,
            'credit'          => 0,
            'created_by'      => $createdBy,
        ]);

        // Credit entry
        static::upsertLine([
            'account_id'      => $creditAccountId,
            'reference'       => $reference . ' Credit',
            'reference_id'    => $referenceId,
            'reference_sub_id'=> $referenceSubId,
            'date'            => $date,
            'debit'           => 0,
            'credit'          => $amount,
            'created_by'      => $createdBy,
        ]);
    }

    /**
     * Reverse/delete GL entries for a reference.
     */
    public static function reverse(string $reference, int $referenceId): void
    {
        TransactionLines::where('reference_id', $referenceId)
            ->where(function ($q) use ($reference) {
                $q->where('reference', $reference)
                  ->orWhere('reference', $reference . ' Credit');
            })
            ->delete();
    }

    /**
     * Post payroll expense when a payslip is paid.
     * Dr: Salary Expense Account   Cr: Bank/Cash Account
     */
    public static function postPayroll(
        float $amount,
        int $payslipId,
        int $bankChartAccountId,
        string $date
    ): void {
        $settings = Utility::settings();
        $salaryExpenseAccount = (int) ($settings['default_salary_expense_account'] ?? 0);

        if (!$salaryExpenseAccount) {
            return;
        }

        static::post(
            $salaryExpenseAccount,
            $bankChartAccountId,
            $amount,
            'Payroll',
            $payslipId,
            0,
            $date
        );
    }

    /**
     * Post center-operations cost when marked paid.
     * Dr: Operating Expense Account   Cr: Cash Account
     */
    public static function postCenterCost(
        float $amount,
        int $costId,
        string $date
    ): void {
        $settings = Utility::settings();
        $expenseAccount = (int) ($settings['default_ops_expense_account'] ?? 0);
        $cashAccount    = (int) ($settings['default_cash_account'] ?? 0);

        if (!$expenseAccount || !$cashAccount) {
            return;
        }

        static::post($expenseAccount, $cashAccount, $amount, 'Center Cost', $costId, 0, $date);
    }

    /**
     * Post OSS sale revenue.
     * Dr: Cash/AR Account   Cr: Sales Revenue Account
     */
    public static function postOssSale(
        float $amount,
        int $saleId,
        string $date
    ): void {
        $settings = Utility::settings();
        $revenueAccount = (int) ($settings['default_oss_revenue_account'] ?? 0);
        $cashAccount    = (int) ($settings['default_cash_account'] ?? 0);

        if (!$revenueAccount || !$cashAccount) {
            return;
        }

        static::post($cashAccount, $revenueAccount, $amount, 'OSS Sale', $saleId, 0, $date);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private static function upsertLine(array $data): void
    {
        $existing = TransactionLines::where('reference_id', $data['reference_id'])
            ->where('reference_sub_id', $data['reference_sub_id'])
            ->where('reference', $data['reference'])
            ->where('account_id', $data['account_id'])
            ->first();

        if ($existing) {
            $existing->update($data);
        } else {
            TransactionLines::create($data);
        }
    }
}
