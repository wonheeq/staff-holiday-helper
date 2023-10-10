<?php

namespace Tests\Unit;

use App\Http\Controllers\EmailController;
use App\Jobs\SendAppCanceledManager;
use App\Jobs\SendApplicationDecision;
use App\Jobs\SendAppWaitingRev;
use App\Jobs\SendConfirmSubstitutions;
use App\Jobs\SendNominationCancelled;
use App\Jobs\SendNominationDeclined;
use App\Jobs\SendNominationEmail;
use App\Jobs\SendNominationsCancelled;
use App\Jobs\SendNomineeAppEdited;
use App\Jobs\SendSubPeriodEditSubset;
use App\Jobs\SendSystemNotification;
use App\Mail\MJML;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Concerns\InteractsWithPages;
use App\Models\Account;
use App\Models\EmailPreference;
use App\Models\UnsentEmail;
use Illuminate\Support\Facades\Queue;

use function PHPUnit\Framework\assertEquals;

class EmailTest extends TestCase
{
    private Account $user;
    private $emails;

    protected function setup(): void
    {
        parent::setup();
        $this->emails = [];
        $this->user = Account::factory()->create();
        EmailPreference::factory()->create(['accountNo' => $this->user->accountNo]);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'New Nominations', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Application Awaiting Review', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Application Cancelled', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Nomination Cancelled', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Nomination/s Cancelled', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Substitution Period Edited (Subset)', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Edited Substitution Request', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Nomination/s Rejected', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Application Updated', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'System Notification', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        $email = UnsentEmail::create([
            'accountNo' => $this->user->accountNo, 'subject' => 'Confirmed Substitutions', 'data' => json_encode('test'),
        ]); array_push($this->emails, $email);

        // $this->$emails = $emails;
    }

    protected function teardown(): void
    {
        UnsentEmail::where('accountNo', $this->user->accountNo)->delete();
        $this->user->delete();
        parent::teardown();
    }

    // public function testApplicationApproval(): void
    // {
    //     $dynamicData = [
    //         'name' => 'Yun Mei',
    //         'appNo' => 123123,
    //         'nName' => 'Tan Lok',
    //         'role' => 'Unit Coordinator',
    //         'uCode' => 'COMP3003',
    //         'uName' => "Foundation of Computer Science and Data Engineering",
    //         'period' => '00:00 23/04/2022 - 00:00 25/04/2022',
    //         'reason' => 'No more leaves.'
    //     ];
    //     $mailable = new MJML("Application Rejected", "email/applicationRejected", $dynamicData);
    //     Mail::fake(); //a method that sends the email without actually sending it
    //     Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
    //     Mail::assertSent(MJML::class); //check if email has really been sent
    //     $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
    //     $this->assertEquals('Application Rejected', $mailable->subject); //check if subject of the email is correct

    //     //after render, check if the rendered content contains below strings/details:
    //     $this->assertStringContainsString('Yun Mei', $mailable->render());
    //     $this->assertStringContainsString('123123', $mailable->render());
    //     $this->assertStringContainsString('Tan Lok', $mailable->render());
    //     $this->assertStringContainsString('Unit Coordinator', $mailable->render());
    //     $this->assertStringContainsString('COMP3003', $mailable->render());
    //     $this->assertStringContainsString('Foundation of Computer Science and Data Engineering', $mailable->render());
    //     $this->assertStringContainsString('00:00 23/04/2022 - 00:00 25/04/2022', $mailable->render());
    //     $this->assertStringContainsString('No more leaves.', $mailable->render());
    // }
    // public function testApplicationRejection(): void
    // {
    //     $dynamicData = [
    //         'name' => 'Benny',
    //         'appNo' => 123123,
    //         'nName' => 'Messi',
    //         'role' => 'Lecturer',
    //         'uCode' => 'COMP3003',
    //         'uName' => "Foundation of Computer Science and Data Engineering",
    //         'period' => '00:00 23/04/2022 - 00:00 25/04/2022'
    //     ];
    //     $mailable = new MJML("Application Accepted", "email/applicationAccepted", $dynamicData);
    //     Mail::fake(); //a method that sends the email without actually sending it
    //     Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
    //     Mail::assertSent(MJML::class); //check if email has really been sent
    //     $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
    //     $this->assertEquals('Application Accepted', $mailable->subject); //check if subject of the email is correct

    //     //after render, check if the rendered content contains below strings/details:
    //     $this->assertStringContainsString('Benny', $mailable->render());
    //     $this->assertStringContainsString('123123', $mailable->render());
    //     $this->assertStringContainsString('Messi', $mailable->render());
    //     $this->assertStringContainsString('Lecturer', $mailable->render());
    //     $this->assertStringContainsString('COMP3003', $mailable->render());
    //     $this->assertStringContainsString('Foundation of Computer Science and Data Engineering', $mailable->render());
    //     $this->assertStringContainsString('00:00 23/04/2022 - 00:00 25/04/2022', $mailable->render());
    // }

    // public function testPasswordReset(): void
    // {
    //     $dynamicData = [
    //         'name' => 'Peter',
    //         'password' => '123!@ASDL##',
    //     ];
    //     $mailable = new MJML("Password Reset", "email/passwordReset", $dynamicData);
    //     Mail::fake(); //a method that sends the email without actually sending it
    //     Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
    //     Mail::assertSent(MJML::class); //check if email has really been sent
    //     $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
    //     $this->assertEquals('Password Reset', $mailable->subject); //check if subject of the email is correct

    //     //after render, check if the rendered content contains below strings/details:
    //     $this->assertStringContainsString('Peter', $mailable->render());
    //     $this->assertStringContainsString('123!@ASDL##', $mailable->render());
    // }
    // public function testPasswordChanged()
    // {
    //     $dynamicData = [
    //         'name' => 'Chris'
    //     ];
    //     $mailable = new MJML("Password Changed", "email/passwordChanged", $dynamicData);
    //     Mail::fake(); //a method that sends the email without actually sending it
    //     Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
    //     Mail::assertSent(MJML::class); //check if email has really been sent
    //     $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
    //     $this->assertEquals('Password Changed', $mailable->subject); //check if subject of the email is correct
    //     //after render, check if the rendered content contains below strings/details:
    //     $this->assertStringContainsString('Chris', $mailable->render());
    //     $this->assertStringContainsString('You are receiving email because the password of your LeaveOnTime account has been succesfully changed.', $mailable->render());
    // }
    // public function testBookingCancelled(): void
    // {
    //     $dynamicData = [
    //         'name' => 'Peter',
    //         'appNo' => 123123,
    //         'nName' => 'Messi',
    //         'role' => 'Lecturer',
    //         'uCode' => 'COMP3003',
    //         'uName' => "Foundation of Computer Science and Data Engineering",
    //         'period' => '00:00 23/04/2022 - 00:00 25/04/2022'
    //     ];
    //     $mailable = new MJML("Booking Cancelled", "email/bookingCancelled", $dynamicData);
    //     Mail::fake(); //a method that sends the email without actually sending it
    //     Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
    //     Mail::assertSent(MJML::class); //check if email has really been sent
    //     $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
    //     $this->assertEquals('Booking Cancelled', $mailable->subject); //check if subject of the email is correct

    //     //after render, check if the rendered content contains below strings/details:
    //     $this->assertStringContainsString('Peter', $mailable->render());
    //     $this->assertStringContainsString('123123', $mailable->render());
    //     $this->assertStringContainsString('Messi', $mailable->render());
    //     $this->assertStringContainsString('Lecturer', $mailable->render());
    //     $this->assertStringContainsString('COMP3003', $mailable->render());
    //     $this->assertStringContainsString('Foundation of Computer Science and Data Engineering', $mailable->render());
    //     $this->assertStringContainsString('00:00 23/04/2022 - 00:00 25/04/2022', $mailable->render());
    // }
    // public function testBookingCreated(): void
    // {
    //     $dynamicData = [
    //         'name' => 'Peter',
    //         'appNo' => 123123,
    //         'nName' => 'Messi',
    //         'role' => 'Lecturer',
    //         'uCode' => 'COMP3003',
    //         'uName' => "Foundation of Computer Science and Data Engineering",
    //         'period' => '00:00 23/04/2022 - 00:00 25/04/2022'
    //     ];

    //     $mailable = new MJML("Booking Created", "email/bookingCreated", $dynamicData);
    //     Mail::fake(); //a method that sends the email without actually sending it
    //     Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
    //     Mail::assertSent(MJML::class); //check if email has really been sent
    //     $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
    //     $this->assertEquals('Booking Created', $mailable->subject); //check if subject of the email is correct

    //     //after render, check if the rendered content contains below strings/details:
    //     $this->assertStringContainsString('Peter', $mailable->render());
    //     $this->assertStringContainsString('123123', $mailable->render());
    //     $this->assertStringContainsString('Messi', $mailable->render());
    //     $this->assertStringContainsString('Lecturer', $mailable->render());
    //     $this->assertStringContainsString('COMP3003', $mailable->render());
    //     $this->assertStringContainsString('Foundation of Computer Science and Data Engineering', $mailable->render());
    //     $this->assertStringContainsString('00:00 23/04/2022 - 00:00 25/04/2022', $mailable->render());
    // }
    // public function testBookingEdited(): void
    // {
    //     $emailController = new EmailController();
    //     $nominees = new Nominees();
    //     $nominees->nName = "Tony Cranston";
    //     $nominees->nId = 222222;
    //     $nominees->nRoles = "COMP2001 - Unit Coordinator \n COMP2001 - Lecturer \n ISEC2001 - Unit Coordinator";

    //     $nominees2 = new Nominees();
    //     $nominees2->nName = "Tony asdasd";
    //     $nominees2->nId = 123123123;
    //     $nominees2->nRoles = "COMP2001 - Unit Coordinator \n COMP2001 - Lecturer \n ISEC2001 - Unit Coordinator";

    //     $nomineesArray = array($nominees, $nominees2);

    //     $dynamicData = [
    //         'sName' => 'Joe', //supervisor
    //         'editorName' => 'Ronaldo',
    //         'editorId' => 'a123455',
    //         'period' => '00:00 12/04/2024 - 00:00 22/04/2024',
    //         'nomineesArray' => $nomineesArray,
    //     ];
    //     $emailController->sendEmail();
    //     $mailable = new MJML("Booking Edited", "email/bookingEdited", $dynamicData);
    //     Mail::fake(); //a method that sends the email without actually sending it
    //     Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
    //     Mail::assertSent(MJML::class); //check if email has really been sent
    //     $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
    //     $this->assertEquals('Booking Edited', $mailable->subject); //check if subject of the email is correct
    //     //after render, check if the rendered content contains below strings/details:
    //     $this->assertStringContainsString('Joe', $mailable->render());
    //     $this->assertStringContainsString('Ronaldo', $mailable->render());
    //     $this->assertStringContainsString('a123455', $mailable->render());
    //     $this->assertStringContainsString('00:00 12/04/2024 - 00:00 22/04/2024', $mailable->render());
    //     $this->assertStringContainsString('COMP2001 - Unit Coordinator', $mailable->render());
    // }


    public function testStaffDailymessage(): void
    {
        Mail::fake();
        $appMessages = ['AppMessageOne', 'AppMessageTwo', 'AppMessageThree'];
        $otherMessages = ['OtherMessageOne', 'OtherMessageTwo', 'OtherMessageThree'];
        $dynamicData = [
            'name' => 'Test User',
            'num' => 7,
            'appMessages' => $appMessages,
            'numApp' => 6,
            'appRevMessages' => null,
            'numAppRev' => 0,
            'otherMessages' => $otherMessages,
            'numOther' => 4,
        ];

        $mailable = new MJML("Unacknowledged Messages", 'email/staffDailyMessage', $dynamicData);
        Mail::to('000000a@test.com')->send($mailable);
        Mail::assertSent(MJML::class);
        $this->assertEquals('000000a@test.com', $mailable->to[0]['address']); // check recipient
        $this->assertEquals('Unacknowledged Messages', $mailable->subject); // check subject
        // check all messages make it into the email
        $this->assertStringContainsString('7', $mailable->render());
        $this->assertStringContainsString('6', $mailable->render());
        $this->assertStringContainsString('4', $mailable->render());

        $this->assertStringContainsString('AppMessageOne', $mailable->render());
        $this->assertStringContainsString('AppMessageTwo', $mailable->render());
        $this->assertStringContainsString('AppMessageThree', $mailable->render());

        $this->assertStringContainsString('OtherMessageOne', $mailable->render());
        $this->assertStringContainsString('OtherMessageTwo', $mailable->render());
        $this->assertStringContainsString('OtherMessageThree', $mailable->render());

    }


    public function testManagerDailymessage(): void
    {
        Mail::fake();
        $appMessages = ['AppMessageOne', 'AppMessageTwo', 'AppMessageThree'];
        $appRevMessages = ['AppRevMessageOne', 'AppRevMessageTwo', 'AppRevMessageThree'];
        $otherMessages = ['OtherMessageOne', 'OtherMessageTwo', 'OtherMessageThree'];
        $dynamicData = [
            'name' => 'Test User',
            'num' => 7,
            'appMessages' => $appMessages,
            'numApp' => 6,
            'appRevMessages' => $appRevMessages,
            'numAppRev' => 5,
            'otherMessages' => $otherMessages,
            'numOther' => 4,
        ];

        $mailable = new MJML("Unacknowledged Messages", 'email/managerDailyMessage', $dynamicData);
        Mail::to('000000a@test.com')->send($mailable);
        Mail::assertSent(MJML::class);
        $this->assertEquals('000000a@test.com', $mailable->to[0]['address']); // check recipient
        $this->assertEquals('Unacknowledged Messages', $mailable->subject); // check subject
        // check all messages make it into the email
        $this->assertStringContainsString('7', $mailable->render());
        $this->assertStringContainsString('6', $mailable->render());
        $this->assertStringContainsString('5', $mailable->render());
        $this->assertStringContainsString('4', $mailable->render());

        $this->assertStringContainsString('AppMessageOne', $mailable->render());
        $this->assertStringContainsString('AppMessageTwo', $mailable->render());
        $this->assertStringContainsString('AppMessageThree', $mailable->render());

        $this->assertStringContainsString('AppRevMessageOne', $mailable->render());
        $this->assertStringContainsString('AppRevMessageTwo', $mailable->render());
        $this->assertStringContainsString('AppRevMessageThree', $mailable->render());

        $this->assertStringContainsString('OtherMessageOne', $mailable->render());
        $this->assertStringContainsString('OtherMessageTwo', $mailable->render());
        $this->assertStringContainsString('OtherMessageThree', $mailable->render());

    }


    public function test_appAwaitingReview_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'applicantId' => 'message2',
            'applicantName' => 'Pan',
            'application' => ['role1', 'role2'],
            'period' => 'test'
        ];
        $mailable = new MJML("Application Awaiting Review", "email/applicationAwaitingReview", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Application Awaiting Review', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('message2', $mailable->render());
        $this->assertStringContainsString('Pan', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
        $this->assertStringContainsString('role1', $mailable->render());
        $this->assertStringContainsString('role2', $mailable->render());
    }


    public function test_ApplicationCancelled_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'message' => 'message',
            'applicantId' => 'message2',
            'applicantName' => 'Pan',
            'period' => 'test'
        ];
        $mailable = new MJML("Staff Cancelled Application", "email/applicationCancelled", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Staff Cancelled Application', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('message', $mailable->render());
        $this->assertStringContainsString('message2', $mailable->render());
        $this->assertStringContainsString('Pan', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
    }

    public function test_applicationPeriodEditedSubset_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'messageOne' => 'message1',
            'messageTwo' => 'message2',
            'roles' => ['role1', 'role2'],
            'duration' => 'test'
        ];
        $mailable = new MJML("Substitution Period Edited (Subset)", "email/applicationPeriodEditedSubset", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Substitution Period Edited (Subset)', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('message1', $mailable->render());
        $this->assertStringContainsString('message2', $mailable->render());
        $this->assertStringContainsString('role1', $mailable->render());
        $this->assertStringContainsString('role2', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
    }

    public function test_applicationUpdated_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'messageOne' => 'message1',
            'messageTwo' => 'message2',
            'duration' => 'test'
        ];
        $mailable = new MJML("Application Updated", "email/applicationUpdated", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Application Updated', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('message1', $mailable->render());
        $this->assertStringContainsString('message2', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
    }

    public function test_nominationsCancelled_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'messageOne' => 'message1',
            'roles' => ['role1', 'role2'],
            'messageTwo' => 'message2',
            'period' => 'test'
        ];
        $mailable = new MJML("Nomination/s Cancelled", "email/nomination_sCancelled", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Nomination/s Cancelled', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('message1', $mailable->render());
        $this->assertStringContainsString('role1', $mailable->render());
        $this->assertStringContainsString('role2', $mailable->render());
        $this->assertStringContainsString('message2', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
    }

    public function test_nomination_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'message' => 'message',
            'roles' => ['role1', 'role2'],
            'period' => 'test'
        ];
        $mailable = new MJML("New Nominations", "email/nomination", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('New Nominations', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('message', $mailable->render());
        $this->assertStringContainsString('role1', $mailable->render());
        $this->assertStringContainsString('role2', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
    }

    public function test_nominationCancelled_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'senderName' => 'Pan',
            'senderNo' => 'number',
            'message' => 'message',
            'period' => 'test'
        ];
        $mailable = new MJML("Nomination Cancelled", "email/nominationCancelled", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Nomination Cancelled', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('Pan', $mailable->render());
        $this->assertStringContainsString('number', $mailable->render());
        $this->assertStringContainsString('message', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
    }

    public function test_nominationDeclined_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'messageOne' => 'message1',
            'roles' => ['role1', 'role2'],
            'messageTwo' => 'message2',
            'messageThree' => 'message3',
            'duration' => 'test'
        ];
        $mailable = new MJML("Nomination/s Rejected", "email/nominationDeclined", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Nomination/s Rejected', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
        $this->assertStringContainsString('message1', $mailable->render());
        $this->assertStringContainsString('message2', $mailable->render());
        $this->assertStringContainsString('message3', $mailable->render());
        $this->assertStringContainsString('role2', $mailable->render());
        $this->assertStringContainsString('role1', $mailable->render());
    }

    public function test_SubRequestEdited_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'messageOne' => 'message',
            'roles' => ['role1', 'role2'],
            'duration' => 'test'
        ];
        $mailable = new MJML("Edited Substitution Request", "email/substitutionRequestEdited", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Edited Substitution Request', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
        $this->assertStringContainsString('message', $mailable->render());
        $this->assertStringContainsString('role2', $mailable->render());
        $this->assertStringContainsString('role1', $mailable->render());
    }

    public function test_subsConfirmed_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'roles' => ['role1', 'role2'],
            'duration' => 'test'
        ];
        $mailable = new MJML("Confirmed Submissions", "email/substitutionsConfirmed", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('Confirmed Submissions', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
        $this->assertStringContainsString('role2', $mailable->render());
        $this->assertStringContainsString('role1', $mailable->render());
    }

    public function test_SystemNotification_email(): void
    {
        Mail::fake();
        $dynamicData = [
            'name' => 'Peter',
            'content' => 'test'
        ];
        $mailable = new MJML("System Notification", "email/systemNotification", $dynamicData);
        Mail::to('testEmail@gmail.com')->send($mailable);
        Mail::assertSent(MJML::class);
        assertEquals('System Notification', $mailable->subject);

        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('test', $mailable->render());
    }



    public function testPasswordResetLink(): void
    {
        $dynamicData = [
            'name' => 'Peter',
            'url' => 'www.google.com'
        ];
        $mailable = new MJML("Password Reset", "email/passwordResetLink", $dynamicData);
        Mail::fake(); //a method that sends the email without actually sending it
        Mail::to("tvxqbenjamin0123@gmail.com")->send($mailable);
        Mail::assertSent(MJML::class); //check if email has really been sent
        $this->assertEquals('tvxqbenjamin0123@gmail.com', $mailable->to[0]['address']); //check if receiver's email is correct
        $this->assertEquals('Password Reset', $mailable->subject); //check if subject of the email is correct

        //after render, check if the rendered content contains below strings/details:
        $this->assertStringContainsString('Peter', $mailable->render());
        $this->assertStringContainsString('www.google.com', $mailable->render());
    }


    // public function test_handle_newNominations(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[0]);
    //     Queue::assertPushed(SendNominationEmail::class);
    // }

    // public function test_handle_ApplicationAwaitingReview(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[1]);
    //     Queue::assertPushed(SendAppWaitingRev::class);
    // }

    // public function test_handle_ApplicationCancelled(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[2]);
    //     Queue::assertPushed(SendAppCanceledManager::class);
    // }

    // public function test_handle_NominationCancelled(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[3]);
    //     Queue::assertPushed(SendNominationCancelled::class);
    // }

    // public function test_handle_NominationsCancelled(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[4]);
    //     Queue::assertPushed(SendNominationsCancelled::class);
    // }

    // public function test_handle_EditedSubset(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[5]);
    //     Queue::assertPushed(SendSubPeriodEditSubset::class);
    // }

    // public function test_handle_Edited(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[6]);
    //     Queue::assertPushed(SendNomineeAppEdited::class);
    // }

    // public function test_handle_NominationsRejected(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[7]);
    //     Queue::assertPushed(SendNominationDeclined::class);
    // }

    // public function test_handle_applicationUpdated(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[8]);
    //     Queue::assertPushed(SendApplicationDecision::class);
    // }

    // public function test_handle_systemNotification(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[9]);
    //     Queue::assertPushed(SendSystemNotification::class);
    // }

    // public function test_handle_confirmedSubs(): void
    // {
    //     Queue::fake();
    //     app(EmailController::class)->sortMail($this->emails[10]);
    //     Queue::assertPushed(SendConfirmSubstitutions::class);
    // }
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
