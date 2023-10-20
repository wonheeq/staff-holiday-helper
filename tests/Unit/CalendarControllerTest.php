<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Application;
use App\Models\Nomination;

class CalendarControllerTest extends TestCase
{
    private Account $user;
    private array $applications;
    private array $nominations;
    private Account $otherUser;
    private Application $otherUserApp;
    private Nomination $otherUserNom;

    protected function setup(): void
    {
        parent::setup();

        // Create test data
        $this->user = Account::factory()->create();
        AccountRole::factory(3)->create([
            'accountNo' => $this->user->accountNo,
        ]);

        // Do not create application with Cancelled status since it does not get returned with getCalendarData
        $statuses = ["Y", "N", "P", "U"];
        $this->applications = array();
        for ($i = 0; $i < 4; $i++) {
            array_push($this->applications, Application::factory()->create([
                'accountNo' => $this->user->accountNo,
                'status' => $statuses[$i]
            ]));
        }

        $this->nominations = array();
        // Create nominations for applications of test user
        foreach ($this->applications as $app) {
            $appAccountRoleIds = AccountRole::where('accountNo', $this->user->accountNo)->get();
            $nomineeNos = Account::where('accountNo', '!=', $this->user->accountNo)->get()->toArray();
            for ($i = 0; $i < 1; $i++) {
                array_push($this->nominations, Nomination::factory()->create([
                    'applicationNo' => $app->applicationNo,
                    'accountRoleId' => $appAccountRoleIds[$i],
                    'nomineeNo' => $nomineeNos[$i]['accountNo']
                ]));
            }
        }

        // assign test user as nominee for one approved application and having accepted the nominated responsibility
        $this->otherUser = Account::where("accountNo", "!=", "{$this->user->accountNo}")->first();

        $this->otherUserApp = Application::factory()->create([
            'accountNo' => $this->otherUser->accountNo,
            'status' => 'Y',
        ]);

        $this->otherUserNom = Nomination::factory()->create([
            'applicationNo' => $this->otherUserApp->applicationNo,
            'nomineeNo' => $this->user->accountNo,
            'status' => 'Y',
        ]);
    }

    protected function teardown(): void
    {
        Nomination::where('applicationNo', $this->otherUserNom->applicationNo, 'and')
            ->where('nomineeNo', $this->otherUserNom->nomineeNo, 'and')
            ->where('accountRoleId', $this->otherUserNom->accountRoleId)
            ->delete();
        $this->otherUserApp->delete();
        //$this->otherUser->delete(); DO NOT DELETE EXISTING ACTUAL USER

        foreach ($this->nominations as $nom) {
            Nomination::where('applicationNo', $nom->applicationNo, 'and')
                ->where('nomineeNo', $nom->nomineeNo, 'and')
                ->where('accountRoleId', $nom->accountRoleId)
                ->delete();
        }

        foreach ($this->applications as $app) {
            $app->delete();
        }

        AccountRole::where('accountNo', $this->user->accountNo)->delete();
        $this->user->delete();
        parent::teardown();
    }

    public function test_getCalendarData_api_call_is_successful(): void
    {
        $response = $this->actingAs($this->user)->getJson("/api/calendar/{$this->user->accountNo}");
        $response->assertStatus(200);
    }

    public function test_getCalendarData_api_call_is_unsuccessful(): void
    {
        $response = $this->getJson("/api/calendar/aarhgawerhaer");
        $response->assertStatus(401);
    }

    public function test_getCalendarData_api_call_returns_json(): void
    {
        $response = $this->actingAs($this->user)->getJson("/api/calendar/{$this->user->accountNo}");
        $this->assertJson($response->content());
    }

    public function test_getCalendarData_api_returns_valid_content(): void
    {
        $response = $this->actingAs($this->user)->getJson("/api/calendar/{$this->user->accountNo}");
        $array = json_decode($response->content());

        // 4 applications created for test user + 1 substitution from the other User
        $this->assertTrue(count($array) == 5);

        // check that first element is strucutred correctly
        $response->assertJsonStructure([
            0 => [
                'highlight',
                'dates',
                'popover',
            ],
        ]);
    }
}
