<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $hour = config('app.hour');
        $min = config('app.min');
        $scheduledInterval = $hour !== '' ? (($min !== '' && $min != 0) ? $min . ' */' . $hour . ' * * *' : '0 */' . $hour . ' * * *') : '*/' . $min . ' * * * *';

        $schedule->command('edp:pegar-todos-retornos')
            ->name('edp-retornos-08h')
            ->timezone('America/Sao_Paulo')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/edp-retornos.log'));

        $schedule->command('edp:pegar-todos-retornos')
            ->name('edp-retornos-12h30')
            ->timezone('America/Sao_Paulo')
            ->dailyAt('12:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/edp-retornos.log'));

        $schedule->command('edp:pegar-todos-retornos')
            ->name('edp-retornos-17h')
            ->timezone('America/Sao_Paulo')
            ->dailyAt('17:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/edp-retornos.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
