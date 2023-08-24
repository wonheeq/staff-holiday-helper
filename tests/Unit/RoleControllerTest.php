<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Http\Controllers\RoleController;
class RoleControllerTest extends TestCase
{
    private Account $user;
    private $accountRoles;

    protected function setup(): void {
        parent::setup();

        $this->user = Account::factory()->create();

        $this->accountRoles = array();
        
        // assign Course Coordinator to user
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => Role::where('name', "Course Coordinator")->first()->roleId
        ]));

        // assign Major Coordinator to user
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => Role::where('name', "Major Coordinator")->first()->roleId
        ]));

        // assign Unit Coordinator to user
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => Role::where('name', "Unit Coordinator")->first()->roleId
        ]));

        // assign Tutor to user
        array_push($this->accountRoles, AccountRole::factory()->create([
            'accountNo' => $this->user->accountNo,
            'roleId' => Role::where('name', "Tutor")->first()->roleId
        ]));
    }

    protected function teardown(): void {
        AccountRole::where('accountNo', $this->user['accountNo'])->delete();
        $this->user->delete();
        parent::teardown();
    }



    public function test_getRoleFromAccountRoleId_returns_valid_content_for_course_coordinator(): void
    {
        $accRole = $this->accountRoles[0];
        $result = app(RoleController::class)->getRoleFromAccountRoleId($accRole->accountRoleId);

        $course = Course::where('courseId',  $accRole->courseId)->first();

        $this->assertTrue($result == "{$accRole->courseId} {$course->name} - Course Coordinator");
    }

    public function test_getRoleFromAccountRoleId_returns_valid_content_for_major_coordinator(): void
    {
        $accRole = $this->accountRoles[1];
        $result = app(RoleController::class)->getRoleFromAccountRoleId($accRole->accountRoleId);

        $major = Major::where('majorId',  $accRole->majorId)->first();
        $this->assertTrue($result == "{$accRole->majorId} {$major->name} - Major Coordinator");
    }

    public function test_getRoleFromAccountRoleId_returns_valid_content_for_unit_coordinator(): void
    {
        $accRole = $this->accountRoles[2];
        $result = app(RoleController::class)->getRoleFromAccountRoleId($accRole->accountRoleId);

        $unit = Unit::where('unitId',  $accRole->unitId)->first();

        $this->assertTrue($result == "{$accRole->unitId} {$unit->name} - Unit Coordinator");
    }

    public function test_getRoleFromAccountRoleId_returns_valid_content_for_other(): void
    {
        $accRole = $this->accountRoles[3];
        $result = app(RoleController::class)->getRoleFromAccountRoleId($accRole->accountRoleId);

        $unit = Unit::where('unitId',  $accRole->unitId)->first();
        $role = Role::where('roleId', $accRole->roleId)->first();
        $this->assertTrue($result == "{$accRole->unitId} {$unit->name} - {$role->name}");
    }

    public function test_getRoleFromAccountRoleId_returns_INVALID_for_invalid_content(): void
    {
        $result = app(RoleController::class)->getRoleFromAccountRoleId(-54634);
        $this->assertTrue($result == "INVALID");
    }
}
