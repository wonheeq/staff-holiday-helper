<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;

use Illuminate\Support\Facades\Log;

class DatabaseControllerTest extends TestCase
{
    private Account $adminUser, $otherUser1, $otherUser2;
    private Array $validEntry;

    protected function setup(): void {
        parent::setup();

        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);

        $this->otherUser1 = Account::factory()->create([
            'accountType' => "staff"
        ]);

        $this->otherUser2 = Account::factory()->create([
            'accountType' => "lmanager"
        ]);

        // Mock valid entry to be added to db
        $this->validEntry = array('fields' => 'roleFields', 'newEntry' => array(0 => 'testRole'));
        //Log::info($this->validEntry);
    }

    protected function teardown(): void {
        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();

        Role::where('name', 'testRole')->delete();

        parent::teardown();
    }

    /**
     * Unit tests for DatabaseController.php
    */
    public function test_api_request_for_add_entry_is_protected(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", $this->validEntry);
        $response->assertStatus(200);

        $response = $this->/*actingAs($this->otherUser1)->*/postJson("/api/addSingleEntry/{$this->otherUser1['accountNo']}", $this->validEntry);
        $response->assertStatus(500/*403*/);

        $response = $this->/*actingAs($this->otherUser2)->*/postJson("/api/addSingleEntry/{$this->otherUser2['accountNo']}", $this->validEntry);
        $response->assertStatus(500/*403*/);
    }

    public function test_api_request_for_addentry_adding_valid_account(): void
    {
        // Creating data to add to for test
        $accountNo1 = '123456j';
        $accountType1 = array('db_name' => 'staff', 'name' => 'Staff');

        $tempSchool = School::create(['name' => 'School of Test']);
        $schoolObj = array('schoolId' => $tempSchool->schoolId, 'name' => $tempSchool->name);

        $superiorNo1 = array('accountNo' => $this->otherUser2->accountNo, 'fullName' => 'Test Line Manager');

        // Check for valid response to adding valid user
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'accountFields', 'newEntry' => array(
                0 => $accountNo1, 
                1 => $accountType1, 
                2 => 'TestA', 
                3 => 'TestB', 
                4 => $schoolObj, 
                5 => $superiorNo1)
            )
        );
        $response->assertStatus(200);

        // Checking new account added
        $this->assertTrue(
            Account::where('accountNo', $accountNo1)
                ->where('accountType', $accountType1['db_name'])
                ->where('lName', 'TestA')
                ->where('fName', 'TestB')
                ->where('superiorNo', $superiorNo1['accountNo'])
                ->where('schoolId', $schoolObj['schoolId'])->exists()     
        ); 

        // Removing account and school created for this test.
        Account::where('accountNo', $accountNo1)->delete();
        School::where('schoolId', $tempSchool->schoolId)->delete();
    }     
    
    public function test_api_request_for_addentry_adding_invalid_account(): void
    {
        // Creating data to add to for test
        $accountNoInvalid = '123456jj'; // Invalid Syntax
        $accountNoTaken = $this->otherUser1->accountNo; // accountNo already in use

        $accountType1 = array('db_name' => 'staff', 'name' => 'Staff');

        $tempSchool = School::create(['name' => 'School of Test']);
        $schoolObj = array('schoolId' => $tempSchool->schoolId, 'name' => $tempSchool->name);

        $superiorNo1 = array('accountNo' => $this->otherUser2->accountNo, 'fullName' => 'Test Line Manager');

        // Check for valid response to adding invalid user
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'accountFields', 'newEntry' => array(
                0 => $accountNoInvalid, 
                1 => $accountType1, 
                2 => 'TestA', 
                3 => 'TestB', 
                4 => $schoolObj, 
                5 => $superiorNo1)
            )
        );
        $response->assertStatus(500);

        // Checking new account wasn't added
        $this->assertFalse(
            Account::where('accountNo', $accountNoInvalid)
                ->where('accountType', $accountType1['db_name'])
                ->where('lName', 'TestA')
                ->where('fName', 'TestB')
                ->where('superiorNo', $superiorNo1['accountNo'])
                ->where('schoolId', $schoolObj['schoolId'])->exists()     
        ); 

        // Check for valid response to adding user w/ taken accountNo
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'accountFields', 'newEntry' => array(
                0 => $accountNoTaken, 
                1 => $accountType1, 
                2 => 'TestA', 
                3 => 'TestB', 
                4 => $schoolObj, 
                5 => $superiorNo1)
            )
        );
        $response->assertStatus(500);

        // Checking new account wasn't added
        $this->assertFalse(
            Account::where('accountNo', $accountNoTaken)
                ->where('accountType', $accountType1['db_name'])
                ->where('lName', 'TestA')
                ->where('fName', 'TestB')
                ->where('superiorNo', $superiorNo1['accountNo'])
                ->where('schoolId', $schoolObj['schoolId'])->exists()     
        ); 

        // Removing account and school created for this test. (If the invalid accounts were somehow added)
        Account::where('accountNo', $accountNoInvalid)->delete();
        Account::where('accountNo', $accountNoTaken)->where('lname', 'TestA')->delete();
        School::where('schoolId', $tempSchool->schoolId)->delete();
    } 
    
    public function test_api_request_for_addentry_adding_valid_accountrole(): void
    {
        // Creating data to add to for test
        $accountObj = array('accountNo' => $this->otherUser1->accountNo, 'fullName' => 'Test Account');

        $tempRole = Role::create(['name' => 'Tester']);
        $roleObj = array('roleId' => $tempRole->roleId, 'name' => $tempRole->name);

        $tempUnit = Unit::create(['unitId' => 'ABCD1234', 'name' => 'Testing Tester\'s Tests']);
        $unitObj = array('unitId' => $tempUnit->unitId, 'disName' => $tempUnit->name);

        $tempMajor = Major::create(['majorId' => 'MJRU-ABCDE', 'name' => 'Test Testing']);
        $majorObj = array('majorId' => $tempMajor->majorId, 'disName' => $tempMajor->name);

        $tempCourse = Course::create(['courseId' => 'MC-ABCDEFG', 'name' => 'Masters of Testing']);
        $courseObj = array('courseId' => $tempCourse->courseId, 'disName' => $tempCourse->name);

        $tempSchool = School::create(['name' => 'School of Test']);
        $schoolObj = array('schoolId' => $tempSchool->schoolId, 'name' => $tempSchool->name);


        // Check for valid response to adding valid accountRole
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'accountRoleFields', 'newEntry' => array(
                0 => $accountObj, 
                1 => $roleObj, 
                2 => $unitObj, 
                3 => $majorObj, 
                4 => $courseObj, 
                5 => $schoolObj)
            )
        );
        $response->assertStatus(200);

        // Checking new accountRole added
        $this->assertTrue(
            AccountRole::where('accountNo', $accountObj['accountNo'])
                ->where('roleId', $roleObj['roleId'])
                ->where('unitId', $unitObj['unitId'])
                ->where('majorId', $majorObj['majorId'])
                ->where('courseId', $courseObj['courseId'])
                ->where('schoolId', $schoolObj['schoolId'])->exists()
        ); 

        // Removing accountRole and other entries created for this test.
        AccountRole::where('accountNo', $accountObj['accountNo'])->delete();
        Role::where('roleId', $tempRole->roleId)->delete();
        Unit::where('unitId', $tempUnit->unitId)->delete();
        Major::where('majorId', $tempMajor->majorId)->delete();
        Course::where('courseId', $tempCourse->courseId)->delete();
        School::where('schoolId', $tempSchool->schoolId)->delete();
    }  
}