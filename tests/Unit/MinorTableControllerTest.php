<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;

class MinorTableControllerTest extends TestCase
{
    private Account $adminUser, $otherUser1, $otherUser2;

    protected function setup(): void
    {
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

    protected function teardown(): void
    {
        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();

        parent::teardown();
    }


    /**
     * Tests Units, Majors, Courses and Schools
     */
    public function test_units_api_requests(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allUnits/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allUnits/{$this->otherUser1['accountNo']}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allUnits/{$this->otherUser2['accountNo']}");
        $response->assertStatus(403);

        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allUnits/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allUnits/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'unitId',
                'name'
            ],
        ]);
    }

    public function test_majors_api_requests(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allMajors/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allMajors/{$this->otherUser1['accountNo']}");
        $response->assertStatus(403);

        $response = $this->getJson("/api/allMajors/{$this->otherUser2['accountNo']}");
        $response->assertStatus(403);

        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allMajors/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allMajors/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'majorId',
                'name'
            ],
        ]);
    }

    public function test_courses_api_requests(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allCourses/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allCourses/{$this->otherUser1['accountNo']}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allCourses/{$this->otherUser2['accountNo']}");
        $response->assertStatus(403);

        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allCourses/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allCourses/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'courseId',
                'name'
            ],
        ]);
    }

    public function test_schools_api_requests(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allSchools/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allSchools/{$this->otherUser1['accountNo']}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allSchools/{$this->otherUser2['accountNo']}");
        $response->assertStatus(403);

        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allSchools/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allSchools/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'schoolId',
                'name'
            ],
        ]);
    }
}
