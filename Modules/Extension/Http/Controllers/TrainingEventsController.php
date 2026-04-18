<?php

namespace Modules\Extension\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Extension\Models\TrainingEvent;
use Modules\Extension\Models\TrainingAttendee;
use Modules\Extension\Models\TrainingMaterial;
use App\Models\Vender;

class TrainingEventsController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $events = TrainingEvent::where('created_by', Auth::user()->creatorId())
            ->withCount('attendees')
            ->orderByDesc('event_date')->paginate(25);

        return view('extension::training.index', compact('events'));
    }

    public function create()
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $farmers = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();
        $mccs    = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        return view('extension::training.create', compact('farmers', 'mccs'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'event_date'  => 'required|date',
            'center'      => 'nullable|string',
            'location'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $event = TrainingEvent::create([
            'event_id'        => TrainingEvent::generateEventId(),
            'title'           => $request->title,
            'event_date'      => $request->event_date,
            'location'        => $request->location,
            'center'          => $request->center,
            'facilitators'    => $request->facilitators ? array_filter(explode(',', $request->facilitators)) : null,
            'topics_covered'  => $request->topics_covered,
            'notes'           => $request->notes,
            'created_by'      => Auth::user()->creatorId(),
        ]);

        foreach ($request->input('attendees', []) as $att) {
            if (! empty($att['farmer_name'])) {
                TrainingAttendee::create([
                    'event_id'    => $event->id,
                    'farmer_id'   => $att['farmer_id'] ?? null,
                    'farmer_name' => $att['farmer_name'],
                ]);
            }
        }

        foreach ($request->input('materials', []) as $mat) {
            if (! empty($mat['material_name'])) {
                TrainingMaterial::create([
                    'event_id'             => $event->id,
                    'material_name'        => $mat['material_name'],
                    'quantity_distributed' => $mat['quantity_distributed'] ?? 0,
                ]);
            }
        }

        return redirect()->route('training-events.show', $event->id)->with('success', __('Training event recorded.'));
    }

    public function show(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $event = TrainingEvent::with('attendees', 'materials')->where('created_by', Auth::user()->creatorId())->findOrFail($id);
        return view('extension::training.show', compact('event'));
    }

    public function edit(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $event   = TrainingEvent::with('attendees', 'materials')->where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $farmers = Vender::where('created_by', Auth::user()->creatorId())->where('is_active', 1)->orderBy('name')->get();
        $mccs    = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];

        return view('extension::training.create', compact('event', 'farmers', 'mccs'));
    }

    public function update(Request $request, int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $event = TrainingEvent::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $event->update([
            'title'          => $request->title,
            'event_date'     => $request->event_date,
            'location'       => $request->location,
            'center'         => $request->center,
            'facilitators'   => $request->facilitators ? array_filter(explode(',', $request->facilitators)) : null,
            'topics_covered' => $request->topics_covered,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('training-events.show', $event->id)->with('success', __('Event updated.'));
    }

    public function destroy(int $id)
    {
        if (! Auth::user()->can('manage extension agents')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        TrainingEvent::where('created_by', Auth::user()->creatorId())->findOrFail($id)->delete();
        return redirect()->route('training-events.index')->with('success', __('Event deleted.'));
    }
}
