<?php

namespace Modules\Extension\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Extension\Models\ExtensionAgent;
use Modules\Extension\Models\FieldVisit;
use App\Models\User;

class ExtensionAgentsController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $agents = ExtensionAgent::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $mccs   = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        // Agents with fewer than 2 visits this week
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd   = now()->endOfWeek()->toDateString();

        $visitsThisWeek = [];
        if (class_exists(FieldVisit::class)) {
            $visitsThisWeek = FieldVisit::whereBetween('visit_date', [$weekStart, $weekEnd])
                ->selectRaw('agent_id, count(*) as total')
                ->groupBy('agent_id')
                ->pluck('total', 'agent_id')
                ->toArray();
        }

        $belowTarget = $agents->filter(fn($a) => ($visitsThisWeek[$a->id] ?? 0) < 2);

        return view('extension::agents.index', compact('agents', 'mccs', 'belowTarget'));
    }

    public function create()
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $supervisors = User::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $mccs        = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        return view('extension::agents.create', compact('supervisors', 'mccs'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'join_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ExtensionAgent::create([
            'agent_code'           => ExtensionAgent::generateAgentCode(),
            'name'                 => $request->name,
            'phone'                => $request->phone,
            'assigned_communities' => $request->assigned_communities ? json_decode($request->assigned_communities, true) : null,
            'assigned_centers'     => $request->assigned_centers ?? null,
            'join_date'            => $request->join_date,
            'supervisor_id'        => $request->supervisor_id,
            'status'               => 'active',
            'created_by'           => Auth::user()->creatorId(),
        ]);

        return redirect()->route('extension-agents.index')->with('success', __('Extension agent added.'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $agent       = ExtensionAgent::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $supervisors = User::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $mccs        = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        return view('extension::agents.create', compact('agent', 'supervisors', 'mccs'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $agent = ExtensionAgent::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $agent->update([
            'name'                 => $request->name,
            'phone'                => $request->phone,
            'assigned_communities' => $request->assigned_communities ? json_decode($request->assigned_communities, true) : $agent->assigned_communities,
            'assigned_centers'     => $request->assigned_centers ?? $agent->assigned_centers,
            'join_date'            => $request->join_date,
            'supervisor_id'        => $request->supervisor_id,
            'status'               => $request->status ?? $agent->status,
        ]);

        return redirect()->route('extension-agents.index')->with('success', __('Agent updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        ExtensionAgent::where('created_by', Auth::user()->creatorId())->findOrFail($id)->delete();
        return redirect()->route('extension-agents.index')->with('success', __('Agent deleted.'));
    }
}
