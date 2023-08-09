<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Account;

class HomePageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_home_page_can_render(): void
    {
        $user = Account::where('accountNo', '000000a')->first();
        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
    }
}
