<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\RendezVousAccepte;
use App\Mail\RendezVousAnnule;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
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

    use Twilio\Rest\Client;

    use App\Mail\RendezVousAnnule;
    use Illuminate\Support\Facades\Mail;

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
            $message = 'Votre rendez-vous a été accepté avec succès.';
        } else {
            $rdv->status = 'annulé'; // Exemple de statut pour refusé
            $message = 'Votre rendez-vous a été refusé avec succès.';
        }
        $rdv->save();

        // Envoyer un SMS
        $this->sendSms($rdv->patient->telephone, $message);

        // Envoyer un email après annulation
        if ($status !== 'accepté') {
            Mail::to($rdv->patient->email)->send(new RendezVousAnnule($rdv, $message));
        }elseif ($status  == 'accepté') {
                Mail::to($rdv->patient->email)->send(new RendezVousAccepte($rdv, $message));
            }

        return response()->json([
            'message' => $message,
            'status' => $rdv->status
        ], 200);
    }


    private function sendSms($to, $message)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilioNumber = env('TWILIO_PHONE_NUMBER');

        $client = new Client($sid, $token);

        try {
            $client->messages->create($to, [
                'from' => $twilioNumber,
                'body' => $message
            ]);
        } catch (\Exception $e) {
            // Gérer les exceptions si l'envoi échoue
            \Log::error('Erreur lors de l\'envoi du SMS: ' . $e->getMessage());
        }
    }

}
