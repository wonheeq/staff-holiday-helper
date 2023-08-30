<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Application;
use App\Models\Nomination;
use Illuminate\Support\Facades\Log;

class ManagerControllerTest extends TestCase
{
    private Account $lineManager, $staff;
    private $accountRoles;
    private $applications;
    private $nominations;


    protected function setup(): void {
        parent::setup();

        $this->lineManager = Account::factory()->create();

        $this->staff = Account::factory()->create([
            'superiorNo' => $this->lineManager->accountNo,
        ]);

        $this->accountRoles = AccountRole::factory(3)->create([
            'accountNo' => $this->staff->accountNo,
        ]);

        $this->applications = Application::factory(5)->create([
            'accountNo' => $this->staff['accountNo'],
            'processedBy' => $this->lineManager['accountNo'],
            'status' => 'U'
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
        $arr = Application::where('accountNo', $this->staff->accountNo)->get();
        foreach ($arr as $a) {
            Nomination::where('applicationNo', $a->applicationNo)->delete();
        }

        AccountRole::where('accountNo', $this->staff['accountNo'])->delete();
        AccountRole::where('accountNo', $this->lineManager['accountNo'])->delete();
        Application::where('accountNo', $this->staff['accountNo'])->delete();



        $this->lineManager->delete();
        $this->staff->delete();

        $this->applications = null;
        $this->accountRoles = null;

        parent::teardown();
    }

    public function test_api_request_for_manager_applications_successful(): void
    {
        // Check for valid response
        $response = $this->getJson("/api/managerApplications/{$this->lineManager['accountNo']}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_manager_applications_invalid_lineManager(): void
    {
        // Check for invalid response
        $response = $this->getJson('/api/applications/asfasfasfasf');
        $response->assertStatus(500);
    }

    public function test_api_request_for_manager_applications_content_is_json(): void
    {
        // Check if response is json
        $response = $this->getJson("/api/managerApplications/{$this->lineManager['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_managerApplications_applications_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->get("/api/managerApplications/{$this->lineManager['accountNo']}");
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

        // Check if returned applications for correct lineManager
        $array = json_decode($response->content());
        foreach ($array as $app) {
            $this->assertTrue($app->processedBy == $this->lineManager['accountNo']);
        }
    }


    public function test_api_request_for_accepted_applications_successful(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' =>$this->applications[0]['applicationNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'processedBy' => $this->lineManager['accountNo'],
            'status' => 'Y'
        ]);
        $response->assertStatus(200);
    }
    public function test_api_request_for_accepted_applications_unsuccessful_no_account(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/acceptApplication", [
            'accountNo' => 'asdadsdasads',
            'applicationNo' =>$this->applications[0]['applicationNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'processedBy' => $this->lineManager['accountNo'],
            'status' => 'Y',
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_accepted_applications_unsuccessful_no_application(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' => 'asfsadfafsd',
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'processedBy' => $this->lineManager['accountNo'],
            'status' => 'Y',
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_accepted_applications_unsuccessful_wrong_manager(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' =>$this->applications[0]['applicationNo'],
            'processedBy' => 'adadsasddas',
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'status' => 'Y',
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_reject_applications_successful(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/rejectApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' =>$this->applications[0]['applicationNo'],
            'processedBy' => $this->lineManager['accountNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'status' => 'N',
            'rejectReason' => 'No more leaves'
        ]);
        $response->assertStatus(200);
    }
    public function test_api_request_for_reject_applications_unsuccessful_no_account(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/rejectApplication", [
            'accountNo' => 'asdadsdasads',
            'applicationNo' =>$this->applications[0]['applicationNo'],
            'processedBy' => $this->lineManager['accountNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'status' => 'N',
            'rejectReason' => 'No more leaves'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_reject_applications_unsuccessful_no_application(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/rejectApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' => 'asfsadfafsd',
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'processedBy' => $this->lineManager['accountNo'],
            'status' => 'N',
            'rejectReason' => 'No more leaves'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_rejected_applications_unsuccessful_wrong_manager(): void
    {
        // Check for valid response
        $response = $this->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' =>$this->applications[0]['applicationNo'],
            'processedBy' => 'adadsasddas',
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'status' => 'Y',
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acceptedApplication_status_correctly(): void {
        $app = $this->applications[0];
        $response = $this->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' =>$this->applications[0]['applicationNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'processedBy' => $this->lineManager['accountNo'],
            'status' => 'Y'
        ]);
        $response->assertStatus(200);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp['status'] == 'Y');
    }
    public function test_api_request_for_rejected_status_correctly(): void {
        $app = $this->applications[1];
        $response = $this->postJson("/api/rejectApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' =>$this->applications[1]['applicationNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'processedBy' => $this->lineManager['accountNo'],
            'rejectReason' => 'No more leaves.',
            'status' => 'N'
        ]);
        $response->assertStatus(200);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp['status'] == 'N');
    }


    public function test_api_request_for_rejectApplication_deletes_nominations(): void {
        $app = $this->applications[2];
        $response = $this->postJson("/api/rejectApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' =>$this->applications[2]['applicationNo'],
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'processedBy' => $this->lineManager['accountNo'],
            'rejectReason' => 'No more leaves.',
            'status' => 'N'
        ]);
        $response->assertStatus(200);

        $nominationsForApp = Nomination::where('applicationNo', $app->applicationNo)->get()->toArray();
        $this->assertFalse(count($nominationsForApp) > 0);
    }

}