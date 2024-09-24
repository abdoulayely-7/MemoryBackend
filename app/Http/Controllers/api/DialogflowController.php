<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Http\Request;

class DialogflowController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        // Vérifier si l'intent existe dans la requête
        if (!isset($data['queryResult']['intent']['displayName'])) {
            return response()->json([
                'fulfillmentText' => 'Je ne comprends pas votre demande.'
            ]);
        }

        $intentName = $data['queryResult']['intent']['displayName'];

        if ($intentName == 'RendezVousIntent') {
            $patientName = $data['queryResult']['parameters']['prenom'] ?? null;

            // Vérifiez si le nom du patient a été extrait correctement
            if (!$patientName) {
                return response()->json([
                    'fulfillmentText' => 'Je n\'ai pas pu identifier votre nom. Veuillez réessayer.'
                ]);
            }

            // Rechercher le patient par son prénom
            $patient = User::where('prenom', $patientName)->first();

            if (!$patient) {
                return response()->json([
                    'fulfillmentText' => 'Je ne trouve aucun patient avec ce nom.'
                ]);
            }

            // Chercher le rendez-vous par l'ID du patient
            $rendezVous = RendezVous::where('patient_id', $patient->id)->first();

            if ($rendezVous) {
                $responseText = "Votre rendez-vous est confirmé pour le "
                    . $rendezVous->creneau->planning->datePlanning .
                    " à " . $rendezVous->creneau->heureDebut;
            } else {
                $responseText = "Je ne trouve aucun rendez-vous pour vous.";
            }

            return response()->json([
                'fulfillmentText' => $responseText
            ]);
        }

        return response()->json([
            'fulfillmentText' => 'Je ne comprends pas votre demande.'
        ]);
    }

}
