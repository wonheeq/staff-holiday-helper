<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthControllerTest extends TestCase
{
    private Account $testAccount;


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

    // Test for login that has credentials, but are invalid
    public function test_login_invalid_credentials(): void
    {
        // create request with credentials, test for correct 200 response
        $response = $this->post('/login', [
            'accountNo' => 'invalidNo',
            'password' => 'invalidPass'
        ])->assertStatus(200);

        // test that json is being returned
        $this->assertJson($response->content());

        // test that Json contains the fail with the correct message
        $response->assertJson([
            'response' => 'fail',
            'error' => 'Invalid Credentials',
        ]);
    }


    // test login with no credentials entered
    public function test_login_no_credentials(): void
    {
        // create empty request, test for correct 302 response
        // (Credential validation failed)
        $response = $this->post('/login', [
            'accountNo' => null,
            'password' => null,
        ])->assertStatus(302);
    }

    public function test_login_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'accountNo' => 'AAAAAA1',
            'password' => 'knownPassword7'
        ])->assertStatus(200);

        // test that json is being returned
        $this->assertJson($response->content());

        // test that Json contains the fail with the correct message
        $response->assertJson([
            'response' => 'success',
            'url' => 'http://localhost:8000/home',
        ]);
    }

    public function test_logout(): void
    {
        $response = $this->post('/logout')->assertStatus(200);

        // test that json is being returned
        $this->assertJson($response->content());

        // test that Json contains the fail with the correct message
        $response->assertJson([
            'response' => 'success',
            'url' => 'http://localhost:8000',
        ]);
    }
}
