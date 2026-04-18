<?php

namespace Modules\MilkCollection\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\MilkCollection\Models\MilkCollection;
use App\Models\Vender;

class MilkCollectionController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('manage milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = MilkCollection::with('farmer', 'recorder')
            ->where('created_by', Auth::user()->creatorId());

        if (Auth::user()->assignedMcc()) {
            $query->where('mcc', Auth::user()->assignedMcc());
        }

        if ($request->filled('mcc'))   $query->where('mcc', $request->mcc);
        if ($request->filled('grade')) $query->where('quality_grade', $request->grade);
        if ($request->filled('date_from')) $query->where('date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('date', '<=', $request->date_to);

        $collections   = $query->orderByDesc('date')->orderByDesc('id')->paginate(25)->withQueryString();
        $mccs          = MilkCollection::MCCS;
        $grades        = MilkCollection::GRADES;
        $todayLitres   = MilkCollection::where('created_by', Auth::user()->creatorId())
                            ->whereDate('date', today())->sum('quantity_litres');
        $todayFarmers  = MilkCollection::where('created_by', Auth::user()->creatorId())
                            ->whereDate('date', today())->distinct('farmer_id')->count();
        $todayGradeA   = MilkCollection::where('created_by', Auth::user()->creatorId())
                            ->whereDate('date', today())->where('quality_grade', 'A')->count();
        $todayTotal    = MilkCollection::where('created_by', Auth::user()->creatorId())
                            ->whereDate('date', today())->count();
        $gradeAPct     = $todayTotal > 0 ? round($todayGradeA / $todayTotal * 100) : 0;

        return view('milkcollection::index', compact(
            'collections', 'mccs', 'grades', 'todayLitres', 'todayFarmers', 'gradeAPct'
        ));
    }

    public function create()
    {
        if (! Auth::user()->can('create milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $farmers = Vender::where('created_by', Auth::user()->creatorId())
                         ->where('is_active', 1)->orderBy('name')->get();
        $mccs    = MilkCollection::MCCS;
        $grades  = MilkCollection::GRADES;

        return view('milkcollection::create', compact('farmers', 'mccs', 'grades'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rules = [
            'date'              => 'required|date',
            'mcc'               => 'required|in:' . implode(',', MilkCollection::MCCS),
            'farmer_id'         => 'required|exists:venders,id',
            'quantity_litres'   => 'required|numeric|min:0.01',
            'quality_grade'     => 'required|in:A,B,C',
            'temperature_celsius' => 'nullable|numeric',
            'rejection_reason'  => 'required_if:quality_grade,C|nullable|string',
            'photo'             => 'nullable|image|max:2048',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('milk-collection-photos', 'public');
        }

        MilkCollection::create([
            'collection_id'      => MilkCollection::generateCollectionId($request->mcc),
            'date'               => $request->date,
            'time'               => $request->time ?? now()->format('H:i:s'),
            'mcc'                => $request->mcc,
            'farmer_id'          => $request->farmer_id,
            'quantity_litres'    => $request->quantity_litres,
            'quality_grade'      => $request->quality_grade,
            'temperature_celsius' => $request->temperature_celsius,
            'rejection_reason'   => $request->rejection_reason,
            'collection_batch_id' => $request->collection_batch_id,
            'recorded_by'        => Auth::id(),
            'notes'              => $request->notes,
            'photo_path'         => $photoPath,
            'created_by'         => Auth::user()->creatorId(),
        ]);

        return redirect()->route('milk-collections.index')
            ->with('success', __('Milk collection recorded successfully.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $collection = MilkCollection::with('farmer', 'recorder')
            ->where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        return view('milkcollection::show', compact('collection'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('edit milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $collection = MilkCollection::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $farmers    = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();
        $mccs       = MilkCollection::MCCS;
        $grades     = MilkCollection::GRADES;

        return view('milkcollection::edit', compact('collection', 'farmers', 'mccs', 'grades'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('edit milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $collection = MilkCollection::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        $rules = [
            'date'            => 'required|date',
            'mcc'             => 'required|in:' . implode(',', MilkCollection::MCCS),
            'farmer_id'       => 'required|exists:venders,id',
            'quantity_litres' => 'required|numeric|min:0.01',
            'quality_grade'   => 'required|in:A,B,C',
            'rejection_reason' => 'required_if:quality_grade,C|nullable|string',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $photoPath = $collection->photo_path;
        if ($request->hasFile('photo')) {
            if ($photoPath) Storage::disk('public')->delete($photoPath);
            $photoPath = $request->file('photo')->store('milk-collection-photos', 'public');
        }

        $collection->update([
            'date'               => $request->date,
            'mcc'                => $request->mcc,
            'farmer_id'          => $request->farmer_id,
            'quantity_litres'    => $request->quantity_litres,
            'quality_grade'      => $request->quality_grade,
            'temperature_celsius' => $request->temperature_celsius,
            'rejection_reason'   => $request->rejection_reason,
            'collection_batch_id' => $request->collection_batch_id,
            'notes'              => $request->notes,
            'photo_path'         => $photoPath,
        ]);

        return redirect()->route('milk-collections.index')
            ->with('success', __('Milk collection updated successfully.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('delete milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $collection = MilkCollection::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        if ($collection->photo_path) Storage::disk('public')->delete($collection->photo_path);
        $collection->delete();

        return redirect()->route('milk-collections.index')
            ->with('success', __('Record deleted.'));
    }

    public function dailySummary(Request $request)
    {
        $creatorId = Auth::user()->creatorId();
        $summary   = [];
        $totalLitres = 0;
        $totalFarmers = 0;

        foreach (MilkCollection::MCCS as $mcc) {
            $litres  = MilkCollection::where('created_by', $creatorId)->where('mcc', $mcc)->whereDate('date', today())->sum('quantity_litres');
            $farmers = MilkCollection::where('created_by', $creatorId)->where('mcc', $mcc)->whereDate('date', today())->distinct('farmer_id')->count();
            $summary[$mcc] = compact('litres', 'farmers');
            $totalLitres  += $litres;
            $totalFarmers += $farmers;
        }

        if ($request->wantsJson()) {
            return response()->json($summary);
        }

        $recentCollections = MilkCollection::with('farmer')
            ->where('created_by', $creatorId)
            ->whereDate('date', today())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('milkcollection::daily_summary', compact(
            'summary', 'totalLitres', 'totalFarmers', 'recentCollections'
        ));
    }

    public function export()
    {
        if (! Auth::user()->can('manage milk collection')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $collections = MilkCollection::with('farmer')
            ->where('created_by', Auth::user()->creatorId())
            ->orderByDesc('date')->get();

        $rows   = [['Collection ID', 'Date', 'MCC', 'Farmer', 'Quantity (L)', 'Grade', 'Rejection Reason', 'Recorded By']];
        foreach ($collections as $c) {
            $rows[] = [
                $c->collection_id,
                $c->date->format('d/m/Y'),
                $c->mcc,
                $c->farmer?->name ?? 'N/A',
                $c->quantity_litres,
                $c->quality_grade . ' - ' . $c->grade_label,
                $c->rejection_reason ?? '',
                $c->recorder?->name ?? 'N/A',
            ];
        }

        $filename = 'milk_collections_' . date('Y-m-d') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($handle, $row);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
