<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\User;
use App\Models\Application;

class ApplicationControllerTest extends TestCase
{
    private User $user;
    private array $applications;

    public function setup(): void {
        parent::setup();

        $this->user = User::factory()->create();
        $this->applications = array();


        for($i = 0; $i < 5; $i++) {
            array_push($this->applications, Application::factory()->create([
                'accountNo' => $this->user->id
            ]));
        }
    }

    public function teardown(): void {
        parent::teardown();
        foreach ($this->applications as $app) {
            $app->delete();
        }
        $this->user->delete();
    }

    public function test_api_request_for_applications_successful(): void
    {
        // Check for valid response
        $response = $this->getJson("/api/applications/{$this->user->id}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_applications_invalid_user(): void
    {
        // Check for invalid response
        $response = $this->getJson('/api/applications/a1234545354356');
        $response->assertStatus(500);
    }

    public function test_api_request_for_applications_valid_length(): void
    {
      
        // Test if amount of applications is 5
        $response = $this->getJson("/api/applications/{$this->user->id}");
        $array = $response->getData();
        $this->assertTrue(count($array) == count($this->applications));
    }

    public function test_api_request_for_applications_content_is_json(): void
    {
        // Check if response is json
        $response = $this->getJson("/api/applications/{$this->user->id}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_applications_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->get("/api/applications/{$this->user->id}");
        $response->assertJsonStructure([
            0 => [
                'id',
                'accountNo',
                'start',
                'end',
                'status',
                'processedBy',
                'rejectReason',
            ],
        ]);

        // Check if returned applications for correct user
        $array = json_decode($response->content());
        foreach ($array as $message) {
            $this->assertTrue($message->accountNo == $this->user->id);
        }
    }
}