<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\School;
use App\Models\ReminderTimeframe;
use App\Models\Application;
use App\Models\Nomination;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateInterval;
use App\Console\Kernel;
class KernelTest extends TestCase
{
    private School $school;
    private Account $admin, $nonAdmin;
    private ReminderTimeframe $reminder;
    private Application $application;
    private $nominations;

    protected function setup(): void {
        parent::setup();

        $this->school = School::create([
            'schoolId' => 1,
            'name' => 'test'
        ]);

        $this->reminder = ReminderTimeframe::create([
            'schoolId' => $this->school->schoolId,
            'timeframe' => '1 day'
        ]);

        $this->admin = Account::factory()->create([
            'schoolId' => $this->school->schoolId,
            'accountType' => 'sysAdmin'
        ]);

        $this->nonAdmin = Account::factory()->create([
            'schoolId' => $this->school->schoolId,
            'accountType' => 'staff'
        ]);

        // generate datetime based off of NOW - 7 days
        $this->now = new DateTime();
        
        $interval = 'P7D';
        $this->now->sub(new DateInterval($interval));

        $this->application = Application::create([
            'status' => 'P',
            'accountNo' => $this->nonAdmin->accountNo,
            'created_at' => $this->now->format('Y-m-d H:i:s'),
            'updated_at' => $this->now->format('Y-m-d H:i:s')
        ]);

        $this->nominations = Nomination::factory(3)->create([
            'applicationNo' => $this->application->applicationNo,
            'nomineeNo' => $this->admin->accountNo,
            'status' => 'U',
            'created_at' => $this->now->format('Y-m-d H:i:s'),
            'updated_at' => $this->now->format('Y-m-d H:i:s')
        ]);
    }

    protected function teardown(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->delete();
        foreach ($this->nominations as $nomination) {
            Nomination::where('applicationNo', $nomination->applicationNo, "and")
            ->where('nomineeNo', $nomination->nomineeNo, "and")
            ->where('status', $nomination->status)->delete();
        }
        Application::where('applicationNo', $this->application->applicationNo)->delete();
        Account::where('accountNo', $this->admin->accountNo)->delete();
        Account::where('accountNo', $this->nonAdmin->accountNo)->delete();
        DB::table('schools')->where('schoolId', $this->school->schoolId)->delete();

        parent::teardown();
    }


    public function test_getReminderLists_is_successful_works_with_timeframe_1_day(): void {
        $date = new DateTime();
        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(count($reminderLists[$this->school->schoolId][$this->admin->accountNo]) == 1);
    
        // Set nominations created_at date so that the difference is less than the timeframe        
        Nomination::where('applicationNo', $this->application->applicationNo)->update([
            'created_at' => (new DateTime($this->application->created_at))->sub(new DateInterval('PT2H'))
        ]);

        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(!array_key_exists($this->school->schoolId, $reminderLists));
    }

    public function test_getReminderLists_is_successful_works_with_timeframe_2_days(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->update([
            'timeframe' => "2 days"
        ]);
        
        $date = new DateTime();
        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(count($reminderLists[$this->school->schoolId][$this->admin->accountNo]) == 1);
        
        // Set nominations created_at date so that the difference is less than the timeframe        
        Nomination::where('applicationNo', $this->application->applicationNo)->update([
            'created_at' => (new DateTime($this->application->created_at))->add(new DateInterval('P1DT2H'))
        ]);

        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(!array_key_exists($this->school->schoolId, $reminderLists));
    }

    public function test_getReminderLists_is_successful_works_with_timeframe_3_days(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->update([
            'timeframe' => "3 days"
        ]);
        
        $date = new DateTime();
        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(count($reminderLists[$this->school->schoolId][$this->admin->accountNo]) == 1);
        
        // Set nominations created_at date so that the difference is less than the timeframe        
        Nomination::where('applicationNo', $this->application->applicationNo)->update([
            'created_at' => (new DateTime($this->application->created_at))->add(new DateInterval('P2DT2H'))
        ]);

        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(!array_key_exists($this->school->schoolId, $reminderLists));
    }

    public function test_getReminderLists_is_successful_works_with_timeframe_4_days(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->update([
            'timeframe' => "4 days"
        ]);
        
        $date = new DateTime();
        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(count($reminderLists[$this->school->schoolId][$this->admin->accountNo]) == 1);
        
        // Set nominations created_at date so that the difference is less than the timeframe        
        Nomination::where('applicationNo', $this->application->applicationNo)->update([
            'created_at' => (new DateTime($this->application->created_at))->add(new DateInterval('P3DT2H'))
        ]);

        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(!array_key_exists($this->school->schoolId, $reminderLists));
    }

    public function test_getReminderLists_is_successful_works_with_timeframe_5_days(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->update([
            'timeframe' => "5 days"
        ]);
        
        $date = new DateTime();
        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(count($reminderLists[$this->school->schoolId][$this->admin->accountNo]) == 1);
        
        // Set nominations created_at date so that the difference is less than the timeframe        
        Nomination::where('applicationNo', $this->application->applicationNo)->update([
            'created_at' => (new DateTime($this->application->created_at))->add(new DateInterval('P4DT2H'))
        ]);

        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(!array_key_exists($this->school->schoolId, $reminderLists));
    }

    public function test_getReminderLists_is_successful_works_with_timeframe_6_days(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->update([
            'timeframe' => "6 days"
        ]);
        
        $date = new DateTime();
        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(count($reminderLists[$this->school->schoolId][$this->admin->accountNo]) == 1);
        
        // Set nominations created_at date so that the difference is less than the timeframe        
        Nomination::where('applicationNo', $this->application->applicationNo)->update([
            'created_at' => (new DateTime($this->application->created_at))->add(new DateInterval('P5DT2H'))
        ]);

        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(!array_key_exists($this->school->schoolId, $reminderLists));
    }

    public function test_getReminderLists_is_successful_works_with_timeframe_1_week(): void {
        ReminderTimeframe::where('schoolId', $this->school->schoolId)->update([
            'timeframe' => "1 week"
        ]);
        
        $date = new DateTime();
        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(count($reminderLists[$this->school->schoolId][$this->admin->accountNo]) == 1);
        
        // Set nominations created_at date so that the difference is less than the timeframe        
        Nomination::where('applicationNo', $this->application->applicationNo)->update([
            'created_at' => (new DateTime($this->application->created_at))->add(new DateInterval('P6DT2H'))
        ]);

        $reminderLists = app(Kernel::class)->getReminderLists($date);
        $this->assertTrue(!array_key_exists($this->school->schoolId, $reminderLists));
    }
}
