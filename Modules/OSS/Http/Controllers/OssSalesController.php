<?php

namespace Modules\OSS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\OSS\Models\OssProduct;
use Modules\OSS\Models\OssSale;
use Modules\OSS\Models\OssSaleItem;
use App\Models\Vender;

class OssSalesController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = OssSale::with('farmer')->where('created_by', Auth::user()->creatorId());

        if ($request->filled('center'))      $query->where('center', $request->center);
        if ($request->filled('farmer_id'))   $query->where('farmer_id', $request->farmer_id);
        if ($request->filled('date_from'))   $query->where('date', '>=', $request->date_from);
        if ($request->filled('date_to'))     $query->where('date', '<=', $request->date_to);

        $sales        = $query->orderByDesc('date')->paginate(25)->withQueryString();
        $totalRevenue = OssSale::where('created_by', Auth::user()->creatorId())->sum('total_amount');
        $creditTotal  = OssSale::where('created_by', Auth::user()->creatorId())->where('is_credit', true)->where('credit_settled', false)->sum('total_amount');
        $mccs         = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $farmers      = Vender::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();

        return view('oss::sales.index', compact('sales', 'totalRevenue', 'creditTotal', 'mccs', 'farmers'));
    }

    public function create()
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $farmers  = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();
        $products = OssProduct::where('created_by', Auth::user()->creatorId())->active()->orderBy('name')->get();
        $mccs     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        return view('oss::sales.create', compact('farmers', 'products', 'mccs'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'farmer_id'              => 'required|exists:venders,id',
            'date'                   => 'required|date',
            'center'                 => 'nullable|string',
            'payment_method'         => 'required|in:Cash,Credit,Mobile Money',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:oss_products,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $total = collect($request->items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));

        $sale = OssSale::create([
            'sale_id'        => OssSale::generateSaleId(),
            'date'           => $request->date,
            'farmer_id'      => $request->farmer_id,
            'center'         => $request->center,
            'total_amount'   => $total,
            'payment_method' => $request->payment_method,
            'is_credit'      => $request->payment_method === 'Credit',
            'created_by'     => Auth::user()->creatorId(),
        ]);

        foreach ($request->items as $item) {
            OssSaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $item['quantity'] * $item['unit_price'],
            ]);

            // Deduct stock
            \Modules\OSS\Models\OssInventory::create([
                'transaction_id' => \Modules\OSS\Models\OssInventory::generateTransactionId(),
                'type'           => 'Stock Out',
                'product_id'     => $item['product_id'],
                'quantity'       => $item['quantity'],
                'date'           => $request->date,
                'center'         => $request->center,
                'reference'      => $sale->sale_id,
                'created_by'     => Auth::user()->creatorId(),
            ]);
        }

        return redirect()->route('oss-sales.show', $sale->id)->with('success', __('Sale recorded successfully.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $sale = OssSale::with('farmer', 'items.product')->where('created_by', Auth::user()->creatorId())->findOrFail($id);
        return view('oss::sales.show', compact('sale'));
    }
}
