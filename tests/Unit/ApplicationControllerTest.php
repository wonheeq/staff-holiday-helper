<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Application;
use App\Models\Nomination;
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
        ]);

        $this->applications = Application::factory(5)->create([
            'accountNo' => $this->user['accountNo']
        ]);

        $firstApp = $this->applications[0];
        $this->nominations = array();
        
        // set nominations for first application
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[0],
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[1]
        ]));
        array_push($this->nominations, Nomination::factory()->create([
            'applicationNo' => $firstApp->applicationNo,
            'accountRoleId' => $this->accountRoles[2]
        ]));

    }

    protected function teardown(): void {
        $arr = Application::where('accountNo', $this->user->accountNo)->get();
        foreach ($arr as $a) {
            Nomination::where('applicationNo', $a->applicationNo)->delete();
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


    /**
     * Unit tests for getAllApplications
     */
    public function test_api_request_for_all_applications(): void
    {
        $response = $this->getJson("/api/allApplications/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->getJson("/api/allApplications/{$this->otherUser1['accountNo']}");
        $response->assertStatus(500);

        $response = $this->getJson("/api/allApplications/{$this->otherUser2['accountNo']}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_accounts_content_is_json(): void
    {
        // Check if response is json
        $response = $this->getJson("/api/allApplications/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_accounts_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->getJson("/api/allApplications/{$this->adminUser['accountNo']}");
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
}