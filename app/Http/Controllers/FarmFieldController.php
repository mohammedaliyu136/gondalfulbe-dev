<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FarmField;
use Illuminate\Support\Facades\Crypt;

class FarmFieldController extends Controller

{

    public function index()
    {
        
        
        if(\Auth::user()->can('manage farm fields'))
        {
           $farmFields = FarmField::all();
    
        return view('farm_fields.index', compact('farmFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create farm fields'))
        {
            return view('farm_fields.create');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create farm fields'))
        {
           $request->validate([
            'field_name' => 'required|string|max:255',
            'size' => 'required|numeric',
            'crop_type' => 'nullable|string|max:255',
            'activities' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
    
    
        FarmField::create([
            'field_name' => $request->field_name,
            'size' => $request->size,
            'crop_type' => $request->crop_type,
            'activities' => $request->activities,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    
        return redirect()->route('farm-fields.index')->with('success', 'Farm field created.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }
    
      /**
     * Display the specified farm field (with encrypted ID).
     */
    public function show($ids)
    {
        if(\Auth::user()->can('show farm fields'))
        {
            try {
                $id = Crypt::decrypt($ids);
                $farmField = FarmField::findOrFail($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Farm Field not found.'));
            }
    
            return view('farm_fields.show', compact('farmField'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    /**
     * Show the form for editing the specified farm field (with encrypted ID).
     */
    public function edit($ids)
    {
        if(\Auth::user()->can('edit farm fields'))
        {
            try {
                $id = Crypt::decrypt($ids);
                $farmField = FarmField::findOrFail($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Farm Field not found.'));
            }
    
            return view('farm_fields.edit', compact('farmField'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }

    /**
     * Update the specified farm field (with encrypted ID).
     */
    public function update(Request $request, $ids)
    {
        if(\Auth::user()->can('edit farm fields'))
        {
            try {
                $id = Crypt::decrypt($ids);
                $farmField = FarmField::findOrFail($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Farm Field not found.'));
            }
    
            $request->validate([
                'field_name' => 'required|string|max:255',
                'size'       => 'required|numeric',
                'crop_type'  => 'nullable|string|max:255',
                'activities' => 'nullable|string',
                'latitude'   => 'nullable|numeric',
                'longitude'  => 'nullable|numeric',
            ]);
    
            $farmField->update($request->only([
                'field_name', 'size', 'crop_type', 'activities', 'latitude', 'longitude'
            ]));
    
            return redirect()->route('farm-fields.show', $ids)
                             ->with('success', 'Farm field updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    }

    /**
     * Remove the specified farm field (with encrypted ID).
     */
    public function destroy($ids)
    {
        if(\Auth::user()->can('delete farm fields'))
        {
            try {
                $id = Crypt::decrypt($ids);
                $farmField = FarmField::findOrFail($id);
        
                // Check if farm activities exist for this field
                if ($farmField->farmActivities()->exists()) {
                    return redirect()->back()->with('error', 'Cannot delete. This farm field has related activities.');
                }
        
                $farmField->delete();
        
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Farm Field not found.'));
            }
    
            return redirect()->route('farm-fields.index')
                         ->with('success', 'Farm field deleted successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

}