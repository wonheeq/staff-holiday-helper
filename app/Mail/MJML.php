<?php

namespace App\Mail;
use Asahasrabuddhe\LaravelMJML\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;


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

    public function failed(Throwable $exception): void {
        // dd("here'");
        Log::debug("asdfasdfadsf");
        // if error, encode data and create row
        // $encoded = json_encode($this->data);
        // UnsentEmail::create([
        //     'accountNo' => $this->data[0],
        //     'subject' => 'Application Awaiting Review',
        //     'data' => $encoded,
        // ]);
    }
}
