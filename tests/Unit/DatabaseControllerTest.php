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

use App\Models\Application;
use App\Models\Nomination;
use App\Models\Message;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseControllerTest extends TestCase
{
    private Account $adminUser, $otherUser1, $otherUser2;
    private School $tempSchoolMain;
    private Array $validEntry, $validCSVEntry;

    protected function setup(): void {
        parent::setup();

        $this->tempSchoolMain = School::create(['name' => 'main test school']);

        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin",
            'schoolId' => $this->tempSchoolMain->schoolId
        ]);

        $this->otherUser1 = Account::factory()->create([
            'accountType' => "staff",
            'schoolId' => $this->tempSchoolMain->schoolId
        ]);

        $this->otherUser2 = Account::factory()->create([
            'accountType' => "lmanager",
            'schoolId' => $this->tempSchoolMain->schoolId
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
        $this->tempSchoolMain->delete();

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
        $response->assertStatus(302);

        $response = $this->actingAs($this->otherUser2)->postJson("/api/addSingleEntry/{$this->otherUser2['accountNo']}", $this->validEntry);
        $response->assertStatus(302);
    }

    public function test_api_request_for_addentry_adding_valid_account(): void
    {
        // Creating data to add to for test
        $accountNo1 = '123456j';
        $accountType1 = array('db_name' => 'staff', 'name' => 'Staff');

        $tempSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();
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
        //School::where('schoolId', $tempSchool->schoolId)->delete();
    }     
    
    public function test_api_request_for_addentry_adding_invalid_account(): void
    {
        // Creating data to add to for test
        $accountNoInvalid = '123456jj'; // Invalid Syntax
        $accountNoTaken = $this->otherUser1->accountNo; // accountNo already in use

        $accountType1 = array('db_name' => 'staff', 'name' => 'Staff');

        $tempSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();
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
        Account::where('accountNo', $accountNoTaken)->where('lName', 'TestA')->delete();
        //School::where('schoolId', $tempSchool->schoolId)->delete();
    } 
    
    public function test_api_request_for_addentry_adding_valid_accountrole(): void
    {
        // Creating data to add to for test
        $accountObj = array('accountNo' => $this->otherUser1->accountNo, 'fullName' => 'Test Account');

        $tempRole = Role::create(['name' => 'Tester']);
        $roleObj = array('roleId' => $tempRole->roleId, 'name' => $tempRole->name);

        $tempUnit = Unit::create(['unitId' => 'ABCD1234', 'name' => 'Testing Tester\'s Tests']);
        $unitObj = array('unitId' => $tempUnit->unitId, 'disName' => $tempUnit->name);

        $tempMajor = Major::factory()->create();
        $majorObj = array('majorId' => $tempMajor->majorId, 'disName' => $tempMajor->name);

        $tempCourse = Course::create(['courseId' => 'MC-ABCDEFG', 'name' => 'Masters of Testing']);
        $courseObj = array('courseId' => $tempCourse->courseId, 'disName' => $tempCourse->name);

        $tempSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();
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
        $tempMajor = Major::factory()->create();
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

        // Only super administrators can create new schools
        $superUser = Account::where('schoolId', 1)->first();

        // Check for valid response to adding valid School
        $response = $this->actingAs($this->adminUser)->postJson("/api/addSingleEntry/{$superUser['accountNo']}", 
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
        $response->assertStatus(302);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/getCSVTemplate/{$this->otherUser2['accountNo']}/{$validFileName}");
        $response->assertStatus(302);
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
        $response->assertStatus(302);

        $response = $this->actingAs($this->otherUser2)->postJson("/api/addEntriesFromCSV/{$this->otherUser2['accountNo']}", $this->validCSVEntry);
        $response->assertStatus(302);
    }

    public function test_api_request_for_adding_valid_accounts_via_csv_entries(): void
    {
        $tempSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();
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
            'Line Manager\'s ID' => 'none', // 'none' should be a valid input
        );

        $testCSVEntry = array('table' => 'add_staffaccounts.csv', 'entries' => array(
            0 => $account1,
            1 => $account2,
            2 => $account3
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        //Log::info($response->getContent());
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
                ->where('superiorNo', null)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        );

        Account::where('accountNo', '123456f')->delete();
        Account::where('accountNo', '123456g')->delete();
        Account::where('accountNo', '123456h')->delete();
        //School::where('schoolId', $tempSchool->schoolId)->delete();
    }

    public function test_api_request_for_adding_invalid_accounts_via_csv_entries(): void
    {
        $tempSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();
        $testSuperiorNo = $this->otherUser2->accountNo;
        $existingStaffAccountNo = $this->otherUser1->accountNo;

        // Each account invalid in a different way.
        $account1 = array(
            'Account Number (Staff ID)' => $existingStaffAccountNo, // Account number already in use
            'Account Type' => 'staff',
            'Surname' => 'testlast',
            'First/Other Names' => 'test fore',
            'School Code' => $tempSchool->schoolId,
            'Line Manager\'s ID' => $testSuperiorNo,
        );
        $account2 = array(
            'Account Number (Staff ID)' => '123456g',
            'Account Type' => 'invalidtype', // Invalid account type
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
            'Line Manager\'s ID' => $existingStaffAccountNo, // Account is not a line manager
        );

        $testCSVEntry = array('table' => 'add_staffaccounts.csv', 'entries' => array(
            0 => $account1
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        //Log::info($response->getContent());
        $response->assertStatus(500);

        // Checking new account wasn't added
        $this->assertFalse(
            Account::where('accountNo', $existingStaffAccountNo)
                ->where('accountType', 'staff')
                ->where('lName', 'testlast')
                ->where('fName', 'test fore')
                ->where('superiorNo', $testSuperiorNo)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 

        $testCSVEntry['entries'] = array(
            0 => $account2
        );
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        //Log::info($response->getContent());
        $response->assertStatus(500);

        // Checking new account wasn't added
        $this->assertFalse(
            Account::where('accountNo', '123456g')
                ->where('accountType', 'invalidtype')
                ->where('lName', 'testlast')
                ->where('fName', 'test fore')
                ->where('superiorNo', $testSuperiorNo)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 


        $testCSVEntry['entries'] = array(
            0 => $account3
        );
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        //Log::info($response->getContent());
        $response->assertStatus(500);

        // Checking new account wasn't added
        $this->assertFalse(
            Account::where('accountNo', '123456h')
                ->where('accountType', 'sysadmin')
                ->where('lName', 'testlast')
                ->where('fName', 'test fore')
                ->where('superiorNo', $existingStaffAccountNo)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 

        Account::where('accountNo', $existingStaffAccountNo)->where('lName', 'testlast')->delete();
        Account::where('accountNo', '123456g')->delete();
        Account::where('accountNo', '123456h')->delete();
        //School::where('schoolId', $tempSchool->schoolId)->delete();
    }


    public function test_api_request_for_adding_valid_accountroles_via_csv_entries(): void
    {
        $tempSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();
        $tempRole = Role::create(['name' => 'test role']);
        $tempUnit = Unit::create(['unitId' => 'ABCD1234','name' => 'test unit']);
        $tempMajor = Major::factory()->create();
        $tempCourse = Course::create(['courseId' => 'MC-ABCDEFG','name' => 'test course']);
        $accountNo1 = $this->otherUser1->accountNo;
        $accountNo2 = $this->otherUser2->accountNo;

        $accountRole1 = array(
            'Account Number' => $accountNo1,
            'Role ID' => $tempRole->roleId,
            'Unit Code' => $tempUnit->unitId,
            'Major Code' => $tempMajor->majorId,
            'Course Code' => $tempCourse->courseId,
            'School Code' => $tempSchool->schoolId
        );
        $accountRole2 = array(
            'Account Number' => $accountNo1,
            'Role ID' => $tempRole->roleId,
            'Unit Code' => 'none',
            'Major Code' => 'none',
            'Course Code' => 'none',
            'School Code' => $tempSchool->schoolId
        );
        $accountRole3 = array(
            'Account Number' => $accountNo2,
            'Role ID' => $tempRole->roleId,
            'Unit Code' => $tempUnit->unitId,
            'Major Code' => $tempMajor->majorId,
            'Course Code' => $tempCourse->courseId,
            'School Code' => $tempSchool->schoolId
        );
        

        $testCSVEntry = array('table' => 'add_accountroles.csv', 'entries' => array(
            0 => $accountRole1,
            1 => $accountRole2,
            2 => $accountRole3
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        //Log::info($response->getContent());
        $response->assertStatus(200);

        // Check entries have been added
        $this->assertTrue(
            AccountRole::where('accountNo', $accountNo1)
                ->where('roleId', $tempRole->roleId)
                ->where('unitId', $tempUnit->unitId)
                ->where('majorId', $tempMajor->majorId)
                ->where('courseId', $tempCourse->courseId)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 
        $this->assertTrue(
            AccountRole::where('accountNo', $accountNo1)
                ->where('roleId', $tempRole->roleId)
                ->where('unitId', null)
                ->where('majorId', null)
                ->where('courseId', null)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 
        $this->assertTrue(
            AccountRole::where('accountNo', $accountNo2)
                ->where('roleId', $tempRole->roleId)
                ->where('unitId', $tempUnit->unitId)
                ->where('majorId', $tempMajor->majorId)
                ->where('courseId', $tempCourse->courseId)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 
        
        AccountRole::where('accountNo', $accountNo1)->delete();
        AccountRole::where('accountNo', $accountNo2)->delete();
        Role::where('roleId', $tempRole->roleId)->delete();
        Unit::where('unitId', $tempUnit->unitId)->delete();
        Major::where('majorId', $tempMajor->majorId)->delete();
        Course::where('courseId', $tempCourse->courseId)->delete();
        //School::where('schoolId', $tempSchool->schoolId)->delete();
    }

    public function test_api_request_for_adding_invalid_accountroles_via_csv_entries(): void
    {
        $tempSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();
        $tempRole = Role::create(['name' => 'test role']);
        $tempUnit = Unit::create(['unitId' => 'ABCD1234','name' => 'test unit']);
        $tempMajor = Major::factory()->create();
        $tempCourse = Course::create(['courseId' => 'MC-ABCDEFG','name' => 'test course']);
        $accountNo1 = $this->otherUser1->accountNo;

        $accountRole1 = array(
            'Account Number' => '999999z', // Invalid accountNo (doesn't exist (chances of being randomly generated is 1 in 26 million))
            'Role ID' => $tempRole->roleId,
            'Unit Code' => $tempUnit->unitId,
            'Major Code' => $tempMajor->majorId,
            'Course Code' => $tempCourse->courseId,
            'School Code' => $tempSchool->schoolId
        );
        $accountRole2 = array(
            'Account Number' => $accountNo1,
            'Role ID' => 'abc', // Invalid Role (Incorrect Syntax)
            'Unit Code' => $tempUnit->unitId,
            'Major Code' => $tempMajor->majorId,
            'Course Code' => $tempCourse->courseId,
            'School Code' => $tempSchool->schoolId
        );
        $accountRole3 = array(
            'Account Number' => $accountNo1,
            'Role ID' => $tempRole->roleId,
            'Unit Code' => 'XXXXOOOO', // Invalid Unit (Doesn't exist)
            'Major Code' => $tempMajor->majorId,
            'Course Code' => $tempCourse->courseId,
            'School Code' => $tempSchool->schoolId
        );
        

        $testCSVEntry = array('table' => 'add_accountroles.csv', 'entries' => array(
            0 => $accountRole1
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Checking new accountRole wasn't added
        $this->assertFalse(
            AccountRole::where('accountNo', '999999z')
                ->where('roleId', $tempRole->roleId)
                ->where('unitId', $tempUnit->unitId)
                ->where('majorId', $tempMajor->majorId)
                ->where('courseId', $tempCourse->courseId)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 

        $testCSVEntry['entries'] = array(
            0 => $accountRole2
        );

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Checking new accountRole wasn't added
        $this->assertFalse(
            AccountRole::where('accountNo', $accountNo1)
                ->where('roleId', 'abc')
                ->where('unitId', $tempUnit->unitId)
                ->where('majorId', $tempMajor->majorId)
                ->where('courseId', $tempCourse->courseId)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 

        $testCSVEntry['entries'] = array(
            0 => $accountRole3
        );

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Checking new accountRole wasn't added
        $this->assertFalse(
            AccountRole::where('accountNo', $accountNo1)
                ->where('roleId', $tempRole->roleId)
                ->where('unitId', 'XXXX0000')
                ->where('majorId', $tempMajor->majorId)
                ->where('courseId', $tempCourse->courseId)
                ->where('schoolId', $tempSchool->schoolId)->exists()     
        ); 
        
        AccountRole::where('accountNo', $accountNo1)->delete();
        AccountRole::where('accountNo', '999999z')->delete();
        Role::where('roleId', $tempRole->roleId)->delete();
        Unit::where('unitId', $tempUnit->unitId)->delete();
        Major::where('majorId', $tempMajor->majorId)->delete();
        Course::where('courseId', $tempCourse->courseId)->delete();
        //School::where('schoolId', $tempSchool->schoolId)->delete();
    }

    public function test_api_request_for_adding_valid_roles_and_schools_via_csv_entries(): void
    {
        // Roles
        $testRole1 = array(
            'Role Name' => 'test role 1',         
        );
        $testRole2 = array(
            'Role Name' => 'test role 2',         
        );

        $testCSVEntry = array('table' => 'add_roles.csv', 'entries' => array(
            0 => $testRole1,
            1 => $testRole2,
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(200);

        // Check entries have been added
        $this->assertTrue(
            Role::where('name', 'test role 1')->exists()     
        ); 
        $this->assertTrue(
            Role::where('name', 'test role 2')->exists()     
        ); 

        // Schools
        $testSchool1 = array(
            'School Name' => 'test school 1',         
        );
        $testSchool2 = array(
            'School Name' => 'test school 2',         
        );

        $testCSVEntry = array('table' => 'add_schools.csv', 'entries' => array(
            0 => $testSchool1,
            1 => $testSchool2,
        ));

        // Only super administrators can create new schools
        $superUser = Account::where('schoolId', 1)->first();
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$superUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(200);

        // Check entries have been added
        $this->assertTrue(
            School::where('name', 'test school 1')->exists()     
        ); 
        $this->assertTrue(
            School::where('name', 'test school 2')->exists()     
        ); 

        Role::where('name', 'test role 1')->delete();
        Role::where('name', 'test role 2')->delete();
        School::where('name', 'test school 1')->delete();
        School::where('name', 'test school 2')->delete();
    }


    public function test_api_request_for_adding_invalid_roles_and_schools_via_csv_entries(): void
    {
        // Roles
        $testRole1 = array(
            'Role Name' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',  // Value too long (>40 characters)       
        );

        $testCSVEntry = array('table' => 'add_roles.csv', 'entries' => array(
            0 => $testRole1
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries have been added
        $this->assertFalse(
            Role::where('name', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')->exists()     
        ); 

        // Schoole
        $testSchool1 = array( // Value too long (>70 characters)
            'School Name' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',         
        );


        $testCSVEntry = array('table' => 'add_schools.csv', 'entries' => array(
            0 => $testSchool1
        ));

        // Only super administrators can create new schools
        $superUser = Account::where('schoolId', 1)->first();
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$superUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries have been added
        $this->assertFalse(
            School::where('name', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')->exists()     
        ); 
        
        Role::where('name', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')->delete();
        School::where('name', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')->delete();
    }

    public function test_api_request_for_adding_valid_and_invalid_units_via_csv_entries(): void
    {
        $tempUnit = Unit::create(['unitId' => 'ABCD1234','name' => 'test unit']);

        // Valid units
        $testUnit1 = array(
            'Unit Code' => 'YYYY6666',
            'Unit Name' => 'test unit 1'        
        );
        $testUnit2 = array(
            'Unit Code' => 'XXXX5555',
            'Unit Name' => 'test unit 2'         
        );

        $testCSVEntry = array('table' => 'add_units.csv', 'entries' => array(
            0 => $testUnit1,
            1 => $testUnit2
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(200);

        // Check entries have been added
        $this->assertTrue(
            Unit::where('unitId', 'YYYY6666')->exists()     
        ); 
        $this->assertTrue(
            Unit::where('unitId', 'XXXX5555')->exists()     
        ); 

        Unit::where('unitId', 'YYYY6666')->delete();
        Unit::where('unitId', 'XXXX5555')->delete();


        // Invalid Units
        $testUnit1 = array(
            'Unit Code' => $tempUnit->unitId, // Invalid (Code already in use)
            'Unit Name' => 'test unit 1'        
        );
        $testUnit2 = array(
            'Unit Code' => 'XXXXX555', // Invalid (Code in invalid syntax)
            'Unit Name' => 'test unit 2'         
        );

        $testCSVEntry = array('table' => 'add_units.csv', 'entries' => array(
            0 => $testUnit1
        ));
    
        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries haven't been added
        $this->assertFalse(
            Unit::where('unitId', $tempUnit->unitId)->where('name', 'test unit 1')->exists()     
        ); 


        $testCSVEntry = array('table' => 'add_units.csv', 'entries' => array(
            0 => $testUnit2
        ));
    
        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries haven't been added
        $this->assertFalse(
            Unit::where('unitId', 'XXXXX555')->exists()     
        ); 
       

        Unit::where('unitId', 'XXXXX555')->delete();
        Unit::where('unitId', $tempUnit->unitId)->delete();
    }


    public function test_api_request_for_adding_valid_and_invalid_majors_via_csv_entries(): void
    {
        $tempMajor = Major::factory()->create();

        // Valid Majors
        $testMajor1 = array(
            'Major Code' => 'MJXU-QWERT',
            'Major Name' => 'test major 1'        
        );
        $testMajor2 = array(
            'Major Code' => 'MJRX-HIJKL',
            'Major Name' => 'test major 2'         
        );

        $testCSVEntry = array('table' => 'add_majors.csv', 'entries' => array(
            0 => $testMajor1,
            1 => $testMajor2
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(200);

        // Check entries have been added
        $this->assertTrue(
            Major::where('majorId', 'MJXU-QWERT')->exists()     
        ); 
        $this->assertTrue(
            Major::where('majorId', 'MJRX-HIJKL')->exists()     
        ); 

        Major::where('majorId', 'MJXU-QWERT')->delete();
        Major::where('majorId', 'MJRX-HIJKL')->delete();


        // Invalid Majors
        $testMajor1 = array(
            'Major Code' => $tempMajor->majorId, // Invalid (Code already in use)
            'Major Name' => 'test major 1'        
        );
        $testMajor2 = array(
            'Major Code' => 'MCXU-QWERT', // Invalid (Code in invalid syntax)
            'Major Name' => 'test major 2'         
        );

        $testCSVEntry = array('table' => 'add_majors.csv', 'entries' => array(
            0 => $testMajor1
        ));
    
        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries haven't been added
        $this->assertFalse(
            Major::where('majorId', $tempMajor->majorId)->where('name', 'test major 1')->exists()     
        ); 


        $testCSVEntry = array('table' => 'add_majors.csv', 'entries' => array(
            0 => $testMajor2
        ));
    
        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries haven't been added
        $this->assertFalse(
            Major::where('majorId', 'MCXU-QWERT')->exists()     
        ); 
       

        Major::where('majorId', 'MCXU-QWERT')->delete();
        Major::where('majorId', $tempMajor->majorId)->delete();
    }

    public function test_api_request_for_adding_valid_and_invalid_Courses_via_csv_entries(): void
    {
        $tempCourse = Course::create(['courseId' => 'MC-ABCDEFG','name' => 'test course']);

        // Valid Courses
        $testCourse1 = array(
            'Course Code' => 'F-HIJK',
            'Course Name' => 'test course 1'        
        );
        $testCourse2 = array(
            'Course Code' => 'XX-LMNOPQR',
            'Course Name' => 'test course 2'         
        );

        $testCSVEntry = array('table' => 'add_courses.csv', 'entries' => array(
            0 => $testCourse1,
            1 => $testCourse2
        ));
    
        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(200);

        // Check entries have been added
        $this->assertTrue(
            Course::where('courseId', 'F-HIJK')->exists()     
        ); 
        $this->assertTrue(
            Course::where('courseId', 'XX-LMNOPQR')->exists()     
        ); 

        Course::where('courseId', 'F-HIJK')->delete();
        Course::where('courseId', 'XX-LMNOPQR')->delete();


        // Invalid Courses
        $testCourse1 = array(
            'Course Code' => $tempCourse->courseId, // Invalid (Code already in use)
            'Course Name' => 'test course 1'        
        );
        $testCourse2 = array(
            'Course Code' => 'XXVLMNOPQR', // Invalid (Code in invalid syntax)
            'Course Name' => 'test course 2'         
        );

        $testCSVEntry = array('table' => 'add_courses.csv', 'entries' => array(
            0 => $testCourse1
        ));
    
        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries haven't been added
        $this->assertFalse(
            Course::where('courseId', $tempCourse->courseId)->where('name', 'test course 1')->exists()     
        ); 


        $testCSVEntry = array('table' => 'add_courses.csv', 'entries' => array(
            0 => $testCourse2
        ));
    
        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/addEntriesFromCSV/{$this->adminUser['accountNo']}", $testCSVEntry);
        $response->assertStatus(500);

        // Check entries haven't been added
        $this->assertFalse(
            Course::where('courseId', 'XXVLMNOPQR')->exists()     
        ); 
       

        Course::where('courseId', 'XXVLMNOPQR')->delete();
        Course::where('courseId', $tempCourse->courseId)->delete();
    }


    public function test_api_request_for_delete_entry_is_protected(): void
    {
        // Mock valid entry to be removed from db
        $testUnit1 = Unit::factory()->create([
            'unitId' => "TTTT5555"
        ]);

        $validRequest = array('table' => 'units', 'entryId' => $testUnit1->unitId);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $testUnit2 = Unit::factory()->create([
            'unitId' => "TTTT6666"
        ]);
        $validRequest = array('table' => 'units', 'entryId' => $testUnit2->unitId);

        $response = $this->actingAs($this->otherUser1)->postJson("/api/dropEntry/{$this->otherUser1['accountNo']}", $validRequest);
        $response->assertStatus(302);

        $testUnit3 = Unit::factory()->create([
            'unitId' => "TTTT7777"
        ]);
        $validRequest = array('table' => 'units', 'entryId' => $testUnit3->unitId);

        $response = $this->actingAs($this->otherUser2)->postJson("/api/dropEntry/{$this->otherUser2['accountNo']}", $validRequest);
        $response->assertStatus(302);

        $testUnit1->delete();
        $testUnit2->delete();
        $testUnit3->delete();
    }


    public function test_api_request_for_deleting_account(): void
    {
        // Mock valid account entry to be removed from db
        $testAccount = Account::factory()->create([
            'schoolId' => $this->tempSchoolMain->schoolId
        ]);

        // Mock accountRole using testAccount for testing onDeleteCascade()
        $testAccountRole = AccountRole::factory()->create([
            'accountNo' => $testAccount->accountNo
        ]);

        // Other entries that use testAccount for a foreign key
        $testNomination = Nomination::factory()->create([
            'nomineeNo' => $testAccount->accountNo
        ]);
        $testApplication = Application::factory()->create([
            'accountNo' => $testAccount->accountNo
        ]);
        $testMessage = Message::factory()->create([
            'senderNo' => $testAccount->accountNo
        ]);

        $validRequest = array('table' => 'accounts', 'entryId' => $testAccount->accountNo);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertFalse(
            Account::where('accountNo', $testAccount->accountNo)->exists()     
        ); 

        $this->assertFalse(
            AccountRole::where('accountNo', $testAccountRole->accountNo)->exists(),
            Nomination::where('nomineeNo', $testNomination->nomineeNo)->exists(),    
            Application::where('accountNo', $testApplication->accountNo)->exists(), 
            Message::where('senderNo', $testMessage->senderNo)->exists()
        );

        $testAccount->delete();
        $testAccountRole->delete();
        Nomination::where('nomineeNo', $testNomination->nomineeNo)
                    ->where('applicationNo', $testNomination->applicationNo)
                    ->where('accountRoleId', $testNomination->accountRoleId)->delete();
        $testApplication->delete();
        $testMessage->delete();
    }

    public function test_api_request_for_deleting_line_manager_account(): void
    {
        // Mock line manager account
        $testLMAccount = Account::factory()->create([
            'accountType' => 'lmanager',
            'schoolId' => $this->tempSchoolMain->schoolId
        ]);

        $testStaffAccount = Account::factory()->create([
            'accountType' => 'staff',
            'superiorNo' => $testLMAccount->accountNo,
            'schoolId' => $this->tempSchoolMain->schoolId
        ]);

        $validRequest = array('table' => 'accounts', 'entryId' => $testLMAccount->accountNo);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertFalse(
            Account::where('accountNo', $testLMAccount->accountNo)->exists()     
        ); 

        $this->assertTrue(
            Account::where('accountNo', $testStaffAccount->accountNo)
                   ->where('superiorNo', NULL)->exists()     
        );

        $testLMAccount->delete();
        $testStaffAccount->delete();
    } 

    public function test_api_request_for_deleting_application(): void
    {
        // Mock valid application entry to be removed from db
        $testApplication = Application::factory()->create();

        $validRequest = array('table' => 'applications', 'entryId' => $testApplication->applicationNo);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertFalse(
            Application::where('applicationNo', $testApplication->applicationNo)->exists()     
        ); 

        $testApplication->delete();
    }

    public function test_api_request_for_deleting_nomination(): void
    {
        // Mock valid nomination entry to be removed from db
        $testNomination = Nomination::factory()->create();

        $validRequest = array('table' => 'nominations', 'applicationNo' => $testNomination->applicationNo,
                              'nomineeNo' => $testNomination->nomineeNo, 'accountRoleId' => $testNomination->accountRoleId);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertFalse(
            Nomination::where('nomineeNo', $testNomination->nomineeNo)
                        ->where('applicationNo', $testNomination->applicationNo)
                        ->where('accountRoleId', $testNomination->accountRoleId)->exists()     
        ); 

        Nomination::where('nomineeNo', $testNomination->nomineeNo)
                    ->where('applicationNo', $testNomination->applicationNo)
                    ->where('accountRoleId', $testNomination->accountRoleId)->delete();
    }

    public function test_api_request_for_deleting_accountrole(): void
    {
        // Mock valid accountRole entry to be removed from db
        $testAccountRole = AccountRole::factory()->create();

        $validRequest = array('table' => 'accountRoles', 'entryId' => $testAccountRole->accountRoleId);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertFalse(
            AccountRole::where('accountRoleId', $testAccountRole->accountRoleId)->exists()     
        ); 

        $testAccountRole->delete();
    }

    public function test_api_request_for_deleting_role(): void
    {
        // Mock valid role entry to be removed from db
        $testRole = Role::create(['name' => 'test role']);


        $validRequest = array('table' => 'roles', 'entryId' => $testRole->roleId);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertFalse(
            Role::where('roleId', $testRole->roleId)->exists()     
        ); 

        $testRole->delete();
    }
    
    public function test_api_request_for_deleting_unit_major_and_course(): void
    {
        // Mock valid unit, major and course entry to be removed from db
        $testUnit = Unit::factory()->create();
        $testMajor = Major::factory()->create();
        $testCourse = Course::factory()->create();

        // Mock AccountRole to test nulling of the 3 FKs
        $testAccountRole = AccountRole::factory()->create([
            'unitId' => $testUnit->unitId,
            'majorId' => $testMajor->majorId,
            'courseId' => $testCourse->courseId
        ]);

        $validRequestU = array('table' => 'units', 'entryId' => $testUnit->unitId);
        $validRequestM = array('table' => 'majors', 'entryId' => $testMajor->majorId);
        $validRequestC = array('table' => 'courses', 'entryId' => $testCourse->courseId);

        // Check for valid responses
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequestU);
        $response->assertStatus(200);
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequestM);
        $response->assertStatus(200);
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequestC);
        $response->assertStatus(200);

        $this->assertFalse(
            Unit::where('unitId', $testUnit->unitId)->exists()     
        ); 
        $this->assertFalse(
            Major::where('majorId', $testMajor->majorId)->exists()     
        );
        $this->assertFalse(
            Course::where('courseId', $testCourse->courseId)->exists()     
        ); 

        $this->assertTrue(
            AccountRole::where('accountRoleId', $testAccountRole->accountRoleId)
                       ->where('unitId', NULL)
                       ->where('majorId', NULL)
                       ->where('courseId', NULL)->exists()
        );

        $testAccountRole->delete();
        $testUnit->delete();
        $testMajor->delete();
        $testCourse->delete();
    }

    public function test_api_request_for_deleting_valid_and_invalid_school(): void
    {
        // Mock valid school entry to be removed from db
        $testSchool = School::create(['name' => 'test school']);

        $validRequest = array('table' => 'schools', 'entryId' => $testSchool->schoolId);

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertFalse(
            School::where('schoolId', $testSchool->schoolId)->exists()     
        ); 

        $testSchool->delete();

        $inValidRequest = array('table' => 'schools', 'entryId' => 1); // School '1' should not be deleted

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/dropEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        $this->assertTrue(
            School::where('schoolId', 1)->exists()     
        ); 
    }


    public function test_api_request_for_edit_entry_is_protected(): void
    {
        // Mock valid entry to be edited in db
        $testUnit = Unit::factory()->create();

        $validRequest = array('table' => 'Units', 
        'entry' => array(
            'Unit Code' => $testUnit->unitId,
            'Unit Name' => 'different name'
        ), 
        'initialEntry' => array(
            'Unit Code' => $testUnit->unitId,
            'Unit Name' => $testUnit->name
        ));

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        Unit::where('unitId', $testUnit->unitId)->update(['name' => 'default']);

        $response = $this->actingAs($this->otherUser1)->postJson("/api/editEntry/{$this->otherUser1['accountNo']}", $validRequest);
        $response->assertStatus(302);

        Unit::where('unitId', $testUnit->unitId)->update(['name' => 'default']);

        $response = $this->actingAs($this->otherUser2)->postJson("/api/editEntry/{$this->otherUser2['accountNo']}", $validRequest);
        $response->assertStatus(302);

        $testUnit->delete();
    }

    public function test_api_request_for_edit_valid_account(): void
    {
        // Mock valid account to be edited in db
        $testAccount = Account::factory()->create([
            'accountType' => 'staff',
            'schoolId' => $this->tempSchoolMain->schoolId
        ]);
        $tempLMAccount = Account::factory()->create([
            'accountType' => 'lmanager',
            'schoolId' => $this->tempSchoolMain->schoolId
        ]);

        $validRequest = array('table' => 'Accounts', 
        'entry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => 'lmanager',
            'Surname' => 'test surname',
            'First/Other Names' => 'test forename',
            'School Code' => $testAccount->schoolId,
            'Line Manager' => $tempLMAccount->accountNo
        ), 
        'initialEntry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => $testAccount->accountType,
            'Surname' => $testAccount->lName,
            'First/Other Names' => $testAccount->fName,
            'School Code' => $testAccount->schoolId,
            'Line Manager' => $testAccount->superiorNo
        ));

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertTrue(
            Account::where('accountNo', $testAccount->accountNo)
                ->where('accountType', 'lmanager')
                ->where('lName', 'test surname')
                ->where('fName', 'test forename')
                ->where('schoolId', $testAccount->schoolId)
                ->where('superiorNo', $tempLMAccount->accountNo)->exists()
        );

        $testAccount->delete();
        $tempLMAccount->delete();
    }

    public function test_api_request_for_edit_invalid_account(): void
    {
        // Mock valid account to be edited in db
        $testAccount = Account::factory()->create([
            'accountType' => 'staff'
        ]);
        $tempLMAccount = Account::factory()->create([
            'accountType' => 'lmanager'
        ]);

        // Identical to current account
        $inValidRequest = array('table' => 'Accounts', 
        'entry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => $testAccount->accountType,
            'Surname' => $testAccount->lName,
            'First/Other Names' => $testAccount->fName,
            'School Code' => $testAccount->schoolId,
            'Line Manager' => $testAccount->superiorNo
        ), 
        'initialEntry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => $testAccount->accountType,
            'Surname' => $testAccount->lName,
            'First/Other Names' => $testAccount->fName,
            'School Code' => $testAccount->schoolId,
            'Line Manager' => $testAccount->superiorNo
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Account should be unchanged
        $this->assertTrue(
            Account::where('accountNo', $testAccount->accountNo)
                ->where('accountType', $testAccount->accountType)
                ->where('lName', $testAccount->lName)
                ->where('fName', $testAccount->fName)
                ->where('schoolId', $testAccount->schoolId)
                ->where('superiorNo', $testAccount->superiorNo)->exists()
        );

        // Trying to assign school id of 1
        $inValidRequest = array('table' => 'Accounts', 
        'entry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => $testAccount->accountType,
            'Surname' => $testAccount->lName,
            'First/Other Names' => $testAccount->fName,
            'School Code' => 1,
            'Line Manager' => $testAccount->superiorNo
        ), 
        'initialEntry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => $testAccount->accountType,
            'Surname' => $testAccount->lName,
            'First/Other Names' => $testAccount->fName,
            'School Code' => $testAccount->schoolId,
            'Line Manager' => $testAccount->superiorNo
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Account should be unchanged
        $this->assertFalse(
            Account::where('accountNo', $testAccount->accountNo)
                ->where('accountType', $testAccount->accountType)
                ->where('lName', $testAccount->lName)
                ->where('fName', $testAccount->fName)
                ->where('schoolId', 1)
                ->where('superiorNo', $testAccount->superiorNo)->exists()
        );
        $this->assertTrue(
            Account::where('accountNo', $testAccount->accountNo)
                ->where('accountType', $testAccount->accountType)
                ->where('lName', $testAccount->lName)
                ->where('fName', $testAccount->fName)
                ->where('schoolId', $testAccount->schoolId)
                ->where('superiorNo', $testAccount->superiorNo)->exists()
        );

        // Invalid syntax
        $inValidRequest = array('table' => 'Accounts', 
        'entry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => $testAccount->accountType,
            'Surname' => $testAccount->lName,
            'First/Other Names' => $testAccount->fName,
            'School Code' => $testAccount->schoolId,
            'Line Manager' => $this->otherUser1->accountNo // Not a line manager
        ), 
        'initialEntry' => array(
            'Account Number' => $testAccount->accountNo,
            'Account Type' => $testAccount->accountType,
            'Surname' => $testAccount->lName,
            'First/Other Names' => $testAccount->fName,
            'School Code' => $testAccount->schoolId,
            'Line Manager' => $testAccount->superiorNo
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Account should be unchanged
        $this->assertFalse(
            Account::where('accountNo', $testAccount->accountNo)
                ->where('accountType', $testAccount->accountType)
                ->where('lName', $testAccount->lName)
                ->where('fName', $testAccount->fName)
                ->where('schoolId', $testAccount->schoolId)
                ->where('superiorNo', $this->otherUser1->accountNo)->exists()
        );
        $this->assertTrue(
            Account::where('accountNo', $testAccount->accountNo)
                ->where('accountType', $testAccount->accountType)
                ->where('lName', $testAccount->lName)
                ->where('fName', $testAccount->fName)
                ->where('schoolId', $testAccount->schoolId)
                ->where('superiorNo', $testAccount->superiorNo)->exists()
        );

        $testAccount->delete();
        $tempLMAccount->delete();
    }

    public function test_api_request_for_edit_valid_and_invalid_role(): void
    {
        // Mock valid role to be edited in db
        $testRole = Role::factory()->create([
            'name' => 'default'
        ]);

        $validRequest = array('table' => 'Roles', 
        'entry' => array(
            'Role ID' => '999',
            'Role Name' => 'different name'
        ), 
        'initialEntry' => array(
            'Role ID' => $testRole->roleId,
            'Role Name' => $testRole->name
        ));

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertTrue(
            Role::where('roleId', '999')->where('name', 'different name')->exists()
        );

        $testRole = Role::where('roleId', '999')->first();

        // No change to Role
        $inValidRequest = array('table' => 'Roles', 
        'entry' => array(
            'Role ID' => $testRole->roleId,
            'Role Name' => $testRole->name
        ), 
        'initialEntry' => array(
            'Role ID' => $testRole->roleId,
            'Role Name' => $testRole->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Role should be unaffected
        $this->assertTrue(
            Role::where('roleId', $testRole->roleId)->where('name', $testRole->name)->exists()
        );

        // Invalid roleId
        $inValidRequest = array('table' => 'Roles', 
        'entry' => array(
            'Role ID' => 'a999', // Invalid id
            'Role Name' => $testRole->name
        ), 
        'initialEntry' => array(
            'Role ID' => $testRole->roleId,
            'Role Name' => $testRole->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Role should be unaffected
        $this->assertFalse(
            Role::where('roleId', 'a999')->where('name', $testRole->name)->exists()
        );
        $this->assertTrue(
            Role::where('roleId', $testRole->roleId)->where('name', $testRole->name)->exists()
        );  

        $testRole->delete();
    }

    public function test_api_request_for_edit_valid_and_invalid_unit(): void
    {
        // Mock valid unit to be edited in db
        $testUnit = Unit::factory()->create();

        $validRequest = array('table' => 'Units', 
        'entry' => array(
            'Unit Code' => 'ABCD1234',
            'Unit Name' => 'different name'
        ), 
        'initialEntry' => array(
            'Unit Code' => $testUnit->unitId,
            'Unit Name' => $testUnit->name
        ));

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertTrue(
            Unit::where('unitId', 'ABCD1234')->where('name', 'different name')->exists()
        );

        $testUnit = Unit::where('unitId', 'ABCD1234')->first();

        // No change to unit
        $inValidRequest = array('table' => 'Units', 
        'entry' => array(
            'Unit Code' => $testUnit->unitId,
            'Unit Name' => $testUnit->name
        ), 
        'initialEntry' => array(
            'Unit Code' => $testUnit->unitId,
            'Unit Name' => $testUnit->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Unit should be unaffected
        $this->assertTrue(
            Unit::where('unitId', $testUnit->unitId)->where('name', $testUnit->name)->exists()
        );

        // Invalid unitId
        $inValidRequest = array('table' => 'Units', 
        'entry' => array(
            'Unit Code' => '0000AAAA', // Invalid code
            'Unit Name' => $testUnit->name
        ), 
        'initialEntry' => array(
            'Unit Code' => $testUnit->unitId,
            'Unit Name' => $testUnit->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Unit should be unaffected
        $this->assertFalse(
            Unit::where('unitId', '0000AAAA')->where('name', $testUnit->name)->exists()
        );
        $this->assertTrue(
            Unit::where('unitId', $testUnit->unitId)->where('name', $testUnit->name)->exists()
        );  

        $testUnit->delete();
    }

    public function test_api_request_for_edit_valid_and_invalid_major(): void
    {
        // Mock valid major to be edited in db
        $testMajor = Major::factory()->create();

        $validRequest = array('table' => 'Majors', 
        'entry' => array(
            'Major Code' => 'MJXU-QWERT',
            'Major Name' => 'different name'
        ), 
        'initialEntry' => array(
            'Major Code' => $testMajor->majorId,
            'Major Name' => $testMajor->name
        ));

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertTrue(
            Major::where('majorId', 'MJXU-QWERT')->where('name', 'different name')->exists()
        );

        $testMajor = Major::where('majorId', 'MJXU-QWERT')->first();

        // No change to Major
        $inValidRequest = array('table' => 'Majors', 
        'entry' => array(
            'Major Code' => $testMajor->majorId,
            'Major Name' => $testMajor->name
        ), 
        'initialEntry' => array(
            'Major Code' => $testMajor->majorId,
            'Major Name' => $testMajor->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Major should be unaffected
        $this->assertTrue(
            Major::where('majorId', $testMajor->majorId)->where('name', $testMajor->name)->exists()
        );

        // Invalid majorId
        $inValidRequest = array('table' => 'Majors', 
        'entry' => array(
            'Major Code' => '00XU-QWERT', // Invalid code
            'Major Name' => $testMajor->name
        ), 
        'initialEntry' => array(
            'Major Code' => $testMajor->majorId,
            'Major Name' => $testMajor->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Major should be unaffected
        $this->assertFalse(
            Major::where('majorId', '00XU-QWERT')->where('name', $testMajor->name)->exists()
        );
        $this->assertTrue(
            Major::where('majorId', $testMajor->majorId)->where('name', $testMajor->name)->exists()
        );  

        $testMajor->delete();
    }

    public function test_api_request_for_edit_valid_and_invalid_course(): void
    {
        // Mock valid course to be edited in db
        $testCourse = Course::factory()->create();

        $validRequest = array('table' => 'Courses', 
        'entry' => array(
            'Course Code' => 'F-HIJK',
            'Course Name' => 'different name'
        ), 
        'initialEntry' => array(
            'Course Code' => $testCourse->courseId,
            'Course Name' => $testCourse->name
        ));

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertTrue(
            Course::where('courseId', 'F-HIJK')->where('name', 'different name')->exists()
        );

        $testCourse = Course::where('courseId', 'F-HIJK')->first();

        // No change to Course
        $inValidRequest = array('table' => 'Courses', 
        'entry' => array(
            'Course Code' => $testCourse->courseId,
            'Course Name' => $testCourse->name
        ), 
        'initialEntry' => array(
            'Course Code' => $testCourse->courseId,
            'Course Name' => $testCourse->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Course should be unaffected
        $this->assertTrue(
            Course::where('courseId', $testCourse->courseId)->where('name', $testCourse->name)->exists()
        );

        // Invalid courseId
        $inValidRequest = array('table' => 'Courses', 
        'entry' => array(
            'Course Code' => 'F=HIJK', // Invalid code
            'Course Name' => $testCourse->name
        ), 
        'initialEntry' => array(
            'Course Code' => $testCourse->courseId,
            'Course Name' => $testCourse->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // Course should be unaffected
        $this->assertFalse(
            Course::where('courseId', 'F=HIJK')->where('name', $testCourse->name)->exists()
        );
        $this->assertTrue(
            Course::where('courseId', $testCourse->courseId)->where('name', $testCourse->name)->exists()
        );  

        $testCourse->delete();
    }

    public function test_api_request_for_edit_valid_and_invalid_school(): void
    {
        // Mock valid school to be edited in db
        $testSchool = School::where('schoolId', $this->tempSchoolMain->schoolId)->first();

        $validRequest = array('table' => 'Schools', 
        'entry' => array(
            'School Code' => '999',
            'School Name' => 'different name'
        ), 
        'initialEntry' => array(
            'School Code' => $testSchool->schoolId,
            'School Name' => $testSchool->name
        ));

        // Check for valid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $validRequest);
        $response->assertStatus(200);

        $this->assertTrue(
            School::where('schoolId', '999')->where('name', 'different name')->exists()
        );

        $testSchool = School::where('schoolId', '999')->first();

        // No change to School
        $inValidRequest = array('table' => 'Schools', 
        'entry' => array(
            'School Code' => $testSchool->schoolId,
            'School Name' => $testSchool->name
        ), 
        'initialEntry' => array(
            'School Code' => $testSchool->schoolId,
            'School Name' => $testSchool->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // School should be unaffected
        $this->assertTrue(
            School::where('schoolId', $testSchool->schoolId)->where('name', $testSchool->name)->exists()
        );

        // Invalid schoolId
        $inValidRequest = array('table' => 'Schools', 
        'entry' => array(
            'School Code' => 'a999', // Invalid id
            'School Name' => $testSchool->name
        ), 
        'initialEntry' => array(
            'School Code' => $testSchool->schoolId,
            'School Name' => $testSchool->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // School should be unaffected
        $this->assertFalse(
            School::where('schoolId', 'a999')->where('name', $testSchool->name)->exists()
        );
        $this->assertTrue(
            School::where('schoolId', $testSchool->schoolId)->where('name', $testSchool->name)->exists()
        );  

        // Super Administrator should not be editable
        $superAdminSchool = School::where('schoolId', 1)->first();

        $inValidRequest = array('table' => 'Schools', 
        'entry' => array(
            'School Code' => 1,
            'School Name' => 'new name'
        ), 
        'initialEntry' => array(
            'School Code' => 1,
            'School Name' => $superAdminSchool->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // School should be unaffected
        $this->assertFalse(
            School::where('schoolId', 1)->where('name', 'new name')->exists()
        );
        $this->assertTrue(
            School::where('schoolId', 1)->where('name', $superAdminSchool->name)->exists()
        );
        
        // School Code should not be changed to 1.
        $inValidRequest = array('table' => 'Schools', 
        'entry' => array(
            'School Code' => 1,
            'School Name' => $testSchool->name
        ), 
        'initialEntry' => array(
            'School Code' => $testSchool->schoolId,
            'School Name' => $testSchool->name
        ));

        // Check for invalid response
        $response = $this->actingAs($this->adminUser)->postJson("/api/editEntry/{$this->adminUser['accountNo']}", $inValidRequest);
        $response->assertStatus(500);

        // School should be unaffected
        $this->assertFalse(
            School::where('schoolId', 1)->where('name', $testSchool->name)->exists()
        );
        $this->assertTrue(
            School::where('schoolId', $testSchool->schoolId)->where('name', $testSchool->name)->exists()
        );
            
        $testSchool->delete();
    }
}   