<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\School;
use App\Models\ReminderTimeframe;
use Illuminate\Support\Facades\DB;

class AdminControllerTest extends TestCase
{
    private School $school;
    private Account $admin, $nonAdmin;
    private ReminderTimeframe $reminder;

    protected function setup(): void {
        parent::setup();

        $this->school = School::create([
            'schoolId' => 10,
            'name' => 'test'
        ]);

        $this->reminder = ReminderTimeframe::create([
            'schoolId' => $this->school->schoolId,
            'timeframe' => '1 week'
        ]);

        $this->admin = Account::factory()->create([
            'schoolId' => $this->school->schoolId,
            'accountType' => 'sysAdmin'
        ]);

        $this->nonAdmin = Account::factory()->create([
            'schoolId' => $this->school->schoolId,
            'accountType' => 'staff'
        ]);
    }

    protected function teardown(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->delete();
        Account::where('accountNo', $this->admin->accountNo)->delete();
        Account::where('accountNo', $this->nonAdmin->accountNo)->delete();
        DB::table('schools')->where('schoolId', $this->school->schoolId)->delete();

        parent::teardown();
    }

    public function test_getReminderTimeframe_is_successful(): void {
        $response = $this->actingAs($this->admin)->get("/api/getReminderTimeframe/{$this->admin->accountNo}");
        $response->assertStatus(200);
    }

    public function test_getReminderTimeframe_is_unsuccessful_invalid_accountNo(): void {
        $response = $this->actingAs($this->admin)->get("/api/getReminderTimeframe/3q40godfjk");
        $response->assertStatus(500);
    }


    public function test_setReminderTimeframe_is_successful(): void {
        $response = $this->actingAs($this->admin)->postJson("/api/setReminderTimeframe", [
            'accountNo' => $this->admin->accountNo,
            'timeframe' => '1 day'
        ]);
        $response->assertStatus(200);
    }

    public function test_setReminderTimeframe_is_unsuccessful_account_not_admin(): void {
        $response = $this->actingAs($this->nonAdmin)->postJson("/api/setReminderTimeframe", [
            'accountNo' => $this->nonAdmin->accountNo,
            'timeframe' => '1 day'
        ]);
        $response->assertStatus(500); // change to 401 after merge
    }

    public function test_setReminderTimeframe_is_unsuccessful_invalid_timeframe(): void {
        $response = $this->actingAs($this->admin)->postJson("/api/setReminderTimeframe", [
            'accountNo' => $this->admin->accountNo,
            'timeframe' => '1 days'
        ]);
        $response->assertStatus(500);
    }
}
