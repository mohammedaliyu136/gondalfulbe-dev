<?php

namespace Modules\CenterOperations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\CenterOperations\Models\CenterCost;
use App\Services\AccountingService;

class CenterOperationsController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('manage center operations')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = CenterCost::where('created_by', Auth::user()->creatorId());

        if (Auth::user()->assignedMcc()) {
            $query->where('mcc', Auth::user()->assignedMcc());
        }

        if ($request->filled('mcc'))      $query->where('mcc', $request->mcc);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('date_from')) $query->where('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $costs      = $query->orderByDesc('id')->paginate(25)->withQueryString();
        $mccs       = CenterCost::MCCS;
        $categories = CenterCost::CATEGORIES;
        $statuses   = CenterCost::STATUSES;
        $countByStatus = [];
        foreach (CenterCost::STATUSES as $s) {
            $countByStatus[$s] = CenterCost::where('created_by', Auth::user()->creatorId())->where('status', $s)->count();
        }

        return view('centeroperations::index', compact('costs', 'mccs', 'categories', 'statuses', 'countByStatus'));
    }

    public function create()
    {
        if (! Auth::user()->can('create center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $mccs       = CenterCost::MCCS;
        $categories = CenterCost::CATEGORIES;

        return view('centeroperations::create', compact('mccs', 'categories'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'mcc'          => 'required|in:' . implode(',', CenterCost::MCCS),
            'category'     => 'required|in:' . implode(',', CenterCost::CATEGORIES),
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'nullable|string',
            'period_start' => 'nullable|date',
            'period_end'   => 'nullable|date|after_or_equal:period_start',
            'receipt'      => 'nullable|file|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('center-cost-receipts', 'public');
        }

        CenterCost::create([
            'cost_entry_id' => CenterCost::generateCostEntryId($request->mcc),
            'mcc'           => $request->mcc,
            'category'      => $request->category,
            'amount'        => $request->amount,
            'description'   => $request->description,
            'receipt_path'  => $receiptPath,
            'period_start'  => $request->period_start,
            'period_end'    => $request->period_end,
            'status'        => 'draft',
            'created_by'    => Auth::user()->creatorId(),
        ]);

        return redirect()->route('center-costs.index')->with('success', __('Cost entry saved as draft.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage center operations')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        return view('centeroperations::show', compact('cost'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('edit center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if (! $cost->isEditable()) {
            return redirect()->back()->with('error', __('Only draft entries can be edited.'));
        }

        $mccs       = CenterCost::MCCS;
        $categories = CenterCost::CATEGORIES;

        return view('centeroperations::edit', compact('cost', 'mccs', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('edit center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if (! $cost->isEditable()) {
            return redirect()->back()->with('error', __('Only draft entries can be edited.'));
        }

        $validator = \Validator::make($request->all(), [
            'mcc'      => 'required|in:' . implode(',', CenterCost::MCCS),
            'category' => 'required|in:' . implode(',', CenterCost::CATEGORIES),
            'amount'   => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $receiptPath = $cost->receipt_path;
        if ($request->hasFile('receipt')) {
            if ($receiptPath) Storage::disk('public')->delete($receiptPath);
            $receiptPath = $request->file('receipt')->store('center-cost-receipts', 'public');
        }

        $cost->update([
            'mcc'          => $request->mcc,
            'category'     => $request->category,
            'amount'       => $request->amount,
            'description'  => $request->description,
            'receipt_path' => $receiptPath,
            'period_start' => $request->period_start,
            'period_end'   => $request->period_end,
        ]);

        return redirect()->route('center-costs.index')->with('success', __('Cost entry updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('delete center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if (! $cost->isEditable()) {
            return redirect()->back()->with('error', __('Only draft entries can be deleted.'));
        }

        if ($cost->receipt_path) Storage::disk('public')->delete($cost->receipt_path);
        $cost->delete();

        return redirect()->route('center-costs.index')->with('success', __('Entry deleted.'));
    }

    public function submit(int $id)
    {
        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $cost->update(['status' => 'submitted', 'submitted_by' => Auth::id(), 'submitted_at' => now()]);
        return redirect()->route('center-costs.show', $id)->with('success', __('Cost entry submitted for approval.'));
    }

    public function approve(int $id)
    {
        if (! Auth::user()->can('approve center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $cost->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        return redirect()->route('center-costs.show', $id)->with('success', __('Cost entry approved.'));
    }

    public function reject(Request $request, int $id)
    {
        if (! Auth::user()->can('approve center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $cost->update([
            'status'           => 'rejected',
            'rejected_by'      => Auth::id(),
            'rejected_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);
        return redirect()->route('center-costs.show', $id)->with('success', __('Cost entry rejected.'));
    }

    public function markPaid(int $id)
    {
        if (! Auth::user()->can('pay center cost')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cost = CenterCost::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $cost->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);

        // Post to GL: Dr Operating Expense / Cr Cash
        AccountingService::postCenterCost((float) $cost->amount, $cost->id, now()->toDateString());

        return redirect()->route('center-costs.show', $id)->with('success', __('Cost entry marked as paid.'));
    }

    public function export()
    {
        if (! Auth::user()->can('manage center operations')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $costs    = CenterCost::where('created_by', Auth::user()->creatorId())->orderByDesc('id')->get();
        $rows     = [['Entry ID', 'MCC', 'Category', 'Amount (NGN)', 'Description', 'Status', 'Period']];
        foreach ($costs as $c) {
            $rows[] = [$c->cost_entry_id, $c->mcc, $c->category, $c->amount, $c->description, $c->status, ($c->period_start?->format('d/m/Y') ?? '') . ' - ' . ($c->period_end?->format('d/m/Y') ?? '')];
        }

        $filename = 'center_costs_' . date('Y-m-d') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        $callback = function () use ($rows) {
            $h = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($h, $row);
            fclose($h);
        };

        return response()->stream($callback, 200, $headers);
    }
}
