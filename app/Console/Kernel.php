<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\RoleController;
use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
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
            // now timestamp UTC
            $now = new DateTime();
            $reminderLists = $this->getReminderLists($now);
            $this->sendReminders($reminderLists);
        })->everyThirtySeconds();
    }

    /*
    For each school, generates a list of accountNo and for each accountNo a list of applications they have yet to respond to
    Format:
    [
        SchoolList,
        SchoolList,
        SchoolList,
    ]

    SchoolList:
    [
        NomineeNo = [
            ApplicationNo,
            ApplicationNo,
            ApplicationNo,
        ],
        NomineeNo = [
            ApplicationNo,
            ApplicationNo,
            ApplicationNo,
        ],
    ]
    */
    public function getReminderLists($now) {
        Log::debug("running reminder checker");
        $remindersToSend = array();

        // iterate through each school
        $schools = DB::select('SELECT * FROM schools');

        foreach ($schools as $school) {
            // get reminder timeframe for school
            $reminderQuery = DB::select("SELECT * FROM reminder_timeframes WHERE schoolId = {$school->schoolId}");
            $reminderTimeframe = $reminderQuery[0]->timeframe;
            // split using space delimiter and get the value + day/days/week
            $split = explode(" ", $reminderTimeframe);
            $reminderValue = intval($split[0]);
            $reminderPeriod = $split[1];

            // get all pending applications
            $applications = DB::select("SELECT * FROM applications WHERE status='P'");

            // iterate through each application
            foreach ($applications as $application) {
                // get all undecided nominations
                $nominations = DB::select("SELECT * FROM nominations WHERE status='U' AND applicationNo={$application->applicationNo}");

                // iterate through each nomination
                foreach ($nominations as $nomination) {
                    $schoolId = Account::where('accountNo', $nomination->nomineeNo)->first()->schoolId;
                    
                    $created = new DateTime($nomination->created_at); // UTC
                    
                    $diff = date_diff($created, $now);
                    
                    // reminder timeframe is in days
                    if (str_contains($reminderPeriod, "day")) {
                        // check if period has been surpassed
                        if ($diff->d >= $reminderValue) {
                            // Add schoolId to remindersToSend if not there already
                            if (!array_key_exists($schoolId, $remindersToSend)) {
                                $remindersToSend[$schoolId] = array();
                            }

                            // Add nomineeNo to remindersToSend if not in there already
                            if (!array_key_exists($nomination->nomineeNo, $remindersToSend[$schoolId])) {
                                $remindersToSend[$schoolId][$nomination->nomineeNo] = array();
                            }

                            // Add applicationNo to remindersToSend if not in there already
                            if (!in_array($application->applicationNo, $remindersToSend[$schoolId][$nomination->nomineeNo])) {
                                array_push($remindersToSend[$schoolId][$nomination->nomineeNo], $application->applicationNo);
                            }
                        }
                    }
                    // reminder timeframe is in weeks
                    else if (str_contains($reminderPeriod, "week")) {
                        // check if period has been surpassed
                        if ($diff->d >= 7) {                   
                            // Add schoolId to remindersToSend if not there already
                            if (!array_key_exists($schoolId, $remindersToSend)) {
                                $remindersToSend[$schoolId] = array();
                            }

                            // Add nomineeNo to remindersToSend if not in there already
                            if (!array_key_exists($nomination->nomineeNo, $remindersToSend[$schoolId])) {
                                $remindersToSend[$schoolId][$nomination->nomineeNo] = array();
                            }

                            // Add applicationNo to remindersToSend if not in there already
                            if (!in_array($application->applicationNo, $remindersToSend[$schoolId][$nomination->nomineeNo])) {
                                array_push($remindersToSend[$schoolId][$nomination->nomineeNo], $application->applicationNo);
                            }
                        }
                    }
                }
            }
        }
        return $remindersToSend;
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
    public function sendReminders($reminders) {
        Log::debug("Sending Reminders:");
        Log::debug($reminders);

        // TEMPORARY
        $hasSentOne = false;

        // iterate through each schoolId, array pair
        foreach ($reminders as $schoolReminders) {
            // iterate through each accountNo, array of applicationNos pair
            foreach ($schoolReminders as $accountNo => $applicationNoList) {
                $account = Account::where('accountNo', $accountNo)->first();
                // create dynamicData for the user
                $dynamicData = array(
                    'receiverName' => "{$account->fName} {$account->lName}",
                    'numApps' => count($applicationNoList),
                    'applications' => array()
                );
                $num = 0;
                // iterate through list of applicationNo's
                foreach ($applicationNoList as $applicationNo) {
                    $application = Application::where('applicationNo', $applicationNo)->first();
                    
                    $applicant = Account::where('accountNo', $application->accountNo)->first();
                    $applicantName = "{$applicant->fName} {$applicant->lName}";    
                
                    $duration = "{$application->sDate} - {$application->eDate}";

                    $roles = [];
                    // Get the nominated roles
                    $nominations = Nomination::where('applicationNo', $applicationNo)->where('nomineeNo', $accountNo)->where('status', 'U')->get();
                    Log::debug("NOMINATIONS: appNo{$applicationNo} nom{$accountNo}");
                    Log::debug($nominations);
                    foreach ($nominations as $nomination) {
                        $num++;
                        
                        $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nomination->accountRoleId);
                        
                        array_push($roles, $roleName);
                    }

                    $roleString = implode("\n", $roles);
                    Log::debug($roleString);

                    array_push($dynamicData['applications'], array(
                        'duration' => $duration,
                        'applicantName' => $applicantName,
                        'roles' => $roleString
                    ));
                }

                $dynamicData['numNominations'] = $num;
                // TEMPORARY: don't wanna spam too many emails
                if (!$hasSentOne) {
                    $hasSentOne = true;
                    Log::debug($dynamicData);
                    app(EmailController::class)->nominationReminder($dynamicData, $accountNo);
                }
                //Log::debug($dynamicData);
                //app(EmailController::class)->nominationReminder($dynamicData, $accountNo);
            }
        }
    }
}
