<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Account;


class RolesControllerTest extends TestCase
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
     * Unit tests for AccountController.php
     */
    public function test_api_request_for_all_Roles(): void
    {
        $response = $this->getJson("/api/allRoles/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->getJson("/api/allRoles/{$this->otherUser1['accountNo']}");
        $response->assertStatus(500);

        $response = $this->getJson("/api/allRoles/{$this->otherUser2['accountNo']}");
        $response->assertStatus(500);
    }

    public function test_api_request_for_Roles_content_is_json(): void
    {
        // Check if response is json
        $response = $this->getJson("/api/allRoles/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_Roles_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->getJson("/api/allRoles/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'roleId',
                'name',
                'updated_at'
            ],
        ]);
    }
}
