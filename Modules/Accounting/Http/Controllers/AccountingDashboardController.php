<?php

namespace Modules\Accounting\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\TransactionLines;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Budget;
use Modules\Accounting\Models\ExpenseClaim;
use Modules\Accounting\Models\Reconciliation;

class AccountingDashboardController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage accounting')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $uid = Auth::user()->creatorId();

        // ── Cash position ──────────────────────────────────────────────
        $bankAccounts = BankAccount::where('created_by', $uid)->get();
        $totalCash    = $bankAccounts->sum('opening_balance');

        // ── AR: unpaid / overdue invoices ──────────────────────────────
        $invoices    = Invoice::where('created_by', $uid)->whereIn('status', [2, 3])->get(); // Unpaid / Partially paid
        $totalAR     = $invoices->sum(fn($i) => $i->getDue());
        $overdueAR   = $invoices->filter(fn($i) => $i->due_date < now()->toDateString())->sum(fn($i) => $i->getDue());

        // AR ageing buckets
        $arAgeing = $this->invoiceAgeing($invoices);

        // ── AP: unpaid / overdue bills ─────────────────────────────────
        $bills       = Bill::where('created_by', $uid)->whereIn('status', [2, 3])->get();
        $totalAP     = $bills->sum(fn($b) => $b->getDue());
        $overdueAP   = $bills->filter(fn($b) => $b->due_date < now()->toDateString())->sum(fn($b) => $b->getDue());

        $apAgeing    = $this->billAgeing($bills);

        // ── Monthly revenue vs expense (last 6 months) ─────────────────
        $revenueChart = $this->monthlyRevenue($uid);
        $expenseChart = $this->monthlyExpense($uid);

        // ── Recent transactions ─────────────────────────────────────────
        $recentRevenues = Revenue::with('customer')
            ->where('created_by', $uid)->latest()->limit(5)->get();
        $recentPayments = Payment::with('vender')
            ->where('created_by', $uid)->latest()->limit(5)->get();

        // ── Budget summary ─────────────────────────────────────────────
        $activeBudget = Budget::where('created_by', $uid)
            ->where('status', 'active')
            ->with('lines')
            ->first();

        // ── Pending expense claims ─────────────────────────────────────
        $pendingClaims = ExpenseClaim::where('created_by', $uid)
            ->whereIn('status', ['submitted', 'approved'])
            ->count();

        // ── Open reconciliations ───────────────────────────────────────
        $openRecon = Reconciliation::where('created_by', $uid)
            ->where('status', 'open')->count();

        return view('accounting::dashboard.index', compact(
            'bankAccounts', 'totalCash',
            'totalAR', 'overdueAR', 'arAgeing',
            'totalAP', 'overdueAP', 'apAgeing',
            'revenueChart', 'expenseChart',
            'recentRevenues', 'recentPayments',
            'activeBudget', 'pendingClaims', 'openRecon'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function invoiceAgeing($invoices): array
    {
        $buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
        $today   = now();
        foreach ($invoices as $inv) {
            $due  = $inv->getDue();
            $days = (int) $today->diffInDays($inv->due_date, false);
            if ($days >= 0)          $buckets['current'] += $due;
            elseif ($days >= -30)    $buckets['1_30']    += $due;
            elseif ($days >= -60)    $buckets['31_60']   += $due;
            elseif ($days >= -90)    $buckets['61_90']   += $due;
            else                     $buckets['over_90'] += $due;
        }
        return $buckets;
    }

    private function billAgeing($bills): array
    {
        $buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
        $today   = now();
        foreach ($bills as $b) {
            $due  = $b->getDue();
            $days = (int) $today->diffInDays($b->due_date, false);
            if ($days >= 0)          $buckets['current'] += $due;
            elseif ($days >= -30)    $buckets['1_30']    += $due;
            elseif ($days >= -60)    $buckets['31_60']   += $due;
            elseif ($days >= -90)    $buckets['61_90']   += $due;
            else                     $buckets['over_90'] += $due;
        }
        return $buckets;
    }

    private function monthlyRevenue(int $uid): array
    {
        $rows = Revenue::where('created_by', $uid)
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date,'%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')->orderBy('month')->get()
            ->pluck('total', 'month')->toArray();
        return $this->fillMonths($rows);
    }

    private function monthlyExpense(int $uid): array
    {
        $rows = Payment::where('created_by', $uid)
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date,'%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')->orderBy('month')->get()
            ->pluck('total', 'month')->toArray();
        return $this->fillMonths($rows);
    }

    private function fillMonths(array $data): array
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $key           = now()->subMonths($i)->format('Y-m');
            $result[$key]  = $data[$key] ?? 0;
        }
        return $result;
    }
}
