<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Console\Kernel;
use App\Models\Account;
use App\Models\ManagerNomination;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\AccountRole;
use App\Models\Message;
use App\Models\EmailPreference;
use App\Http\Controllers\ApplicationController;
use DateTime;
use Illuminate\Support\Facades\Log;


class Kernel_ProcessManagerNominationsTest extends TestCase
{
    private Account $staff, $tempManager, $manager, $admin;
    private array $applications;
    private array $accountRoles;
    protected function setup(): void {
        parent::setup();

        

        // Create admin
        $this->admin = Account::factory()->create([
            'accountType' => "sysadmin",
            'superiorNo' => null,
        ]);

        // Create manager with admin as superior
        $this->manager = Account::factory()->create([
            'accountType' => 'lmanager',
            'superiorNo' => $this->admin->accountNo,
        ]);

        // Create staff member with manager as superior which will be temporarily making a manager in our tests
        $this->tempManager = Account::factory()->create([
            'accountType' => 'staff',
            'superiorNo' => $this->manager->accountNo,
        ]);

        // Create staff member with manager as superior
        $this->staff = Account::factory()->create([
            'accountType' => 'staff',
            'superiorNo' => $this->manager->accountNo,
        ]);

        EmailPreference::factory()->create(['accountNo' => $this->admin->accountNo]);
        EmailPreference::factory()->create(['accountNo' => $this->manager->accountNo]);
        EmailPreference::factory()->create(['accountNo' => $this->tempManager->accountNo]);
        EmailPreference::factory()->create(['accountNo' => $this->staff->accountNo]);

        // Create AccountRoles for staff account
        $this->accountRoles = [];
        for ($i = 0; $i < 3; $i++) {
            array_push(
                $this->accountRoles,
                AccountRole::factory()->create([
                    'accountNo' => $this->staff->accountNo,
                ])
            );
        }

        $this->applications = [];
    }

    protected function teardown(): void {
        // Delete AccountRoles
        foreach ($this->accountRoles as $accountRole) {
            // Delete Nomination
            Nomination::where('accountRoleId', $accountRole->accountRoleId)
            ->delete();
            $accountRole->delete();
        }

        // Iterate through applications array
        foreach ($this->applications as $application) {
            // Delete ManagerNominations with the applicationNo
            ManagerNomination::where('applicationNo', $application->applicationNo)->delete();

            // Delete application
            $application->delete();
        }

        Message::where('receiverNo', $this->staff->accountNo)->delete();
        Message::where('senderNo', $this->staff->accountNo)->delete();
        Message::where('receiverNo', $this->tempManager->accountNo)->delete();
        Message::where('senderNo', $this->tempManager->accountNo)->delete();
        Message::where('receiverNo', $this->manager->accountNo)->delete();
        Message::where('senderNo', $this->manager->accountNo)->delete();

        Application::where('accountNo', $this->staff->accountNo)->delete();


        EmailPreference::where('accountNo', $this->admin->accountNo)->delete();
        EmailPreference::where('accountNo', $this->manager->accountNo)->delete();
        EmailPreference::where('accountNo', $this->tempManager->accountNo)->delete();
        EmailPreference::where('accountNo', $this->staff->accountNo)->delete();

        // Delete accounts
        $this->staff->delete();
        $this->tempManager->delete();
        $this->manager->delete();
        $this->admin->delete();

        parent::teardown();
    }


    public function test_processManagerNominations_successfully_promotes_users(): void {
        // Ongoing application:
        // -> at least one ManagerNomination
        // -> application status is accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "Y",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 1
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 1);
    }

    public function test_processManagerNominations_successfully_demotes_users(): void {
        // Ongoing application:
        // -> at least one ManagerNomination
        // -> application status is accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "Y",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 1
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 1);

        // Now we alter the application's period so that it has expired
        $application->update([
            'eDate' => new DateTime("2023-10-01 01:00:00")
        ]);
        // Now we call processManagerNominations to attempt to demote the tempManager since the period has now expired
        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        // After we call processManagerNominations on the now expired application,
        // the application's status should be set to 'E'
        $this->assertTrue(Application::where('applicationNo', $application->applicationNo)->first()->status == 'E');
    }

    public function test_processManagerNominations_successfully_ignores_non_approved_applications(): void {
        // Unproccessed application:
        // -> at least one ManagerNomination
        // -> application status is NOT accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "U",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should still equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);
    }

    public function test_temporary_manager_receives_application_requests(): void {
        // Ongoing application:
        // -> at least one ManagerNomination
        // -> application status is accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "Y",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 1
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 1);

        /* The tempManager account has successfully been promoted */
        /* Now we create a fully self nominated application from the staff account */

        $response = $this->actingAs($this->staff)->postJson("/api/createApplication", [
            'accountNo' => $this->staff->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        // The manager should not have a "Application Awaiting Review" message from the staff account
        $this->assertTrue(count(Message::where('receiverNo', $this->manager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==0);

        // The tempManager should be receiving the "Application Awaiting Review" message
        $this->assertTrue(count(Message::where('receiverNo', $this->tempManager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==1);
    }

    public function test_temporary_manager_can_approve_application_requests(): void {
        // Ongoing application:
        // -> at least one ManagerNomination
        // -> application status is accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "Y",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 1
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 1);

        /* The tempManager account has successfully been promoted */
        /* Now we create a fully self nominated application from the staff account */

        $response = $this->actingAs($this->staff)->postJson("/api/createApplication", [
            'accountNo' => $this->staff->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $staffApplication = Application::where('accountNo', $this->staff->accountNo)->first();

        // The manager should not have a "Application Awaiting Review" message from the staff account
        $this->assertTrue(count(Message::where('receiverNo', $this->manager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==0);

        // The tempManager should be receiving the "Application Awaiting Review" message
        $this->assertTrue(count(Message::where('receiverNo', $this->tempManager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==1);

        // The application status should be 'U' before we accept it
        $this->assertTrue(Application::where('applicationNo', $staffApplication->applicationNo)->first()->status == 'U');

        /* The temp manager should be able to approve the application */
        $response = $this->actingAs($this->tempManager)->postJson("/api/acceptApplication", [
            'accountNo' => $this->tempManager->accountNo,
            'applicationNo' => $staffApplication->applicationNo,
        ]);

        $response->assertStatus(200);
        // The application status should be 'Y' now
        $this->assertTrue(Application::where('applicationNo', $staffApplication->applicationNo)->first()->status == 'Y');
    }

    public function test_temporary_manager_can_reject_application_requests(): void {
        // Ongoing application:
        // -> at least one ManagerNomination
        // -> application status is accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "Y",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 1
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 1);

        /* The tempManager account has successfully been promoted */
        /* Now we create a fully self nominated application from the staff account */

        $response = $this->actingAs($this->staff)->postJson("/api/createApplication", [
            'accountNo' => $this->staff->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $staffApplication = Application::where('accountNo', $this->staff->accountNo)->first();

        // The manager should not have a "Application Awaiting Review" message from the staff account
        $this->assertTrue(count(Message::where('receiverNo', $this->manager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==0);

        // The tempManager should be receiving the "Application Awaiting Review" message
        $this->assertTrue(count(Message::where('receiverNo', $this->tempManager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==1);

        // The application status should be 'U' before we accept it
        $this->assertTrue(Application::where('applicationNo', $staffApplication->applicationNo)->first()->status == 'U');

        /* The temp manager should be able to approve the application */
        $response = $this->actingAs($this->tempManager)->postJson("/api/rejectApplication", [
            'accountNo' => $this->tempManager->accountNo,
            'applicationNo' => $staffApplication->applicationNo,
            'rejectReason' => 's'
        ]);

        $response->assertStatus(200);
        // The application status should be 'N' now
        $this->assertTrue(Application::where('applicationNo', $staffApplication->applicationNo)->first()->status == 'N');
    }

    public function test_processManagerNominations_removes_application_awaiting_review_message_from_demoted_temporary_managers(): void {
        // Ongoing application:
        // -> at least one ManagerNomination
        // -> application status is accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "Y",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 1
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 1);

        /* The tempManager account has successfully been promoted */
        /* Now we create a fully self nominated application from the staff account */

        $response = $this->actingAs($this->staff)->postJson("/api/createApplication", [
            'accountNo' => $this->staff->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        // The manager should not have a "Application Awaiting Review" message from the staff account
        $this->assertTrue(count(Message::where('receiverNo', $this->manager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==0);

        // The tempManager should be receiving the "Application Awaiting Review" message
        $this->assertTrue(count(Message::where('receiverNo', $this->tempManager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==1);

        /* Now we demote the tempManager */
        // alter the application's period so that it has expired
        $application->update([
            'eDate' => new DateTime("2023-10-01 01:00:00")
        ]);
        // Now we demote the tempManager since the period has now expired
        app(Kernel::class)->demoteStaffFromLineManager($application->applicationNo);

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        // After we call processManagerNominations on the now expired application,
        // the application's status should be set to 'E'
        $this->assertTrue(Application::where('applicationNo', $application->applicationNo)->first()->status == 'E');

        // The tempManager should no longer have the "Application Awaiting Review" message from the staff account
        $this->assertTrue(count(Message::where('receiverNo', $this->tempManager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==0);

        // The "Application Awaiting Review" message from the staff account should now be back with the original manager
        $this->assertTrue(count(Message::where('receiverNo', $this->manager->accountNo)->where('subject', 'Application Awaiting Review')->where('senderNo', $this->staff->accountNo)->get())==1);
    }

    public function test_temporary_manager_can_call_manager_restricted_api_requests(): void {
        // Ongoing application:
        // -> at least one ManagerNomination
        // -> application status is accepted
        // -> sDate >= currentDate <= eDate
        $application = Application::create([
            'accountNo' => $this->manager->accountNo,
            'status' => "Y",
            'sDate' => new DateTime("2023-10-01 00:00:00"),
            'eDate' => new DateTime("2028-01-01 00:00:00"),
        ]);
        ManagerNomination::create([
            'applicationNo' => $application->applicationNo,
            'subordinateNo' => $this->staff->accountNo,
            'nomineeNo' => $this->tempManager->accountNo,
            'status' => 'Y'
        ]);
        array_push($this->applications, $application);

        // Before we call processManagerNominations, the tempManager isTemporaryManager should equal 0
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 0);

        app(Kernel::class)->processManagerNominations();

        // After we call processManagerNominations, the tempManager isTemporaryManager should equal 1
        $this->assertTrue(Account::where('accountNo', $this->tempManager->accountNo)->first()->isTemporaryManager == 1);

         /* Now we create a fully self nominated application from the staff account */
         $response = $this->actingAs($this->staff)->postJson("/api/createApplication", [
            'accountNo' => $this->staff->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->staff->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $response = $this->actingAs($this->tempManager)->get("/api/getStaffMembers/{$this->tempManager->accountNo}");
        $response->assertStatus(200);
        // the staff members the temp manager is in charge of should be 1
        $this->assertTrue(count($response->getData()) == 1);

        $response = $this->actingAs($this->tempManager)->get("/api/getRolesForStaffs/{$this->staff->accountNo}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->tempManager)->get("/api/managerApplications/{$this->tempManager->accountNo}");
        $response->assertStatus(200);
        // this should return an array with 1 element

        $this->assertTrue(count($response->getData()) == 1);

        $response = $this->actingAs($this->tempManager)->get("/api/getSpecificStaffMember/{$this->staff->accountNo}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->tempManager)->get("/api/getUCM");
        $response->assertStatus(200);
    }
}
