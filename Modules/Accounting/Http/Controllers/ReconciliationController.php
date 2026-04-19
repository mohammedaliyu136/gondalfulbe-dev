<?php

namespace Modules\Accounting\Http\Controllers;

use App\Models\BankAccount;
use App\Models\TransactionLines;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Accounting\Models\Reconciliation;
use Modules\Accounting\Models\ReconciliationItem;

class ReconciliationController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage reconciliation')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $reconciliations = Reconciliation::where('created_by', Auth::user()->creatorId())
            ->with('bankAccount')->orderByDesc('statement_date')->paginate(20);
        $bankAccounts = BankAccount::where('created_by', Auth::user()->creatorId())->get();
        return view('accounting::reconciliation.index', compact('reconciliations', 'bankAccounts'));
    }

    public function create()
    {
        if (! Auth::user()->can('create reconciliation')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $bankAccounts = BankAccount::where('created_by', Auth::user()->creatorId())->get();
        return view('accounting::reconciliation.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create reconciliation')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'bank_account_id'  => 'required|exists:bank_accounts,id',
            'statement_date'   => 'required|date',
            'opening_balance'  => 'required|numeric',
            'closing_balance'  => 'required|numeric',
            'items'            => 'required|array|min:1',
            'items.*.date'     => 'required|date',
            'items.*.description' => 'required|string',
            'items.*.type'     => 'required|in:debit,credit',
            'items.*.amount'   => 'required|numeric|min:0.01',
        ]);

        $rec = Reconciliation::create([
            'reconciliation_id' => Reconciliation::generateId(),
            'bank_account_id'   => $request->bank_account_id,
            'statement_date'    => $request->statement_date,
            'opening_balance'   => $request->opening_balance,
            'closing_balance'   => $request->closing_balance,
            'status'            => 'open',
            'created_by'        => Auth::user()->creatorId(),
        ]);

        foreach ($request->items as $item) {
            ReconciliationItem::create([
                'reconciliation_id' => $rec->id,
                'date'        => $item['date'],
                'description' => $item['description'],
                'type'        => $item['type'],
                'amount'      => $item['amount'],
                'reference'   => $item['reference'] ?? null,
                'is_matched'  => false,
            ]);
        }

        return redirect()->route('accounting.reconciliation.show', $rec->id)
            ->with('success', __('Reconciliation created. Please match the items below.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage reconciliation')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $rec = Reconciliation::where('created_by', Auth::user()->creatorId())
            ->with(['bankAccount', 'items'])->findOrFail($id);

        // Load unmatched transaction lines for this bank account in the statement period
        $accountId = $rec->bankAccount->chart_account_id ?? 0;
        $lines = $accountId
            ? TransactionLines::where('account_id', $accountId)
                ->whereBetween('date', [
                    $rec->statement_date->copy()->startOfMonth()->toDateString(),
                    $rec->statement_date->toDateString(),
                ])
                ->get()
            : collect();

        return view('accounting::reconciliation.show', compact('rec', 'lines'));
    }

    /** Match a statement item to a TransactionLine. */
    public function matchItem(Request $request, int $recId, int $itemId)
    {
        if (! Auth::user()->can('reconcile bank')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $item = ReconciliationItem::whereHas('reconciliation', function ($q) use ($recId) {
            $q->where('id', $recId)->where('created_by', Auth::user()->creatorId());
        })->findOrFail($itemId);

        $item->update([
            'is_matched'          => true,
            'transaction_line_id' => $request->transaction_line_id,
        ]);

        return redirect()->back()->with('success', __('Item matched.'));
    }

    /** Unmatch a previously matched item. */
    public function unmatchItem(int $recId, int $itemId)
    {
        $item = ReconciliationItem::whereHas('reconciliation', function ($q) use ($recId) {
            $q->where('id', $recId)->where('created_by', Auth::user()->creatorId());
        })->findOrFail($itemId);

        $item->update(['is_matched' => false, 'transaction_line_id' => null]);
        return redirect()->back()->with('success', __('Match removed.'));
    }

    /** Mark the entire reconciliation as complete. */
    public function finalize(int $id)
    {
        if (! Auth::user()->can('reconcile bank')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rec = Reconciliation::where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        $unmatched = $rec->items()->where('is_matched', false)->count();
        if ($unmatched > 0) {
            return redirect()->back()->with('error',
                __(':n item(s) still unmatched. Match or remove them before finalising.', ['n' => $unmatched]));
        }

        $rec->update([
            'status'        => 'reconciled',
            'reconciled_at' => now(),
            'reconciled_by' => Auth::id(),
        ]);

        return redirect()->route('accounting.reconciliation.index')
            ->with('success', __('Reconciliation finalised.'));
    }
}
