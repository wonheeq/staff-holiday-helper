<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;
use DateTimeZone;
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
        // $schedule->command('auth:clear-resets')->everyFifteenSeconds();



        // check all unresponded nominations every day
        // after the reminder timeframe has passed...
        //    the system will send a reminder every day until the nominations are responded to
        $schedule->call(function () {
            // iterate through each school
            $schools = DB::select('SELECT * FROM schools');

            // now timestamp UTC
            $now = new DateTime();

            foreach ($schools as $school) {
                // get reminder timeframe for school
                $reminderQuery = DB::select("SELECT * FROM reminder_timeframes WHERE schoolId = {$school->schoolId}");
                $reminderTimeframe = $reminderQuery[0]->timeframe;
                // split using space delimiter and get the value + day/days/week
                $split = explode(" ", $reminderTimeframe);
                $reminderValue = intval($split[0]);
                $reminderPeriod = $split[1];

                $remindersToSend = array();

                // get all pending applications for each school
                $applications = DB::select("SELECT * FROM applications INNER JOIN accounts ON applications.accountNo = accounts.accountNo WHERE accounts.schoolId = {$school->schoolId} AND applications.status = 'P'");

                // iterate through each application
                foreach ($applications as $application) {
                    // get all undecided nominations
                    $nominations = DB::select("SELECT * FROM nominations WHERE status='U'");

                    // iterate through each nomination
                    foreach ($nominations as $nomination) {
                        $created = new DateTime($nomination->created_at); // UTC
                        
                        $diff = date_diff($created, $now);
                        
                        // reminder timeframe is in days
                        if (str_contains($reminderPeriod, "day")) {
                            // check if period has been surpassed
                            if ($diff->d >= $reminderPeriod) {
                                // Add nomineeNo to remindersToSend if not in there already
                                if (!array_key_exists($nomination->nomineeNo, $remindersToSend)) {
                                    $remindersToSend[$nomination->nomineeNo] = array();
                                }

                                // Add applicationNo to remindersToSend if not in there already
                                if (!in_array($application->applicationNo, $remindersToSend[$nomination->nomineeNo])) {
                                    array_push($remindersToSend[$nomination->nomineeNo], $application->applicationNo);
                                }
                            }
                        }
                        // reminder timeframe is in weeks
                        else if (str_contains($reminderPeriod, "week")) {
                            // check if period has been surpassed
                            if ($diff->d >= 7) {                   
                                // Add nomineeNo to remindersToSend if not in there already
                                if (!array_key_exists($nomination->nomineeNo, $remindersToSend)) {
                                    $remindersToSend[$nomination->nomineeNo] = array();
                                }

                                // Add applicationNo to remindersToSend if not in there already
                                if (!in_array($application->applicationNo, $remindersToSend[$nomination->nomineeNo])) {
                                    array_push($remindersToSend[$nomination->nomineeNo], $application->applicationNo);
                                }    
                            }
                        }
                    }
                }

                if (count($remindersToSend) > 0) {
                    $this->sendReminders($remindersToSend);
                }
            }

        })->everyTenSeconds();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }


    /*
    Processes reminders to be sent via email
    Format:
    {
        nomineeNo: [
            applicationNo,
            applicationNo,
            applicationNo
        ]
    }

    nomineeNo as key
    array of applications with unresponded nominations as the value
    */
    private function sendReminders($reminders) {
        Log::debug("Sending Reminders:");
        Log::debug($reminders);
    }
}
