<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Logistics\Models\LogisticsTrip;
use App\Models\Rider;

class LogisticsController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('manage logistics')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = LogisticsTrip::with('rider')->where('created_by', Auth::user()->creatorId());

        if (Auth::user()->assignedMcc()) {
            $query->where('mcc_source', Auth::user()->assignedMcc());
        }

        if ($request->filled('mcc'))     $query->where('mcc_source', $request->mcc);
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('rider_id')) $query->where('rider_id', $request->rider_id);
        if ($request->filled('date_from')) $query->where('trip_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('trip_date', '<=', $request->date_to);

        $trips        = $query->orderByDesc('trip_date')->paginate(25)->withQueryString();
        $mccs         = LogisticsTrip::MCCS;
        $statuses     = LogisticsTrip::STATUSES;
        $riders       = Rider::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->get();
        $monthLitres  = LogisticsTrip::where('created_by', Auth::user()->creatorId())->whereMonth('trip_date', now()->month)->sum('litres_transported');
        $monthTrips   = LogisticsTrip::where('created_by', Auth::user()->creatorId())->whereMonth('trip_date', now()->month)->count();
        $avgCostLitre = LogisticsTrip::where('created_by', Auth::user()->creatorId())->whereMonth('trip_date', now()->month)->avg('cost_per_litre');

        return view('logistics::index', compact('trips', 'mccs', 'statuses', 'riders', 'monthLitres', 'monthTrips', 'avgCostLitre'));
    }

    public function create()
    {
        if (! Auth::user()->can('create logistics trip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $riders   = Rider::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->get();
        $mccs     = LogisticsTrip::MCCS;
        $statuses = LogisticsTrip::STATUSES;

        return view('logistics::create', compact('riders', 'mccs', 'statuses'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create logistics trip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'trip_date'          => 'required|date',
            'mcc_source'         => 'required|in:' . implode(',', LogisticsTrip::MCCS),
            'rider_id'           => 'required|exists:riders,id',
            'litres_transported' => 'nullable|numeric|min:0',
            'fuel_cost'          => 'nullable|numeric|min:0',
            'other_expenses'     => 'nullable|numeric|min:0',
            'status'             => 'required|in:' . implode(',', LogisticsTrip::STATUSES),
            'delivery_note'      => 'nullable|file|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $notePath = null;
        if ($request->hasFile('delivery_note')) {
            $notePath = $request->file('delivery_note')->store('delivery-notes', 'public');
        }

        $trip = LogisticsTrip::create([
            'trip_id'                   => LogisticsTrip::generateTripId($request->mcc_source),
            'trip_date'                 => $request->trip_date,
            'mcc_source'                => $request->mcc_source,
            'destination'               => $request->destination ?? 'Sebore Plant',
            'rider_id'                  => $request->rider_id,
            'vehicle_registration'      => $request->vehicle_registration,
            'departure_time'            => $request->departure_time,
            'arrival_time'              => $request->arrival_time,
            'litres_transported'        => $request->litres_transported ?? 0,
            'collection_batch_id'       => $request->collection_batch_id,
            'fuel_cost'                 => $request->fuel_cost ?? 0,
            'other_expenses'            => $request->other_expenses ?? 0,
            'other_expenses_description' => $request->other_expenses_description,
            'status'                    => $request->status,
            'delivery_note_path'        => $notePath,
            'created_by'                => Auth::user()->creatorId(),
        ]);

        $trip->update(['cost_per_litre' => $trip->computeCostPerLitre()]);

        return redirect()->route('logistics.index')->with('success', __('Trip created successfully.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage logistics')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $trip = LogisticsTrip::with('rider')->where('created_by', Auth::user()->creatorId())->findOrFail($id);
        return view('logistics::show', compact('trip'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('edit logistics trip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $trip     = LogisticsTrip::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $riders   = Rider::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->get();
        $mccs     = LogisticsTrip::MCCS;
        $statuses = LogisticsTrip::STATUSES;

        return view('logistics::edit', compact('trip', 'riders', 'mccs', 'statuses'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('edit logistics trip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $trip = LogisticsTrip::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'trip_date'          => 'required|date',
            'mcc_source'         => 'required|in:' . implode(',', LogisticsTrip::MCCS),
            'rider_id'           => 'required|exists:riders,id',
            'litres_transported' => 'nullable|numeric|min:0',
            'fuel_cost'          => 'nullable|numeric|min:0',
            'status'             => 'required|in:' . implode(',', LogisticsTrip::STATUSES),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $notePath = $trip->delivery_note_path;
        if ($request->hasFile('delivery_note')) {
            if ($notePath) Storage::disk('public')->delete($notePath);
            $notePath = $request->file('delivery_note')->store('delivery-notes', 'public');
        }

        $trip->update([
            'trip_date'                 => $request->trip_date,
            'mcc_source'                => $request->mcc_source,
            'destination'               => $request->destination ?? 'Sebore Plant',
            'rider_id'                  => $request->rider_id,
            'vehicle_registration'      => $request->vehicle_registration,
            'departure_time'            => $request->departure_time,
            'arrival_time'              => $request->arrival_time,
            'litres_transported'        => $request->litres_transported ?? 0,
            'collection_batch_id'       => $request->collection_batch_id,
            'fuel_cost'                 => $request->fuel_cost ?? 0,
            'other_expenses'            => $request->other_expenses ?? 0,
            'other_expenses_description' => $request->other_expenses_description,
            'status'                    => $request->status,
            'delivery_note_path'        => $notePath,
        ]);

        $trip->update(['cost_per_litre' => $trip->computeCostPerLitre()]);

        return redirect()->route('logistics.index')->with('success', __('Trip updated successfully.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('delete logistics trip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $trip = LogisticsTrip::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        if ($trip->delivery_note_path) Storage::disk('public')->delete($trip->delivery_note_path);
        $trip->delete();

        return redirect()->route('logistics.index')->with('success', __('Trip deleted.'));
    }

    public function complete(int $id)
    {
        if (! Auth::user()->can('edit logistics trip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $trip = LogisticsTrip::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        if ($trip->collection_batch_id) {
            $batchTotal = \Modules\MilkCollection\Models\MilkCollection::where('collection_batch_id', $trip->collection_batch_id)
                ->sum('quantity_litres');

            $transported = (float) $trip->litres_transported;
            $tolerance   = $batchTotal * 0.02;

            if ($batchTotal > 0 && abs($transported - $batchTotal) > $tolerance) {
                return redirect()->back()->with('error', __(
                    'Litres transported (:transported L) does not match collection batch total (:batch L). Cannot complete trip.',
                    ['transported' => number_format($transported, 2), 'batch' => number_format($batchTotal, 2)]
                ));
            }
        }

        $trip->update(['status' => 'Completed']);

        return redirect()->route('logistics.index')->with('success', __('Trip marked as completed.'));
    }

    public function export()
    {
        if (! Auth::user()->can('manage logistics')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $trips    = LogisticsTrip::with('rider')->where('created_by', Auth::user()->creatorId())->orderByDesc('trip_date')->get();
        $rows     = [['Trip ID', 'Date', 'MCC', 'Destination', 'Rider', 'Litres', 'Fuel Cost', 'Other Exp.', 'Cost/Litre', 'Status']];
        foreach ($trips as $t) {
            $rows[] = [$t->trip_id, $t->trip_date->format('d/m/Y'), $t->mcc_source, $t->destination, $t->rider?->name ?? 'N/A', $t->litres_transported, $t->fuel_cost, $t->other_expenses, $t->cost_per_litre, $t->status];
        }

        $filename = 'logistics_trips_' . date('Y-m-d') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        $callback = function () use ($rows) {
            $h = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($h, $row);
            fclose($h);
        };

        return response()->stream($callback, 200, $headers);
    }
}
