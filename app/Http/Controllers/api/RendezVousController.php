<?php

namespace App\Http\Controllers\api;

use App\Events\RendezVousNotification;
use App\Http\Controllers\Controller;
use App\Mail\RendezVousStatusEmail;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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


        // Préparer l'email
        $subject = 'Demande de rendez-vous';
        $body = "Bonjour " . $rdv->patient->prenom . ",\n\nVotre rendez-vous prévu pour le " . $rdv->creneau->planning->datePlanning . " a été " . ($rdv->status == 'confirmé' ? "accepté" : "refusé") . ".\n\nMerci de votre confiance.\nCordialement,\nL'équipe médicale";

        // Envoyer l'email
        Mail::to($rdv->patient->email)->send(new RendezVousStatusEmail($subject, $body, $rdv->patient, $rdv->creneau->planning->datePlanning, $rdv->status));
        // Diffuser une notification en temps réel au patient
//        broadcast(new RendezVousNotification($rdv->patient, $rdv->status))->toOthers();
        // Message en fonction du nouveau statut
        $message = $status === 'accepté' ? 'Rendez-vous accepté avec succès.' : 'Rendez-vous refusé avec succès.';

        return response()->json([
            'message' => $message,
            'status' => $rdv->status
        ], 200);
    }
    public function getMedecinAppointment($medecinId)
    {
        $appointments = RendezVous::where('medecin_id', $medecinId)
            ->with(['patient', 'creneau.planning'])
            ->where('status', 'confirmé')
            ->get();

        return response()->json([
            'statut' => true,
            'data' => $appointments,
        ], 200);
    }
    public function getPatientAppointments()
    {
        // Récupérer l'ID du patient connecté
        $patientId = auth()->user()->id;

        // Récupérer les rendez-vous du patient
        $appointments = RendezVous::where('patient_id', $patientId)
            ->with(['medecin', 'creneau.planning'])
            ->where('status', 'confirmé')
            ->get();

        return response()->json([
            'statut' => true,
            'data' => $appointments,
        ], 200);
    }

    public function testEmail()
    {
        Mail::to('afndiaye@groupeisi.com')->send(new RendezVousStatusEmail('Test Subject',
            'Test Body', (object)['prenom' => 'Test'], '2024-09-22', 'confirmé'));

        return response()->json(['message' => 'Email envoyé avec succès.']);
    }
    public function getAppointmentC()
    {
        try {
            $totalRdv = RendezVous::count();
            $confirmeRdv = RendezVous::where('status','confirmé')->count();
            $refuserRdv = RendezVous::where('status','annulé')->count();
            $percentageAnnu = $totalRdv > 0 ? ($refuserRdv / $totalRdv) * 100 : 0;
            $percentage = $totalRdv > 0 ? ($confirmeRdv  / $totalRdv) * 100 : 0;
            return response()->json([
                'statut' => 200,
                'data' => [
                    'total' => $totalRdv,
                    'confirmer' => $confirmeRdv,
                    'refuser' => $refuserRdv,
                    'pourcentage' => round($percentage, 2),
                    'pourcentagerefuser' => round($percentageAnnu, 2),
                ],
            ], 200);
        }catch (\Exception $e)
        {
            return response()->json([
                'statut' => false,
                'message' => 'Erreur lors de la récupération des rendez-vous',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
