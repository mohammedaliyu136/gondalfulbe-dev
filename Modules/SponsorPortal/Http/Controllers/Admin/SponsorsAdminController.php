<?php

namespace Modules\SponsorPortal\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\SponsorPortal\Models\Sponsor;
use Modules\SponsorPortal\Models\SponsorProject;
use App\Models\Vender;

class SponsorsAdminController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $sponsors = Sponsor::where('created_by_admin', Auth::user()->creatorId())
            ->withCount('projects')->orderBy('organization_name')->paginate(25);
        return view('sponsorportal::admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $orgTypes = Sponsor::ORG_TYPES;
        return view('sponsorportal::admin.sponsors.create', compact('orgTypes'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $validator = \Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'contact_person'    => 'required|string|max:255',
            'email'             => 'required|email|unique:sponsors,email',
            'password'          => 'required|string|min:8',
            'organization_type' => 'required|in:' . implode(',', Sponsor::ORG_TYPES),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        Sponsor::create([
            'sponsor_code'      => Sponsor::generateSponsorCode(),
            'organization_name' => $request->organization_name,
            'contact_person'    => $request->contact_person,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'phone'             => $request->phone,
            'organization_type' => $request->organization_type,
            'country'           => $request->country,
            'status'            => 'active',
            'created_by_admin'  => Auth::user()->creatorId(),
        ]);
        return redirect()->route('admin.sponsors.index')->with('success', __('Sponsor account created.'));
    }

    public function show(int $id)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $sponsor = Sponsor::with('projects')->where('created_by_admin', Auth::user()->creatorId())->findOrFail($id);
        return view('sponsorportal::admin.sponsors.show', compact('sponsor'));
    }

    public function edit(int $id)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $sponsor  = Sponsor::where('created_by_admin', Auth::user()->creatorId())->findOrFail($id);
        $orgTypes = Sponsor::ORG_TYPES;
        return view('sponsorportal::admin.sponsors.create', compact('sponsor', 'orgTypes'));
    }

    public function update(Request $request, int $id)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $sponsor = Sponsor::where('created_by_admin', Auth::user()->creatorId())->findOrFail($id);
        $data    = $request->only(['organization_name','contact_person','phone','organization_type','country','status']);
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);
        $sponsor->update($data);
        return redirect()->route('admin.sponsors.index')->with('success', __('Sponsor updated.'));
    }

    public function destroy(int $id)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        Sponsor::where('created_by_admin', Auth::user()->creatorId())->findOrFail($id)->delete();
        return redirect()->route('admin.sponsors.index')->with('success', __('Sponsor deleted.'));
    }

    public function assignProject(int $sponsorId)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $sponsor    = Sponsor::where('created_by_admin', Auth::user()->creatorId())->findOrFail($sponsorId);
        $statuses   = SponsorProject::STATUSES;
        $focusAreas = SponsorProject::FOCUS_AREAS;
        return view('sponsorportal::admin.projects.create', compact('sponsor', 'statuses', 'focusAreas'));
    }

    public function storeProject(Request $request, int $sponsorId)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $sponsor = Sponsor::where('created_by_admin', Auth::user()->creatorId())->findOrFail($sponsorId);
        $validator = \Validator::make($request->all(), [
            'title'  => 'required|string|max:255',
            'status' => 'required|in:' . implode(',', SponsorProject::STATUSES),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        SponsorProject::create([
            'project_code' => SponsorProject::generateCode(),
            'sponsor_id'   => $sponsor->id,
            'title'        => $request->title,
            'description'  => $request->description,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'budget'       => $request->budget,
            'status'       => $request->status,
            'focus_areas'  => $request->focus_areas ?? [],
            'created_by'   => Auth::user()->creatorId(),
        ]);
        return redirect()->route('admin.sponsors.show', $sponsor->id)->with('success', __('Project created.'));
    }

    public function manageFarmers(int $sponsorId, int $projectId)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $sponsor     = \Modules\SponsorPortal\Models\Sponsor::where('created_by_admin', Auth::user()->creatorId())->findOrFail($sponsorId);
        $project     = SponsorProject::with('farmers')->findOrFail($projectId);
        $assignedIds = $project->farmers->pluck('id')->toArray();
        $farmers     = Vender::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        return view('sponsorportal::admin.projects.manage-farmers', compact('sponsor', 'project', 'farmers', 'assignedIds'));
    }

    public function syncFarmers(Request $request, int $sponsorId, int $projectId)
    {
        if (!Auth::user()->can('manage sponsors')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $project        = SponsorProject::findOrFail($projectId);
        $farmerIds      = $request->farmer_ids ?? [];
        $syncData       = [];
        foreach ($farmerIds as $fid) {
            $syncData[$fid] = ['enrolled_date' => now()->toDateString()];
        }
        $project->farmers()->sync($syncData);
        return redirect()->route('admin.sponsors.show', $sponsorId)->with('success', __('Farmers updated.'));
    }
}
