<?php

namespace Modules\Cooperatives\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Cooperatives\Models\Cooperative;
use App\Exports\CooperativeExport;
use App\Exports\CooperativeFarmerExport;
use App\Imports\CooperativeImport;

class CooperativesController extends Controller
{
    // ─── READ ─────────────────────────────────────────────────────────────────

    public function index()
    {
        if (! Auth::user()->can('manage cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cooperatives = Cooperative::where('created_by', Auth::user()->creatorId())
            ->withCount('farmers')
            ->orderBy('name')
            ->get();

        return view('cooperatives::index', compact('cooperatives'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        // Paginate so large cooperatives don't dump hundreds of rows at once.
        $farmers = $cooperative->farmers()->paginate(20);

        return view('cooperatives::show', compact('cooperative', 'farmers'));
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function create()
    {
        if (! Auth::user()->can('create cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('cooperatives::create');
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rules = [
            'name'                  => 'required|string|max:255|unique:cooperatives,name',
            'location'              => 'nullable|string|max:255',
            'leader_name'           => 'nullable|string|max:255',
            'leader_phone'          => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]{7,20}$/'],
            'site_location'         => 'nullable|string|max:255',
            'formation_date'        => 'nullable|date',
            'average_daily_supply'  => 'nullable|numeric|min:0',
            'status'                => 'nullable|in:active,inactive',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('cooperatives.index')
                ->with('error', $validator->getMessageBag()->first());
        }

        $cooperative = Cooperative::create([
            'name'                  => $request->name,
            'location'              => $request->location,
            'leader_name'           => $request->leader_name,
            'leader_phone'          => $request->leader_phone,
            'site_location'         => $request->site_location,
            'formation_date'        => $request->formation_date,
            'average_daily_supply'  => $request->average_daily_supply ?? 0,
            'status'                => $request->status ?? 'active',
            'created_by'            => Auth::user()->creatorId(),
        ]);

        // Auto-generate stable code after we have the PK.
        $cooperative->update([
            'code' => Cooperative::generateCode($cooperative->id, $cooperative->location),
        ]);

        return redirect()->route('cooperatives.index')
            ->with('success', __('Cooperative created successfully.'));
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function edit(int $id)
    {
        if (! Auth::user()->can('edit cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        return view('cooperatives::edit', compact('cooperative'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('edit cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        $rules = [
            'name'                  => 'required|string|max:255|unique:cooperatives,name,' . $id,
            'location'              => 'nullable|string|max:255',
            'leader_name'           => 'nullable|string|max:255',
            'leader_phone'          => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]{7,20}$/'],
            'site_location'         => 'nullable|string|max:255',
            'formation_date'        => 'nullable|date',
            'average_daily_supply'  => 'nullable|numeric|min:0',
            'status'                => 'nullable|in:active,inactive',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('cooperatives.index')
                ->with('error', $validator->getMessageBag()->first());
        }

        $cooperative->update([
            'name'                  => $request->name,
            'location'              => $request->location,
            'leader_name'           => $request->leader_name,
            'leader_phone'          => $request->leader_phone,
            'site_location'         => $request->site_location,
            'formation_date'        => $request->formation_date,
            'average_daily_supply'  => $request->average_daily_supply ?? 0,
            'status'                => $request->status ?? $cooperative->status,
        ]);

        return redirect()->route('cooperatives.index')
            ->with('success', __('Cooperative updated successfully.'));
    }

    // ─── DELETE ───────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        if (! Auth::user()->can('delete cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        if ($cooperative->farmers()->count() > 0) {
            return redirect()->route('cooperatives.index')
                ->with('error', __('Cannot delete: this cooperative has registered farmers. Reassign them first.'));
        }

        $cooperative->delete();

        return redirect()->route('cooperatives.index')
            ->with('success', __('Cooperative deleted successfully.'));
    }

    // ─── EXPORT (all cooperatives) ────────────────────────────────────────────

    public function export()
    {
        if (! Auth::user()->can('manage cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $filename = 'cooperatives_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new CooperativeExport(Auth::user()->creatorId()), $filename);
    }

    // ─── EXPORT (farmers of one cooperative) ─────────────────────────────────

    public function exportFarmers(int $id)
    {
        if (! Auth::user()->can('manage cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        $filename = 'farmers_' . \Str::slug($cooperative->name) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new CooperativeFarmerExport($cooperative->id), $filename);
    }

    // ─── IMPORT ───────────────────────────────────────────────────────────────

    public function importForm()
    {
        if (! Auth::user()->can('create cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('cooperatives::import');
    }

    public function importProcess(Request $request)
    {
        if (! Auth::user()->can('create cooperative')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('cooperatives.import.form')
                ->with('error', $validator->getMessageBag()->first());
        }

        $import = new CooperativeImport(Auth::user()->creatorId());

        Excel::import($import, $request->file('file'));

        $inserted = $import->getInserted();
        $skipped  = $import->getSkipped();

        return redirect()->route('cooperatives.index')
            ->with('success', __(':inserted cooperative(s) imported. :skipped row(s) skipped (duplicate or missing name).', [
                'inserted' => $inserted,
                'skipped'  => $skipped,
            ]));
    }

    public function leaderDashboard()
    {
        if (! Auth::user()->can('manage cooperatives')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())
            ->where('leader_user_id', Auth::id())
            ->first();

        if (! $cooperative) {
            $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())->first();
        }

        $members     = $cooperative ? $cooperative->farmers()->get() : collect();
        $memberCount = $members->count();
        $activeCount = $members->where('is_active', 1)->count();

        $weekLitres  = 0;
        $monthLitres = 0;

        if ($cooperative && class_exists(\Modules\MilkCollection\Models\MilkCollection::class)) {
            $farmerIds   = $members->pluck('id');
            $mc          = \Modules\MilkCollection\Models\MilkCollection::class;
            $weekLitres  = $mc::whereIn('farmer_id', $farmerIds)
                ->whereBetween('date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                ->sum('quantity_litres');
            $monthLitres = $mc::whereIn('farmer_id', $farmerIds)
                ->whereMonth('date', now()->month)
                ->sum('quantity_litres');
        }

        return view('cooperatives::leader_dashboard', compact(
            'cooperative', 'members', 'memberCount', 'activeCount', 'weekLitres', 'monthLitres'
        ));
    }
}
