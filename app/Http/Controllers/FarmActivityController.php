<?php

namespace App\Http\Controllers;

use App\Models\FarmActivity;
use App\Models\FarmField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\FarmActivityExport;
use Maatwebsite\Excel\Facades\Excel;


class FarmActivityController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage farm activity'))
        {
           $activities = FarmActivity::with('farmField')->latest()->get();

           return view('farm.activities.index', compact('activities'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }

    public function create()
    {
        if(\Auth::user()->can('create farm activity'))
        {
            $fields = FarmField::all();

            return view('farm.activities.create', compact('fields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create farm activity'))
        {
            $request->validate([
                'farm_field_id' => 'required|exists:farm_fields,id',
                'activity_date' => 'required|date',
                'activity_type' => 'required|string|max:255',
                'description' => 'nullable|string',
                'worker' => 'nullable|string|max:255',
                'cost' => 'nullable|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        
            $data = $request->all();
        
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/activities'), $filename);
                $data['image'] = 'assets/activities/' . $filename;
            }
        
            FarmActivity::create($data);
        
            return redirect()->route('farm-activities.index')->with('success', 'Activity added successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        
    }


    public function show($id)
    {
        if(\Auth::user()->can('show farm activity'))
        {
            $activity = FarmActivity::with('farmField')->findOrFail($id);

            return view('farm.activities.show', compact('activity'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

    }

    public function edit(FarmActivity $farmActivity)
    {
        if(\Auth::user()->can('edit farm activity'))
        {
            $fields = FarmField::all();
    
            return view('farm.activities.edit', compact('farmActivity', 'fields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        
    }


    public function update(Request $request, FarmActivity $farmActivity)
    {
        if(\Auth::user()->can('edit farm activity'))
        {
            $request->validate([
                'farm_field_id' => 'required|exists:farm_fields,id',
                'activity_date' => 'required|date',
                'activity_type' => 'required|string|max:255',
                'description' => 'nullable|string',
                'worker' => 'nullable|string|max:255',
                'cost' => 'nullable|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            $data = $request->all();
    
            // Handle image upload
            if ($request->hasFile('image')) {
                // Optionally delete old image if it exists
                if ($farmActivity->image && Storage::disk('public')->exists($farmActivity->image)) {
                    Storage::disk('public')->delete($farmActivity->image);
                }
    
                $data['image'] = $request->file('image')->store('activities', 'public');
            }
    
            $farmActivity->update($data);
    
            return redirect()->route('farm-activities.index', 1)->with('success', 'Activity updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        
    }

    public function destroy(FarmActivity $farmActivity)
    {
        if(\Auth::user()->can('delete farm activity'))
        {
            // Optionally delete image file
            if ($farmActivity->image && Storage::disk('public')->exists($farmActivity->image)) {
                Storage::disk('public')->delete($farmActivity->image);
            }
    
            $farmActivity->delete();
    
            return redirect()->route('farm-activities.index', 1)->with('success', 'Activity deleted.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

    }
    public function reportForm()
    {
        if(\Auth::user()->can('manage farm activity'))
        {
            $fields = FarmField::all();
    
            return view('farm.activities.report-form', compact('fields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function generateReport(Request $request)
    {
        if(\Auth::user()->can('manage farm activity'))
        {
            $request->validate([
                'farm_field_id' => 'nullable|exists:farm_fields,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
        
        
            $query = FarmActivity::with('farmField')
                ->whereBetween('activity_date', [$request->start_date, $request->end_date]);
        
            if ($request->filled('farm_field_id')) {
                $query->where('farm_field_id', $request->farm_field_id);
            }
        
            $activities = $query->orderBy('activity_date', 'asc')->get();
        
            return view('farm.activities.report-result', compact('activities', 'request')); 
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        
    }
    
    public function export(Request $request)
    {
        if(\Auth::user()->can('manage farm activity'))
        {
            $fieldId = $request->input('farm_field_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        
            return Excel::download(new FarmActivitiesExport($fieldId, $startDate, $endDate), 'farm_activities.xlsx');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }
    
    public function exportReport(Request $request)
    {
        if(\Auth::user()->can('manage farm activity'))
        {
            return Excel::download(
                new FarmActivityExport($request->field_id, $request->from_date, $request->to_date),
                'farm_activities_report.xlsx'
            );
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
       
    }


}
