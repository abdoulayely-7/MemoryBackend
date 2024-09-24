<?php

namespace App\Mail;

use App\Models\RendezVous;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    public function __construct(RendezVous $appointment)
    {
        $this->appointment = $appointment;
    }

    public function build()
    {
        return $this->view('emails.reminder')
            ->subject('Rappel de votre rendez-vous')
            ->with([
                'patientName' => $this->appointment->patient->prenom,
                'patientLastName' => $this->appointment->patient->nom,
                'appointmentDate' => $this->appointment->creneau->heureDebut,
            ]);
    }
}
