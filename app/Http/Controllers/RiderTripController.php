<?php

namespace App\Http\Controllers;

use App\Models\Rider;
use App\Models\RiderTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class RiderTripController extends Controller
{
    public function index()
    {
        $trips = RiderTrip::with('rider')->latest()->get();
        return view('rider_trips.index', compact('trips'));
    }

    public function create($encryptedRiderId)
    {
        if(\Auth::user()->can('create trip')){
            $riderId = Crypt::decrypt($encryptedRiderId);
            $rider = Rider::findOrFail($riderId);
        
            return view('rider_trips.create', [
                'rider' => $rider
            ]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create trip')){
            $request->validate([
            'rider_id' => 'required|integer|exists:riders,id',
            'trips' => 'required|array',
            'trips.*.trip_date' => 'required|date',
            'trips.*.trip_count' => 'required|integer|min:1',
            'trips.*.amount_per_trip' => 'required|numeric|min:0',
        ]);
    
        try {
            $amountDue = 0;
            foreach ($request->input('trips') as $trip) {
                
                \App\Models\RiderTrip::create([
                    'rider_id' => $request->rider_id,
                    'trip_date' => $trip['trip_date'],
                    'trip_count' => $trip['trip_count'],
                    'amount_per_trip' => $trip['amount_per_trip'],
                    'status' => 0,
                    //'total_amount' => $trip['trip_count'] * $trip['amount_per_trip'],
                ]);
                $amountDue += $trip['trip_count'] * $trip['amount_per_trip'];
            }
            
            
    
            return redirect()->back()->with('success', 'Rider trips added successfully.');
        } catch (\Exception $e) {
            \Log::error('Trip store failed: ' . $e->getMessage());
    
            return redirect()->back()
                ->withErrors(['message' => 'Failed to save rider trips. Please try again.'])
                ->withInput();
        }
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }




    public function show($id)
    {
        $trip = RiderTrip::findOrFail($id);
        return view('rider_trips.show', compact('trip'));
    }

    public function edit($id)
    {
        $trip = RiderTrip::findOrFail($id);
        return view('rider_trips.edit', compact('trip'));
    }

    public function update(Request $request, $id)
    {
        $trip = RiderTrip::findOrFail($id);

        $request->validate([
            'rider_id' => 'required|integer',
            'trip_date' => 'required|date',
            'trip_count' => 'required|integer',
            'amount_per_trip' => 'required|numeric',
            'total_amount' => 'required|numeric',
        ]);

        $trip->update($request->all());

        return redirect()->route('rider-trips.index')->with('success', 'Rider trip updated successfully.');
    }

    public function destroy($id)
    {
        $trip = RiderTrip::findOrFail($id);
        $trip->delete();

        return redirect()->route('rider-trips.index')->with('success', 'Rider trip deleted successfully.');
    }
    
    public function validateTrip($encryptedTripId)
    {
        if(\Auth::user()->can('validate trip')){
            $tripId = Crypt::decrypt($encryptedTripId);
            $trip = RiderTrip::findOrFail($tripId);
            
            if ($trip->status != 0) {
                return redirect()->back()->with('error', __('Not a draft.'));
            }
        
            return view('rider_trips.validate', [
                'trip' => $trip
            ]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }
    
    public function processValidate(Request $request) 
    {
        if (!\Auth::user()->can('validate trip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
        $trip = RiderTrip::findOrFail($request->trip_id);
        
        if ($trip->status != 0) {
            return redirect()->back()->with('error', __('Not a draft.'));
        }
        
        if ($request->action === 'valid') {
            $rider = Rider::where('id', $trip->rider_id)->first();
            $rider->book_balance = ($rider->book_balance + $trip->total_amount);
            $rider->balance = ($rider->balance + $trip->total_amount);
            $rider->save();
            
            // You might also want to update the trip status here
            $trip->status = 1;
            $trip->validated_by = \Auth::user()->id;
            $trip->save();
            
            return redirect()->back()->with('success', 'Rider trips validated successfully.');
        } elseif ($request->action === 'invalid') {
            // Handle invalid case - maybe mark trip as invalid
            $trip->status = 4;
            $trip->validated_by = \Auth::user()->id;
            $trip->save();
            
            return redirect()->back()->with('success', 'Trip marked as invalid.');
        }
    
        return redirect()->back()->with('error', 'Invalid action.');
    }
}
