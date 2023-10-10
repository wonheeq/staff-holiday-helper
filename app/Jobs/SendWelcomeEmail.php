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
use Hash;
use App\Models\WelcomeHash;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $isUnsent;
    protected $email;

    /**
     * Create a new job instance.
     */
    // public function __construct($data, $isUnsent)
    public function __construct($data, $isUnsent)

    {
        $this->data = $data;

        // dd($data);
        // $this->$email = $email;
        $this->isUnsent = $isUnsent;
        // $this->data = json_decode($email->data);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = $this->data;
        $accountNo = $this->data;
        // dd($accountNo);
        $account = Account::where('accountNo', $accountNo)->first();
        // dd($account);
        $name = $account->getName();
        // dd($name);
        try
        {
            $hash = md5($accountNo);
            WelcomeHash::create([
                'accountNo' => $accountNo,
                'hash' => $hash
            ]);
            $link = 'localhost:8000/set-password/'.$hash;
            // dd($link);

            $dynamicData = [
                'name' => $name,
                'accountNo' => $account->accountNo,
                'link' => 'https://leaveontime.cyber.curtin.io/set-password/'.$hash,
                // 'link' => 'test text',


            ];

            
            // $this->sendEmail();

            // Mail::to($reciever->getEmail)->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));

            // Mail::to("wonhee.qin@student.curtin.edu.au")->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));
            Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML ("Welcome to LeaveOnTime", "email/welcomeEmail", $dynamicData));

            if ($this->isUnsent)
            {
                // dd("here");
                
                UnsentEmail::where('accountNo', $accountNo)->where('subject', 'Welcome to LeaveOnTime')->delete();
            }
            
            // $this->sendEmail($dynamicData);
            // Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));
            // Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJML ("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData));
        }
        catch(TransportException $e)
        {
            $encoded = json_encode($data);

            if ($this->isUnsent == false)
            {
                // if error, encode data and create row
                UnsentEmail::create([
                    'accountNo' => $data,
                    'subject' => 'Welcome to LeaveOnTime',
                    'data' => $encoded,
                ]);
            }
            else if($this->isUnsent == true)
            {
                // dd('elseif');

                //
            }
            else{
                dd('else');
            }
        }
        
    }
}
