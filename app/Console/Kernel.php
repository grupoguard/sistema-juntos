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
        $scheduledInterval = $hour !== '' ? ( ($min !== '' && $min != 0) ?  $min .' */'. $hour .' * * *' : '0 */'. $hour .' * * *') : '*/'. $min .' * * * *';
        if(env('IS_DEMO')) {
            $schedule->command('migrate:fresh --seed')->cron($scheduledInterval);
        }

        $schedule->job(new \App\Jobs\BaixarArquivoRetornoJob)->timezone('America/Sao_Paulo')->at('08:00');
        $schedule->job(new \App\Jobs\BaixarArquivoRetornoJob)->timezone('America/Sao_Paulo')->at('12:30');
        $schedule->job(new \App\Jobs\BaixarArquivoRetornoJob)->timezone('America/Sao_Paulo')->at('17:00');
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
