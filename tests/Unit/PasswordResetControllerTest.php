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

        // insert  temporary accounts into Database for testing
        DB::table('accounts')->insert([
            'accountNo' => 'AAAAAA1',
            'accountType' => fake()->randomElement(['staff', 'lmanager', 'sysadmin']),
            'lName' => fake()->lastName(),
            'fName' => fake()->firstName(),
            'password' => Hash::make('knownPassword1'),
            'superiorNo' => fake()->randomElement(['112237t', '123456a', '441817e', '877873p']),
            'schoolId' => '101',
        ]);
    }



    protected function teardown(): void
    {
        // Delete temporary account from database
        DB::table('accounts')->where('accountNo', '=', 'AAAAAA1')->delete();
        DB::table('accounts')->where('accountNo', '=', 'AAAAAA2')->delete();

        parent::teardown();
    }



    // // Test the sending of a password reset email using valid credentials
    // public function test_sending_reset_email_valid_credentials(): void
    // {
    //     $response = $this->post('/reset-password', [
    //         'email' => 'AAAAAA1@curtin.edu.au',
    //         'accountNo' => 'AAAAAA1'
    //     ])->assertStatus(200);

    //     $this->assertJson($response->content());

    //     $response->assertJson([
    //         'status' => 'We have emailed your password reset link.',
    //     ]);
    // }



    // // Test for correct behaviour when request a password reset email with no credentials provided
    // public function test_sending_reset_email_no_credentials(): void
    // {
    //     $response = $this->post('/reset-password', [
    //         'email' => 'AAAAAB1@curtin.edu.au',
    //         'accountNo' => 'AAAAAB1'
    //     ])->assertStatus(302);

    //     // Except the validation exception from no email
    //     $this->expectException(ValidationException::class);
    //     $response->assertJson([
    //         'message' => 'The email field must be a valid email address.',
    //     ]);
    // }



    // // Test for correct behaviour when request a password reset email with invalid credentials provided
    // public function test_sending_reset_email_bad_credentials(): void
    // {
    //     $response = $this->post('/reset-password', [
    //         'email' => 'AAAAAB1',
    //         'accountNo' => 'AAAAAB1'
    //     ])->assertStatus(302);

    //     // Except the validation exception from no email
    //     $this->expectException(ValidationException::class);
    //     $response->assertJson([
    //         'message' => 'The email field must be a valid email address.',
    //     ]);
    // }



    // // Test for correct behaviour when requesting a password reset from the HOME page
    // // with valid credentials
    // public function test_home_reset_valid_credentials(): void
    // {
    //     // create fake user
    //     $user = Account::factory()->create([
    //         'accountNo' => 'AAAAAA2',
    //         'password' => Hash::make(fake()->regexify('testPassword1')),
    //     ]);

    //     // request while acting as user, assert successful
    //     $response = $this->actingAs($user)->post('/change-password', [
    //         'accountNo' => 'AAAAAA2',
    //         'currentPassword' => 'testPassword1',
    //         'password' => 'testPassword2',
    //         'password_confirmation' => 'testPassword2'
    //     ])->assertStatus(200);
    // }

    // // Test for correct behaviour when requesting a password reset from the HOME page
    // // with no credentials
    // public function test_home_reset_no_credentials(): void
    // {
    //     // create fake user
    //     $user = Account::factory()->create([
    //         'accountNo' => 'AAAAAA2',
    //         'password' => Hash::make(fake()->regexify('testPassword1')),
    //     ]);

    //     // request while acting as user, assert 302 (failed validation)
    //     $response = $this->actingAs($user)->post('/change-password', [
    //         'accountNo' => '',
    //         'currentPassword' => '',
    //         'password' => '',
    //         'password_confirmation' => ''
    //     ])->assertStatus(302);
    // }

    // // Test for correct behaviour when requesting a password reset from the HOME page
    // // with no credentials
    // public function test_home_reset_passwords_dont_match(): void
    // {
    //     // create fake user
    //     $user = Account::factory()->create([
    //         'accountNo' => 'AAAAAA2',
    //         'password' => Hash::make(fake()->regexify('testPassword1')),
    //     ]);

    //     // request while acting as user, assert 302 (failed validation)
    //     $response = $this->actingAs($user)->post('/change-password', [
    //         'accountNo' => 'AAAAAA2',
    //         'currentPassword' => 'testPassword1',
    //         'password' => 'testPassword2',
    //         'password_confirmation' => 'testPassword3'
    //     ])->assertStatus(302);
    // }

    // public function test_home_reset_current_password_wrong(): void
    // {
    //     // create fake user
    //     $user = Account::factory()->create([
    //         'accountNo' => 'AAAAAA2',
    //         'password' => Hash::make(fake()->regexify('testPassword1')),
    //     ]);

    //     // request while acting as user, assert 302 (failed validation)
    //     $response = $this->actingAs($user)->post('/change-password', [
    //         'accountNo' => 'AAAAAA2',
    //         'currentPassword' => 'testPassword5',
    //         'password' => 'testPassword2',
    //         'password_confirmation' => 'testPassword2'
    //     ])->assertStatus(302);
    // }
}
