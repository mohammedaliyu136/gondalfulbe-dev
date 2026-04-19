<?php

namespace Modules\Accounting\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Accounting\Models\Budget;
use Modules\Accounting\Models\BudgetLine;

class BudgetController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $budgets = Budget::where('created_by', Auth::user()->creatorId())
            ->orderByDesc('fiscal_year')->get();
        return view('accounting::budget.index', compact('budgets'));
    }

    public function create()
    {
        if (! Auth::user()->can('create budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $accounts = ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->where('is_enabled', 1)->orderBy('code')->get();
        return view('accounting::budget.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'fiscal_year' => 'required|digits:4',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'description' => 'nullable|string',
        ]);

        $budget = Budget::create([
            'budget_id'   => Budget::generateId(),
            'name'        => $request->name,
            'fiscal_year' => $request->fiscal_year,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => 'draft',
            'description' => $request->description,
            'created_by'  => Auth::user()->creatorId(),
        ]);

        // Save budget lines
        if ($request->has('lines')) {
            foreach ($request->lines as $line) {
                if (empty($line['chart_account_id'])) continue;
                BudgetLine::create([
                    'budget_id'        => $budget->id,
                    'chart_account_id' => $line['chart_account_id'],
                    'description'      => $line['description'] ?? null,
                    'jan' => $line['jan'] ?? 0, 'feb' => $line['feb'] ?? 0,
                    'mar' => $line['mar'] ?? 0, 'apr' => $line['apr'] ?? 0,
                    'may' => $line['may'] ?? 0, 'jun' => $line['jun'] ?? 0,
                    'jul' => $line['jul'] ?? 0, 'aug' => $line['aug'] ?? 0,
                    'sep' => $line['sep'] ?? 0, 'oct' => $line['oct'] ?? 0,
                    'nov' => $line['nov'] ?? 0, 'dec' => $line['dec'] ?? 0,
                ]);
            }
        }

        return redirect()->route('accounting.budget.show', $budget->id)
            ->with('success', __('Budget created successfully.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $budget = Budget::where('created_by', Auth::user()->creatorId())
            ->with(['lines.chartAccount'])->findOrFail($id);
        return view('accounting::budget.show', compact('budget'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('edit budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $budget = Budget::where('created_by', Auth::user()->creatorId())
            ->with('lines')->findOrFail($id);
        if ($budget->status === 'closed') {
            return redirect()->back()->with('error', __('Cannot edit a closed budget.'));
        }
        $accounts = ChartOfAccount::where('created_by', Auth::user()->creatorId())
            ->where('is_enabled', 1)->orderBy('code')->get();
        return view('accounting::budget.edit', compact('budget', 'accounts'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('edit budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $budget = Budget::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'status'      => 'required|in:draft,active,closed',
        ]);

        $budget->update($request->only('name', 'start_date', 'end_date', 'status', 'description'));

        // Replace lines
        $budget->lines()->delete();
        if ($request->has('lines')) {
            foreach ($request->lines as $line) {
                if (empty($line['chart_account_id'])) continue;
                BudgetLine::create(array_merge($line, ['budget_id' => $budget->id]));
            }
        }

        return redirect()->route('accounting.budget.show', $budget->id)
            ->with('success', __('Budget updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('delete budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        Budget::where('created_by', Auth::user()->creatorId())->findOrFail($id)->delete();
        return redirect()->route('accounting.budget.index')->with('success', __('Budget deleted.'));
    }

    public function activate(int $id)
    {
        if (! Auth::user()->can('edit budget')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        // Only one active budget at a time
        Budget::where('created_by', Auth::user()->creatorId())->where('status', 'active')
            ->update(['status' => 'draft']);
        Budget::where('created_by', Auth::user()->creatorId())->findOrFail($id)
            ->update(['status' => 'active']);
        return redirect()->back()->with('success', __('Budget activated.'));
    }
}
