<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Planning;

class PlanningController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'medecin_id' => 'required|exists:users,id',
            'jour' => 'required|string',
            'heureDebut' => 'required|date_format:H:i',
            'heureFin' => 'required|date_format:H:i|after:heureDebut',
        ]);

        $planning = Planning::create($request->all());

        return response()->json($planning, 201);
    }

    public function show($id)
    {
        $planning = Planning::with('creneaux')->find($id);

        if (!$planning) {
            return response()->json(['message' => 'Planning not found'], 404);
        }

        return response()->json($planning);
    }

}
