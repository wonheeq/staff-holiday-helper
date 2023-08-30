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




    public function test_api_request_for_welcomeMessageData_is_successful(): void
    {
        $response = $this->get("/api/getWelcomeMessageData/000000a");
        $response->assertStatus(200);
    }

    public function test_api_request_for_welcomeMessageData_is_succesful_valid_structure(): void
    {
        $response = $this->get("/api/getWelcomeMessageData/000000a");
        $this->assertJson($response->content());

        $array = json_decode($response->content(), true);
        $this->assertTrue(gettype($array['lineManager']['name']) == 'string');
        $this->assertTrue($array['lineManager']['id'] != null);
    }

    public function test_api_request_for_welcomeMessageData_is_unsuccesful_invalid_account(): void
    {
        $response = $this->get("/api/getWelcomeMessageData/aerghasrega");
        $response->assertStatus(500);
    }
}
