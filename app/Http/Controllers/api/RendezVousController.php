<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\RendezVous;

class RendezVousController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'creneau_id' => 'required|exists:creneaux,id',
            'status' => 'required|string',
            'commentaire' => 'nullable|string',
        ]);

        $rendezVous = RendezVous::create($request->all());

        return response()->json($rendezVous, 201);
    }

    public function show($id)
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous) {
            return response()->json(['message' => 'Rendez-vous not found'], 404);
        }

        return response()->json($rendezVous);
    }
}
