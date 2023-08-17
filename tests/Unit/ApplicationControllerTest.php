<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\Role;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class ApplicationControllerTest extends TestCase
{
    private Account $user, $otherUser;
    private $accountRoles;
    private $applications;
    private $nominations;


    protected function setup(): void {
        parent::setup();

        $this->user = Account::factory()->create();

        $this->otherUser = Account::factory()->create();


        $roles = Role::pluck('roleId');
        $this->accountRoles = array();
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => $roles[0]
        ]));
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => $roles[1]
        ]));
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => $roles[2]
        ]));

        $this->applications = Application::factory(5)->create([
            'accountNo' => $this->user['accountNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
        ]);

        $firstApp = $this->applications[0];
        $this->nominations = array();
        
        // set nominations for first application
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo
        ]));

    }

    protected function teardown(): void {
        $arr = Application::where('accountNo', $this->user->accountNo)->get();
        foreach ($arr as $a) {
            Nomination::where('applicationNo', $a->applicationNo)->delete();
            Message::where('applicationNo', $a->applicationNo)->delete();
        }

        AccountRole::where('accountNo', $this->user['accountNo'])->delete();

        AccountRole::where('accountNo', $this->otherUser['accountNo'])->delete();
        Application::where('accountNo', $this->user['accountNo'])->delete();



        $this->user->delete();
        $this->otherUser->delete();

        $this->applications = null;
        $this->accountRoles = null;

        parent::teardown();
    }

    public function test_api_request_for_applications_successful(): void
    {
        // Check for valid response
        $response = $this->getJson("/api/applications/{$this->user['accountNo']}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_applications_invalid_user(): void
    {
        // Check for invalid response
        $response = $this->getJson('/api/applications/asfasfasfasf');
        $response->assertStatus(500);
    }

    public function test_api_request_for_applications_valid_length(): void
    {
        // Test if amount of applications matches
        $response = $this->getJson("/api/applications/{$this->user['accountNo']}");
        $array = $response->getData();
        $this->assertTrue(count($array) == count($this->applications));
    }

    public function test_api_request_for_applications_content_is_json(): void
    {
        // Check if response is json
        $response = $this->getJson("/api/applications/{$this->user['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_applications_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->get("/api/applications/{$this->user['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'applicationNo',
                'accountNo',
                'sDate',
                'eDate',
                'status',
                'processedBy',
                'rejectReason',
            ],
        ]);

        // Check if returned applications for correct user
        $array = json_decode($response->content());
        foreach ($array as $app) {
            $this->assertTrue($app->accountNo == $this->user['accountNo']);
        }
    }


    public function test_api_request_for_create_applications_successful(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_api_request_for_create_applications_unsuccessful_end_date_before_start_date(): void
    {
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2023-08-06 20:00:00',
            'eDate' => '2023-08-01 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_unsuccessful_start_date_after_end_date(): void
    {
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-01 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_unsuccessful_date_is_before_current_date(): void
    {
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2023-08-04 20:00:00',
            'eDate' => '2023-08-05 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_unsuccessful_nomination_missing_nomineeno(): void
    {
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-04 20:00:00',
            'eDate' => '2030-08-05 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => "",
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_unsuccessful_nomination_missing_accountroleid(): void
    {
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-04 20:00:00',
            'eDate' => '2030-08-05 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => "",
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_unsuccessful_nominations_are_null(): void
    {
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-04 20:00:00',
            'eDate' => '2030-08-05 20:00:00',
            'nominations' => null,
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_unsuccessful_nominations_are_empty(): void
    {
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-04 20:00:00',
            'eDate' => '2030-08-05 20:00:00',
            'nominations' => [],
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_successful_manager_notified_about_fully_self_nominated_app(): void {
        // Check for valid response
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-07 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        // get application from db
        $application = Application::where('sDate', '2030-08-07 20:00:00', 'and')
        ->where('eDate', '2030-08-08 20:00:00', 'and')
        ->where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($application != null);

        $message = Message::where('applicationNo', $application->applicationNo, "and")
        ->where('receiverNo', $this->user->superiorNo, 'and')
        ->where('senderNo', $this->user->accountNo)->first();
        $this->assertTrue($message->subject == 'Application Awaiting Review');
    }

    public function test_api_request_for_create_applications_successful_nominees_notified_about_nominations(): void {
        // Check for valid response
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-07 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        // get application from db
        $application = Application::where('sDate', '2030-08-07 20:00:00', 'and')
        ->where('eDate', '2030-08-08 20:00:00', 'and')
        ->where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($application != null);


        $message = Message::where('applicationNo', $application->applicationNo, "and")
        ->where('receiverNo', $this->otherUser->accountNo, 'and')
        ->where('senderNo', $this->user->accountNo)->first();
        $this->assertTrue($message->subject == 'Substitution Request');
    }





    public function test_api_request_for_cancelApplication_is_successful() : void {
        $app = $this->applications[0];
        $response = $this->getJson("/api/cancelApplication/{$this->user->accountNo}/{$app->applicationNo}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_cancelApplication_is_unsuccessful_user_does_not_exist(): void {
        $app = $this->applications[0];
        $response = $this->getJson("/api/cancelApplication/baduseracc/{$app->applicationNo}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_cancelApplication_is_unsuccessful_application_does_not_exist(): void {
        $response = $this->getJson("/api/cancelApplication/{$this->user->accountNo}/badapplicaitonno");
        $response->assertStatus(500);
    }

    public function test_api_request_for_cancelApplication_is_unsuccessful_application_does_not_belong_to_user(): void {
        $app = $this->applications[0];
        $response = $this->getJson("/api/cancelApplication/000000a/{$app->applicationNo}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_cancelApplication_sets_status_correctly(): void {
        $app = $this->applications[0];
        $response = $this->getJson("/api/cancelApplication/{$this->user->accountNo}/{$app->applicationNo}");
        $response->assertStatus(200);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp['status'] == 'C');
    }

    public function test_api_request_for_cancelApplication_deletes_nominations(): void {
        $app = $this->applications[0];
        $response = $this->getJson("/api/cancelApplication/{$this->user->accountNo}/{$app->applicationNo}");
        $response->assertStatus(200);

        $nominationsForApp = Nomination::where('applicationNo', $app->applicationNo)->get()->toArray();
        $this->assertFalse(count($nominationsForApp) > 0);
    }

    public function test_api_request_for_cancelApplication_is_successful_manager_is_notified_of_application_cancelleation() : void {
        // Check for valid response
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        // get application from db
        $application = Application::where('sDate', '2030-08-06 20:00:00', 'and')
        ->where('eDate', '2030-08-08 20:00:00', 'and')
        ->where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($application != null);
        
        $response = $this->getJson("/api/cancelApplication/{$this->user->accountNo}/{$application->applicationNo}");
        $response->assertStatus(200);

        $message = Message::where('applicationNo', $application->applicationNo, "and")
        ->where('senderNo', $this->user->accountNo)->first();
        $this->assertTrue($message->subject == 'Application Cancelled');
    }

    public function test_api_request_for_cancelApplication_is_successful_nominee_is_notified_of_application_cancelleation() : void {
        // Check for valid response
        $response = $this->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        // get application from db
        $application = Application::where('sDate', '2030-08-06 20:00:00', 'and')
        ->where('eDate', '2030-08-08 20:00:00', 'and')
        ->where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($application != null);
        
        $response = $this->getJson("/api/cancelApplication/{$this->user->accountNo}/{$application->applicationNo}");
        $response->assertStatus(200);

        $message = Message::where('applicationNo', $application->applicationNo, "and")
        ->where('receiverNo', $this->otherUser->accountNo, 'and')
        ->where('senderNo', $this->user->accountNo)->first();
        $this->assertTrue($message->subject == 'Application Cancelled');
    }






    public function test_api_request_for_edit_applications_successful(): void
    {
        $firstApp = $this->applications[0];
        // Check for valid response
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_api_request_for_edit_applications_unsuccessful_invalid_account_no(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => 'ascascasc',
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_a_date_is_null(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => null,
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_a_end_date_is_earlier_than_start_date(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2020-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_end_date_same_as_start_date(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-06 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_a_date_is_in_the_past(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2000-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_nominations_is_null(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => null,
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_nominations_is_empty(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => []
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_nomineeNo_is_missing(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => null,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_all_nominations_are_selfnomination_but_self_nomination_not_selected(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_unsuccessful_an_accountRoleId_is_null(): void
    {
        $firstApp = $this->applications[0];
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => null,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_edit_applications_changes_data_successfully(): void
    {
        $firstApp = $this->applications[0];
        $sDate = '2030-08-06 20:00:00';
        $eDate = '2030-08-08 20:00:00';
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => $sDate,
            'eDate' => $eDate,
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $application = Application::where('applicationNo', $firstApp->applicationNo)->first();
        $this->assertTrue($application->accountNo == $this->user->accountNo);
        $this->assertTrue($application->sDate == $sDate);
        $this->assertTrue($application->eDate == $eDate);
        $this->assertTrue($application->status == 'U');


        $nominations = Nomination::where('applicationNo', $firstApp->applicationNo)->get();
        foreach ($nominations as $nomination) {
            $this->assertTrue($nomination->nomineeNo == $this->user->accountNo);
        }

    }

    public function test_api_request_for_edit_applications_successful_nominees_notified_of_nomination_cancellation(): void
    {
        $firstApp = $this->applications[0];
        $sDate = '2030-08-06 20:00:00';
        $eDate = '2030-08-08 20:00:00';
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => $sDate,
            'eDate' => $eDate,
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->user->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $message = Message::where('applicationNo', $firstApp->applicationNo, "and")
        ->where('receiverNo', $this->otherUser->accountNo, "and")
        ->where('senderNo', $this->user->accountNo)->first();

        $this->assertTrue($message->subject == "Nomination/s Cancelled");
    }   

    public function test_api_request_for_edit_applications_successful_nominees_notified_of_nomination_edited_period_edited_out_of_range(): void
    {
        $firstApp = $this->applications[0];
        $sDate = '2030-08-06 20:00:00';
        $eDate = '2030-12-07 20:00:00';
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => $sDate,
            'eDate' => $eDate,
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $message = Message::where('applicationNo', $firstApp->applicationNo, "and")
        ->where('receiverNo', $this->otherUser->accountNo, "and")
        ->where('senderNo', $this->user->accountNo)->first();

        $this->assertTrue($message->subject == "Edited Substitution Request");
    }   

    // only period of application was edited, and it became a subset of the original period
    public function test_api_request_for_edit_applications_successful_nominees_notified_of_nomination_edited_period_edited_soley_subset(): void
    {    
        $firstApp = $this->applications[0];
        $sDate = '2030-08-07 20:00:00';
        $eDate = '2030-08-08 20:00:00';
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => $sDate,
            'eDate' => $eDate,
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $message = Message::where('applicationNo', $firstApp->applicationNo, "and")
        ->where('receiverNo', $this->otherUser->accountNo, "and")
        ->where('senderNo', $this->user->accountNo)->first();

        $this->assertTrue($message->subject == "Substitution Period Edited (Subset)");
    }     

    public function test_api_request_for_edit_applications_successful_nominees_notified_of_nomination_edited_period_edited_subset_and_extra_account_role(): void
    {
        $roles = Role::pluck('roleId');
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => $roles[3]
        ]));

        $firstApp = $this->applications[0];
        $sDate = '2030-08-07 20:00:00';
        $eDate = '2030-08-08 20:00:00';
        $response = $this->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => $sDate,
            'eDate' => $eDate,
            'nominations' => [
                [
                    'accountRoleId' => $this->accountRoles[0]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[1]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
                [
                    'accountRoleId' => $this->accountRoles[2]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ], 
                [
                    'accountRoleId' => $this->accountRoles[3]->accountRoleId,
                    'nomineeNo' => $this->otherUser->accountNo,
                ],
            ]
        ]);
        $response->assertStatus(200);

        $message = Message::where('applicationNo', $firstApp->applicationNo, "and")
        ->where('receiverNo', $this->otherUser->accountNo, "and")
        ->where('senderNo', $this->user->accountNo)->first();
        
        $this->assertTrue($message->subject == "Edited Substitution Request");
    }   
}