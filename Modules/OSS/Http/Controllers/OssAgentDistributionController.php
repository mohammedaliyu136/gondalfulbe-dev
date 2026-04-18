<?php

namespace Modules\OSS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\OSS\Models\OssProduct;
use Modules\OSS\Models\OssInventory;
use Modules\OSS\Models\OssAgentAllocation;
use Modules\OSS\Models\OssAgentSale;
use Modules\OSS\Models\OssAgentSaleItem;
use Modules\OSS\Models\OssAgentReturn;
use App\Models\User;
use App\Models\Vender;

class OssAgentDistributionController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $allocations = OssAgentAllocation::with('product', 'agent')
            ->where('created_by', Auth::user()->creatorId())
            ->orderByDesc('allocated_date')->get();

        $products = OssProduct::where('created_by', Auth::user()->creatorId())->active()->orderBy('name')->get();
        $agents   = User::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $farmers  = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();

        $balances = [];
        foreach ($allocations as $alloc) {
            $key = $alloc->agent_id . '_' . $alloc->product_id;
            $balances[$key] = OssAgentAllocation::getAgentBalance($alloc->agent_id, $alloc->product_id);
        }

        return view('oss::agent.index', compact('allocations', 'products', 'agents', 'farmers', 'balances'));
    }

    public function allocate(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'agent_id'           => 'required|exists:users,id',
            'product_id'         => 'required|exists:oss_products,id',
            'quantity_allocated' => 'required|numeric|min:0.01',
            'allocated_date'     => 'required|date',
            'center'             => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create OSS Stock Out
        $stockOut = OssInventory::create([
            'transaction_id' => OssInventory::generateTransactionId(),
            'type'           => 'Stock Out',
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity_allocated,
            'date'           => $request->allocated_date,
            'center'         => $request->center,
            'reference'      => 'Agent Allocation',
            'created_by'     => Auth::user()->creatorId(),
        ]);

        OssAgentAllocation::create([
            'allocation_id'          => OssAgentAllocation::generateAllocationId(),
            'agent_id'               => $request->agent_id,
            'product_id'             => $request->product_id,
            'quantity_allocated'     => $request->quantity_allocated,
            'allocated_date'         => $request->allocated_date,
            'allocated_by'           => Auth::id(),
            'center'                 => $request->center,
            'reference_stock_out_id' => $stockOut->id,
            'notes'                  => $request->notes,
            'created_by'             => Auth::user()->creatorId(),
        ]);

        return redirect()->route('oss.agent.index')->with('success', __('Stock allocated to agent.'));
    }

    public function saleForm()
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $agents   = User::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $farmers  = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();
        $products = OssProduct::where('created_by', Auth::user()->creatorId())->active()->orderBy('name')->get();
        return view('oss::agent.sale', compact('agents', 'farmers', 'products'));
    }

    public function recordSale(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'agent_id'               => 'required|exists:users,id',
            'farmer_id'              => 'required|exists:venders,id',
            'date'                   => 'required|date',
            'payment_method'         => 'required|in:Cash,Credit,Mobile Money',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:oss_products,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        foreach ($request->items as $item) {
            $balance = OssAgentAllocation::getAgentBalance($request->agent_id, $item['product_id']);
            if ((float) $item['quantity'] > $balance) {
                return redirect()->back()->with('error', __('Agent does not have sufficient stock for this product.'));
            }
        }

        $total = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);

        $sale = OssAgentSale::create([
            'sale_id'        => OssAgentSale::generateSaleId(),
            'agent_id'       => $request->agent_id,
            'farmer_id'      => $request->farmer_id,
            'date'           => $request->date,
            'total_amount'   => $total,
            'payment_method' => $request->payment_method,
            'is_credit'      => $request->payment_method === 'Credit',
            'visit_id'       => $request->visit_id,
            'created_by'     => Auth::user()->creatorId(),
        ]);

        foreach ($request->items as $item) {
            OssAgentSaleItem::create([
                'agent_sale_id' => $sale->id,
                'product_id'    => $item['product_id'],
                'quantity'      => $item['quantity'],
                'unit_price'    => $item['unit_price'],
                'subtotal'      => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('oss.agent.index')->with('success', __('Field sale recorded.'));
    }

    public function recordReturn(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'agent_id'          => 'required|exists:users,id',
            'product_id'        => 'required|exists:oss_products,id',
            'quantity_returned' => 'required|numeric|min:0.01',
            'return_date'       => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create OSS Stock In
        $stockIn = OssInventory::create([
            'transaction_id' => OssInventory::generateTransactionId(),
            'type'           => 'Stock In',
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity_returned,
            'date'           => $request->return_date,
            'reference'      => 'Agent Return',
            'created_by'     => Auth::user()->creatorId(),
        ]);

        OssAgentReturn::create([
            'return_id'              => OssAgentReturn::generateReturnId(),
            'agent_id'               => $request->agent_id,
            'product_id'             => $request->product_id,
            'quantity_returned'      => $request->quantity_returned,
            'return_date'            => $request->return_date,
            'reason'                 => $request->reason,
            'reference_stock_in_id'  => $stockIn->id,
            'created_by'             => Auth::user()->creatorId(),
        ]);

        return redirect()->route('oss.agent.index')->with('success', __('Return recorded and stock restored.'));
    }

    public function agentBalance(int $agentId)
    {
        $products = OssProduct::where('created_by', Auth::user()->creatorId())->active()->get();
        $balances = [];
        foreach ($products as $p) {
            $bal = OssAgentAllocation::getAgentBalance($agentId, $p->id);
            if ($bal > 0) {
                $balances[] = ['product' => $p->name, 'unit' => $p->unit, 'balance' => $bal];
            }
        }
        $agent = User::find($agentId);
        return response()->json(['agent' => $agent?->name, 'balances' => $balances]);
    }
}
