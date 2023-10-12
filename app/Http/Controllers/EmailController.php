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

    public function sendEmail()
    {
        $this->bookingCreated();
        $this->bookingEdit();
        $this->bookingCancelled();
        $this->passwordChanged();
        $this->passwordReset();
        $this->passwordResetLink();
        $this->applicationAccept();
        $this->applicationReject();
    }
    public function passwordResetLink()
    {
        $dynamicData = [
            'name' => 'Chris',
            'url' => 'www.google.com'
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Password Reset", "email/passwordResetLink", $dynamicData));
    }
    public function passwordChanged()
    {
        $dynamicData = [
            'name' => 'Chris'
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Password Changed", "email/passwordChanged", $dynamicData));
    }
    public function bookingCancelled()
    {
        $dynamicData = [
            'name' => 'Peter',
            'appNo' => 123123,
            'nName' => 'Messi',
            'role' => 'Lecturer',
            'uCode' => 'COMP3003',
            'uName' => "Foundation of Computer Science and Data Engineering",
            'period' => '00:00 23/04/2022 - 00:00 25/04/2022'
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Booking Edited", "email/bookingCancelled", $dynamicData));
    }
    public function bookingCreated()
    {
        $dynamicData = [
            'name' => 'Peter',
            'appNo' => 123123,
            'nName' => 'Messi',
            'role' => 'Lecturer',
            'uCode' => 'COMP3003',
            'uName' => "Foundation of Computer Science and Data Engineering",
            'period' => '00:00 23/04/2022 - 00:00 25/04/2022'
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Booking Edited", "email/bookingCreated", $dynamicData));
    }
    public function passwordReset()
    {
        $dynamicData = [
            'name' => 'Peter',
            'password' => '123!@ASDL##',
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Password reset", "email/passwordReset", $dynamicData));
    }
    public function applicationAccept()
    {
        $dynamicData = [
            'name' => 'Benny',
            'appNo' => 123123,
            'nName' => 'Messi',
            'role' => 'Lecturer',
            'uCode' => 'COMP3003',
            'uName' => "Foundation of Computer Science and Data Engineering",
            'period' => '00:00 23/04/2022 - 00:00 25/04/2022'
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Application Accepted", "email/applicationAccepted", $dynamicData));
    }
    public function applicationReject()
    {
        $dynamicData = [
            'name' => 'Yun Mei',
            'appNo' => 123123,
            'nName' => 'Tan Lok',
            'role' => 'Unit Coordinator',
            'uCode' => 'COMP3003',
            'uName' => "Foundation of Computer Science and Data Engineering",
            'period' => '00:00 23/04/2022 - 00:00 25/04/2022',
            'reason' => 'No more leaves.'
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Application Rejected", "email/applicationRejected", $dynamicData));
    }
    public function bookingEdit()
    {
        $nominees = new Nominees();
        $nominees->nName ="Tony Cranston";
        $nominees->nId = 222222;
        $nominees->nRoles ="COMP2001 - Unit Coordinator \n COMP2001 - Lecturer \n ISEC2001 - Unit Coordinator";

        $nominees2 = new Nominees();
        $nominees2->nName ="Tony asdasd";
        $nominees2->nId = 123123123;
        $nominees2->nRoles ="COMP2001 - Unit Coordinator \n COMP2001 - Lecturer \n ISEC2001 - Unit Coordinator";

        $nomineesArray = array($nominees, $nominees2);

        $dynamicData = [
            'sName' => 'Joe',
            'editorName' => 'Ronaldo',
            'editorId' => 'a123455',
            'period' => '00:00 12/04/2024 - 00:00 22/04/2024',
            'nomineesArray' => $nomineesArray,
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Booking Edited", "email/bookingEdited", $dynamicData));
    }

    public function nominationReminder($dynamicData, $accountNo): void {
        Mail::to("wonhee.qin@student.curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
        //Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
        Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
        //Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));

        // Mail::to("{$accountNo}@curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
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
