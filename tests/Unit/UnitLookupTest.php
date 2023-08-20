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

    protected function setup(): void
    {
        parent::setup();

        // create test accounts
        $this->createAccount("000000b"); // UnitCoord
        $this->createAccount("000000c"); // MajorCoord
        $this->createAccount("000000d"); // CourseCoord
        $this->createAccount("000000e"); // Lecturer1
        $this->createAccount("000000f"); // Lecturer2
        $this->createAccount("000000g"); // NOMINATION

        // create test unit
        Unit::where('unitId', 'AAAA0000')->delete();
        Unit::create([
            'unitId' => 'AAAA0000',
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
        $this->deleteAllNominations();
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

        Unit::where('unitId', 'AAAA0000')->delete();
        parent::tearDown();
    }

    private function deleteAllNominations(): void
    {
        $this->deleteNominations('000000b');
        $this->deleteNominations('000000c');
        $this->deleteNominations('000000d');
        $this->deleteNominations('000000e');
        $this->deleteNominations('000000f');
        $this->deleteNominations('000000g');
    }

    private function deleteNominations($accountNo): void
    {
        Nomination::where('nomineeNo', $accountNo)->delete();
    }

    private function deleteAllApplications(): void
    {
        $this->deleteApplications('000000b');
        $this->deleteApplications('000000c');
        $this->deleteApplications('000000d');
        $this->deleteApplications('000000e');
        $this->deleteApplications('000000f');
        $this->deleteApplications('000000g');
    }


    // Test using a valid unit with no substitutions
    public function test_lookup_valid_unit_no_subs(): void
    {
        // check response code
        $response = $this->post('/api/getUnitDetails', [
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

    private function createCoordSub($accountNo, $roleId): void
    {
        $application = $this->createActiveApplication('000000c');
        $accountRoleId = AccountRole::where([
            ['accountNo', '=', $accountNo],
            ['roleId', '=', $roleId],
            ['unitId', '=', 'AAAA0000']
        ])->value('accountRoleId');

        $this->createAcceptedNomination($application->applicationNo, $accountRoleId);

        // $this->deleteApplications('000000c');
    }

    private function createAcceptedNomination($applicationNo, $accountRoleId)
    {
        Nomination::create([
            'applicationNo' => $applicationNo,
            'accountRoleId' => $accountRoleId,
            'nomineeNo' => '000000g',
            'status' => 'Y'
        ]);
    }

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

    private function deleteApplications($accountNo): void
    {
        Application::where([
            ['accountNo', '=', $accountNo],
            ['processedBy', '=', $accountNo]
        ])->delete();
    }


    // Test using a valid unit and a coordinator substitute
    // (all coordinators types use the same logic for substitution checks)
    public function test_lookup_valid_unit_majorCoord_sub(): void
    {
        $this->createCoordSub('000000c', 2);

        // // check response code
        // $response = $this->post('/api/getUnitDetails', [
        //     'code' => 'AAAA0000'
        // ])->assertStatus(200);

        // // check structure
        // $response->assertJsonStructure([
        //     'unitId',
        //     'unitName',
        //     'courseCoord',
        //     'majorCoord',
        //     'unitCoord',
        //     'lecturers'
        // ]);

        // // check data
        // $response->assertJsonPath('unitId', 'AAAA0000');
        // $response->assertJsonPath('unitName', 'tempName');
        // $response->assertJsonPath('courseCoord', array('000000d@curtin.edu.au', 'Static Test User'));
        // $response->assertJsonPath('majorCoord', array('000000c@curtin.edu.au', 'Static Test User'));
        // $response->assertJsonPath('unitCoord', array('000000b@curtin.edu.au', 'Static Test User'));
        // $response->assertJsonPath('lecturers', array(
        //     array('000000e@curtin.edu.au', 'Static Test User'),
        //     array('000000f@curtin.edu.au', 'Static Test User')
        // ));
    }

    public function test_lookup_valid_unit_CourseCoord_sub(): void
    {
    }

    public function test_lookup_valid_unit_UnitCoord_sub(): void
    {
    }

    public function test_lookup_valid_unit_Lecturer_sub(): void
    {
    }

    public function test_lookup_valid_unit_all_sub(): void
    {
    }

    public function test_lookup_invalid_unit(): void
    {
        // assert fails validation
        $response = $this->post('/api/getUnitDetails', [
            'code' => 'thisIsNotAValidCode'
        ])->assertStatus(302);
    }

    public function test_lookup_no_unit(): void
    {
        // assert fails validation
        $response = $this->post('/api/getUnitDetails', [
            'code' => ''
        ])->assertStatus(302);
    }

    public function test_lookup_valid_but_nonexistent_unit(): void
    {
        // unit matches regex but doesn't exist
        $response = $this->post('/api/getUnitDetails', [
            'code' => 'BBBB0000'
        ])->assertStatus(500);

        // assert has json
        $this->assertJson($response->content());

        // assert has error
        $response->assertJson([
            'error' => 'Unit not found',
        ]);
    }



    private function deleteAccountRole($accountNo, $roleId): void
    {
        AccountRole::where([
            ['accountNo', '=', $accountNo],
            ['roleId', '=', $roleId],
            ['unitId', '=', 'AAAA0000']
        ])->delete();
    }

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
}
