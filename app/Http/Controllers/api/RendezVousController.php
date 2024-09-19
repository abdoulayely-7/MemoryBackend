<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Creneau;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class RendezVousController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data=$request->validate([
                'medecin_id' => 'required|exists:users,id',
                'creneau_id' => 'required|exists:creneaus,id',
                'motif' => 'nullable|string',
            ]);
            $data['patient_id'] = Auth::id();
            $data['status'] = 'en attente';

            $rendezVous = RendezVous::create($data);

            // Mettre à jour le créneau pour le marquer comme "en prise"
            $creneau = Creneau::find($data['creneau_id']);
            if ($creneau) {
                $creneau->status = 'en attente de confirmation';
                $creneau->save();
            }

//            $token = JWTAuth::fromUser($rendezVous);

            return response()->json([
                'statut' => 201,
                'data' => $rendezVous,
                "token" => null,
            ], 201);
        }catch (\Exception $e)
        {
            return response()->json([
                "statut" => false,
                "message" => "Erreur lors de la creation du rendez-vous",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous) {
            return response()->json(['message' => 'Rendez-vous not found'], 404);
        }

        return response()->json($rendezVous);
    }

    public function getMedecinAppointments($medecinId)
    {
        $appointments = RendezVous::where('medecin_id', $medecinId)
            ->where('status', 'en attente')
            ->with(['patient', 'creneau.planning'])
            ->get();

        if (!$appointments) {
            return response()->json([
                'statut' => false,
                'message' => 'Aucun Rendez-vous non trouvé',
            ], 404);
        }
        return response()->json([
            'statut' => true,
            'data' => $appointments,
        ], 200);
    }

    public function validaterdv($id, Request $request)
    {
        // Trouver le rendez-vous par ID
        $rdv = RendezVous::find($id);

        if (!$rdv) {
            return response()->json([
                'message' => 'Rendez-vous non trouvé.',
                'status' => 'error'
            ], 404);
        }

        // Mettre à jour le statut
        $status = $request->input('status'); // Attendu: 'accepté' ou 'refusé'
        if ($status === 'accepté') {
            $rdv->status = 'confirmé'; // Exemple de statut pour accepté
        } else {
            $rdv->status = 'annulé'; // Exemple de statut pour refusé
        }
        $rdv->save();

        // Message en fonction du nouveau statut
        $message = $status === 'accepté' ? 'Rendez-vous accepté avec succès.' : 'Rendez-vous refusé avec succès.';

        return response()->json([
            'message' => $message,
            'status' => $rdv->status
        ], 200);
    }
}
