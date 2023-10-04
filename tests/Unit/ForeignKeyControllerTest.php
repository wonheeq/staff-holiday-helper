<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;

use Illuminate\Support\Facades\Log;

class ForeignKeyControllerTest extends TestCase
{
    private Account $adminUser, $otherUser1, $otherUser2;

    protected function setup(): void {
        parent::setup();

        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);

        $this->otherUser1 = Account::factory()->create([
            'accountType' => "staff"
        ]);

        $this->otherUser2 = Account::factory()->create([
            'accountType' => "lmanager"
        ]);
    }

    protected function teardown(): void {
        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();

        parent::teardown();
    }

    /**
     * Unit tests for ForeignKeyController.php
     */
    public function test_api_request_for_all_fks(): void
    {
                // Delete before merge
                //$response = $this->getJson("/api/allFKData/{$this->adminUser['accountNo']}");
               // $response->assertStatus(200);

                //$response = $this->getJson("/api/allFKData/{$this->otherUser2['accountNo']}");
                //$response->assertStatus(500);

                //$response = $this->getJson("/api/allFKData/{$this->otherUser1['accountNo']}");
                //$response->assertStatus(500);
        
                
        $response = $this->actingAs($this->adminUser)->getJson("/api/allFKData/{$this->adminUser['accountNo']}");
        $response->assertStatus(200); 

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allFKData/{$this->otherUser1['accountNo']}");
        $response->assertStatus(302);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allFKData/{$this->otherUser2['accountNo']}");
        $response->assertStatus(302);
    }

    public function test_api_request_for_fks_content_is_json(): void
    {
        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allFKData/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_fks_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allFKData/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                '*' => [
                    'roleId',
                    'name'
                ]
            ],
            1 => [
                '*' => [
                    'unitId',
                    'disName'
                ]
            ],
            2 => [
                '*' => [
                    'majorId',
                    'disName'
                ]
            ],
            3 => [
                '*' => [
                    'courseId',
                    'disName'
                ]
            ],
            4 => [
                '*' => [
                    'schoolId',
                    'name'
                ]
            ],
        ]);
    }
}