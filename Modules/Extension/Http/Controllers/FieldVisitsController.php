<?php

namespace Modules\Extension\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Extension\Models\ExtensionAgent;
use Modules\Extension\Models\FieldVisit;
use Modules\Extension\Models\VisitFarmer;
use Modules\Extension\Models\VisitTopic;
use Modules\Extension\Models\VisitPhoto;
use Modules\Extension\Models\FollowUpTask;
use App\Models\Vender;

class FieldVisitsController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = FieldVisit::with('agent')->where('created_by', Auth::user()->creatorId());

        if ($request->filled('agent_id'))  $query->where('agent_id', $request->agent_id);
        if ($request->filled('center'))    $query->where('center', $request->center);
        if ($request->filled('date_from')) $query->where('visit_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('visit_date', '<=', $request->date_to);

        $visits  = $query->orderByDesc('visit_date')->paginate(25)->withQueryString();
        $agents  = ExtensionAgent::where('created_by', Auth::user()->creatorId())->orderBy('name')->get();
        $mccs    = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        $belowTarget = $agents->filter(fn($a) => $a->isBelowTarget());

        return view('extension::visits.index', compact('visits', 'agents', 'mccs', 'belowTarget'));
    }

    public function create()
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $agents  = ExtensionAgent::where('created_by', Auth::user()->creatorId())->where('status', 'active')->orderBy('name')->get();
        $farmers = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();
        $topics  = FieldVisit::TOPICS;
        $mccs    = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        return view('extension::visits.create', compact('agents', 'farmers', 'topics', 'mccs'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'agent_id'       => 'required|exists:extension_agents,id',
            'visit_date'     => 'required|date',
            'center'         => 'nullable|string',
            'community'      => 'nullable|string',
            'topics'         => 'nullable|array',
            'farmers'        => 'nullable|array',
            'photos.*'       => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $visit = FieldVisit::create([
            'visit_id'          => FieldVisit::generateVisitId(),
            'agent_id'          => $request->agent_id,
            'visit_date'        => $request->visit_date,
            'center'            => $request->center,
            'community'         => $request->community,
            'notes'             => $request->notes,
            'follow_up_required' => $request->boolean('follow_up_required'),
            'follow_up_date'    => $request->follow_up_date,
            'follow_up_note'    => $request->follow_up_note,
            'created_by'        => Auth::user()->creatorId(),
        ]);

        foreach ($request->input('topics', []) as $topic) {
            VisitTopic::create(['visit_id' => $visit->id, 'topic' => $topic]);
        }

        foreach ($request->input('farmers', []) as $farmerRow) {
            VisitFarmer::create([
                'visit_id'    => $visit->id,
                'farmer_id'   => $farmerRow['farmer_id'] ?? null,
                'farmer_name' => $farmerRow['farmer_name'] ?? null,
            ]);
        }

        if ($request->hasFile('photos')) {
            foreach (array_slice($request->file('photos'), 0, 3) as $photo) {
                $path = $photo->store('visit-photos', 'public');
                VisitPhoto::create(['visit_id' => $visit->id, 'photo_path' => $path]);
            }
        }

        if ($visit->follow_up_required && $visit->follow_up_date) {
            FollowUpTask::create([
                'visit_id'   => $visit->id,
                'agent_id'   => $visit->agent_id,
                'due_date'   => $visit->follow_up_date,
                'note'       => $visit->follow_up_note,
                'status'     => 'pending',
                'created_by' => Auth::user()->creatorId(),
            ]);
        }

        return redirect()->route('field-visits.show', $visit->id)->with('success', __('Field visit logged.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $visit = FieldVisit::with('agent', 'farmers', 'topics', 'photos')
            ->where('created_by', Auth::user()->creatorId())
            ->findOrFail($id);

        return view('extension::visits.show', compact('visit'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $visit   = FieldVisit::with('farmers', 'topics', 'photos')->where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $agents  = ExtensionAgent::where('created_by', Auth::user()->creatorId())->where('status', 'active')->orderBy('name')->get();
        $farmers = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();
        $topics  = FieldVisit::TOPICS;
        $mccs    = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        return view('extension::visits.create', compact('visit', 'agents', 'farmers', 'topics', 'mccs'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $visit = FieldVisit::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $visit->update([
            'agent_id'          => $request->agent_id,
            'visit_date'        => $request->visit_date,
            'center'            => $request->center,
            'community'         => $request->community,
            'notes'             => $request->notes,
            'follow_up_required' => $request->boolean('follow_up_required'),
            'follow_up_date'    => $request->follow_up_date,
            'follow_up_note'    => $request->follow_up_note,
        ]);

        $visit->topics()->delete();
        foreach ($request->input('topics', []) as $topic) {
            VisitTopic::create(['visit_id' => $visit->id, 'topic' => $topic]);
        }

        return redirect()->route('field-visits.show', $visit->id)->with('success', __('Visit updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $visit = FieldVisit::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        foreach ($visit->photos as $photo) Storage::disk('public')->delete($photo->photo_path);
        $visit->delete();

        return redirect()->route('field-visits.index')->with('success', __('Visit deleted.'));
    }
}
