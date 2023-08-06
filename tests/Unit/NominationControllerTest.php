<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\NominationController;
use App\Models\Application;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Nomination;
use Illuminate\Support\Facades\Log;

class NominationControllerTest extends TestCase
{
    private Account $user;
    private Application $application;
    private array $nominations;

    protected function setup(): void {
        parent::setup();

        // create temp user
        $this->user = Account::factory()->create();
        // create temp application
        $this->application = Application::factory()->create([
            'accountNo' => $this->user->accountNo,
        ]);

        AccountRole::factory(3)->create([
            'accountNo' => $this->user->accountNo,
        ]);

        // create temp nominations
        $this->nominations = array();
        for ($i = 0; $i < 3; $i++) {
            array_push($this->nominations, Nomination::factory()->create([
                'applicationNo' => $this->application->applicationNo,
                'accountRoleId' => AccountRole::where('accountNo',  $this->user->accountNo)->pluck('accountRoleId')[$i],
            ]));
        }
    }

    protected function teardown(): void {
        // delete nominations then application then user
        foreach ($this->nominations as $nom) {
            Nomination::where('applicationNo', $nom->applicationNo, 'and')
            ->where('nomineeNo',$nom->nomineeNo, 'and')
            ->where('accountRoleId', $nom->accountRoleId)
            ->delete();
        }
        AccountRole::where('accountNo', $this->user->accountNo)->delete();
        $this->application->delete();
        $this->user->delete();

        parent::teardown();
    }

    public function test_call_getNominations_successful(): void
    {
        $result = app(NominationController::class)->getNominations($this->application->applicationNo);
        $this->assertTrue($this->nominations != null && gettype($this->nominations) == "array");
    }

    public function test_getNominations_returns_correct_amount(): void {
        $result = app(NominationController::class)->getNominations($this->application->applicationNo);
        $this->assertTrue(count($result) == count($this->nominations));
    }

    public function test_getNominations_content_is_valid(): void {
        $result = app(NominationController::class)->getNominations($this->application->applicationNo);

        // contents should match our original objects in setUp()
        $names = array();
        $nomineeNos = array();
        $statuses = array();
        foreach ($result as $index => $element) {
            $user = Account::where('accountNo', $this->nominations[$index]->nomineeNo)->first();

            array_push($names, "{$user->fName} {$user->lName}");
            array_push($nomineeNos, $user->accountNo);
            array_push($statuses, $this->nominations[$index]->status);
        }

        foreach ($result as $element) {
            $this->assertTrue(in_array($element['name'], $names));
            $this->assertTrue(in_array($element['nomineeNo'], $nomineeNos));
            $this->assertTrue(in_array($element['status'], $statuses));
        }
    }
}