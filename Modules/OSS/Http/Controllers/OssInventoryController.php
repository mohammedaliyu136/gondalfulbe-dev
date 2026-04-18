<?php

namespace Modules\OSS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\OSS\Models\OssProduct;
use Modules\OSS\Models\OssInventory;

class OssInventoryController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $products     = OssProduct::where('created_by', Auth::user()->creatorId())->active()->orderBy('name')->get();
        $transactions = OssInventory::with('product')
            ->whereHas('product', fn($q) => $q->where('created_by', Auth::user()->creatorId()))
            ->orderByDesc('date')->paginate(50);
        $mccs         = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        $stockLevels = $products->map(fn($p) => [
            'product' => $p->name,
            'unit'    => $p->unit,
            'stock'   => $p->current_stock,
            'low'     => $p->isLowStock(),
        ]);

        return view('oss::inventory.index', compact('products', 'transactions', 'mccs', 'stockLevels'));
    }

    public function stockInForm()
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $products = OssProduct::where('created_by', Auth::user()->creatorId())->active()->orderBy('name')->get();
        $mccs     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        return view('oss::inventory.stock-in', compact('products', 'mccs'));
    }

    public function stockOutForm()
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $products = OssProduct::where('created_by', Auth::user()->creatorId())->active()->orderBy('name')->get();
        $mccs     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        return view('oss::inventory.stock-out', compact('products', 'mccs'));
    }

    public function stockIn(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'product_id' => 'required|exists:oss_products,id',
            'quantity'   => 'required|numeric|min:0.01',
            'center'     => 'nullable|string',
            'date'       => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        OssInventory::create([
            'transaction_id' => OssInventory::generateTransactionId(),
            'type'           => 'Stock In',
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity,
            'date'           => $request->date,
            'center'         => $request->center,
            'reference'      => $request->reference,
            'unit_cost'      => $request->unit_cost,
            'batch_number'   => $request->batch_number,
            'notes'          => $request->notes,
            'created_by'     => Auth::user()->creatorId(),
        ]);

        return redirect()->route('oss-inventory.index')->with('success', __('Stock added successfully.'));
    }

    public function stockOut(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'product_id' => 'required|exists:oss_products,id',
            'quantity'   => 'required|numeric|min:0.01',
            'center'     => 'nullable|string',
            'date'       => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        OssInventory::create([
            'transaction_id' => OssInventory::generateTransactionId(),
            'type'           => 'Stock Out',
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity,
            'date'           => $request->date,
            'center'         => $request->center,
            'reference'      => $request->reference,
            'notes'          => $request->notes,
            'created_by'     => Auth::user()->creatorId(),
        ]);

        return redirect()->route('oss-inventory.index')->with('success', __('Stock removed successfully.'));
    }
}
