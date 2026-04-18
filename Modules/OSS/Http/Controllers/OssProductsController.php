<?php

namespace Modules\OSS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\OSS\Models\OssProduct;
use Modules\OSS\Models\OssInventory;

class OssProductsController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $products   = OssProduct::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $categories = OssProduct::CATEGORIES;

        return view('oss::products.index', compact('products', 'categories'));
    }

    public function create()
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categories = OssProduct::CATEGORIES;
        return view('oss::products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'category'      => 'required|in:' . implode(',', OssProduct::CATEGORIES),
            'unit'          => 'required|string|max:30',
            'price'         => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        OssProduct::create([
            'product_code'  => OssProduct::generateProductCode(),
            'name'          => $request->name,
            'category'      => $request->category,
            'unit'          => $request->unit,
            'price'         => $request->price,
            'reorder_level' => $request->reorder_level ?? 0,
            'description'   => $request->description,
            'supplier'      => $request->supplier,
            'is_active'     => true,
            'created_by'    => Auth::user()->creatorId(),
        ]);

        return redirect()->route('oss-products.index')->with('success', __('Product added successfully.'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product    = OssProduct::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $categories = OssProduct::CATEGORIES;

        return view('oss::products.create', compact('product', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = OssProduct::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', OssProduct::CATEGORIES),
            'unit'     => 'required|string|max:30',
            'price'    => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product->update($request->only(['name', 'category', 'unit', 'price', 'reorder_level', 'description', 'supplier', 'is_active']));

        return redirect()->route('oss-products.index')->with('success', __('Product updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('manage oss products')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = OssProduct::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $product->update(['is_active' => false]);

        return redirect()->route('oss-products.index')->with('success', __('Product deactivated.'));
    }
}
