<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\NominationController;
use App\Models\Application;
use App\Models\User;
use App\Models\Nomination;

class NominationControllerTest extends TestCase
{
    private User $user;
    private Application $application;
    private array $nominations;

    protected function setUp(): void {
        parent::setUp();

        // create temp user
        $this->user = User::factory()->create();
        // create temp application
        $this->application = Application::factory()->create([
            'accountNo' => $this->user->id,
        ]);

        // create temp nominations
        $this->nominations = array();
        for ($i = 0; $i < 5; $i++) {
            array_push($this->nominations, Nomination::factory()->create([
                'applicationNo' => $this->application->id,
            ]));
        }
    }

    protected function tearDown(): void {
        parent::tearDown();

        // delete nominations then application then user
        foreach ($this->nominations as $nomination) {
            $nomination->delete();
        }

        $this->application->delete();
        $this->user->delete();
    }

    public function test_call_getNominations_successful(): void
    {
        $result = app(NominationController::class)->getNominations($this->application->id);
        $this->assertTrue($this->nominations != null && gettype($this->nominations) == "array");
    }

    public function test_getNominations_returns_correct_amount(): void {
        $result = app(NominationController::class)->getNominations($this->application->id);
        $this->assertTrue(count($result) == count($this->nominations));
    }

    public function test_getNominations_content_is_valid(): void {
        $result = app(NominationController::class)->getNominations($this->application->id);

        // contents should match our original objects in setUp()
        foreach ($result as $index => $element) {
            $user = User::find($this->nominations[$index]->nominee);

            $this->assertTrue($element['name'] == $user->name);
            $this->assertTrue($element['user_id'] == $user->id);
            $this->assertTrue($element['task'] == $this->nominations[$index]->task);
            $this->assertTrue($element['status'] == $this->nominations[$index]->status);
        }
    }
}
