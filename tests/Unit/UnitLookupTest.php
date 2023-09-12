<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Unit;
use App\Models\AccountRole;
use App\Models\Account;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Application;
use App\Models\Nomination;


class UnitLookupTest extends TestCase
{
    private Account $adminUser, $otherUser1, $otherUser2;

    protected function setup(): void
    {
        parent::setup();

        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);
        // create test accounts
        $this->createAccount("000000b"); // UnitCoord
        $this->createAccount("000000c"); // MajorCoord
        $this->createAccount("000000d"); // CourseCoord
        $this->createAccount("000000e"); // Lecturer1
        $this->createAccount("000000f"); // Lecturer2
        $this->createAccount("000000g"); // NOMINATION ACCOUNT

        // create test units
        Unit::where('unitId', 'AAAA0000')->delete();
        Unit::create([
            'unitId' => 'AAAA0000',
            'name' => 'tempName',
        ]);

        // has no roles, for testing results without roles
        Unit::create([
            'unitId' => 'BBBB0000',
            'name' => 'tempName',
        ]);

        // create roles for each account
        $this->createAccountRole("000000b", 1); //UC
        $this->createAccountRole("000000c", 2); //MC
        $this->createAccountRole("000000d", 3); //CC
        $this->createAccountRole("000000e", 4); //L1
        $this->createAccountRole("000000f", 4); //L2
    }



    protected function tearDown(): void
    {
        $this->adminUser->delete();
        // delete any leftover applications
        $this->deleteNominations('000000g');
        $this->deleteAllApplications();

        // delete created roles
        $this->deleteAccountRole('000000b', 1);
        $this->deleteAccountRole('000000c', 2);
        $this->deleteAccountRole('000000d', 3);
        $this->deleteAccountRole('000000e', 4);
        $this->deleteAccountRole('000000f', 4);

        // delete created accounts
        Account::where('accountNo', '000000b')->delete();
        Account::where('accountNo', '000000c')->delete();
        Account::where('accountNo', '000000d')->delete();
        Account::where('accountNo', '000000e')->delete();
        Account::where('accountNo', '000000f')->delete();
        Account::where('accountNo', '000000g')->delete();

        // delete test unit
        Unit::where('unitId', 'AAAA0000')->delete();
        Unit::where('unitId', 'BBBB0000')->delete();

        parent::tearDown();
    }



    // Test for correct behavior with a valid unit, and no substitions
    public function test_lookup_valid_unit_no_subs(): void
    {
        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check structure
        $response->assertJsonStructure([
            'unitId',
            'unitName',
            'courseCoord',
            'majorCoord',
            'unitCoord',
            'lecturers'
        ]);

        // check data
        $response->assertJsonPath('unitId', 'AAAA0000');
        $response->assertJsonPath('unitName', 'tempName');
        $response->assertJsonPath('courseCoord', array('000000d@curtin.edu.au', 'Static Test User'));
        $response->assertJsonPath('majorCoord', array('000000c@curtin.edu.au', 'Static Test User'));
        $response->assertJsonPath('unitCoord', array('000000b@curtin.edu.au', 'Static Test User'));
        $response->assertJsonPath('lecturers', array(
            array('000000e@curtin.edu.au', 'Static Test User'),
            array('000000f@curtin.edu.au', 'Static Test User')
        ));
    }



    // Test for correct behaviour with a valid unit, and substitutes for all roles
    public function test_lookup_valid_unit_all_sub(): void
    {
        // create subs for eahc role
        $this->createSub('000000b', 1);
        $this->createSub('000000c', 2);
        $this->createSub('000000d', 3);
        $this->createSub('000000e', 4);
        $this->createSub('000000f', 4);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check structure
        $response->assertJsonStructure([
            'unitId',
            'unitName',
            'courseCoord',
            'majorCoord',
            'unitCoord',
            'lecturers'
        ]);

        // check that the email for the substitute was returned (000000g), not the orignals (b/c/d/e/f)
        $response->assertJsonPath('unitId', 'AAAA0000');
        $response->assertJsonPath('unitName', 'tempName');
        $response->assertJsonPath('courseCoord', array('000000g@curtin.edu.au', 'Static Test User'));
        $response->assertJsonPath('majorCoord', array('000000g@curtin.edu.au', 'Static Test User'));
        $response->assertJsonPath('unitCoord', array('000000g@curtin.edu.au', 'Static Test User'));
        $response->assertJsonPath('lecturers', array(
            array('000000g@curtin.edu.au', 'Static Test User'),
            array('000000g@curtin.edu.au', 'Static Test User')
        ));

        $this->deleteNominations('000000g');
        $this->deleteAllApplications();
    }



    // Test for correct behaviour with a valid unit,
    // and a valid substitute for only the unit coordinator
    public function test_lookup_valid_unit_valid_unitCoord_sub(): void
    {
        // create a valid substution
        $this->createSub('000000b', 1);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check that the email for the substitute is returned (000000g, not the original b).
        $response->assertJsonPath('unitCoord', array('000000g@curtin.edu.au', 'Static Test User'));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000b');
    }



    // Test for correct behaviour with a valid unit,
    // and a valid substute only for the major coordinator
    public function test_lookup_valid_unit_valid_majorCoord_sub(): void
    {
        // create a valid substution
        $this->createSub('000000c', 2);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check that the email for the substitute is returned (000000g, not the original c).
        $response->assertJsonPath('majorCoord', array('000000g@curtin.edu.au', 'Static Test User'));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000c');
    }



    // Test for correct behaviour with a valid unit,
    // and a valid substute only for the course coordinator
    public function test_lookup_valid_unit_valid_courseCoord_sub(): void
    {
        // create a valid substution
        $this->createSub('000000d', 3);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check that the email for the substitute is returned (000000g, not the original d).
        $response->assertJsonPath('courseCoord', array('000000g@curtin.edu.au', 'Static Test User'));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000d');
    }



    // Test for correct behaviour with a valid unit,
    // and a valid substute only for a lecturer
    public function test_lookup_valid_unit_Lecturer_sub(): void
    {
        // create valid sub
        $this->createSub('000000e', 4);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        $response->assertJsonPath('lecturers', array(
            // check that the email for the substitute is returned (000000g, not the original e).
            array('000000g@curtin.edu.au', 'Static Test User'),
            array('000000f@curtin.edu.au', 'Static Test User')
        ));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000e');
    }



    // Test for correct behaviour with an invalid unit code
    public function test_lookup_invalid_unit(): void
    {
        // assert fails validation
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'thisIsNotAValidCode'
        ])->assertStatus(302);
    }



    // Test for correct behaviour with no unit code
    public function test_lookup_no_unit(): void
    {
        // assert fails validation
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => ''
        ])->assertStatus(302);
    }



    // Test for correct behaviour with a "valid" unit code,
    // where the unit does not actually exist.
    public function test_lookup_valid_but_nonexistent_unit(): void
    {
        // unit matches regex but doesn't exist
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'CCCC0000'
        ])->assertStatus(500);

        // assert has json
        $this->assertJson($response->content());

        // assert has error
        $response->assertJson([
            'error' => 'Unit not found',
        ]);
    }



    // Test for correct behaviour when there exist valid substitutes for the major coordinator
    // role, but the relevant applications are invalid
    public function test_lookup_valid_unit_invalid_majorCoord_sub(): void
    {
        // create two invalid applications.
        // One where the leave isn't active, one where the application isn't accepted
        $this->createSubAppBadTime('000000c', 2);
        $this->createSubAppNotAcc('000000c', 2);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check that there was no sub ( email is still 000000c, not g)
        $response->assertJsonPath('majorCoord', array('000000c@curtin.edu.au', 'Static Test User'));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000c');
    }



    // Test for correct behaviour when there exist valid substitutes for the course coordinator
    // role, but the relevant applications are invalid
    public function test_lookup_valid_unit_invalid_courseCoord_sub(): void
    {
        // create two invalid applications.
        // One where the leave isn't active, one where the application isn't accepted
        $this->createSubAppBadTime('000000d', 3);
        $this->createSubAppNotAcc('000000d', 3);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check that there was no sub ( email is still 000000d, not g)
        $response->assertJsonPath('courseCoord', array('000000d@curtin.edu.au', 'Static Test User'));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000d');
    }



    // Test for correct behaviour when there exist valid substitutes for the unit coordinator
    // role, but the relevant applications are invalid
    public function test_lookup_valid_unit_invalid_unitCoord_sub(): void
    {
        // create two invalid applications.
        // One where the leave isn't active, one where the application isn't accepted
        $this->createSubAppBadTime('000000b', 1);
        $this->createSubAppNotAcc('000000b', 1);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check that there was no sub ( email is still 000000b, not g)
        $response->assertJsonPath('unitCoord', array('000000b@curtin.edu.au', 'Static Test User'));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000b');
    }



    // Test for correct behaviour when there exist valid substitutes for a lecturer role
    // role, but the relevant applications are invalid
    public function test_lookup_valid_unit_invalid_lecturer_sub(): void
    {
        // create two invalid applications.
        // One where the leave isn't active, one where the application isn't accepted
        $this->createSubAppBadTime('000000e', 4);
        $this->createSubAppNotAcc('000000e', 4);

        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);

        // check that there was no sub ( email is still 000000e, not g)
        $response->assertJsonPath('lecturers', array(
            array('000000e@curtin.edu.au', 'Static Test User'),
            array('000000f@curtin.edu.au', 'Static Test User')
        ));

        $this->deleteNominations('000000g');
        $this->deleteApplications('000000e');
    }


    public function test_lookup_valid_unit_no_staff_for_role(): void
    {
        // check response code
        $response = $this->actingAs($this->adminUser)->post('/api/getUnitDetails', [
            'code' => 'BBBB0000'
        ])->assertStatus(200);

        // check structure
        $response->assertJsonStructure([
            'unitId',
            'unitName',
            'courseCoord',
            'majorCoord',
            'unitCoord',
            'lecturers'
        ]);

        // check that nothing is being returned
        $response->assertJsonPath('unitId', 'BBBB0000');
        $response->assertJsonPath('unitName', 'tempName');
        $response->assertJsonPath('courseCoord', '');
        $response->assertJsonPath('majorCoord', '');
        $response->assertJsonPath('unitCoord', '');
        $response->assertJsonPath('lecturers', array());
    }



    // ------------------------ HELPER FUNCTIONS, NO TESTS FROM HERE ------------------------------ //

    // delete the account role for the given details
    private function deleteAccountRole($accountNo, $roleId): void
    {
        AccountRole::where([
            ['accountNo', '=', $accountNo],
            ['roleId', '=', $roleId],
            ['unitId', '=', 'AAAA0000']
        ])->delete();
    }



    // create an account for the given number
    private function createAccount($accountNo): void
    {
        // Account::where('accountNo', $accountNo)->delete();
        Account::factory()->create([
            'accountNo' => $accountNo,
            'fName' => 'Static',
            'lName' => 'Test User',
            'password' => Hash::make('testPassword1'),
            'superiorNo' => "000002L",
        ]);
    }



    // create an account role for the given details
    private function createAccountRole($accountNo, $roleId): void
    {
        AccountRole::create([
            'accountNo' => $accountNo,
            'roleId' => $roleId,
            'unitId' => 'AAAA0000',
            'majorId' => fake()->randomElement(Major::pluck('majorId')),
            'courseId' => fake()->randomElement(Course::pluck('courseId')),
            'schoolId' => fake()->randomElement(School::pluck('schoolId')),
        ]);
    }



    // create a valid and accepted nomination for the given details
    private function createAcceptedNomination($applicationNo, $accountRoleId)
    {
        Nomination::create([
            'applicationNo' => $applicationNo,
            'accountRoleId' => $accountRoleId,
            'nomineeNo' => '000000g',
            'status' => 'Y'
        ]);
    }



    // create a valid and accepted leave application for the given account
    private function createActiveApplication($accountNo): Application
    {
        $start = new DateTime('NOW');
        $start->modify("-1 day");

        $end = new DateTime('NOW');
        $end->modify('+2 days');

        $application = Application::create([
            'accountNo' => $accountNo,
            'status' => 'Y',
            'sDate' => $start,
            'eDate' => $end,
            'processedBy' => $accountNo,
            'rejectReason' => fake()->randomElement(['Not enough leave remaining', 'A nominee declined to takeover a responsibility', 'Invalid nomination details']),
        ]);
        return $application;
    }



    // delete all applications for a given account
    private function deleteApplications($accountNo): void
    {
        Application::where([
            ['accountNo', '=', $accountNo],
            ['processedBy', '=', $accountNo]
        ])->delete();
    }



    // delete all nominations for a given account
    private function deleteNominations($accountNo): void
    {
        Nomination::where('nomineeNo', $accountNo)->delete();
    }


    // delete all applications for all accounts
    private function deleteAllApplications(): void
    {
        $this->deleteApplications('000000b');
        $this->deleteApplications('000000c');
        $this->deleteApplications('000000d');
        $this->deleteApplications('000000e');
        $this->deleteApplications('000000f');
    }


    // create a valid substute for the given details
    private function createSub($accountNo, $roleId): void
    {
        $application = $this->createActiveApplication($accountNo);
        $accountRoleId = AccountRole::where([
            ['accountNo', '=', $accountNo],
            ['roleId', '=', $roleId],
            ['unitId', '=', 'AAAA0000']
        ])->value('accountRoleId');

        $this->createAcceptedNomination($application->applicationNo, $accountRoleId);
    }



    // create an a substitute in an application where the leave period is not currently underway
    private function createSubAppBadTime($accountNo, $roleId)
    {
        $application = $this->createTimeInactiveApplication($accountNo);
        $accountRoleId = AccountRole::where([
            ['accountNo', '=', $accountNo],
            ['roleId', '=', $roleId],
            ['unitId', '=', 'AAAA0000']
        ])->value('accountRoleId');

        $this->createAcceptedNomination($application->applicationNo, $accountRoleId);
    }


    // create a substitute in an application that has not been accepted yet
    private function createSubAppNotAcc($accountNo, $roleId)
    {
        $application = $this->createStatusInactiveApplication($accountNo);
        $accountRoleId = AccountRole::where([
            ['accountNo', '=', $accountNo],
            ['roleId', '=', $roleId],
            ['unitId', '=', 'AAAA0000']
        ])->value('accountRoleId');

        $this->createAcceptedNomination($application->applicationNo, $accountRoleId);
    }



    // create an application where the leave period has not started
    private function createTimeInactiveApplication($accountNo): Application
    {
        $start = new DateTime('NOW');
        $start->modify("+2 day");

        $end = new DateTime('NOW');
        $end->modify('+5 days');

        $application = Application::create([
            'accountNo' => $accountNo,
            'status' => 'Y',
            'sDate' => $start,
            'eDate' => $end,
            'processedBy' => $accountNo,
            'rejectReason' => fake()->randomElement(['Not enough leave remaining', 'A nominee declined to takeover a responsibility', 'Invalid nomination details']),
        ]);
        return $application;
    }



    // create an application where the leave period is active, but application not accepted
    private function createStatusInactiveApplication($accountNo): Application
    {
        $start = new DateTime('NOW');
        $start->modify("-1 day");

        $end = new DateTime('NOW');
        $end->modify('+2 days');

        $application = Application::create([
            'accountNo' => $accountNo,
            'status' => 'N',
            'sDate' => $start,
            'eDate' => $end,
            'processedBy' => $accountNo,
            'rejectReason' => fake()->randomElement(['Not enough leave remaining', 'A nominee declined to takeover a responsibility', 'Invalid nomination details']),
        ]);
        return $application;
    }
}
