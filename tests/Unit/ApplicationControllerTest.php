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
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Log;
use App\Models\EmailPreference;

class ApplicationControllerTest extends TestCase
{
    private Account $user, $otherUser, $otherUser1, $otherUser2, $adminUser;
    private $accountRoles;
    private $applications;
    private $nominations;


    protected function setup(): void
    {
        parent::setup();

        $this->otherUser = Account::factory()->create([
            'accountType' => 'lmanager'
        ]);
        $this->user = Account::factory()->create([
            'superiorNo' => $this->otherUser->accountNo,
            'accountType' => 'staff',
        ]);

        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);

        $this->otherUser1 = Account::factory()->create([
            'accountType' => "staff"
        ]);

        $this->otherUser2 = Account::factory()->create([
            'accountType' => "lmanager"
        ]);


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
            'nomineeNo' => $this->otherUser->accountNo,
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
        ]));

        EmailPreference::factory()->create(['accountNo' => $this->user->accountNo]);
        EmailPreference::factory()->create(['accountNo' => $this->otherUser->accountNo]);
        EmailPreference::factory()->create(['accountNo' => $this->adminUser->accountNo]);
        EmailPreference::factory()->create(['accountNo' => $this->otherUser1->accountNo]);
        EmailPreference::factory()->create(['accountNo' => $this->otherUser2->accountNo]);
    }

    protected function teardown(): void
    {
        EmailPreference::where(['accountNo' => $this->user->accountNo])->delete();
        EmailPreference::where(['accountNo' => $this->otherUser->accountNo])->delete();
        EmailPreference::where(['accountNo' => $this->adminUser->accountNo])->delete();
        EmailPreference::where(['accountNo' => $this->otherUser1->accountNo])->delete();
        EmailPreference::where(['accountNo' => $this->otherUser2->accountNo])->delete();

        $arr = Application::where('accountNo', $this->user->accountNo)->get();
        foreach ($arr as $a) {
            Nomination::where('applicationNo', $a->applicationNo)->delete();
            Message::where('applicationNo', $a->applicationNo)->delete();
        }

        AccountRole::where('accountNo', $this->user['accountNo'])->delete();

        AccountRole::where('accountNo', $this->otherUser['accountNo'])->delete();
        Application::where('accountNo', $this->user['accountNo'])->delete();

        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();

        $this->user->delete();
        $this->otherUser->delete();

        $this->applications = null;
        $this->accountRoles = null;

        parent::teardown();
    }

    public function test_api_request_for_applications_successful(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->user)->getJson("/api/applications/{$this->user['accountNo']}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_applications_invalid_user(): void
    {
        // Check for invalid response
        $response = $this->actingAs($this->user)->getJson('/api/applications/asfasfasfasf');
        $response->assertStatus(500);
    }

    public function test_api_request_for_applications_valid_length(): void
    {
        // Test if amount of applications matches
        $response = $this->actingAs($this->user)->getJson("/api/applications/{$this->user['accountNo']}");
        $array = $response->getData();
        $this->assertTrue(count($array) == count($this->applications));
    }

    public function test_api_request_for_applications_content_is_json(): void
    {
        // Check if response is json
        $response = $this->actingAs($this->user)->getJson("/api/applications/{$this->user['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_applications_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->actingAs($this->user)->get("/api/applications/{$this->user['accountNo']}");
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


    /**
     * Unit tests for getAllApplications
     */
    public function test_api_request_for_all_applications(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allApplications/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allApplications/{$this->otherUser1['accountNo']}");
        $response->assertStatus(302);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allApplications/{$this->otherUser2['accountNo']}");
        $response->assertStatus(302);
    }

    public function test_api_request_for_accounts_content_is_json(): void
    {
        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allApplications/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_accounts_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allApplications/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'applicationNo',
                'accountNo',
                'sDate',
                'eDate',
                'status',
                'processedBy',
                'rejectReason',
                'updated_at'
            ],
        ]);
    }


    public function test_api_request_for_create_applications_successful(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
            'sDate' => '2030-08-04 20:00:00',
            'eDate' => '2030-08-05 20:00:00',
            'nominations' => [],
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_create_applications_successful_manager_notified_about_fully_self_nominated_app(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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

    public function test_api_request_for_create_applications_successful_nominees_notified_about_nominations(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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





    public function test_api_request_for_cancelApplication_is_successful(): void
    {
        $app = $this->applications[0];
        $response = $this->actingAs($this->user)->getJson("/api/cancelApplication/{$this->user->accountNo}/{$app->applicationNo}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_cancelApplication_is_unsuccessful_user_does_not_exist(): void
    {
        $app = $this->applications[0];
        $response = $this->actingAs($this->user)->getJson("/api/cancelApplication/baduseracc/{$app->applicationNo}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_cancelApplication_is_unsuccessful_application_does_not_exist(): void {
        $response = $this->actingAs($this->user)->getJson("/api/cancelApplication/{$this->user->accountNo}/badapplicaitonno");
        $response->assertStatus(500);
    }

    public function test_api_request_for_cancelApplication_is_unsuccessful_application_does_not_belong_to_user(): void
    {
        $app = $this->applications[0];
        $response = $this->actingAs($this->user)->getJson("/api/cancelApplication/{$this->user->accountNo}/{$app->applicationNo}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_cancelApplication_sets_status_correctly(): void
    {
        $app = $this->applications[0];
        $response = $this->actingAs($this->user)->getJson("/api/cancelApplication/{$this->user->accountNo}/{$app->applicationNo}");
        $response->assertStatus(200);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp['status'] == 'C');
    }

    public function test_api_request_for_cancelApplication_is_successful_manager_is_notified_of_application_cancelleation(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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

        $response = $this->actingAs($this->adminUser)->getJson("/api/cancelApplication/{$this->user->accountNo}/{$application->applicationNo}");
        $response->assertStatus(200);

        // Get current line manager account number
        $superiorNo = app(AccountController::class)->getCurrentLineManager($this->user->accountNo)->accountNo;

        $message = Message::where('applicationNo', $application->applicationNo, "and")
            ->where('senderNo', $this->user->accountNo, 'and')
            ->where('receiverNo', $superiorNo)->first();

            // manager may be a nominee too
        $this->assertTrue($message->subject == "Application Cancelled" || $message->subject == "Nomination Cancelled");
    }

    public function test_api_request_for_cancelApplication_is_successful_nominee_is_notified_of_application_cancelleation(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
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

        $response = $this->actingAs($this->user)->getJson("/api/cancelApplication/{$this->user->accountNo}/{$application->applicationNo}");
        $response->assertStatus(200);

        $message = Message::where('applicationNo', $application->applicationNo, "and")
            ->where('receiverNo', $this->otherUser->accountNo, 'and')
            ->where('senderNo', $this->user->accountNo, 'and')
            ->where('subject', "Nomination Cancelled")->first();
            //dd($message);
        $this->assertTrue($message->subject == "Nomination Cancelled");
    }






    public function test_api_request_for_edit_applications_successful(): void
    {
        $firstApp = $this->applications[0];
        // Check for valid response
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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

        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
            'applicationNo' => $firstApp->applicationNo,
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => false,
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

        $messages = Message::where('applicationNo', $firstApp->applicationNo)
            ->where('senderNo', $this->user->accountNo)->get();

        // add message subjects to array
        $messageSubjects = [];
        foreach ($messages as $message) {
            array_push($messageSubjects, $message->subject);
        }

        $this->assertTrue(in_array("Substitution Period Edited (Subset)", $messageSubjects));
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
        $response = $this->actingAs($this->user)->postJson("/api/editApplication", [
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





    public function test_api_request_for_getApplicationForReview_is_successful(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->getJson("/api/getApplicationForReview/{$this->user->superiorNo}/{$secondApp->applicationNo}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_getApplicationForReview_is_successful_is_json(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->getJson("/api/getApplicationForReview/{$this->user->superiorNo}/{$secondApp->applicationNo}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_getApplicationForReview_is_successful_structure_is_correct(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->getJson("/api/getApplicationForReview/{$this->user->superiorNo}/{$secondApp->applicationNo}");
        $this->assertJson($response->content());

        $response->assertJsonStructure([
            'applicationNo',
            'applicantNo',
            'applicantName',
            'duration',
            'nominations'
        ]);

        $array = json_decode($response->content(), true);
        foreach ($array['nominations'] as $nom) {
            // nominations are arrays of numbers
            $this->assertTrue(gettype($array['nominations']) == 'array');

            foreach ($nom as $role) {
                // can convert and compare to a number therefore is a number
                $this->assertTrue(intval($role) >= 0);
            }
        }
    }

    public function test_api_request_for_getApplicationForReview_is_unsuccessful_account_does_not_exist(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->getJson("/api/getApplicationForReview/wackydoodle/{$secondApp->applicationNo}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_getApplicationForReview_is_unsuccessful_application_does_not_exist(): void {
        $response = $this->actingAs($this->otherUser)->getJson("/api/getApplicationForReview/{$this->user->superiorNo}/bad");
        $response->assertStatus(500);
    }

    public function test_api_request_for_getApplicationForReview_is_unsuccessful_application_has_outstanding_nominations(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'N',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        $secondApp->status = 'P';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->getJson("/api/getApplicationForReview/{$this->user->superiorNo}/{$secondApp->applicationNo}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_getApplicationForReview_is_unsuccessful_invalid_superior(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->getJson("/api/getApplicationForReview/{$this->user->accountNo}/{$secondApp->applicationNo}");
        $response->assertStatus(500);
    }






    // ACCEPT APPLICATION TESTS
    public function test_api_request_for_acceptApplication_is_successful(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
        ]);

        $response->assertStatus(200);
    }

    public function test_api_request_for_acceptApplication_is_successful_application_is_updated(): void {
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
        ]);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp->status == 'Y');
        $this->assertTrue($updatedApp->processedBy == $this->otherUser->accountNo);
    }

    public function test_api_request_for_acceptApplication_is_successful_message_of_superivisor_is_set_to_acknowledged(): void {
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
        ]);

        $message = Message::where('applicationNo', $app->applicationNo, "and")
            ->where('receiverNo', $this->otherUser->accountNo, "and")
            ->where('senderNo', $this->user->accountNo, "and")
            ->where('subject', "Application Awaiting Review")->first();
        $this->assertTrue($message != null);
        $this->assertTrue($message->acknowledged == true);
    }

    public function test_api_request_for_acceptApplication_is_successful_applicant_is_messaged(): void {
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
        ]);

        $message = Message::where('applicationNo', $app->applicationNo, "and")
            ->where('receiverNo', $this->user->accountNo, "and")
            ->where('senderNo', $this->otherUser->accountNo, "and")
            ->where('subject', "Application Approved")->first();
        $this->assertTrue($message != null);
    }

    public function test_api_request_for_acceptApplication_is_unsuccessful_supervisor_does_not_exist(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'superiorNo' => "aoueirhgoiarg",
            'applicationNo' => $secondApp->applicationNo,
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acceptApplication_is_unsuccessful_application_does_not_exist(): void {
        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'superiorNo' => $this->user->accountNo,
            'applicationNo' => '03q495u0fd',
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acceptApplication_is_unsuccessful_application_status_is_not_undecided(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        $secondApp->status = 'N';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $secondApp->applicationNo,
        ]);

        $response->assertStatus(500);
    }

    public function test_api_request_for_acceptApplication_is_unsuccessful_account_is_not_superior_of_applicant(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/acceptApplication", [
            'accountNo' => Account::where('accountNo', "!=", $this->user->superiorNo )->first()->accountNo,
            'applicationNo' => $secondApp->applicationNo,
        ]);

        $response->assertStatus(500);
    }








    // REJECT APPLICATION TESTS
    public function test_api_request_for_rejectApplication_is_successful(): void {
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
            'rejectReason' => "Not enough leave"
        ]);

        $response->assertStatus(200);
    }

    public function test_api_request_for_rejectApplication_is_successful_application_is_updated(): void {
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
            'rejectReason' => "Not enough leave"
        ]);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp->status == 'N');
        $this->assertTrue($updatedApp->processedBy == $this->otherUser->accountNo);
    }

    public function test_api_request_for_rejectApplication_is_successful_message_of_superivisor_is_set_to_acknowledged(): void {
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
            'rejectReason' => "Not enough leave"
        ]);

        $message = Message::where('applicationNo', $app->applicationNo, "and")
            ->where('receiverNo', $this->otherUser->accountNo, "and")
            ->where('senderNo', $this->user->accountNo, "and")
            ->where('subject', "Application Awaiting Review")->first();
        $this->assertTrue($message != null);
        $this->assertTrue($message->acknowledged == true);
    }

    public function test_api_request_for_rejectApplication_is_successful_applicant_is_messaged(): void {
        $response = $this->actingAs($this->user)->postJson("/api/createApplication", [
            'accountNo' => $this->user->accountNo,
            'selfNominateAll' => true,
            'sDate' => '2024-09-06 20:00:00',
            'eDate' => '2024-09-08 20:00:00',
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

        $app = Application::where('sDate', '2024-09-06 20:00:00', 'and')
            ->where('eDate', '2024-09-08 20:00:00', "and")
            ->where('accountNo', $this->user->accountNo)->first();


        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $app->applicationNo,
            'rejectReason' => "Not enough leave"
        ]);

        $message = Message::where('applicationNo', $app->applicationNo, "and")
            ->where('receiverNo', $this->user->accountNo, "and")
            ->where('senderNo', $this->otherUser->accountNo, "and")
            ->where('subject', "Application Denied")->first();
        $this->assertTrue($message != null);
    }

    public function test_api_request_for_rejectApplication_is_unsuccessful_supervisor_does_not_exist(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'superiorNo' => "aoueirhgoiarg",
            'applicationNo' => $secondApp->applicationNo,
            'rejectReason' => "Not enough leave"
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_rejectApplication_is_unsuccessful_application_does_not_exist(): void {
        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'superiorNo' => $this->user->accountNo,
            'applicationNo' => '03q495u0fd',
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_rejectApplication_is_unsuccessful_application_status_is_not_undecided(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        $secondApp->status = 'N';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $secondApp->applicationNo,
            'rejectReason' => "Not enough leave"
        ]);

        $response->assertStatus(500);
    }

    public function test_api_request_for_rejectApplication_is_unsuccessful_account_is_not_superior_of_applicant(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => Account::where('accountNo', "!=", $this->user->superiorNo )->first()->accountNo,
            'applicationNo' => $secondApp->applicationNo,
            'rejectReason' => "Not enough leave"
        ]);

        $response->assertStatus(500);
    }

    public function test_api_request_for_rejectApplication_is_unsuccessful_no_reject_reason(): void {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $secondApp->applicationNo,
        ]);

        $response->assertStatus(500);
    }

    public function test_api_request_for_rejectApplication_is_unsuccessful_empty_reject_reason(): void
    {
        $secondApp = $this->applications[1];
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $secondApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2],
            'nomineeNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]));

        // setup - make sure that application is status 'U'
        $secondApp->status = 'U';
        $secondApp->save();

        $response = $this->actingAs($this->otherUser)->postJson("/api/rejectApplication", [
            'accountNo' => $this->otherUser->accountNo,
            'applicationNo' => $secondApp->applicationNo,
            'rejectReason' => ""
        ]);

        $response->assertStatus(500);
    }
}
