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

class SendNominationCancelled implements ShouldQueue
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
        $sender = Account::where('accountNo', $data[2])->first();

        try
        {
            $dynamicData = [
                'name' => $name,
                'senderName' => $sender->getName(),
                'senderNo' => $data[2],
                'message' => $data[1][0],
                'period' => $data[1][1], // last index
            ];

            // Mail::to($reciever->getEmail)->send(new MJML("Nomination Cancelled", "email/nominationCancelled", $dynamicData));
            Mail::to("jansonferrall@gmail.com")->queue(new MJML("Nomination Cancelled", "email/nominationCancelled", $dynamicData));
        }
        catch(TransportException $e)
        {
            // if error, encode data and create row
            $encoded = json_encode($data);
            UnsentEmail::create([
                'accountNo' => $data[0],
                'subject' => 'Nomination Cancelled',
                'data' => $encoded,
            ]);
        }
    }
}
