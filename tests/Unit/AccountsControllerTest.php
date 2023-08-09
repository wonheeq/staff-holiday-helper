<?php

namespace Tests\Unit;

use Tests\TestCase;


class AccountsControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_api_request_for_all_accounts(): void
    {
        $response = $this->getJson('/api/accounts');
        $response->assertStatus(200);
    }

    public function test_api_request_for_accounts_content_is_json(): void
    {
        // Check if response is json
        $response = $this->getJson("/api/accounts/");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_accounts_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->get("/api/accounts/");
        $response->assertJsonStructure([
            0 => [
                'accountNo',
                'accountType',
                'lName',
                'fName',
                'superiorNo',
                'schoolId'
            ],
        ]);
    }
}
