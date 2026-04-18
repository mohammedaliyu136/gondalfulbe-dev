<?php

namespace Modules\Requisitions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Requisitions\Models\Requisition;
use Modules\Requisitions\Models\RequisitionItem;
use Modules\Requisitions\Models\RequisitionApproval;

class RequisitionsController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('manage requisitions')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $myQuery = Requisition::with('requester')
            ->where('created_by', Auth::user()->creatorId())
            ->where('requester_id', Auth::id());

        $pendingQuery = Requisition::with('requester')
            ->where('created_by', Auth::user()->creatorId())
            ->whereIn('status', ['pending', 'supervisor_approved', 'manager_approved']);

        if (Auth::user()->assignedMcc()) {
            $myQuery->where('center', Auth::user()->assignedMcc());
            $pendingQuery->where('center', Auth::user()->assignedMcc());
        }

        foreach ([$myQuery, $pendingQuery] as $q) {
            if ($request->filled('status'))   $q->where('status', $request->status);
            if ($request->filled('center'))   $q->where('center', $request->center);
            if ($request->filled('priority')) $q->where('priority', $request->priority);
        }

        $myRequisitions     = $myQuery->orderByDesc('created_at')->paginate(15, ['*'], 'my_page')->withQueryString();
        $pendingApprovals   = $pendingQuery->orderByDesc('created_at')->paginate(15, ['*'], 'pending_page')->withQueryString();
        $mccs               = Requisition::MCCS;
        $priorities         = Requisition::PRIORITIES;
        $statuses           = Requisition::STATUSES;

        return view('requisitions::index', compact('myRequisitions', 'pendingApprovals', 'mccs', 'priorities', 'statuses'));
    }

    public function create()
    {
        if (! Auth::user()->can('create requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $mccs       = Requisition::MCCS;
        $priorities = Requisition::PRIORITIES;

        return view('requisitions::create', compact('mccs', 'priorities'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'title'                    => 'required|string|max:255',
            'request_date'             => 'required|date',
            'center'                   => 'nullable|in:' . implode(',', Requisition::MCCS),
            'priority'                 => 'required|in:' . implode(',', Requisition::PRIORITIES),
            'items'                    => 'required|array|min:1',
            'items.*.item_name'        => 'required|string',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.estimated_cost'   => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $totalCost = collect($request->items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['estimated_cost'] ?? 0));

        $req = Requisition::create([
            'requisition_ref'      => Requisition::generateRef(),
            'request_date'         => $request->request_date,
            'requester_id'         => Auth::id(),
            'center'               => $request->center,
            'title'                => $request->title,
            'description'          => $request->description,
            'total_estimated_cost' => $totalCost,
            'priority'             => $request->priority,
            'status'               => 'pending',
            'created_by'           => Auth::user()->creatorId(),
        ]);

        foreach ($request->items as $item) {
            RequisitionItem::create([
                'requisition_id' => $req->id,
                'item_name'      => $item['item_name'],
                'quantity'       => $item['quantity'],
                'unit'           => $item['unit'] ?? null,
                'estimated_cost' => $item['estimated_cost'],
                'purpose'        => $item['purpose'] ?? null,
            ]);
        }

        return redirect()->route('requisitions.show', $req->id)->with('success', __('Requisition submitted successfully.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage requisitions')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $req = Requisition::with(['requester', 'items', 'approvals.actor'])
            ->where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        return view('requisitions::show', compact('req'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('edit requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $req  = Requisition::with('items')->where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if ($req->status !== 'pending') {
            return redirect()->back()->with('error', __('Only pending requisitions can be edited.'));
        }

        $mccs       = Requisition::MCCS;
        $priorities = Requisition::PRIORITIES;

        return view('requisitions::edit', compact('req', 'mccs', 'priorities'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('edit requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $req = Requisition::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if ($req->status !== 'pending') {
            return redirect()->back()->with('error', __('Only pending requisitions can be edited.'));
        }

        $totalCost = collect($request->items ?? [])->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['estimated_cost'] ?? 0));

        $req->update([
            'title'                => $request->title,
            'description'          => $request->description,
            'center'               => $request->center,
            'priority'             => $request->priority,
            'request_date'         => $request->request_date,
            'total_estimated_cost' => $totalCost,
        ]);

        $req->items()->delete();
        foreach ($request->items ?? [] as $item) {
            RequisitionItem::create([
                'requisition_id' => $req->id,
                'item_name'      => $item['item_name'],
                'quantity'       => $item['quantity'],
                'unit'           => $item['unit'] ?? null,
                'estimated_cost' => $item['estimated_cost'],
                'purpose'        => $item['purpose'] ?? null,
            ]);
        }

        return redirect()->route('requisitions.show', $req->id)->with('success', __('Requisition updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('manage requisitions')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $req = Requisition::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if ($req->status !== 'pending') {
            return redirect()->back()->with('error', __('Only pending requisitions can be deleted.'));
        }

        $req->items()->delete();
        $req->approvals()->delete();
        $req->delete();

        return redirect()->route('requisitions.index')->with('success', __('Requisition deleted.'));
    }

    public function approve(Request $request, int $id)
    {
        if (! Auth::user()->can('approve requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $req    = Requisition::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $level  = $req->current_approver_level;
        $newStatus = match ($level) {
            1 => 'supervisor_approved',
            2 => 'manager_approved',
            3 => 'approved',
            default => 'approved',
        };

        RequisitionApproval::create([
            'requisition_id' => $req->id,
            'actor_id'       => Auth::id(),
            'action'         => 'approved',
            'level'          => $level,
            'comments'       => $request->comments,
            'acted_at'       => now(),
        ]);

        $req->update([
            'status'                => $newStatus,
            'current_approver_level' => $level + 1,
        ]);

        return redirect()->route('requisitions.show', $req->id)->with('success', __('Requisition approved.'));
    }

    public function reject(Request $request, int $id)
    {
        if (! Auth::user()->can('approve requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $req = Requisition::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        RequisitionApproval::create([
            'requisition_id' => $req->id,
            'actor_id'       => Auth::id(),
            'action'         => 'rejected',
            'level'          => $req->current_approver_level,
            'comments'       => $request->rejection_reason,
            'acted_at'       => now(),
        ]);

        $req->update(['status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);

        return redirect()->route('requisitions.show', $req->id)->with('success', __('Requisition rejected.'));
    }

    public function markPaid(Request $request, int $id)
    {
        if (! Auth::user()->can('pay requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $req = Requisition::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $req->update([
            'status'            => 'paid',
            'approved_amount'   => $request->approved_amount ?? $req->total_estimated_cost,
            'payment_reference' => $request->payment_reference,
        ]);

        return redirect()->route('requisitions.show', $req->id)->with('success', __('Requisition marked as paid.'));
    }

    public function showConfirm(int $id)
    {
        $req = Requisition::with('items', 'requester')
            ->where('created_by', Auth::user()->creatorId())
            ->whereIn('status', ['approved', 'paid'])
            ->findOrFail($id);

        if ($req->requester_id !== Auth::id()) {
            return redirect()->back()->with('error', __('Only the requesting farmer can confirm receipt.'));
        }

        return view('requisitions::confirm', compact('req'));
    }

    public function confirmReceipt(int $id)
    {
        $req = Requisition::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if ($req->requester_id !== Auth::id()) {
            return redirect()->back()->with('error', __('Only the requesting farmer can confirm receipt.'));
        }

        $req->update(['status' => 'completed']);
        return redirect()->route('requisitions.show', $req->id)->with('success', __('Receipt confirmed. Requisition completed.'));
    }

    public function export()
    {
        if (! Auth::user()->can('manage requisitions')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $reqs     = Requisition::with('requester')->where('created_by', Auth::user()->creatorId())->orderByDesc('request_date')->get();
        $rows     = [['Ref', 'Date', 'Title', 'Center', 'Total Cost (NGN)', 'Priority', 'Status', 'Requested By']];
        foreach ($reqs as $r) {
            $rows[] = [$r->requisition_ref, $r->request_date->format('d/m/Y'), $r->title, $r->center ?? '', number_format($r->total_estimated_cost, 2), $r->priority, $r->status, $r->requester?->name ?? ''];
        }

        $filename = 'requisitions_' . date('Y-m-d') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        $callback = function () use ($rows) {
            $h = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($h, $row);
            fclose($h);
        };

        return response()->stream($callback, 200, $headers);
    }
}
