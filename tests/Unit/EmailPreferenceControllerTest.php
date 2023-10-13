<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\EmailPreference;

class EmailPreferenceControllerTest extends TestCase
{
    private Account $user;

    protected function setup(): void
    {
        parent::setup();

        $this->user = Account::factory()->create([
            'accountType' => "staff"
        ]);
        EmailPreference::factory()->create([
            'accountNo' => $this->user->accountNo
        ]);
    }


    protected function teardown(): void
    {
        EmailPreference::where('accountNo', $this->user->accountNo)->delete();
        $this->user->delete();

        parent::teardown();
    }



    public function test_get_preference_valid_account(): void
    {
        $response = $this->actingAs($this->user)->get('/api/getEmailFrequency');
        $response->assertStatus(200);
        $this->assertTrue(json_decode($response->content()) == "Daily");
    }



    public function test_set_preference_invalid_details(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => 'AAAAAAA',
            'frequency' => 'Daily'
        ]);
        $response->assertStatus(500);
        $this->assertTrue(str_contains($response->content(), "Account does not exist."));
    }



    public function test_set_preference_hourly(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Hourly'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 1);
    }



    public function test_set_preference_twice_a_day(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Hourly'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 1);
    }



    public function test_set_preference_Daily(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Daily'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 24);
    }



    public function test_set_preference_2_days(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Every 2 days'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 48);
    }



    public function test_set_preference_3_days(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Every 3 days'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 72);
    }



    public function test_set_preference_4_days(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Every 4 days'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 96);
    }



    public function test_set_preference_5_days(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Every 5 days'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 120);
    }



    public function test_set_preference_6_days(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Every 6 days'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 144);
    }



    public function test_set_preference_weekly(): void
    {
        $response = $this->actingAs($this->user)->post('/api/setEmailFrequency', [
            'accountNo' => $this->user->accountNo,
            'frequency' => 'Once a week'
        ]);
        $response->assertStatus(200);
        $userPreference = EmailPreference::where('accountNo', $this->user->accountNo)->first();
        $this->assertTrue($userPreference->hours == 168);
    }
}
