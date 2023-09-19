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
            'url' => URL::to('/home'),
        ];
        // create and return mailable object
        $mailable = new MJML("Unacknowledged Messages", $this->getMailName(), $dynamicData);
        return $mailable->to($notifiable->getEmailForPasswordReset());
    }

    protected function getAppRevMessages()
    {
        $messages = $this->messages;
        $appRevMessages = [];
        $stringMessages = "";

        foreach ($messages as $message) {
            if ($message->subject == "Application Awaiting Review" || $message->subject == "Application Cancelled") {
                $this->numAppRev++;
                $stringMessages = $stringMessages . $message->content . "<br></br>";
            }
        }
        return $stringMessages;
    }

    protected function getNumAppRev()
    {
    }

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


    private function getAppMessages()
    {
        $messages = $this->messages;
        $appMessages = [];
        foreach ($messages as $message) {
            $this->numApp++;
            if (
                $message->subject == "Application Approved" || $message->subject == "Application Denied" ||
                $message->subject == "Nomination/s Rejected"
            ) {
                $content = json_decode($message->content);
                array_push($appMessages, $content);
            }
        }
        return $appMessages;
    }

    private function getNumOther()
    {
    }

    private function getOtherMessages()
    {
        $messages = $this->messages;
        $otherMessages = [];
        foreach ($messages as $message) {
            if (
                $message->subject != "Application Awaiting Review" && $message->subject != "Application Cancelled" &&
                $message->subject != "Application Approved" && $message->subject != "Application Denied" &&
                $message->subject != "Nomination/s Rejected"
            ) {
                $this->numOther++;
                $content = $this->formatContent(json_decode($message->content));
                array_push($otherMessages, $content);
            }
        }

        return $otherMessages;
    }

    private function formatContent($content)
    {
        for ($i = 1; $i < sizeof($content); $i++) {
            $content[$i] = " " . $content[$i] . ", ";
        }
        $content[sizeof($content) - 1] = rtrim($content[sizeof($content) - 1], ', ');
        return $content;
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
