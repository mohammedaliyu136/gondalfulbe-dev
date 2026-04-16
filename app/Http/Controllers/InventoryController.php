<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
         if (\Auth::user()->can('manage inventory')) {
                $inventories = Inventory::latest()->get();
                return view('inventory.index', compact('inventories'));
         } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        
    }

    // returns modal fragment
    public function create()
    {
        if (\Auth::user()->can('create inventory')) {
            return view('inventory.create');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }   

   public function store(Request $request)
    {
        if (\Auth::user()->can('create inventory')) {
             $validated = $request->validate([
                'item_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string',
                'reorder_level' => 'required|integer|min:1',
            ]);
    
        Inventory::create($validated);
    
        return redirect()->back()->with('success', 'Inventory item added successfully.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }


    // returns modal fragment
    public function edit(Inventory $inventory)
    {
        return view('inventory.modal.edit', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $inventory->update($request->only('name','description'));

        return response()->json(['success' => true, 'message' => 'Inventory updated']);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->back()->with('success', 'Inventory deleted');
    }
    
    public function getQuantity(Inventory $inventory)
    {
        return response()->json([
            'quantity' => $inventory->quantity,
        ]);
    }
}