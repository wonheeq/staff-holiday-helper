<?php

namespace App\Http\Controllers;

use App\Jobs\SendAppCancelledManager;
use App\Jobs\SendApplicationDecision;
use App\Jobs\SendAppWaitingRev;
use App\Jobs\SendConfirmSubstitutions;
use App\Jobs\SendNominationCancelled;
use App\Jobs\SendNominationDeclined;
use App\Jobs\SendNominationEmail;
use App\Jobs\SendWelcomeEmail;
use App\Mail\MJML;
use App\Models\UnsentEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\Account;
use App\Models\Message;
use App\Models\EmailPreference;
use DateTime;
use App\Jobs\SendNominationEmailJob;
use App\Jobs\SendNominationsCancelled;
use App\Jobs\SendNomineeAppEdited;
use App\Jobs\SendSubPeriodEditSubset;
use App\Jobs\SendSystemNotification;
use Error;
use ErrorException;

class EmailController extends Controller
{
    public function nominationReminder($dynamicData, $accountNo): void {
        Mail::to("{$accountNo}@curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
    }


    // Goes through the backlog of unset emails and attempts to send them
    public function attemptBacklog(): void {
        $backlog = UnsentEmail::all();
        foreach($backlog as $email)
        {
            $this->sortMail($email);
        }
    }



    public function sortMail($email)
    {
        // switch case used here for expandability to other email types
        // that might be added later
        $subject = $email->subject;
        $data = json_decode($email->data);
        $id = $email->id;
        switch($subject){
            case "Unacknowledged Messages":
                $this->attemptUnackMsg($email);
            break;

            case "New Nominations":
                SendNominationEmail::dispatch($data, true, $id);
            break;

            case "Application Awaiting Review":
                SendAppWaitingRev::dispatch($data, true, $id);
            break;

            case "Application Cancelled":
                SendAppCancelledManager::dispatch($data, true, $id);
            break;

            case "Nomination Cancelled":
                SendNominationCancelled::dispatch($data, true, $id);
            break;

            case "Nomination/s Cancelled":
                SendNominationsCancelled::dispatch($data, true, $id);
            break;

            case "Substitution Period Edited (Subset)":
                SendSubPeriodEditSubset::dispatch($data, true, $id);
            break;

            case "Edited Substitution Request":
                SendNomineeAppEdited::dispatch($data, true, $id);
            break;

            case "Nomination/s Rejected":
                SendNominationDeclined::dispatch($data, true, $id);
            break;

            case "Application Updated":
                SendApplicationDecision::dispatch($data, true, $id);
            break;

            case "System Notification":
                SendSystemNotification::dispatch($data, true, $id);
            break;

            case "Confirmed Substitutions":
                SendConfirmSubstitutions::dispatch($data, true, $id);
            break;

            case "Welcome to LeaveOnTime":
                $data = json_decode($email->data);
                SendWelcomeEmail::dispatch($data, true);
            break;
        }
    }



    // Handles the attempted resending of an archive email
    private function attemptUnackMsg($email)
    {

            // Get account and preferences
            $accountNo = $email->accountNo;
            $user = Account::where('accountNo', $accountNo)->first();
            $preferences = EmailPreference::get()->where('accountNo', $accountNo)->first();

            // calculate hours between current time and last time the reminder was sent
            $now = new DateTime('NOW');
            $lastSent = new DateTime($preferences->timeLastSent);
            $interval = $now->diff($lastSent);
            $hourInterval = ($interval->h) + ($interval->days * 24);

            // if the time passed is greater than the reminder frequency
            $frequency = $preferences->hours;
            if( $hourInterval > $frequency)
            {
                // try and send the email if they have any messages
                $messages = Message::where('receiverNo', $user->accountNo)->where('acknowledged', 0)->get();
                try
                {
                    if ($messages->count() != 0) { // if Has messages
                        $result = $user->sendDailyMessageNotification($messages);
                        if( $result == true)
                        {
                            $email->delete(); // delete from backlog
                            $preferences->timeLastSent = $now;
                            $preferences->save();
                        }
                    }
                }
                catch(TransportException $e)
                {
                    error_log($e);
                    // Do Nothing, email stays in backlog
                }
            }

    }

}
class Nominees
{
    public $nName; //nomineesName
    public $nId; //nomineesId
    public $nRoles; //nomineesRoles
}
