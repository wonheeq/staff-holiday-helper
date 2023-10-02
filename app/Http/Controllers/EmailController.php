<?php

namespace App\Http\Controllers;

use App\Mail\MJML;
use App\Models\UnsentEmail;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\Account;
use App\Models\Message;
use App\Models\EmailPreference;
use DateTime;

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
        Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
        Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
        Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));

        // Mail::to("{$accountNo}@curtin.edu.au")->send(new MJML("Nomination Reminder", "email/nominationReminder", $dynamicData));
    }


    // Goes through the backlog of unset emails and attempts to send them
    public function attemptBacklog(): void {
        $backlog = UnsentEmail::all();
        foreach($backlog as $email)
        {
            // switch case used here for expandability to other email types
            // that might be added later
            $subject = $email->subject;
            switch($subject){
                case "Unacknowledged Messages":
                    $this->attemptUnackMsg($email);
                break;
            }
        }
    }


    // Handles the attempted resending of an archive email
    private function attemptUnackMsg($email)
    {
        try
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
                if ($messages->count() != 0) { // if Has messages
                    $user->sendDailyMessageNotification($messages);
                }
                $email->delete(); // delete from backlog
            }
        }
        catch(TransportException $e)
        {
            // Do Nothing, email stays in backlog
        }
    }
}
class Nominees
{
    public $nName; //nomineesName
    public $nId; //nomineesId
    public $nRoles; //nomineesRoles
}
