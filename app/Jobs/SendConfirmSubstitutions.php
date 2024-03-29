<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Account;
use Illuminate\Support\Facades\Mail;
use App\Mail\MJML;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\UnsentEmail;

class SendConfirmSubstitutions implements ShouldQueue
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
            // Extract indexes into new array for formatting
            $roles = [];
            for( $i = 2; $i < sizeof($data[1]) - 1; $i++)
            {
                array_push($roles, $data[1][$i]);
            }

            $dynamicData = [
                'name' => $name,
                'roles' => $roles,
                'duration' => $data[1][sizeof($data[1]) - 1], // last index
            ];

            Mail::to($reciever->getEmail())->send(new MJML("Confirmed Substitutions", "email/substitutionsConfirmed", $dynamicData));

            if ($this->isUnsent)
            {
                UnsentEmail::where('accountNo', $reciever->accountNo)
                    ->where('subject', 'Confirmed Substitutions')
                    ->where('id', $this->unsentId)->delete();
            }
        }
        catch(TransportException $e)
        {
            $encoded = json_encode($data);

            if ($this->isUnsent == false)
            {
                // if error, encode data and create row
                UnsentEmail::create([
                    'accountNo' => $data[0],
                    'subject' => 'Confirmed Substitutions',
                    'data' => $encoded,
                ]);
            }

        }
    }
}
