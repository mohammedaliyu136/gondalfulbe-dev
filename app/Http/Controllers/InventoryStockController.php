<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryStockController extends Controller
{
    /**
     * Show modal form to add stock for an inventory item
     */
    public function create(Inventory $inventory)
    {
        return view('inventory.modals.add_stock', compact('inventory'));
    }

    /**
     * Store stock entry and update inventory
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_id'   => 'required|exists:inventories,id',
            'quantity_added' => 'required|integer|min:1',
            'note'           => 'nullable|string',
            'supplier'       => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'total_purchase_price' => 'required|numeric|min:0',

            
        ]);

        $inventory = Inventory::findOrFail($validated['inventory_id']);

        // Create stock record
        InventoryStock::create([
            'inventory_id'   => $inventory->id,
            'quantity_added' => $validated['quantity_added'],
            'added_by'       => Auth::id(),
            'note'           => $validated['note'] ?? null,
            'supplier'       => $validated['supplier'],
            'purchase_price' => $validated['purchase_price'],
            'total_purchase_price' => $validated['total_purchase_price'],

        ]);

        // Update total quantity in inventory
        $inventory->increment('quantity', $validated['quantity_added']);

        return redirect()->back()->with('success', 'Stock added successfully.');
    }
}
