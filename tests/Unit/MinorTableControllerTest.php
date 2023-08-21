<?php

namespace Tests\Unit;

use Tests\TestCase;

class MinorTableControllerTest extends TestCase
{
    /**
     * Tests Units, Majors, Courses and Schools
     */
    public function test_units_api_requests(): void
    {
        $response = $this->getJson('/api/units');
        $response->assertStatus(200);

        // Check if response is json
        $response = $this->getJson("/api/units/");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->get("/api/units/");
        $response->assertJsonStructure([
            0 => [
                'unitId',
                'name'
            ],
        ]);
    }

    public function test_majors_api_requests(): void
    {
        $response = $this->getJson('/api/majors');
        $response->assertStatus(200);

        // Check if response is json
        $response = $this->getJson("/api/majors/");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->get("/api/majors/");
        $response->assertJsonStructure([
            0 => [
                'majorId',
                'name'
            ],
        ]);
    }

    public function test_courses_api_requests(): void
    {
        $response = $this->getJson('/api/courses');
        $response->assertStatus(200);

        // Check if response is json
        $response = $this->getJson("/api/courses/");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->get("/api/courses/");
        $response->assertJsonStructure([
            0 => [
                'courseId',
                'name'
            ],
        ]);
    }

    public function test_schools_api_requests(): void
    {
        $response = $this->getJson('/api/schools');
        $response->assertStatus(200);

        // Check if response is json
        $response = $this->getJson("/api/schools/");
        $this->assertJson($response->content());

        // Check if correct structure
        $response = $this->get("/api/schools/");
        $response->assertJsonStructure([
            0 => [
                'schoolId',
                'name'
            ],
        ]);
    }
}
