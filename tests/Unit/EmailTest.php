<?php

namespace Tests\Unit;

use App\Mail\MJML;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Concerns\InteractsWithPages;



class EmailTest extends TestCase
{

    public function testApplicationApproval(): void
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
        $mailable = new MJML("Application Accepted", "email/applicationAccepted", $dynamicData);
        Mail::fake(); //a method that sends the email without actually sending it
        Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
        Mail::assertSent(MJML::class); //check if email has really been sent
        $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
        $this->assertEquals('Application Accepted', $mailable->subject); //check if subject of the email is correct
        
        //after render, check if the rendered content contains below strings/details:
        $this->assertStringContainsString('Benny', $mailable->render());
        $this->assertStringContainsString('123123', $mailable->render());
        $this->assertStringContainsString('Messi', $mailable->render());
        $this->assertStringContainsString('Lecturer', $mailable->render());
        $this->assertStringContainsString('COMP3003', $mailable->render());
        $this->assertStringContainsString('Foundation of Computer Science and Data Engineering', $mailable->render());
        $this->assertStringContainsString('00:00 23/04/2022 - 00:00 25/04/2022', $mailable->render());
    }
    public function testEmailContents(): void
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
            'sName' => 'Joe', //supervisor
            'editorName' => 'Ronaldo',
            'editorId' => 'a123455',
            'period' => '00:00 12/04/2024 - 00:00 22/04/2024',
            'nomineesArray' => $nomineesArray,
        ];

        $mailable = new MJML("Booking Edited", "email/bookingEdited", $dynamicData);
        Mail::fake(); //a method that sends the email without actually sending it
        Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
        Mail::assertSent(MJML::class); //check if email has really been sent
        $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
        $this->assertEquals('Booking Edited', $mailable->subject); //check if subject of the email is correct
        
        //after render, check if the rendered content contains below strings/details:
        $this->assertStringContainsString('Joe', $mailable->render());
        $this->assertStringContainsString('Ronaldo', $mailable->render());
        $this->assertStringContainsString('a123455', $mailable->render());
        $this->assertStringContainsString('00:00 12/04/2024 - 00:00 22/04/2024', $mailable->render());
        $this->assertStringContainsString('COMP2001 - Unit Coordinator', $mailable->render());
    }
}
/*
class to store details for nominees to be use in list
*/
class Nominees
{
    public $nName; //nomineesName
    public $nId; //nomineesId
    public $nRoles; //nomineesRoles
}
