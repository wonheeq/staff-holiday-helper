<?php

namespace App\Jobs;

use ErrorException;
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

class SendAppCancelledManager implements ShouldQueue
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
        $staffMember = Account::where('accountNo', $data[2])->first();
        $name = $reciever->getName();
        $accountNo = $reciever->accountNo;
        try
        {
            $dynamicData = [
                'name' => $name,
                'message' => $data[1][0],
                'applicantId' => $data[2],
                'applicantName' => $staffMember->getName(),
                'period' => $data[1][1],
            ];

            Mail::to($reciever->getEmail())->send(new MJML("Staff Cancelled Application", "email/applicationCancelled", $dynamicData));

            if ($this->isUnsent)
            {
                UnsentEmail::where('accountNo', $accountNo)
                    ->where('subject', 'Staff Cancelled Application')
                    ->where('id', $this->unsentId)->delete();
            }
        }
        catch(TransportException $e)
        {
            $encoded = json_encode($data);
            if ($this->isUnsent == false)
            {
                UnsentEmail::create([ // create one if not
                    'accountNo' => $data[0],
                    'subject' => 'Application Cancelled',
                    'data' => $encoded,
                ]);
            }
        }
    }
}
