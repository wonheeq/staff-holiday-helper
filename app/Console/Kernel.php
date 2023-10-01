<?php

namespace App\Console;

use App\Http\Controllers\MessageController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\RoleController;
use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\Message;
use App\Models\ManagerNomination;
use App\Http\Controllers\AccountController;
use DateTime;
use DateTimeZone;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        date_default_timezone_set("Australia/Perth");

        // ON THE ACTUAL SERVER, ADD THIS CRON COMMAND:
        /*
        * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
        */

        // delete expired password reset tokens every hour
        $schedule->command('auth:clear-resets')->hourly();


        // Every Hour, run commands to check and send archive emails.
        $schedule->call(function () {
            $msgController = new MessageController();
            $msgController->checkArchiveMessages();
        })->everyHour();


        // Every Odd Hour, attempt to send emails in backlog
        $schedule->call(function () {
            $emailController = new EmailController();
            $emailController->attemptBacklog();
        })->everyOddHour();


        // check all unresponded nominations every day
        // after the reminder timeframe has passed...
        //    the system will send a reminder every day until the nominations are responded to
        $schedule->call(function () {
            // now timestamp UTC
            $now = new DateTime();
            $reminderLists = $this->getReminderLists($now);
            $this->sendReminders($reminderLists);
        })->twiceDaily();
        //->everyThirtySeconds(); // 30 seconds for testing purposes



        // Checks manager nominations every 15 minutes (15 minutes seems like the most reasonable balance between too frequent and not frequent enough)
        $schedule->call(function () {
            $this->processManagerNominations();
        })//->everyFifteenMinutes();
        ->everyTenSeconds(); // Ten seconds for testing purposes
    }

    // Gets all approved applications
    // If the approved application has some associated ManagerNominations
    //   then check if the approved application is currently ongoing:
    //        AKA:  startDate >= now <= endDate
    //     then call promoteStaffTolineManager()
    //   otherwise if the approved application has expired
    //     Call demoteStaffFromLineManager()
    public function processManagerNominations() {
        // Get all approved applications
        $applications = DB::select("SELECT * FROM applications WHERE status='Y'");

        foreach ($applications as $application) {
            $now = new DateTime();
            $now->setTimezone(new DateTimeZone("Australia/Perth"));
            $startDate = new DateTime($application->sDate);
            $endDate = new DateTime($application->eDate);
            /*
            Log::debug("{$app->applicationNo}");
            Log::debug("Now: ".$now->format("Y-m-d H:i:s"));
            Log::debug("Start: ".$startDate->format("Y-m-d H:i:s"));
            Log::debug("End: ".$endDate->format("Y-m-d H:i:s"));
            */
            if ($now >= $startDate && $now <= $endDate) {
                //Log::debug("App {$application->applicationNo} Ongoing");
                $this->promoteStaffToLineManager($application->applicationNo);
            }
            // Application is expired
            else if ($now >= $endDate){
                //Log::debug("App {$application->applicationNo} Expired");
                $this->demoteStaffFromLineManager($application->applicationNo);
            }
        }
    }

    /*
    Temporarily sets accounts that agreed to takeover as a manager for a staff member
    to a temporary line manager
    */
    public function promoteStaffToLineManager($applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();
        $managerNominations = ManagerNomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();

        // Iterate through manager nominations
        foreach ($managerNominations as $nomination) {
            // Check if the user has been promoted already
            $account = Account::where('accountNo', $nomination->nomineeNo)->first();

            if ($account->isTemporaryManager == 0) {
                $account->isTemporaryManager = 1;
                $account->save();

                Log::debug("Promoted {$account->accountNo}");
            }
        }
    }
    
    /*
    Sets the application's status to Expired
    Revokes the temporary line manager status from an account
    "Removes" any non-acknowledged "Application Awaiting Review" messages from the temp line manager
        Resends this message to the current line manager of the applicant
    */
    public function demoteStaffFromLineManager($applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Safeguard to avoid double processing if it's even possible
        if ($application->status == "E") {return;}

        $application->status="E"; // Expired
        $application->save();

        $managerNominations = ManagerNomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();

        // Iterate through manager nominations
        foreach ($managerNominations as $nomination) {
            // Check if the user has been demoted already
            $account = Account::where('accountNo', $nomination->nomineeNo)->first();

            if ($account->isTemporaryManager == 1) {
                $account->isTemporaryManager = 0;
                $account->save();

                Log::debug("Demoted {$account->accountNo}");
            }

            // Remove non acknowledged "Application Awaiting Review" messages from the ex-temp line manager
            $messages = Message::where('receiverNo', $nomination->nomineeNo)
            ->where('subject', "Application Awaiting Review")
            ->where('acknowledged', 0)->get();

            foreach ($messages as $message) {
                $currentLineManager = app(AccountController::class)->getCurrentLineManager($message->senderNo);
                
                $createdTime = new DateTime();
                $message->update([
                    // Change receiverNo to the current line manager
                    'receiverNo' => $currentLineManager->accountNo,

                    // Change created_at and updated_at date to now so that currentLineManager doesn't
                    // get spammed with reminders if the temp did not respond for long enough to the messages
                    'created_at' => $createdTime,
                    'updated_at' => $createdTime,
                ]);
            }
        }

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

        // get all pending applications
        $applications = DB::select("SELECT * FROM applications WHERE status='P'");

        // iterate through each application
        foreach ($applications as $application) {
            // get all undecided nominations
            $nominations = DB::select("SELECT * FROM nominations WHERE status='U' AND applicationNo={$application->applicationNo}");

            // iterate through each nomination
            foreach ($nominations as $nomination) {
                $schoolId = Account::where('accountNo', $nomination->nomineeNo)->first()->schoolId;
                // get reminder timeframe for school
                $reminderQuery = DB::select("SELECT * FROM reminder_timeframes WHERE schoolId = {$schoolId}");
                $reminderTimeframe = $reminderQuery[0]->timeframe;
                // split using space delimiter and get the value + day/days/week
                $split = explode(" ", $reminderTimeframe);
                $reminderValue = intval($split[0]);
                $reminderPeriod = $split[1];
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
