<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryIssue;
use Illuminate\Http\Request;

class InventoryIssueController extends Controller
{
    public function create(Inventory $inventory)
    {
        return view('inventory.modals.issue', compact('inventory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'inventory_id'    => 'required|exists:inventories,id',
            'quantity_issued' => 'required|integer|min:1',
            'issued_to'       => 'required|string|max:255',
            'issued_by'       => 'required|string|max:255',
        ]);

        $inventory = Inventory::findOrFail($request->inventory_id);

        if ($inventory->quantity < $request->quantity_issued) {
            return response()->json(['success' => false, 'message' => 'Not enough stock'], 422);
        }

        InventoryIssue::create([
            'inventory_id'    => $inventory->id,
            'quantity'        => $request->quantity_issued, // ← Changed to match your DB column
            'issued_by'       => $request->issued_by,
            'issued_to'       => $request->issued_to,
            
            'issue_date'      => now(), // ← Changed to match your DB column
        ]);

        $inventory->decrement('quantity', $request->quantity_issued);

        return redirect()->back()->with('success', 'Stock issued successfully.');
    }

    // 🔹 FIXED REPORT FUNCTION
    public function report(Request $request)
    {
        $query = InventoryIssue::with('inventory')->orderBy('issue_date', 'desc'); // ← Using correct column

        // Optional filters
        if ($request->filled('item')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->item . '%');
            });
        }

        if ($request->filled('issued_by')) {
            $query->where('issued_by', 'like', '%' . $request->issued_by . '%');
        }

        if ($request->filled('issue_date')) {
            $query->whereDate('issue_date', $request->issue_date); // ← Using correct column
        }

        $issues = $query->get();

        return view('inventory.issues.report', compact('issues'));
    }
}