<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthenticationControllerTest extends TestCase
{
    private Account $account, $user;
    protected function setup(): void
    {
        parent::setup();

        // insert  temporary account into Database for testing
        DB::table('accounts')->insert([
            'accountNo' => 'AAAAAA1',
            'accountType' => fake()->randomElement(['staff', 'lmanager', 'sysadmin']),
            'lName' => fake()->lastName(),
            'fName' => fake()->firstName(),
            'password' => Hash::make('knownPassword1'),
            'superiorNo' => '0000000',
            'schoolId' => '101',
        ]);


        $this->user = Account::factory()->create([
            'accountNo' => 'AAAAAA2',
            'password' => Hash::make(fake()->regexify('testPassword1')),
        ]);

        $this->account = Account::where('accountNo', 'AAAAAA1')->first();
    }



    protected function teardown(): void
    {
        // Delete temporary account from database
        DB::table('accounts')->where('accountNo', '=', 'AAAAAA1')->delete();
        DB::table('accounts')->where('accountNo', '=', 'AAAAAA2')->delete();

        // Delete any password reset tokens leftover
        DB::table('password_reset_tokens')->where('email', 'AAAAAA1@curtin.com.au')->delete();
        DB::table('password_reset_tokens')->where('email','AAAAAA2@curtin.edu.au')->delete();

        parent::teardown();
    }



    // Test for correct behaviour when loggin in with no credentials
    public function test_login_invalid_credentials(): void
    {
        // create request with credentials, test for correct 302 response
        // should be redirect back to the same page
        $response = $this->actingAs($this->account)->post('/login', [
            'accountNo' => 'invalidNo',
            'password' => 'invalidPass'
        ])->assertStatus(302);

        // Test that content returned contains 'Redirecting'
        $this->assertTrue(str_contains($response->content(), "Redirecting"));
    }



    // Test for correct behaviour when logging in with no credentials
    public function test_login_no_credentials(): void
    {
        // create empty request, test for correct 302 response
        // (Credential validation failed)
        $response = $this->actingAs($this->account)->post('/login', [
            'accountNo' => null,
            'password' => null,
        ])->assertStatus(302);
    }



    // Test for correct behaviour when logging in with valid credentials
    public function test_login_valid_credentials(): void
    {
        // test for correct 302 response
        $response = $this->post('/login', [
            'accountNo' => 'AAAAAA1',
            'password' => 'knownPassword1'
        ])->assertStatus(302);

        // Test that content returned contains 'Redirecting'
        $this->assertTrue(str_contains($response->content(), "Redirecting"));

        // Test that content returned contains '/home'
        $this->assertTrue(str_contains($response->content(), "/home"));
    }



    // Test for correct behaviour when logging out
    public function test_logout(): void
    {
        $response = $this->actingAs($this->account)->post('/logout')->assertStatus(200);

        // test that json is being returned
        $this->assertJson($response->content());

        // test that Json contains the fail with the correct message
        $response->assertJson([
            'response' => 'success',
            'url' => 'http://localhost',
        ]);
    }



    // Test the sending of a password reset email using valid credentials
    public function test_sending_reset_email_valid_credentials(): void
    {
        $response = $this->actingAs($this->account)->post('/reset-password', [
            'email' => 'AAAAAA1@baddfeanmigaosdif.edu.au',
            'accountNo' => 'AAAAAA1'
        ])->assertStatus(200);

        $this->assertJson($response->content());

        $response->assertJson([
            'status' => 'We have emailed your password reset link.',
        ]);
    }


    // Immediately try again, should fail due to throttling
    public function test_sending_reset_email_valid_credentials_while_throttled(): void
    {
        // Send first request, and test for 200 'ok' response
        $response = $this->actingAs($this->account)->post('/reset-password', [
            'email' => 'AAAAAA1@eapriogjapsdff.com',
            'accountNo' => 'AAAAAA1'
        ])->assertStatus(302);

        // Send second request, and test for 302 fail due to throttling
        $this->expectException(ValidationException::class);
        $response = $this->post('/reset-password', [
            'email' => 'AAAAAA1@eapriogjapsdff.com',
            'accountNo' => 'AAAAAA1'
        ])->assertStatus(302);

        // Test that correct message passed
        $response->assertJson([
            'message' => 'Please wait before retrying.',
        ]);
    }



    // Test for correct behaviour when request a password reset email with no credentials provided
    public function test_sending_reset_email_no_credentials(): void
    {
        $response = $this->actingAs($this->account)->post('/reset-password', [
            'email' => 'AAAAAB1@eapriogjapsdff.com',
            'accountNo' => 'AAAAAB1'
        ])->assertStatus(302);

        // Except the validation exception from no email
        $this->expectException(ValidationException::class);
        $response->assertJson([
            'message' => 'The email field must be a valid email address.',
        ]);
    }



    // Test for correct behaviour when request a password reset email with invalid credentials provided
    public function test_sending_reset_email_bad_credentials(): void
    {
        $response = $this->actingAs($this->account)->post('/reset-password', [
            'email' => 'AAAAAB1',
            'accountNo' => 'AAAAAB1'
        ])->assertStatus(302);

        // Except the validation exception from no email
        $this->expectException(ValidationException::class);
        $response->assertJson([
            'message' => 'The email field must be a valid email address.',
        ]);
    }



    // Test for correct behaviour when requesting a password reset from the HOME page
    // with valid credentials
    public function test_home_reset_valid_credentials(): void
    {
        // request while acting as user, assert successful
        DB::table('password_reset_tokens')->where('email', 'AAAAAA2@curtin.edu.au')->delete();

        $response = $this->actingAs($this->user)->post('/change-password', [
            'accountNo' => 'AAAAAA2',
            'currentPassword' => 'testPassword1',
            'password' => 'testPassword2',
            'password_confirmation' => 'testPassword2'
        ])->assertStatus(200);
    }



    // Test for correct behaviour when requesting a password reset from the HOME page
    // with no credentials
    public function test_home_reset_no_credentials(): void
    {
        // request while acting as user, assert 302 (failed validation)
        $response = $this->actingAs($this->user)->post('/change-password', [
            'accountNo' => '',
            'currentPassword' => '',
            'password' => '',
            'password_confirmation' => ''
        ])->assertStatus(302);
    }



    // Test for correct behaviour when requesting a password reset from the HOME page
    // with no credentials
    public function test_home_reset_passwords_dont_match(): void
    {

        // request while acting as user, assert 302 (failed validation)
        $response = $this->actingAs($this->user)->post('/change-password', [
            'accountNo' => 'AAAAAA2',
            'currentPassword' => 'testPassword1',
            'password' => 'testPassword2',
            'password_confirmation' => 'testPassword3'
        ])->assertStatus(302);
    }



    // Test for correct behaviour when resetting your password from the home page,
    // where the current password is incorrect
    public function test_home_reset_current_password_wrong(): void
    {

        // request while acting as user, assert 302 (failed validation)
        $response = $this->actingAs($this->user)->post('/change-password', [
            'accountNo' => 'AAAAAA2',
            'currentPassword' => 'testPassword5',
            'password' => 'testPassword2',
            'password_confirmation' => 'testPassword2'
        ])->assertStatus(302);
    }
}
