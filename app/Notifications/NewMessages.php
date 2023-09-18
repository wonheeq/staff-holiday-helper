<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\MJML;
use Illuminate\Mail\Mailable;

class NewMessages extends Notification
{
    use Queueable;

    public $messages;
    public $isManager;

    /**
     * Create a new notification instance.
     */
    public function __construct($messages, $isManager)
    {
        $this->messages = $messages;
        $this->isManager = $isManager;
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
            'numApp' => $this->getNumApp(),
            'appMessages' => $this->getAppMessages(),
            'numAppRev' => $this->getNumAppRev(),
            'appRevMessages' => $this->getAppRevMessages(),
            'numOther' => $this->getNumOther(),
            'otherMessages' => $this->getOtherMessages(),
            'url' => $this->getURL(),
        ];
        dd($dynamicData);
        // create and return mailable object
        $mailable = new MJML("Unacknowledged Messages", $this->getMailName(), $dynamicData);
        return $mailable->to($notifiable->getEmailForPasswordReset());
    }

    protected function getAppRevMessages()
    {
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

    private function getNumApp()
    {
        // Fix email/make second email so that manager can have their
        // leave requests listed as well.
        // consider how to count appliction related emails
        // consider how to display
    }



    private function getAppMessages()
    {
        $messages = $this->messages;
        $appMessages = [];
        foreach ($messages as $message) {
            if (
                $message->subject == "Application Approved" || $message->subject == "Application Denied" ||
                $message->subject == "Nomination/s Rejected"
            ) {
                array_push($appMessages, $message);
            }
        }
        // dd($appMessages);
    }

    private function getNumOther()
    {
    }

    private function getOtherMessages()
    {
    }

    private function getURL()
    {
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
