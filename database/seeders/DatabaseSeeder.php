<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(5)->create();
        \App\Models\Message::factory(30)->create();
        \App\Models\Application::factory(10)->create();


        // Creating 4 Line Manager Accounts for other to use for superiorNo foreign key: ['112237t', '123456a', '441817e', '877873p']
        \App\Models\Account::factory(1)->create([
            'accountNo' => '112237t',
            'aType' => 'lmanager',
            'lName' => fake()->lastName(),
            'fNames' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null
        ]);

        \App\Models\Account::factory(1)->create([
            'accountNo' => '123456a',
            'aType' => 'lmanager',
            'lName' => fake()->lastName(),
            'fNames' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null
        ]);

        \App\Models\Account::factory(1)->create([
            'accountNo' => '441817e',
            'aType' => 'lmanager',
            'lName' => fake()->lastName(),
            'fNames' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null
        ]);

        \App\Models\Account::factory(1)->create([
            'accountNo' => '877873p',
            'aType' => 'lmanager',
            'lName' => fake()->lastName(),
            'fNames' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null
        ]);


        \App\Models\Account::factory(50)->create();

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $test_id = 'a000000';

        \App\Models\User::factory()->create([
            'id' => $test_id,
            'name' => 'Static Test User',
            'email' => 'StaticTestUser@Test.com',
            'email_verified_at' => now(),
            'password' => 'test', // password
            'remember_token' => Str::random(10),
        ]);

        \App\Models\Message::factory(10)->create([
            'receiver_id' => $test_id,
        ]);

        \App\Models\Application::factory(1)->create([
            'accountNo' => $test_id,
            'status' => 'Y'
        ]);
        \App\Models\Application::factory(1)->create([
            'accountNo' => $test_id,
            'status' => 'N'
        ]);
        \App\Models\Application::factory(1)->create([
            'accountNo' => $test_id,
            'status' => 'U'
        ]);
        \App\Models\Application::factory(1)->create([
            'accountNo' => $test_id,
            'status' => 'P'
        ]);


        // generate 5 nominations for each application
        $allApplications = \App\Models\Application::get();
        foreach ($allApplications as $application) {
            $appId = $application['id'];
            \App\Models\Nomination::factory(5)->create([
                'applicationNo' => $appId,
            ]);
        }
    }
}
