<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\MJML;
use Illuminate\Mail\Mailable;

class StaffNewMessages extends Notification
{
    use Queueable;

    public $messages;
    public bool $isManager;

    /**
     * Create a new notification instance.
     */
    public function __construct($messages, bool $isManager)
    {
        $this->$messages = $messages;
        $this->$isManager = $isManager;
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
            'num' => $this->getNum(),
            'numApp' => $this->getNumApp(),
            'appMessage' => $this->getAppText(),
            'appMessages' => $this->getAppMessages(),
            'numOther' => $this->getNumOther(),
            'otherMessages' => $this->getOtherMessages(),
            'url' => $this->getURL(),
        ];
        // create and return mailable object
        $mailable = new MJML("Unacknowledged Messages", "email/dailyMessage", $dynamicData);
        return $mailable->to($notifiable->getEmailForPasswordReset());
    }

    private function getNum()
    {
    }

    private function getNumApp()
    {
    }

    private function getAppText()
    {
    }

    private function getAppMessages()
    {
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
