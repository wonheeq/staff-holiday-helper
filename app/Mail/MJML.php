<?php

namespace App\Mail;
use Asahasrabuddhe\LaravelMJML\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class MJML extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.SS
     *
     * @return void
     */

    public $name;
    public $subject;
    public $dynamicData;

    public function __construct($subject, $name, array $dynamicData)
    {
        $this->subject = $subject;
        $this->name = $name;
        $this->dynamicData = $dynamicData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->mjml($this->name, $this->dynamicData)
                    ->subject($this->subject);
    }
}
