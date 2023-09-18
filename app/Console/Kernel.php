<?php

namespace App\Console;

use App\Http\Controllers\MessageController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ON THE ACTUAL SERVER, ADD THIS CRON COMMAND:
        /*
        * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
        */

        // delete expired password reset tokens every hour
        $schedule->command('auth:clear-resets')->hourly();

        $schedule->call(function () {
            $msgController = new MessageController();
            $msgController->sendDailyMessages();
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
