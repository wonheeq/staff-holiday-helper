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
use App\Models\NewNominationsHash;



class SendNominationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $isUnsent;
    protected $unsentId;
    protected $hash;


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
        $applicationNo = $data[2];
        $accountNo = $reciever->accountNo;
        try
        {
            if(NewNominationsHash::where('accountNo', $accountNo)->first())
            {
                NewNominationsHash::where('accountNo', $accountNo)->delete();
            }
            $this->hash = md5($accountNo);
            NewNominationsHash::create([
                'accountNo' => $accountNo,
                'hash' => $this->hash,
            ]);

            $roles = [];
            for( $i = 1; $i < sizeof($data[1]) - 1; $i++)
            {
                array_push($roles, $data[1][$i]);
            }


            $dynamicData = [
                'name' => $name,
                'message' => $data[1][0],
                'roles' => $roles,
                'period' => $data[1][sizeof($data[1]) - 1],
                // 'acceptLink' => 'http://127.0.0.1:8000/acceptNewNominations/'. $this->hash . '/'. $applicationNo  ,
                'acceptLink' => 'https://leaveontime.cyber.curtin.io/acceptNewNominations/'. $this->hash . '/'. $applicationNo  ,
                // 'acceptSomeLink' => 'http://127.0.0.1:8000/reviewNominations/'. $applicationNo  ,
                'acceptSomeLink' => 'https://leaveontime.cyber.curtin.io/reviewNominations/'. $applicationNo  ,
                // 'rejectLink' => 'http://127.0.0.1:8000/rejectNewNominations/'. $this->hash . '/'. $applicationNo  ,
                'rejectLink' => 'https://leaveontime.cyber.curtin.io/rejectNewNominations/'. $this->hash . '/'. $applicationNo  ,
            ];

            Mail::to($reciever->getEmail)->send(new MJML("New Nominations", "email/nomination", $dynamicData));

            if ($this->isUnsent)
            {
                UnsentEmail::where('accountNo', $reciever->accountNo)
                    ->where('subject', 'New Nominations')
                    ->where('id', $this->unsentId)->delete();
            }
        }
        catch(TransportException $e)
        {

            NewNominationsHash::where([
                'accountNo' => $this->data[0],
                'hash' => $this->hash
            ]);

            $encoded = json_encode($data);
            if($this->isUnsent == false)
            {
                UnsentEmail::create([ // create one if not
                    'accountNo' => $data[0],
                    'subject' => 'New Nominations',
                    'data' => $encoded,
                ]);
            }
        }
    }
}
