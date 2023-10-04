<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->char('accountNo', 7)->primary(); // Needs rule to ensure entered value is unique, don't use '->unique()' here as it will not always re-open a number for use if it is deleted.
            $table->string('accountType', 21);
            $table->string('lName', 20);
            $table->string('fName', 30)->nullable();
            // No size limit on password for now, unsure about encryption
            $table->string('password');
            $table->char('superiorNo', 7)->nullable();
            $table->integer('isTemporaryManager')->length(1)->default(0);
            $table->integer('schoolId')->length(3); // SchoolID: 1 reserved for Super Administrator
            //$table->rememberToken();
            $table->timestamps();
        });

        // Making 'superiorNo' a foreign key of 'accountNo'
        // Making 'schoolId' a foreign key of 'schoolId' in 'schools'
        // https://stackoverflow.com/a/65396800
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('superiorNo')->references('accountNo')->on('accounts')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('schoolId')->references('schoolId')->on('schools')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
