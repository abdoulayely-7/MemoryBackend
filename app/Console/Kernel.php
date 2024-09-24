<?php

namespace App\Console;

use App\Jobs\SendAppointmentReminderEmail;
use App\Models\RendezVous;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $appointments = RendezVous::where('status', 'confirmé')
                ->whereHas('creneau', function($query) {
                    $query->whereHas('planning', function($query) {
                        // On vérifie que la date du planning est bien celle du lendemain
                        $query->whereDate('datePlanning', now()->addDays(2)->format('Y-m-d'));
                    });
                })
                ->get();

            foreach ($appointments as $appointment) {
                dispatch(new SendAppointmentReminderEmail($appointment));
            }
        })->dailyAt('16:45');
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
