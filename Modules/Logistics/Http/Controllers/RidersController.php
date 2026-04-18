<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Rider;

class RidersController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage logistics')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $riders = Rider::where('created_by', Auth::user()->creatorId())->orderBy('name')->paginate(25);

        return view('logistics::riders.index', compact('riders'));
    }

    public function create()
    {
        return view('logistics::riders.create');
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('manage logistics')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'contact'         => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'bank_name'       => 'nullable|string|max:100',
            'bank_account'    => 'nullable|string|max:50',
            'account_name'    => 'nullable|string|max:255',
            'amount_per_trip' => 'nullable|numeric|min:0',
            'collection_centre' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Rider::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'contact'           => $request->contact,
            'bank_name'         => $request->bank_name,
            'bank_account'      => $request->bank_account,
            'account_name'      => $request->account_name,
            'amount_per_trip'   => $request->amount_per_trip ?? 0,
            'collection_centre' => $request->collection_centre,
            'is_active'         => 1,
            'created_by'        => Auth::user()->creatorId(),
        ]);

        return redirect()->route('riders.index')->with('success', __('Rider added successfully.'));
    }

    public function edit(int $id)
    {
        $rider = Rider::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        return view('logistics::riders.create', compact('rider'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('manage logistics')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rider = Rider::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $rider->update($request->only(['name', 'email', 'contact', 'bank_name', 'bank_account', 'account_name', 'amount_per_trip', 'collection_centre', 'is_active']));

        return redirect()->route('riders.index')->with('success', __('Rider updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('manage logistics')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rider = Rider::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $rider->delete();

        return redirect()->route('riders.index')->with('success', __('Rider deleted.'));
    }
}
