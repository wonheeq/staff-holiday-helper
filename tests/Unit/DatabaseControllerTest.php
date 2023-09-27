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

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseControllerTest extends TestCase
{
    private Account $adminUser, $otherUser1, $otherUser2;
    private Array $validEntry, $validCSVEntry;

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

        $this->validCSVEntry = array('table' => 'add_roles.csv', 'entries' => array(
            0 => array('Role Name' => 'testRole'),
            1 => array('Role Name' => 'testRole'),
            2 => array('Role Name' => 'testRole')
        ));
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

        $response = $this->actingAs($this->otherUser1)->postJson("/api/addSingleEntry/{$this->otherUser1['accountNo']}", $this->validEntry);
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->postJson("/api/addSingleEntry/{$this->otherUser2['accountNo']}", $this->validEntry);
        $response->assertStatus(403);
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

    public function test_api_request_for_addentry_adding_valid_role(): void
    {
        $testName = 'Tester';

        // Check for valid response to adding valid Role
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'roleFields', 'newEntry' => array(0 => $testName))
        );
        $response->assertStatus(200);

        // Checking new Role added
        $this->assertTrue(Role::where('name', $testName)->exists()); 

        Role::where('name', $testName)->delete();
    }


    public function test_api_request_for_addentry_adding_valid_and_invalid_unit(): void
    {
        $tempUnit = Unit::create(['unitId' => 'ABCD1234', 'name' => 'Testing Tester\'s Tests']);
        $unitObjInserted = array('unitId' => $tempUnit->unitId, 'disName' => $tempUnit->name);

        $unitObjUninserted = array('unitId' => 'WXYZ6789', 'disName' => 'Other Testing Unit');

        // Check for valid response to adding valid Unit
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'unitFields', 'newEntry' => array(
                0 => $unitObjUninserted['disName'],
                1 => $unitObjUninserted['unitId'])
            )
        );
        $response->assertStatus(200);

        // Checking new Unit added
        $this->assertTrue(Unit::where('unitId', $unitObjUninserted['unitId'])
            ->where('name', $unitObjUninserted['disName'])->exists()); 

        // Check for valid response to adding invalid Unit (attempts to use already-taken unitId)
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'unitFields', 'newEntry' => array(
                0 => $unitObjUninserted['disName'],
                1 => $unitObjInserted['unitId'])
            )
        );
        $response->assertStatus(500);
        
        // Checking new Unit not added
        $this->assertFalse(Unit::where('unitId', $unitObjInserted['unitId'])
            ->where('name', $unitObjUninserted['disName'])->exists()); 

        // Removing added units
        Unit::where('unitId', $unitObjUninserted['unitId'])->delete();
        Unit::where('unitId', $tempUnit->unitId)->delete();
    }


    public function test_api_request_for_addentry_adding_valid_and_invalid_major(): void
    {
        $tempMajor = Major::create(['majorId' => 'MJRU-ABCDE', 'name' => 'Test Testing']);
        $majorObjInserted = array('majorId' => $tempMajor->majorId, 'disName' => $tempMajor->name);

        $majorObjUninserted = array('majorId' => 'MJRU-VWXYZ', 'disName' => 'Other Testing major');

        // Check for valid response to adding valid major
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'majorFields', 'newEntry' => array(
                0 => $majorObjUninserted['disName'],
                1 => $majorObjUninserted['majorId'])
            )
        );
        $response->assertStatus(200);

        // Checking new Major added
        $this->assertTrue(Major::where('majorId', $majorObjUninserted['majorId'])
            ->where('name', $majorObjUninserted['disName'])->exists()); 

        // Check for valid response to adding invalid major (attempts to use already-taken majorId)
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'majorFields', 'newEntry' => array(
                0 => $majorObjUninserted['disName'],
                1 => $majorObjInserted['majorId'])
            )
        );
        $response->assertStatus(500);
        
        // Checking new major not added
        $this->assertFalse(Major::where('majorId', $majorObjInserted['majorId'])
            ->where('name', $majorObjUninserted['disName'])->exists()); 

        // Removing added majors
        Major::where('majorId', $majorObjUninserted['majorId'])->delete();
        Major::where('majorId', $tempMajor->majorId)->delete();
    }


    public function test_api_request_for_addentry_adding_valid_and_invalid_course(): void
    {
        $tempCourse = Course::create(['courseId' => 'MC-ABCDEFG', 'name' => 'Masters of Testing']);
        $courseObjInserted = array('courseId' => $tempCourse->courseId, 'disName' => $tempCourse->name);

        $courseObjUninserted = array('courseId' => 'B-ABCD', 'disName' => 'Other Testing course');

        // Check for valid response to adding valid course
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'courseFields', 'newEntry' => array(
                0 => $courseObjUninserted['disName'],
                1 => $courseObjUninserted['courseId'])
            )
        );
        $response->assertStatus(200);

        // Checking new Course added
        $this->assertTrue(Course::where('courseId', $courseObjUninserted['courseId'])
            ->where('name', $courseObjUninserted['disName'])->exists()); 

        // Check for valid response to adding invalid course (attempts to use already-taken courseId)
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'courseFields', 'newEntry' => array(
                0 => $courseObjUninserted['disName'],
                1 => $courseObjInserted['courseId'])
            )
        );
        $response->assertStatus(500);
        
        // Checking new Course not added
        $this->assertFalse(Course::where('courseId', $courseObjInserted['courseId'])
            ->where('name', $courseObjUninserted['disName'])->exists()); 

        // Removing added courses
        Course::where('courseId', $courseObjUninserted['courseId'])->delete();
        Course::where('courseId', $tempCourse->courseId)->delete();
    }


    public function test_api_request_for_addentry_adding_valid_school(): void
    {
        $testName = 'School of Testing';

        // Check for valid response to adding valid School
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$this->adminUser['accountNo']}", 
            array('fields' => 'schoolFields', 'newEntry' => array(0 => $testName))
        );
        $response->assertStatus(200);

        // Checking new School added
        $this->assertTrue(School::where('name', $testName)->exists()); 

        School::where('name', $testName)->delete();
    }


    /**
     * Unit Tests for adding data through CSV files
     */
    public function test_api_request_for_getting_csv_templates_is_protected(): void
    {
        $validFileName = 'add_staffaccounts.csv';

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/getCSVTemplate/{$this->otherUser1['accountNo']}/{$validFileName}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/getCSVTemplate/{$this->otherUser2['accountNo']}/{$validFileName}");
        $response->assertStatus(403);
    }

    public function test_api_request_for_getting_csv_templates_returns_correct_files(): void
    {
        $validFileName = 'add_staffaccounts.csv';

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}"); 
        $response->assertDownload($validFileName);
        $response->assertStatus(Response::HTTP_OK); 
        //Log::info($response->getFile()->getContent());
        
        
        //Log::info(Storage::disk('public')->exists("csv_templates/$validFileName"));
        //Log::info(file_get_contents(public_path().'/csv_templates/'. $validFileName));
        $contents = $response->getFile()->getContent();
        $expected = file_get_contents(public_path().'/csv_templates/'. $validFileName);

        $this->assertEquals($expected, $contents);

        $validFileName = 'add_accountroles.csv';

        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}"); 
        $response->assertDownload($validFileName);
        $response->assertStatus(Response::HTTP_OK); 
        $contents = $response->getFile()->getContent();
        $expected = file_get_contents(public_path().'/csv_templates/'. $validFileName);


        $validFileName = 'add_roles.csv';

        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}"); 
        $response->assertDownload($validFileName);
        $response->assertStatus(Response::HTTP_OK); 
        $contents = $response->getFile()->getContent();
        $expected = file_get_contents(public_path().'/csv_templates/'. $validFileName);


        $validFileName = 'add_units.csv';

        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}"); 
        $response->assertDownload($validFileName);
        $response->assertStatus(Response::HTTP_OK); 
        $contents = $response->getFile()->getContent();
        $expected = file_get_contents(public_path().'/csv_templates/'. $validFileName);


        $validFileName = 'add_majors.csv';

        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}"); 
        $response->assertDownload($validFileName);
        $response->assertStatus(Response::HTTP_OK); 
        $contents = $response->getFile()->getContent();
        $expected = file_get_contents(public_path().'/csv_templates/'. $validFileName);


        $validFileName = 'add_courses.csv';

        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}"); 
        $response->assertDownload($validFileName);
        $response->assertStatus(Response::HTTP_OK); 
        $contents = $response->getFile()->getContent();
        $expected = file_get_contents(public_path().'/csv_templates/'. $validFileName);


        $validFileName = 'add_schools.csv';

        $response = $this->actingAs($this->adminUser)->getJson("/api/getCSVTemplate/{$this->adminUser['accountNo']}/{$validFileName}"); 
        $response->assertDownload($validFileName);
        $response->assertStatus(Response::HTTP_OK); 
        $contents = $response->getFile()->getContent();
        $expected = file_get_contents(public_path().'/csv_templates/'. $validFileName);
    }


    public function test_api_request_for_adding_csv_entries_is_protected(): void
    {
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $this->validCSVEntry);
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->postJson("/api/addEntriesFromCSV/{$this->otherUser1['accountNo']}", $this->validCSVEntry);
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->postJson("/api/addEntriesFromCSV/{$this->otherUser2['accountNo']}", $this->validCSVEntry);
        $response->assertStatus(403);
    }

    public function test_api_request_for_adding_valid_accounts_via_csv_entries(): void
    {
        $tempSchool = School::create(['name' => 'test school']);
        $testSuperiorNo = $this->otherUser2->accountNo;

        $account1 = array(
            'Account Number (Staff ID)' => '123456f',
            'Account Type' => 'staff',
            'Surname' => 'testlast',
            'First/Other Names' => 'test fore',
            'School Code' => $tempSchool->schoolId,
            'Line Manager\'s ID' => $testSuperiorNo,
        );
        $account2 = array(
            'Account Number (Staff ID)' => '123456g',
            'Account Type' => 'lmanager',
            'Surname' => 'testlast',
            'First/Other Names' => 'test fore',
            'School Code' => $tempSchool->schoolId,
            'Line Manager\'s ID' => $testSuperiorNo,
        );
        $account3 = array(
            'Account Number (Staff ID)' => '123456h',
            'Account Type' => 'sysadmin',
            'Surname' => 'testlast',
            'First/Other Names' => 'test fore',
            'School Code' => $tempSchool->schoolId,
            'Line Manager\'s ID' => $testSuperiorNo,
        );

        $testCSVEntry = array('table' => 'add_staffaccounts.csv', 'entries' => array(
            0 => $account1,
            1 => $account2,
            2 => $account3
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        Log::info($response->getContent());
        $response->assertStatus(200);

        // Check entries have been added
        // Checking new account added
        $this->assertTrue(
            Account::where('accountNo', '123456f')
                ->where('accountType', 'staff')
                ->where('lName', 'testlast')
                ->where('fName', 'test fore')
                ->where('superiorNo', $testSuperiorNo)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 
        $this->assertTrue(
            Account::where('accountNo', '123456g')
                ->where('accountType', 'lmanager')
                ->where('lName', 'testlast')
                ->where('fName', 'test fore')
                ->where('superiorNo', $testSuperiorNo)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 
        $this->assertTrue(
            Account::where('accountNo', '123456h')
                ->where('accountType', 'sysadmin')
                ->where('lName', 'testlast')
                ->where('fName', 'test fore')
                ->where('superiorNo', $testSuperiorNo)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        );

        Account::where('accountNo', '123456f')->delete();
        Account::where('accountNo', '123456g')->delete();
        Account::where('accountNo', '123456h')->delete();
        School::where('schoolId', $tempSchool->schoolId)->delete();
    }
}   