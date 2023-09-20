<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\Message;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Course;
use App\Models\Major;

use Illuminate\Support\Facades\Log;

class ManagerControllerTest extends TestCase
{
    private Account $lineManager, $staff;
    private $accountRoles;
    private $applications;
    private $nominations;
    


    protected function setup(): void {
    protected function setup(): void
    {
        parent::setup();

        $this->lineManager = Account::factory()->create([
            'accountType' => "lmanager"
        ]);

        $this->staff = Account::factory()->create([
            'superiorNo' => $this->lineManager->accountNo,
        ]);

        $this->applications = Application::factory(5)->create([
            'accountNo' => $this->staff->accountNo,
            'status' => 'U'
        ]);

        $this->accountRoles = array();

        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->staff->accountNo,
            'roleId' => Role::where('name', "Course Coordinator")->first()->roleId
        ]));

        // assign Major Coordinator to user
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->staff->accountNo,
            'roleId' => Role::where('name', "Major Coordinator")->first()->roleId
        ]));

        // assign Unit Coordinator to user
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->staff->accountNo,
            'roleId' => Role::where('name', "Unit Coordinator")->first()->roleId
        ]));

        // assign Tutor to user
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->staff->accountNo,
            'roleId' => Role::where('name', "Tutor")->first()->roleId
        ]));

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

    protected function teardown(): void
    {
        $arr = Application::where('accountNo', $this->staff->accountNo)->get();
        foreach ($arr as $a) {
            Nomination::where('applicationNo', $a->applicationNo)->delete();
            Message::where('applicationNo', $a->applicationNo)->delete();
        }

        AccountRole::where('accountNo', $this->staff->accountNo)->delete();
        AccountRole::where('accountNo', $this->lineManager->accountNo)->delete();
        Application::where('accountNo', $this->staff->accountNo)->delete();


        $this->lineManager->delete();
        $this->staff->delete();

        $this->applications = null;
        $this->accountRoles = null;

        parent::teardown();
    }

    public function test_api_request_for_manager_applications_successful(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->getJson("/api/managerApplications/{$this->lineManager->accountNo}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_manager_applications_invalid_lineManager(): void
    {
        // Check for invalid response
        $response = $this->getJson('/api/applications/asfasfasfasf');
        $response->assertStatus(401);
    }

    public function test_api_request_for_manager_applications_content_is_json(): void
    {
        // Check if response is json
        $response = $this->actingAs($this->lineManager)->getJson("/api/managerApplications/{$this->lineManager->accountNo}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_managerApplications_applications_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->actingAs($this->lineManager)->get("/api/managerApplications/{$this->lineManager->accountNo}");
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
        $count = 0;
        foreach ($array as $app) {
            $this->assertTrue($app->status == 'U');
            $count++;
        }
    }


    public function test_api_request_for_accepted_applications_successful(): void
    {
        $app = $this->applications[0];
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/acceptApplication", [
            'accountNo' => $this->lineManager->accountNo,
            'applicationNo' => $app->applicationNo,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
        ]);
        $response->assertStatus(200);
    }
    public function test_api_request_for_accepted_applications_unsuccessful_no_account(): void
    {
        $app = $this->applications[0];
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/acceptApplication", [
            'accountNo' => 'asdadsdasads',
            'applicationNo' => $app->applicationNo,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_accepted_applications_unsuccessful_no_application(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' => 'asfsadfafsd',
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'status' => 'Y',
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_accepted_applications_unsuccessful_wrong_manager(): void
    {        
        $app = $this->applications[0];
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' => $app->applicationNo,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_reject_applications_successful(): void
    {
        $app = $this->applications[0];
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/rejectApplication", [
            'accountNo' => $this->lineManager->accountNo,
            'applicationNo' => $app->applicationNo,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'rejectReason' => 'No more leaves'
        ]);
        $response->assertStatus(200);
    }
    public function test_api_request_for_reject_applications_unsuccessful_no_account(): void
    {
        $app = $this->applications[0];
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/rejectApplication", [
            'accountNo' => 'asdadsdasads',
            'applicationNo' => $app->applicationNo,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'rejectReason' => 'No more leaves'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_reject_applications_unsuccessful_no_application(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/rejectApplication", [
            'accountNo' => $this->lineManager->accountNo,
            'applicationNo' => 'asfsadfafsd',
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'rejectReason' => 'No more leaves'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_rejected_applications_unsuccessful_wrong_manager(): void
    {
        $app = $this->applications[0];
        // Check for valid response
        $response = $this->actingAs($this->lineManager)->postJson("/api/acceptApplication", [
            'accountNo' => $this->staff->accountNo,
            'applicationNo' => $app->applicationNo,
            'processedBy' => 'adadsasddas',
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
        ]);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acceptedApplication_status_correctly(): void
    {
        $app = $this->applications[0];
        $response = $this->actingAs($this->lineManager)->postJson("/api/acceptApplication", [
            'accountNo' => $this->lineManager->accountNo,
            'applicationNo' => $app->applicationNo,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
        ]);
        $response->assertStatus(200);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp['status'] == 'Y');
    }
    public function test_api_request_for_rejected_status_correctly(): void
    {
        $app = $this->applications[1];
        $response = $this->actingAs($this->lineManager)->postJson("/api/rejectApplication", [
            'accountNo' => $this->lineManager->accountNo,
            'applicationNo' => $app->applicationNo,
            'sDate' => '2030-08-06 20:00:00',
            'eDate' => '2030-08-08 20:00:00',
            'rejectReason' => 'No more leaves.',
        ]);
        $response->assertStatus(200);

        $updatedApp = Application::where('applicationNo', $app->applicationNo)->first();
        $this->assertTrue($updatedApp['status'] == 'N');
    }
    /**
     * Testing for manage staffs subpages (add/edit roles)
     */
    public function test_api_request_for_remove_course_roles_success(): void{
        $accRole = $this->accountRoles[0];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => Course::where('courseId', $accRole->courseId)->first()->courseId,
            'roleName' => 'Course Coordinator'
        ]);
        
        $response->assertStatus(200);
        $updatedRoles = AccountRole::where('accountRoleId', $accRole->accountRoleId)->first();
        $this->assertTrue($updatedRoles == null);
    }
    public function test_api_request_for_remove_course_roles_fail_wrong_course_role_name(): void{
        $accRole = $this->accountRoles[0];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => Course::where('courseId', $accRole->courseId)->first()->courseId,
            'roleName' => 'Unit Coordinator'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_remove_course_roles_fail_wrong_course_code(): void{
        $accRole = $this->accountRoles[0];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => 'LLL3123',
            'roleName' => 'Course Coordinator'
        ]);
        
        $response->assertStatus(500);
    }
    public function test_api_request_for_remove_major_roles_success(): void{
        $accRole = $this->accountRoles[1];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => Major::where('majorId', $accRole->majorId)->first()->majorId,
            'roleName' => 'Major Coordinator'
        ]);
        
        $response->assertStatus(200);
        $updatedRoles = AccountRole::where('accountRoleId', $accRole->accountRoleId)->first();
        $this->assertTrue($updatedRoles == null);
    }
    public function test_api_request_for_remove_major_roles_fail_wrong_major_role_name(): void{
        $accRole = $this->accountRoles[1];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => Major::where('majorId', $accRole->majorId)->first()->majorId,
            'roleName' => 'Course Coordinator'
        ]);
        
        $response->assertStatus(500);
    }
    public function test_api_request_for_remove_major_roles_fail_wrong_major_code(): void{
        $accRole = $this->accountRoles[1];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => 'LLL3123',
            'roleName' => 'Major Coordinator'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_remove_unit_roles_success(): void{
        $accRole = $this->accountRoles[2];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => Unit::where('unitId', $accRole->unitId)->first()->unitId,
            'roleName' => 'Unit Coordinator'
        ]);
        
        $response->assertStatus(200);
        $updatedRoles = AccountRole::where('accountRoleId', $accRole->accountRoleId)->first();
        $this->assertTrue($updatedRoles == null);
    }
    public function test_api_request_for_remove_unit_roles_fail_wrong_unit_role_name(): void{
        $accRole = $this->accountRoles[2];
        $currentUnitId = Unit::where('unitId', $accRole->unitId)->first();
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => $currentUnitId->unitId,
            'roleName' => 'Major Coordinator'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_remove_unit_roles_fail_wrong_unit_code(): void{
        $accRole = $this->accountRoles[2];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => 'LL12313',
            'roleName' => 'Major Coordinator'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_remove_role_fail_account_not_exist(): void{
        $accRole = $this->accountRoles[2];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => '1fdadf',
            'unitCode' => 'LL1223',
            'roleName' => 'Major Coordinator'
        ]);
        $response->assertStatus(500);
    }
    public function test_api_request_for_remove_role_fail_account_does_not_have_any_roles(): void{
        $accRole = $this->accountRoles[2];
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->lineManager->accountNo,
            'unitCode' => 'LLLL123123',
            'roleName' => 'Major Coordinator'
        ]);
        $response->assertStatus(500);
    }
    //Remove the course first and then add to show that it succeeded
    public function test_api_request_for_add_course_roles_success(): void{
        $accRole = $this->accountRoles[0];
        $courseCode = Course::where('courseId', $accRole->courseId)->first()->courseId;
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => $courseCode,
            'roleName' => 'Course Coordinator'
        ]);
        $response->assertStatus(200);

        $response = $this->postJson("/api/addStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => $courseCode,
            'roleName' => 'Course Coordinator'
        ]);
        $response->assertStatus(200);
    }
    //Remove the major first and then add to show that it succeeded
    public function test_api_request_for_add_major_roles_success(): void{
        $accRole = $this->accountRoles[1];
        $majorCode = Major::where('majorId', $accRole->majorId)->first()->majorId;
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => $majorCode,
            'roleName' => 'Major Coordinator'
        ]);
        $response->assertStatus(200);

        $response = $this->postJson("/api/addStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => $majorCode,
            'roleName' => 'Major Coordinator'
        ]);
        $response->assertStatus(200);
    }
    //Remove the unit first and then add to show that it succeeded
    public function test_api_request_for_add_unit_roles_success(): void{
        $accRole = $this->accountRoles[3];
        $unitCode = Unit::where('unitId', $accRole->unitId)->first()->unitId;
        $response = $this->postJson("/api/removeStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => $unitCode,
            'roleName' => 'Tutor'
        ]);
        $response->assertStatus(200);

        $response = $this->postJson("/api/addStaffRole", [
            'staffNo' => $this->staff->accountNo,
            'unitCode' => $unitCode,
            'roleName' => 'Tutor'
        ]);
        $response->assertStatus(200);
    }
}
