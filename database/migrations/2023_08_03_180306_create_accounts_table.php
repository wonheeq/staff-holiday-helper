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
            $table->string('aType', 21);
            $table->string('lName', 20);
            $table->string('fNames', 30)->nullable();
            $table->string('pswd', 60);
            $table->char('superiorNo', 7)->nullable();
            $table->timestamps();
        });

        // Making 'superiorNo' a foreign key of 'accountNo'
        // https://stackoverflow.com/a/65396800
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('superiorNo')->references('accountNo')->on('accounts')->cascadeOnUpdate()->nullOnDelete();
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
