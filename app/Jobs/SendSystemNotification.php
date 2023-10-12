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
    protected $isUnsent;
    protected $unsentId;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $isUnsent, $unsentId)
    {
        $this->data = $data;
        $this->isUnsent = $isUnsent;
        $this->unsentId = $unsentId;
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

            // Real one
            // Mail::to($reciever->getEmail)->send(new MJML("System Notification", "email/systemNotification", $dynamicData));

            // Mail::to("wonhee.qin@student.curtin.edu.au")->send(new MJML("System Notification", "email/systemNotification", $dynamicData));
            Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML("System Notification", "email/systemNotification", $dynamicData));
            // Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML("System Notification", "email/systemNotification", $dynamicData));
            //Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJML("System Notification", "email/systemNotification", $dynamicData));

            if ($this->isUnsent)
            {
                UnsentEmail::where('accountNo', $reciever->accountNo)
                    ->where('subject', 'System Notification')
                    ->where('id', $this->unsentId)->delete();
            }
        }
        catch(TransportException $e)
        {
            // if error, encode data and create row
            $encoded = json_encode($data);
            if($this->isUnsent == false)
            {
                UnsentEmail::create([
                    'accountNo' => $data[0],
                    'subject' => 'System Notification',
                    'data' => $encoded,
                ]);
            }

        }
    }
}
