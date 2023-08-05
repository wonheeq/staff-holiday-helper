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
        \App\Models\Unit::factory(15)->create();

        // schools - All 14 curtin schools shown on faculty pages on Curtin Website
        $schools = array(
            array('schoolId' => '101', 'name' => 'Curtin Medical School'), 
            array('schoolId' => '102', 'name' => 'Curtin School of Allied Health'),
            array('schoolId' => '103', 'name' => 'Curtin School of Nursing'),
            array('schoolId' => '104', 'name' => 'Curtin School of Population Health'),
            array('schoolId' => '105', 'name' => 'Curtin Business School'), 
            array('schoolId' => '106', 'name' => 'Curtin Law School'),
            array('schoolId' => '107', 'name' => 'School of Design and the Built Environment'),
            array('schoolId' => '108', 'name' => 'School of Education'),
            array('schoolId' => '109', 'name' => 'School of Media, Creative Arts and Social Inquiry'), 
            array('schoolId' => '110', 'name' => 'School of Civil and Mechanical Engineering'),
            array('schoolId' => '111', 'name' => 'School of Earth and Planetary Sciences'),
            array('schoolId' => '112', 'name' => 'School of Electrical Engineering, Computing and Mathematical Sciences'),
            array('schoolId' => '113', 'name' => 'School of Molecular and Life Sciences'), 
            array('schoolId' => '114', 'name' => 'WA School of Mines: Minerals, Energy and Chemical Engineering')
         );
        
         foreach ($schools as $school) {
            \App\Models\School::create([
              'schoolId' => $school['schoolId'],
              'name' => $school['name'],
            ]);
         }

        // Creating 4 Line Manager Accounts for other to use for superiorNo foreign key: ['112237t', '123456a', '441817e', '877873p']
        \App\Models\Account::factory(1)->create([
            'accountNo' => '112237t',
            'accountType' => 'lmanager', 
            'lName' => fake()->lastName(),
            'fName' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null,
            'schoolId' => fake()->numberBetween(101, 114)
        ]);

        \App\Models\Account::factory(1)->create([
            'accountNo' => '123456a',
            'accountType' => 'lmanager', 
            'lName' => fake()->lastName(),
            'fName' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null,
            'schoolId' => fake()->numberBetween(101, 114)
        ]);

        \App\Models\Account::factory(1)->create([
            'accountNo' => '441817e',
            'accountType' => 'lmanager', 
            'lName' => fake()->lastName(),
            'fName' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null,
            'schoolId' => fake()->numberBetween(101, 114)
        ]);

        \App\Models\Account::factory(1)->create([
            'accountNo' => '877873p',
            'accountType' => 'lmanager', 
            'lName' => fake()->lastName(),
            'fName' => fake()->firstName(),
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' =>  null,
            'schoolId' => fake()->numberBetween(101, 114)
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
