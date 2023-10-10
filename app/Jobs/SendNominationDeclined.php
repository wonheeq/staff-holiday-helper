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

class SendNominationDeclined implements ShouldQueue
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
            // Extract indexes into new array for formatting
            $roles = [];
            for( $i = 1; $i < sizeof($data[1]) - 3; $i++)
            {
                array_push($roles, $data[1][$i]);
            }

            $dynamicData = [
                'name' => $name,
                'messageOne' => $data[1][0],
                'roles' => $roles,
                'messageTwo' => $data[1][sizeof($data[1]) - 2], // 2nd last
                'messageThree' => $data[1][sizeof($data[1]) - 1], // last
                'duration' => $data[1][sizeof($data[1]) - 3], // 3rd last
            ];

            // Mail::to($reciever->getEmail)->send(new MJML("Nomination/s Rejected", "email/nominationDeclined", $dynamicData));

            // Mail::to("wonhee.qin@student.curtin.edu.au")->send(new MJML("Nomination/s Rejected", "email/nominationDeclined", $dynamicData));
            Mail::to("b.lee20@student.curtin.edu.au")->send(new MJML("Nomination/s Rejected", "email/nominationDeclined", $dynamicData));
            // Mail::to("aden.moore@student.curtin.edu.au")->send(new MJML("Nomination/s Rejected", "email/nominationDeclined", $dynamicData));
            //Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJML("Nomination/s Rejected", "email/nominationDeclined", $dynamicData));
        }
        catch(TransportException $e)
        {
            // if error, encode data and create row
            $encoded = json_encode($data);
            UnsentEmail::create([
                'accountNo' => $data[0],
                'subject' => 'Nomination/s Rejected',
                'data' => $encoded,
            ]);
        }
    }
}
