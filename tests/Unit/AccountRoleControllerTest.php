<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AccountRole;
use App\Models\Account;


class AccountRoleControllerTest extends TestCase
{
    private Account $adminUser, $otherUser1, $otherUser2;

    protected function setup(): void
    {
        parent::setup();
        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);

        AccountRole::factory()->create([
            'accountNo' => $this->adminUser->accountNo
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
        AccountRole::where('accountNo', $this->adminUser->accountNo)->delete();
        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();

        parent::teardown();
    }


    /**
     * Unit tests for AccountController.php
     */
    public function test_api_request_for_all_AccountRoles(): void
    {
        // Acting as Admin
        $response = $this->actingAs($this->adminUser)->getJson("/api/allAccountRoles/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        // Acting as staff, assert forbidden
        $response = $this->actingAs($this->otherUser1)->getJson("/api/allAccountRoles/{$this->otherUser1['accountNo']}");
        $response->assertStatus(302);

        // Acting as line manager, assert forbidden
        $response = $this->actingAs($this->otherUser2)->getJson("/api/allAccountRoles/{$this->otherUser2['accountNo']}");
        $response->assertStatus(302);
    }

    public function test_api_request_for_AccountRoles_content_is_json(): void
    {
        // Act as Admin
        $response = $this->actingAs($this->adminUser)->getJson("/api/allAccountRoles/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_AccountRoles_content_is_valid(): void
    {
        // Check if correct structure, acting as admin
        $response = $this->actingAs($this->adminUser)->getJson("/api/allAccountRoles/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'accountRoleId',
                'accountNo',
                'roleId',
                'unitId',
                'majorId',
                'courseId',
                'schoolId',
                'updated_at'
            ],
        ]);
    }
}
