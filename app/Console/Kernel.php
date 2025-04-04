<?php
// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecutar la actualización de precios cada hora
        $schedule->command('prices:update')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/prices-update.log'));
        
        // Alternativa: personalizar la frecuencia según necesidades
        // Ejemplo: Ejecutar cada 6 horas
        // $schedule->command('prices:update')
        //          ->everyFourHours()
        //          ->withoutOverlapping()
        //          ->appendOutputTo(storage_path('logs/prices-update.log'));
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