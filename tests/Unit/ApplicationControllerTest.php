<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Account;
use App\Models\Application;
use Illuminate\Support\Facades\Log;

class ApplicationControllerTest extends TestCase
{
    private Account $user;
    private $applications;


    protected function setup(): void {
        parent::setup();

        $this->user = Account::factory()->create();
    
        Log::debug($this->user['accountNo']);

        $this->applications = Application::factory(5)->create([
            'accountNo' => $this->user['accountNo']
        ]);
    }

    protected function teardown(): void {
        Application::where('accountNo', $this->user['accountNo'])->delete();
        $this->user->delete();

        $this->applications = null;

        parent::teardown();
    }

    public function test_api_request_for_applications_successful(): void
    {
        // Check for valid response
        $response = $this->getJson("/api/applications/{$this->user['accountNo']}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_applications_invalid_user(): void
    {
        // Check for invalid response
        $response = $this->getJson('/api/applications/asfasfasfasf');
        $response->assertStatus(500);
    }

    public function test_api_request_for_applications_valid_length(): void
    {
        // Test if amount of applications matches
        $response = $this->getJson("/api/applications/{$this->user['accountNo']}");
        $array = $response->getData();
        $this->assertTrue(count($array) == count($this->applications));
    }

    public function test_api_request_for_applications_content_is_json(): void
    {
        // Check if response is json
        $response = $this->getJson("/api/applications/{$this->user['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_applications_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->get("/api/applications/{$this->user['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'applicationNo',
                'accountNo',
                'sDate',
                'eDate',
                'status',
                'processedBy',
                'rejectReason',
            ],
        ]);

        // Check if returned applications for correct user
        $array = json_decode($response->content());
        foreach ($array as $app) {
            $this->assertTrue($app->accountNo == $this->user['accountNo']);
        }
    }
}