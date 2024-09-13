<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Creneau;

class CreneauController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'planning_id' => 'required|exists:plannings,id',
            'heureDebut' => 'required|date_format:H:i',
            'heureFin' => 'required|date_format:H:i|after:heureDebut',
        ]);

        $creneau = Creneau::create($request->all());

        return response()->json($creneau, 201);
    }

    public function show($id)
    {
        $creneau = Creneau::with('rendezVous')->find($id);

        if (!$creneau) {
            return response()->json(['message' => 'Creneau not found'], 404);
        }

        return response()->json($creneau);
    }
}
