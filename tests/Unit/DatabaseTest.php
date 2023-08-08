<?php

namespace Tests\Unit;

use Tests\TestCase;
use Schema;
use Artisan;

use App\Models\Account;
use App\Models\Application;
use App\Models\AccountRole;
use App\Models\Nomination;
use App\Models\Message;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;



class DatabaseTest extends TestCase
{
    /**
     * Testing database is working by searching for known entities created in seeder.
     * 
     * : php artisan migrate:fresh
     * : php artisan db:seed  
     */

    public function test_migrator_has_created_tables(): void
    {
        // Ensuring migrator has been used.     
        $this->assertTrue($this->migrationsComplete());     
    }

    public function test_seeder_has_created_tables(): void
    {
        // Ensuring sedder has been used       
        $this->assertTrue($this->seedingsComplete());
    }


    public function test_database_accounts(): void
    {
        // Asserting that Line Manager account was successfully inserted from seeder
        $this->assertDatabaseHas('accounts', [
            'accountNo' => '000002L',
            'accountType' => 'lmanager',
            'superiorNo' => null
        ]);

        // Ensuring at least one account was given '000002L' for superiorNo
        $this->assertDatabaseHas('accounts', [
            'superiorNo' => '000002L'
        ]);
    }

    public function test_database_applications(): void
    {
        // Asserting that Line Manager account was successfully inserted from seeder
        $this->assertDatabaseHas('applications', [
            'accountNo' => '000002L',
            'accountType' => 'lmanager',
            'superiorNo' => null
        ]);

        // Ensuring at least one account was given '000002L' for superiorNo
        $this->assertDatabaseHas('accounts', [
            'superiorNo' => '000002L'
        ]);
    }

    // Checks if tables beve been created
    function migrationsComplete()
    {
        //Ensuring migrator has been used.
        if (!Schema::hasTable('accounts') || !Schema::hasTable('applications') || !Schema::hasTable('account_roles') || 
            !Schema::hasTable('nominations') || !Schema::hasTable('messages') || !Schema::hasTable('units') || !Schema::hasTable('majors') 
            || !Schema::hasTable('courses') || !Schema::hasTable('schools') || !Schema::hasTable('roles')) {
                
                // Migrations incomplete
                return false;
        }
        else {
            return true;
        }
    }

    // Checks if tables have been filled
    function seedingsComplete()
    {
        // Ensuring seeder has been used.
        if (!Account::exists() || !Application::exists() || !AccountRole::exists() || !Nomination::exists() || 
        !Message::exists() || !Role::exists() || !Unit::exists() || !Major::exists() || 
        !Course::exists() || !School::exists()) {

            // Seeding incomplete
            return false;
        }
        else {
            return true;
        }
    }
}
