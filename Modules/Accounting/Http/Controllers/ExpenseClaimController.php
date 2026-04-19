<?php

namespace Modules\Accounting\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Services\AccountingService;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Accounting\Models\ExpenseClaim;
use Modules\Accounting\Models\ExpenseClaimItem;

class ExpenseClaimController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $uid    = Auth::user()->creatorId();
        $claims = ExpenseClaim::where('created_by', $uid)
            ->with('employee')->orderByDesc('claim_date')->paginate(25);

        $summary = [
            'draft'     => ExpenseClaim::where('created_by', $uid)->where('status', 'draft')->count(),
            'submitted' => ExpenseClaim::where('created_by', $uid)->where('status', 'submitted')->count(),
            'approved'  => ExpenseClaim::where('created_by', $uid)->where('status', 'approved')->count(),
            'paid'      => ExpenseClaim::where('created_by', $uid)->where('status', 'paid')->count(),
        ];

        return view('accounting::expense-claim.index', compact('claims', 'summary'));
    }

    public function create()
    {
        if (! Auth::user()->can('create expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $employees = Employee::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $accounts  = ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->where('is_enabled', 1)->orderBy('code')->get();
        return view('accounting::expense-claim.create', compact('employees', 'accounts'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'claim_date'          => 'required|date',
            'title'               => 'required|string|max:255',
            'items'               => 'required|array|min:1',
            'items.*.date'        => 'required|date',
            'items.*.description' => 'required|string',
            'items.*.amount'      => 'required|numeric|min:0.01',
        ]);

        $total = collect($request->items)->sum('amount');

        $claim = ExpenseClaim::create([
            'claim_id'    => ExpenseClaim::generateId(),
            'employee_id' => $request->employee_id,
            'claim_date'  => $request->claim_date,
            'title'       => $request->title,
            'description' => $request->description,
            'total_amount'=> $total,
            'status'      => 'draft',
            'created_by'  => Auth::user()->creatorId(),
        ]);

        foreach ($request->items as $idx => $item) {
            $receiptPath = null;
            if ($request->hasFile("receipts.$idx")) {
                $receiptPath = $request->file("receipts.$idx")
                    ->store('uploads/expense-claims', 'public');
            }
            ExpenseClaimItem::create([
                'expense_claim_id'  => $claim->id,
                'date'              => $item['date'],
                'description'       => $item['description'],
                'chart_account_id'  => $item['chart_account_id'] ?? null,
                'amount'            => $item['amount'],
                'receipt_path'      => $receiptPath,
            ]);
        }

        return redirect()->route('accounting.expense-claims.show', $claim->id)
            ->with('success', __('Expense claim created.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $claim = ExpenseClaim::where('created_by', Auth::user()->creatorId())
            ->with(['employee', 'items.chartAccount', 'approver'])->findOrFail($id);
        return view('accounting::expense-claim.show', compact('claim'));
    }

    public function submit(int $id)
    {
        $claim = ExpenseClaim::where('created_by', Auth::user()->creatorId())
            ->where('status', 'draft')->findOrFail($id);
        $claim->update(['status' => 'submitted']);
        return redirect()->back()->with('success', __('Claim submitted for approval.'));
    }

    public function approve(int $id)
    {
        if (! Auth::user()->can('approve expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $claim = ExpenseClaim::where('created_by', Auth::user()->creatorId())
            ->where('status', 'submitted')->findOrFail($id);
        $claim->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return redirect()->back()->with('success', __('Claim approved.'));
    }

    public function reject(Request $request, int $id)
    {
        if (! Auth::user()->can('approve expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $claim = ExpenseClaim::where('created_by', Auth::user()->creatorId())
            ->whereIn('status', ['submitted', 'approved'])->findOrFail($id);
        $claim->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        return redirect()->back()->with('success', __('Claim rejected.'));
    }

    public function pay(int $id)
    {
        if (! Auth::user()->can('pay expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $claim = ExpenseClaim::where('created_by', Auth::user()->creatorId())
            ->where('status', 'approved')->findOrFail($id);

        $claim->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);

        // Post each line to GL
        foreach ($claim->items as $item) {
            if ($item->chart_account_id) {
                $settings     = Utility::settings();
                $cashAccount  = (int) ($settings['default_cash_account'] ?? 0);
                if ($cashAccount) {
                    AccountingService::post(
                        $item->chart_account_id,
                        $cashAccount,
                        (float) $item->amount,
                        'Expense Claim',
                        $claim->id,
                        $item->id,
                        $item->date->toDateString()
                    );
                }
            }
        }

        return redirect()->back()->with('success', __('Claim marked as paid and posted to GL.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('delete expense claim')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $claim = ExpenseClaim::where('created_by', Auth::user()->creatorId())
            ->where('status', 'draft')->findOrFail($id);
        $claim->delete();
        return redirect()->route('accounting.expense-claims.index')
            ->with('success', __('Claim deleted.'));
    }
}
