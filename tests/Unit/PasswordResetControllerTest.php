<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PasswordResetControllerTest extends TestCase
{
    protected function setup(): void
    {
        parent::setup();

        // insert  temporary account into Database for testing
        DB::table('accounts')->insert([
            'accountNo' => 'AAAAAA1',
            'aType' => fake()->randomElement(['staff', 'lmanager', 'sysadmin']),
            'lName' => fake()->lastName(),
            'fNames' => fake()->firstName(),
            'password' => Hash::make('knownPassword7'),
            'superiorNo' => fake()->randomElement(['112237t', '123456a', '441817e', '877873p'])
        ]);
    }

    protected function teardown(): void
    {
        // Delete temporary account from database
        DB::table('accounts')->where('accountNo', '=', 'AAAAAA1')->delete();

        parent::teardown();
    }
    /**
     * A basic unit test example.
     */
    public function test_sending_reset_email_valid_credentials(): void
    {
        $response = $this->post('/reset-password', [
            'email' => 'AAAAAA1@curtin.edu.au',
            'accountNo' => 'AAAAAA1'
        ])->assertStatus(200);

        $this->assertJson($response->content());

        $response->assertJson([
            'status' => 'We have emailed your password reset link.',
        ]);
    }

    public function test_sending_reset_email_no_credentials(): void
    {
        $response = $this->post('/reset-password', [
            'email' => 'AAAAAB1@curtin.edu.au',
            'accountNo' => 'AAAAAB1'
        ])->assertStatus(302);

        // Except the validation exception from no email
        $this->expectException(ValidationException::class);
        $response->assertJson([
            'message' => 'The email field must be a valid email address.',
        ]);
    }

    public function test_sending_reset_email_bad_credentials(): void
    {
        $response = $this->post('/reset-password', [
            'email' => 'AAAAAB1',
            'accountNo' => 'AAAAAB1'
        ])->assertStatus(302);

        // Except the validation exception from no email
        $this->expectException(ValidationException::class);
        $response->assertJson([
            'message' => 'The email field must be a valid email address.',
        ]);
    }
}
