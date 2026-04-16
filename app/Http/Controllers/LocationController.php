<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\State;

class LocationController extends Controller
{
    public function getLgas($stateId)
    {
        $state = State::with('lgas')->find($stateId);

        if (!$state) {
            return response()->json(['error' => 'State not found'], 404);
        }

        return response()->json($state->lgas);
    }
}
