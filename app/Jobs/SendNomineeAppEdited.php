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

class SendNomineeAppEdited implements ShouldQueue
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
        try
        {
            $roles = [];
            for( $i = 3; $i < sizeof($data[1]) - 1; $i++)
            {
                array_push($roles, $data[1][$i]);
            }


            $dynamicData = [
                'name' => $name,
                'messageOne' => $data[1][1],
                'roles' => $roles,
                'duration' => $data[1][sizeof($data[1]) - 1],
            ];

            // Mail::to($reciever->getEmail)->send(new MJML("Edited Substitution Request", "email/substitutionRequestEdited", $dynamicData));

            // Mail::to("wonhee.qin@student.curtin.edu.au")->send(new MJML("Edited Substitution Request", "email/substitutionRequestEdited", $dynamicData));
            Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML("Edited Substitution Request", "email/substitutionRequestEdited", $dynamicData));
            // Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML("Edited Substitution Request", "email/substitutionRequestEdited", $dynamicData));
            //Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJML("Edited Substitution Request", "email/substitutionRequestEdited", $dynamicData));
        }
        catch(TransportException $e)
        {
            $encoded = json_encode($data);
            UnsentEmail::create([ // create one if not
                'accountNo' => $data[0],
                'subject' => 'Edited Substitution Request',
                'data' => $encoded,
            ]);
        }


    }
}
