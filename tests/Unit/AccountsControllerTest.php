<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;


class AccountsControllerTest extends TestCase
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
     * Unit tests for AccountController.php
     */
    public function test_api_request_for_all_accounts(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allAccounts/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allAccounts/{$this->otherUser1['accountNo']}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allAccounts/{$this->otherUser2['accountNo']}");
        $response->assertStatus(403);
    }

    public function test_api_request_for_accounts_content_is_json(): void
    {
        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allAccounts/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_accounts_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allAccounts/{$this->adminUser['accountNo']}");
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
        $response = $this->actingAs($this->adminUser)->get("/api/getWelcomeMessageData/000000a");
        // dd($response);
        $response->assertStatus(200);
    }

    public function test_api_request_for_welcomeMessageData_is_succesful_valid_structure(): void
    {
        $response = $this->actingAs($this->adminUser)->get("/api/getWelcomeMessageData/000000a");
        $this->assertJson($response->content());

        $array = json_decode($response->content(), true);
        $this->assertTrue(gettype($array['lineManager']['name']) == 'string');
        $this->assertTrue($array['lineManager']['id'] != null);
    }

    public function test_api_request_for_welcomeMessageData_is_unsuccesful_invalid_account(): void
    {
        $response = $this->actingAs($this->adminUser)->get("/api/getWelcomeMessageData/aerghasrega");
        $response->assertStatus(500);
    }
}
