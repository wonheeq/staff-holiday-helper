<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Nomination;
use App\Models\Application;
use App\Http\Controllers\RoleController;
use DateTime;

class BookingControllerTest extends TestCase
{
    private Account $user;
    private $otherUsers;
    private $applications;

    protected function setup(): void {
        parent::setup();

        // Create test data
        $this->user = Account::factory()->create();
        AccountRole::factory(3)->create([
            'accountNo' => $this->user->accountNo,
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

        $this->otherUsers = Account::factory(3)->create();
        $this->applications = array();

        foreach ($this->otherUsers as $otherUser) {
            $accountRoles = AccountRole::factory(3)->create([
                'accountNo' => $otherUser->accountNo,
            ]);

            $app = Application::factory()->create([
                'accountNo' => $otherUser->accountNo,
                'status' => 'Y'
            ]);
            array_push($this->applications, $app);

            Nomination::factory()->create([
                'applicationNo' => $app->applicationNo,
                'nomineeNo'=> $this->user->accountNo,
                'accountRoleId' => $accountRoles[0],
                'status' => 'Y',
            ]);
            Nomination::factory()->create([
                'applicationNo' => $app->applicationNo,
                'nomineeNo'=> $this->user->accountNo,
                'accountRoleId' => $accountRoles[1],
                'status' => 'Y',
            ]);
            Nomination::factory()->create([
                'applicationNo' => $app->applicationNo,
                'nomineeNo'=> $this->user->accountNo,
                'accountRoleId' => $accountRoles[2],
                'status' => 'Y',
            ]);
        } 

        // create application that is old
        $selectedUser = $this->otherUsers[0];
        $accountRoles = AccountRole::where('accountNo', $selectedUser->accountNo)->get();

        $app = Application::factory()->create([
            'accountNo' => $selectedUser->accountNo,
            'sDate' => '2023-05-05 12:00:00',
            'eDate' => '2023-05-06 12:00:00',
            'status' => 'Y'
        ]);
        array_push($this->applications, $app);

        Nomination::factory()->create([
            'applicationNo' => $app->applicationNo,
            'nomineeNo'=> $this->user->accountNo,
            'accountRoleId' => $accountRoles[0],
            'status' => 'Y',
        ]);
        Nomination::factory()->create([
            'applicationNo' => $app->applicationNo,
            'nomineeNo'=> $this->user->accountNo,
            'accountRoleId' => $accountRoles[1],
            'status' => 'Y',
        ]);
        Nomination::factory()->create([
            'applicationNo' => $app->applicationNo,
            'nomineeNo'=> $this->user->accountNo,
            'accountRoleId' => $accountRoles[2],
            'status' => 'Y',
        ]);
    }

    protected function teardown(): void {      
        foreach ($this->applications as $application) {
            Nomination::where('applicationNo', $application->applicationNo)->delete();
        }  
        foreach ($this->otherUsers as $otherUser) {
            AccountRole::where('accountNo', $otherUser->accountNo)->delete();
        }  
        foreach ($this->applications as $application) {
            Application::where('applicationNo', $application->applicationNo)->delete();
        }
        foreach ($this->otherUsers as $otherUser) {
            Account::where('accountNo', $otherUser->accountNo)->delete();
        }  
        AccountRole::where('accountNo', $this->user->accountNo)->delete();

        $this->user->delete();

        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();

        parent::teardown();
    }



    public function test_getBookingOptions_api_call_is_successful(): void
    {
        $response = $this->getJson("/api/getBookingOptions/{$this->user->accountNo}");
        $response->assertStatus(200);
    }

    public function test_getBookingOptions_api_call_is_unsuccessful(): void
    {
        $response = $this->getJson("/api/getBookingOptions/badaccountno");
        $response->assertStatus(500);
    }

    public function test_getBookingOptions_returned_content_is_json(): void
    {
        $response = $this->getJson("/api/getBookingOptions/{$this->user->accountNo}");
        $this->assertJson($response->content());
    }

    public function test_getBookingOptions_returned_content_is_valid(): void
    {
        $response = $this->getJson("/api/getBookingOptions/{$this->user->accountNo}");
        $array = json_decode($response->content());

        foreach ($array as $arr) {
            // should be string
            $this->assertTrue(gettype($arr) == "string");
            // should not include caller's accountNo
            $this->assertFalse($arr == $this->user->accountNo);
        }
    }


    public function test_getRolesFornominations_api_call_is_successful(): void {
        $response = $this->getJson("/api/getRolesForNominations/{$this->user->accountNo}");
        $response->assertStatus(200);
    }

    public function test_getRolesFornominations_api_call_is_unsuccessful(): void {
        $response = $this->getJson("/api/getRolesForNominations/badaccountno");
        $response->assertStatus(500);
    }

    public function test_getRolesFornominations_returned_content_is_json(): void
    {
        $response = $this->getJson("/api/getRolesForNominations/{$this->user->accountNo}");
        $this->assertJson($response->content());
    }

    public function test_getRolesForNominations_returned_structure_is_valid(): void
    {
        $response = $this->get("/api/getRolesForNominations/{$this->user->accountNo}");
        $response->assertJsonStructure([
            [
                'accountRoleId',
                'selected',
                'role',
                'nomination',
                'visible',
            ],
        ]);
    }

    public function test_getRolesForNominations_returned_content_is_valid(): void
    {
        $response = $this->getJson("/api/getRolesForNominations/{$this->user->accountNo}");
        $result = json_decode($response->content());

        // Get all AccountRoles associated with the accountNo
        $accountRoles = AccountRole::where('accountNo', $this->user->accountNo)->get();
        
        $accountRoleIds = array();
        $roles = array();

        // Iterate through each AccountRole, extract the roleId
        // Call RoleController->getRoleFromAccountRoleId() to get the role name
        // push to respective arrays
        foreach ($accountRoles as $accountRole) {
            $roleId = $accountRole['roleId'];
            $roleName = app(RoleController::class)->getRoleFromAccountRoleId($roleId);

            array_push($accountRoleIds, $accountRole['accountRoleId']);
            array_push($roles, $roleName);
        }

        foreach ($result as $element) {
            $this->assertTrue(in_array($element->accountRoleId, $accountRoleIds));
            $this->assertTrue(in_array($element->role, $roles));
        }
    }

    


    public function test_getSubstitutionsForUser_api_call_is_successful(): void {
        $response = $this->getJson("/api/getSubstitutionsForUser/{$this->user->accountNo}");
        $response->assertStatus(200);
    }

    public function test_getSubstitutionsForUser_api_call_is_unsuccessful(): void {
        $response = $this->getJson("/api/getSubstitutionsForUser/badAccountNo");
        $response->assertStatus(500);
    }

    public function test_getSubstitutionsForUser_returned_content_is_json(): void
    {
        $response = $this->getJson("/api/getSubstitutionsForUser/{$this->user->accountNo}");
        $this->assertJson($response->content());
    }
    
    public function test_getSubstitutionsForUser_returned_structure_is_valid(): void
    {
        $response = $this->get("/api/getSubstitutionsForUser/{$this->user->accountNo}");
        $response->assertJsonStructure([
            [
                'sDate',
                'eDate',
                'tasks',
                'applicantName',
            ],
        ]);
    }

    public function test_getSubstitutionsForUser_api_call_does_not_return_old_substitutions(): void {
        $response = $this->actingAs($this->user)->getJson("/api/getSubstitutionsForUser/{$this->user->accountNo}");
        $arr = json_decode($response->content(), true);

        $this->assertTrue(count($arr) == count($this->applications) - 1);

        foreach ($arr as $a) {
            $nowTime = new DateTime();
            $endTime = new DateTime($a['eDate']);
            $this->assertTrue($endTime >= $nowTime);
        }
    }
}
