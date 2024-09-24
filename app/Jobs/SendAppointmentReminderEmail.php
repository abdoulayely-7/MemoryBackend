<?php

namespace App\Jobs;

use App\Mail\AppointmentReminder;
use App\Models\RendezVous;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointment;

    public function __construct(RendezVous $appointment)
    {
        $this->appointment = $appointment;
    }

    public function handle()
    {
        // Envoie l'email de rappel
        Mail::to($this->appointment->patient->email)->send(new AppointmentReminder($this->appointment));
    }
}
