<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Creneau;
use App\Models\Planning;
use App\Models\User;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'medecin_id' => 'required|exists:users,id',
            'datePlanning' => 'required|date',
            'creneaux' => 'required|array',
            'creneaux.*.heureDebut' => 'required',
            'creneaux.*.heureFin' => 'required',
        ]);

        try {
            // Créer le planning
            $planning = Planning::create([
                'medecin_id' => $request->medecin_id,
                'datePlanning' => $request->datePlanning
            ]);

            // Créer les créneaux
            foreach ($request->creneaux as $creneau) {
                Creneau::create([
                    'planning_id' => $planning->id, // Utilisez l'ID du planning créé
                    'heureDebut' => $creneau['heureDebut'],
                    'heureFin' => $creneau['heureFin']
                ]);
            }

            return response()->json([
                'statut' => 201,
                'data' => $planning,
                'message' => 'Planning créé avec succès',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'statut' => false,
                'message' => 'Erreur lors de la création du planning',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $planning = Planning::with('creneaux')->find($id);

        if (!$planning) {
            return response()->json(['message' => 'Planning not found'], 404);
        }

        return response()->json($planning);
    }
    public function list($medecin_id)
    {
        $plannings = Planning::where('medecin_id', $medecin_id)->with('creneaux')->get();
        return response()->json($plannings);
    }
    public function getDoctorDetails($id)
    {
        $doctor = User::where('id', $id)->where('role', 'medecin')->first();

        if (!$doctor) {
            return response()->json([
                'statut' => false,
                'message' => 'Médecin non trouvé',
            ], 404);
        }
        $doctor->photo_url = $doctor->photo ? url('storage/images/' . $doctor->photo) : null;
        return response()->json([
            'statut' => true,
            'data' => $doctor,
            'message' => $doctor->service ? '' : 'Service non attribué'
        ], 200);
    }
    public function getDisponibilites($medecinId, $date)
    {
        // Vérifie si la date existe dans le planning du médecin
        $planning = Planning::where('medecin_id', $medecinId)
            ->where('datePlanning', $date)
            ->first();

        if ($planning) {
            // Si le planning existe pour cette date, récupère les créneaux associés
            $creneaux = Creneau::where('planning_id', $planning->id)
                ->whereDoesntHave('rendezVous', function($query) {
                    // Exclure les créneaux déjà réservés
                    $query->where('status', 'confirmé');
                })
                ->get();

            return response()->json([
                'statut' => 200,
                'creneaux' => $creneaux,
            ]);
        } else {
            // Si la date n'existe pas dans le planning
            return response()->json([
                'statut' => 404,
                'message' => 'Aucun créneau disponible pour cette date'
            ]);
        }
    }
}
