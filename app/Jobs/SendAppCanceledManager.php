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

class SendAppCanceledManager implements ShouldQueue
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
        $staffMember = Account::where('accountNo', $data[2])->first();
        $name = $reciever->getName();
        try
        {
            $dynamicData = [
                'name' => $name,
                'message' => $data[1][0],
                'applicantId' => $data[2],
                'applicantName' => $staffMember->getName(),
                'period' => $data[1][1],
            ];
            // Mail::to($reciever->getEmail)->queue(new MJML("Staff Cancelled Application", "email/applicationCancelled", $dynamicData));

            // Mail::to("wonhee.qin@student.curtin.edu.au")->queue(new MJML("Staff Cancelled Application", "email/applicationCancelled", $dynamicData));
            // Mail::to("b.lee20@student.curtin.edu.au")->queue(new MJML("Staff Cancelled Application", "email/applicationCancelled", $dynamicData));
            // Mail::to("aden.moore@student.curtin.edu.au")->queue(new MJML("Staff Cancelled Application", "email/applicationCancelled", $dynamicData));
            Mail::to("ellis.jansonferrall@student.curtin.edu.au")->queue(new MJML("Staff Cancelled Application", "email/applicationCancelled", $dynamicData));
        }
        catch(TransportException $e)
        {
            $encoded = json_encode($data);
            UnsentEmail::create([ // create one if not
                'accountNo' => $data[0],
                'subject' => 'Application Cancelled',
                'data' => $encoded,
            ]);
        }
    }
}