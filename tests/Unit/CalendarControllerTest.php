<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\Nomination;

class CalendarControllerTest extends TestCase
{
    private User $user;
    private array $applications;
    private array $nominations;
    private User $otherUser;
    private Application $otherUserApp;
    private Nomination $otherUserNom;

    protected function setup(): void {
        parent::setup();

        // Create test data
        $this->user = User::factory()->create();

        $this->applications = array();
        for ($i = 0; $i < 3; $i++) {
            array_push($this->applications, Application::factory()->create([
                'accountNo' => $this->user->id
            ]));
        }

        $this->nominations = array();
        // Create nominations for applications of test user
        foreach ($this->applications as $app) {
            for ($i = 0; $i < 3; $i++) {
                array_push($this->nominations, Nomination::factory()->create([
                    'applicationNo' => $app->id
                ]));
            }
        }

        // assign test user as nominee for one approved application and having accepted the nominated responsibility
        $this->otherUser = User::where("id", "!=", "{$this->user->id}")->first();

        $this->otherUserApp = Application::factory()->create([
            'accountNo' => $this->otherUser->id,
            'status' => 'Y',
        ]);

        $this->otherUserNom = Nomination::factory()->create([
            'applicationNo' => $this->otherUserApp->id,
            'nominee' => $this->user->id,
            'status' => 'Y',
        ]);
    }

    protected function teardown(): void {
        parent::teardown();
        
        $this->otherUserNom->delete();
        $this->otherUserApp->delete();
        //$this->otherUser->delete(); DO NOT DELETE EXISTING ACTUAL USER

        foreach ($this->nominations as $nom) {
            $nom->delete();
        }

        foreach ($this->applications as $app) {
            $app->delete();
        }

        $this->user->delete();
    }

    public function test_getCalendarData_api_call_is_successful(): void {
        $response = $this->getJson("/api/calendar/{$this->user->id}");
        $response->assertStatus(200);
    }

    public function test_getCalendarData_api_call_is_unsuccessful(): void {
        $response = $this->getJson("/api/calendar/aarhgawerhaer");
        $response->assertStatus(500);
    }

    public function test_getCalendarData_api_call_returns_json(): void {
        $response = $this->getJson("/api/calendar/{$this->user->id}");
        $this->assertJson($response->content());
    }

    public function test_getCalendarData_api_returns_valid_content(): void {
        $response = $this->getJson("/api/calendar/{$this->user->id}");
        $array = json_decode($response->content());

        // 3 applications created for test user + 1 from the other User
        $this->assertTrue(count($array) == 4);

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
