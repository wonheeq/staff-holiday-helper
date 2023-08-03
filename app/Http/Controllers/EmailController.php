<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use Illminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class EmailController extends Controller
{

    public function sendEmail()
    {
        $dynamicData = [
            'name' => 'Joe',
            // more dynamic data can be added
        ];

        //Mail::to("tvxqbenjamin0123@gmail.com")->send(new MJMLEmail("Password Reset", "passwordReset", $dynamicData));
;
        Mail::to("tvxqbenjamin0123@gmail.com")->send(new TestMail("Booking Edited", "email/bookingEdited", $dynamicData));
         Mail::to("wonhee.qin@student.curtin.edu.au")->send(new TestMail("Booking Edited", "email/bookingEdited", $dynamicData));
        //  Mail::to("aden.moore@student.curtin.edu.au")->send(new MJMLEmail("Booking Edited", "bookingEdited", $dynamicData));
        //  Mail::to("ellis.jansonferrall@student.curtin.edu.au")->send(new MJMLEmail("Booking Edited", "bookingEdited", $dynamicData));
        
    }
}