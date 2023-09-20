<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\MJML;
use Error;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

class NewMessages extends Notification
{
    use Queueable;

    public $messages;
    public $isManager;
    public $numApp;
    public $numAppRev;
    public $numOther;

    /**
     * Create a new notification instance.
     */
    public function __construct($messages, $isManager)
    {
        $this->messages = $messages;
        $this->isManager = $isManager;
        $this->numApp = 0;
        $this->numAppRev = 0;
        $this->numOther = 0;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): Mailable
    {
        // get the name and build the URL
        $dynamicData = [
            'name' => $notifiable->getName(),
            'num' => $this->messages->count(),
            'appMessages' => $this->getAppMessages(),
            'numApp' => $this->numApp,
            'appRevMessages' => $this->getAppRevMessages(),
            'numAppRev' => $this->numAppRev,
            'otherMessages' => $this->getOtherMessages(),
            'numOther' => $this->numOther,
        ];
        // create and return mailable object
        $mailable = new MJML("Unacknowledged Messages", $this->getMailName(), $dynamicData);

        // return $mailable->to($notifiable->getEmailForPasswordReset()); // The actual return for deployment

        // uncomment / comment so that it goes to you
        // return $mailable->to("wonhee.qin@student.curtin.edu.au");
        // return $mailable->to("b.lee20@student.curtin.edu.au");
        // return $mailable->to("aden.moore@student.curtin.edu.au");
        return $mailable->to("ellis.jansonferrall@student.curtin.edu.au");
    }


    // determine which mail to send based off if the user is a line manager
    protected function getMailName()
    {
        $name = null;
        if ($this->isManager) {
            $name = "email/managerDailyMessage";
        } else {
            $name = "email/staffDailyMessage";
        }
        return $name;
    }


    // get and format messages related to the user's own applications
    private function getAppMessages()
    {
        $messages = $this->messages;
        $appMessages = [];
        foreach ($messages as $message) {
            if (
                $message->subject == "Application Approved" || $message->subject == "Application Denied"
                || $message->subject == "Nomination/s Rejected"
            ) {
                $this->numApp++;
                $content = json_decode($message->content);
                $messageString = $message->subject . ": " . $content[0]; // subject and first line
                for ($i = 1; $i < sizeof($content); $i++) { // rest of lines with a new line
                    $messageString = $messageString . "\n " . $content[$i];
                }
                array_push($appMessages, $messageString);
            }
        }
        return $appMessages;
    }




    // Get and format the messages related to Applications needing review by a line manager
    private function getAppRevMessages()
    {
        $messages = $this->messages;
        $appRevMessages = [];
        foreach ($messages as $message) {
            if ($message->subject == "Application Cancelled") {
                $this->numAppRev++;
                $content = json_decode($message->content);
                $messageString = $message->subject . ": " . $content[0]; // get subject and first line
                for ($i = 1; $i < sizeof($content); $i++) { // get the rest of the lines with a newline
                    $messageString = $messageString . "\n " . $content[$i];
                }
                array_push($appRevMessages, $messageString);

            } else if ($message->subject == "Application Awaiting Review") {
                $this->numAppRev++;
                $content = json_decode($message->content);
                // build a message to reduce clutter
                $messageString = $message->subject . ": " . " Staff Member " . $message->senderNo .
                    " has an application ready for review.\n" . $content[sizeof($content) - 1];
                array_push($appRevMessages, $messageString);
            }
        }
        return $appRevMessages;
    }



    // get and format other messages for the user
    private function getOtherMessages()
    {
        $messages = $this->messages;
        $otherMessages = [];
        foreach ($messages as $message) {
            if ( // I know this is ugly but its still 5 conditions to get all the "other" types
                $message->subject != "Application Awaiting Review" && $message->subject != "Nomination/s Rejected" &&
                $message->subject != "Application Approved" && $message->subject != "Application Denied" &&
                $message->subject != "Application Cancelled"
            ) {
                $this->numOther++;
                $content = json_decode($message->content);
                $messageString = $message->subject . ": " . $content[0]; // subject and first line
                for ($i = 1; $i < sizeof($content); $i++) { // rest of lines with newline
                    $messageString = $messageString . "\n " . $content[$i];
                }
                array_push($otherMessages, $messageString);
            }
        }
        return $otherMessages;
    }



    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
