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
    public function __construct($data, $isUnsent)

    {
        $this->data = $data;
        $this->isUnsent = $isUnsent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = $this->data;
        $accountNo = $this->data;
        $account = Account::where('accountNo', $accountNo)->first();
        $name = $account->getName();
        try
        {
            $hash = md5($accountNo);
            WelcomeHash::create([
                'accountNo' => $accountNo,
                'hash' => $hash
            ]);

            $dynamicData = [
                'name' => $name,
                'accountNo' => $account->accountNo,
                'link' => 'https://leaveontime.cyber.curtin.io/set-password/'.$hash,
                // 'link' => 'http://127.0.0.1:8000/set-password/'.$hash,
            ];

            Mail::to($account->getEmail())->send(new MJML ("Welcome to LeaveOnTime", "email/welcomeEmail", $dynamicData));

            if ($this->isUnsent)
            {
                UnsentEmail::where('accountNo', $accountNo)->where('subject', 'Welcome to LeaveOnTime')->delete();
            }
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
        }
    }
}
