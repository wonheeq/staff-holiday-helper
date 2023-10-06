<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Account;
use Illuminate\Support\Facades\Mail;
use App\Mail\MJML;
use Error;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\UnsentEmail;

class SendSystemNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = $this->data;
        $reciever = Account::where('accountNo', $data[0])->first();
        $name = $reciever->getName();
        try
        {
            $dynamicData = [
                'name' => $name,
                'content' => $data[1],
            ];

            // Mail::to($reciever->getEmail)->send(new MJML("System Notification", "email/systemNotification", $dynamicData));
            Mail::to("jansonferrall@gmail.com")
                ->queue(new MJML("System Notification",
                            "email/systemNotification", $dynamicData));
        }
        catch(TransportException $e)
        {
            // if error, encode data and create row
            $encoded = json_encode($data);
            UnsentEmail::create([
                'accountNo' => $data[0],
                'subject' => 'System Notification',
                'data' => $encoded,
            ]);
        }
    }
}
