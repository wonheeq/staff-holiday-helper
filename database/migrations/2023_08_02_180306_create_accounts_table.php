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
            $table->char('accountNo', 7)->primary();
            $table->string('accountType', 21);
            $table->string('fName', 30);
            $table->string('lName', 20)->nullable();
            // No size limit on password for now, unsure about encryption
            $table->string('password');
            $table->char('superiorNo', 7)->nullable();
            // Left out schoolId for now, not necessary for home page or bookings page
            $table->rememberToken();
            $table->timestamps();
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
