<?php

namespace App\Http\Controllers;

use App\Mail\MJML;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{

    public function sendEmail()
    {
        //$this->bookingEditTest();
        $this->applicationAccept();
    }
    public function applicationAccept()
    {
        $dynamicData = [
            'name' => 'Benny',
            'appNo' => 123123,
            'nName' => 'Messi',
            'role' => 'Lecturer',
            'uCode' => 'COMP3003',
            'uName' => "Foundation of Computer Science and Data Engineering",
            'period' => '00:00 23/04/2022 - 00:00 25/04/2022'
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Application Accepted", "email/applicationAccepted", $dynamicData));
    }
    public function bookingEditTest()
    {
        $nominees = new Nominees();
        $nominees->nName ="Tony Cranston";
        $nominees->nId = 222222;
        $nominees->nRoles ="COMP2001 - Unit Coordinator \n COMP2001 - Lecturer \n ISEC2001 - Unit Coordinator";

        $nominees2 = new Nominees();
        $nominees2->nName ="Tony asdasd";
        $nominees2->nId = 123123123;
        $nominees2->nRoles ="COMP2001 - Unit Coordinator \n COMP2001 - Lecturer \n ISEC2001 - Unit Coordinator";

        $nomineesArray = array($nominees, $nominees2);
        
        $dynamicData = [
            'sName' => 'Joe',
            'editorName' => 'Ronaldo',
            'editorId' => 'a123455',
            'period' => '00:00 12/04/2024 - 00:00 22/04/2024',
            'nomineesArray' => $nomineesArray,
        ];
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJML("Booking Edited", "email/bookingEdited", $dynamicData));
    }
}
class Nominees
{
    public $nName; //nomineesName
    public $nId; //nomineesId
    public $nRoles; //nomineesRoles
}
