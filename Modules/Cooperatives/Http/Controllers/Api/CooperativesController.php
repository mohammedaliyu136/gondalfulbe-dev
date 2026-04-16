<?php

namespace Modules\Cooperatives\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Cooperatives\Models\Cooperative;

/**
 * Dedicated API controller that returns JSON only.
 * The web CooperativesController returns Blade views and must NOT be reused here.
 */
class CooperativesController extends Controller
{
    // ─── LIST ─────────────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        if (! Auth::user()->can('manage cooperative')) {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        $cooperatives = Cooperative::where('created_by', Auth::user()->creatorId())
            ->withCount('farmers')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $cooperatives]);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function show(int $id): JsonResponse
    {
        if (! Auth::user()->can('manage cooperative')) {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())
            ->withCount('farmers')
            ->find($id);

        if (! $cooperative) {
            return response()->json(['message' => 'Cooperative not found.'], 404);
        }

        return response()->json(['data' => $cooperative]);
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        if (! Auth::user()->can('create cooperative')) {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        $validated = $request->validate([
            'name'                  => 'required|string|max:255|unique:cooperatives,name',
            'location'              => 'nullable|string|max:255',
            'leader_name'           => 'nullable|string|max:255',
            'leader_phone'          => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]{7,20}$/'],
            'site_location'         => 'nullable|string|max:255',
            'formation_date'        => 'nullable|date',
            'average_daily_supply'  => 'nullable|numeric|min:0',
            'status'                => 'nullable|in:active,inactive',
        ]);

        $cooperative = Cooperative::create(array_merge($validated, [
            'average_daily_supply' => $validated['average_daily_supply'] ?? 0,
            'status'               => $validated['status'] ?? 'active',
            'created_by'           => Auth::user()->creatorId(),
        ]));

        $cooperative->update([
            'code' => Cooperative::generateCode($cooperative->id, $cooperative->location),
        ]);

        return response()->json(['data' => $cooperative, 'message' => 'Cooperative created.'], 201);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        if (! Auth::user()->can('edit cooperative')) {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())->find($id);

        if (! $cooperative) {
            return response()->json(['message' => 'Cooperative not found.'], 404);
        }

        $validated = $request->validate([
            'name'                  => 'required|string|max:255|unique:cooperatives,name,' . $id,
            'location'              => 'nullable|string|max:255',
            'leader_name'           => 'nullable|string|max:255',
            'leader_phone'          => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]{7,20}$/'],
            'site_location'         => 'nullable|string|max:255',
            'formation_date'        => 'nullable|date',
            'average_daily_supply'  => 'nullable|numeric|min:0',
            'status'                => 'nullable|in:active,inactive',
        ]);

        $cooperative->update($validated);

        return response()->json(['data' => $cooperative, 'message' => 'Cooperative updated.']);
    }

    // ─── DELETE ───────────────────────────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        if (! Auth::user()->can('delete cooperative')) {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        $cooperative = Cooperative::where('created_by', Auth::user()->creatorId())->find($id);

        if (! $cooperative) {
            return response()->json(['message' => 'Cooperative not found.'], 404);
        }

        if ($cooperative->farmers()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete: cooperative has registered farmers. Reassign them first.',
            ], 422);
        }

        $cooperative->delete();

        return response()->json(['message' => 'Cooperative deleted.']);
    }
}
