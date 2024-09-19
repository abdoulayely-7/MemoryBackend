<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Creneau;
use App\Models\Planning;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function store(Request $request)
    {
        // Validation des données du formulaire
        $validatedData = $request->validate([
            'medecin_id' => 'required|exists:users,id', // Assurez-vous que le médecin existe
            'datePlanning' => 'required|date', // Date du planning
            'heureDebut' => 'required|date_format:H:i', // Heure de début
            'heureFin' => 'required|date_format:H:i|after:heureDebut', // Heure de fin après heureDebut
            'dureeCreneau' => 'required|integer|min:5|max:60' // Durée en minutes (exemple: 15, 20, 30)
        ]);

        // Créer le planning pour le médecin
        $planning = Planning::create([
            'medecin_id' => $validatedData['medecin_id'],
            'datePlanning' => $validatedData['datePlanning'],
            'heureDebut' => $validatedData['heureDebut'],
            'heureFin' => $validatedData['heureFin'],
        ]);

        // Générer les créneaux en fonction de la durée spécifiée
        $this->genererCreneaux($planning, $validatedData['dureeCreneau']);

        return response()->json(['message' => 'Planning et créneaux créés avec succès!']);
    }

    /**
     * Générer les créneaux pour un planning donné en fonction de la durée demandée.
     */
    private function genererCreneaux(Planning $planning, int $dureeCreneau)
    {
        $heureDebut = Carbon::createFromFormat('H:i', $planning->heureDebut);
        $heureFin = Carbon::createFromFormat('H:i', $planning->heureFin);

        // Boucle pour générer les créneaux
        while ($heureDebut->lt($heureFin)) {
            $debutCreneau = $heureDebut->copy();
            $finCreneau = $heureDebut->copy()->addMinutes($dureeCreneau);

            // Vérifier que le créneau ne dépasse pas l'heure de fin
            if ($finCreneau->gt($heureFin)) {
                break;
            }

            // Créer le créneau
            Creneau::create([
                'planning_id' => $planning->id,
                'heureDebut' => $debutCreneau->format('H:i'),
                'heureFin' => $finCreneau->format('H:i'),
                'status' => 'libre', // Statut par défaut
            ]);

            // Avancer à la fin du créneau pour générer le suivant
            $heureDebut = $finCreneau;
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
                    $query->where('status', 'en attente de confirmation');
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
