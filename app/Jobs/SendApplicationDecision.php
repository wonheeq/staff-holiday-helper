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

class SendApplicationDecision implements ShouldQueue
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
            $reason = '';
            if(str_contains($data[1][0], "rejected"))
            {
                $reason = $data[1][1];
            }

            $dynamicData = [
                'name' => $name,
                'messageOne' => $data[1][0],
                'messageTwo' => $reason,
                'duration' => $data[1][sizeof($data[1]) - 1], // last
            ];

            // Mail::to($reciever->getEmail)->send(new MJML("Application Updated","email/applicationUpdated", $dynamicData));

            // Mail::to("hannes.herrmann@curtin.edu.au")->send(new MJML("Application Updated","email/applicationUpdated", $dynamicData));
            // Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML("Application Updated","email/applicationUpdated", $dynamicData));
            // Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML("Application Updated","email/applicationUpdated", $dynamicData));
            Mail::to("hannes.herrmann@curtin.edu.au")->send(new MJML("Application Updated","email/applicationUpdated", $dynamicData));

            if ($this->isUnsent)
            {
                UnsentEmail::where('accountNo', $reciever->accountNo)
                    ->where('subject', 'Application Updated')
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
                    'subject' => 'Application Updated',
                    'data' => $encoded,
                ]);
            }
        }
    }
}
