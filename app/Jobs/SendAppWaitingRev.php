<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MJML;
use Error;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\UnsentEmail;
use Throwable;
use ErrorException;

class SendAppWaitingRev implements ShouldQueue
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
        $creator = Account::where('accountNo', $data[1])->first();
        $name = $reciever->getName();
        try
        {
            // Extract indexes into new array for formatting
            $application = [];
            for( $i = 0; $i < sizeof($data[2]) - 1; $i++)
            {
                array_push($application, $data[2][$i]);
            }

            $dynamicData = [
                'name' => $name,
                'applicantId' => $creator->accountNo,
                'applicantName' => $creator->getName(),
                'application' => $application,
                'period' => $data[2][sizeof($data[2]) - 1], // last index
            ];


            // Mail::to($reciever->getEmail)->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));

            // Mail::to("wonhee.qin@student.curtin.edu.au")->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));
            // Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));
            // Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));
            Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));

            if ($this->isUnsent)
            {
                UnsentEmail::where('accountNo', $reciever->accountNo)
                    ->where('subject', 'Application Awaiting Review')
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
                    'subject' => 'Application Awaiting Review',
                    'data' => $encoded,
                ]);
            }
        }

    }
}
