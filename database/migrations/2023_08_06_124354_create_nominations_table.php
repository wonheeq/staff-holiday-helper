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
        Schema::create('nominations', function (Blueprint $table) {
            $table->unsignedBigInteger('applicationNo');
            $table->char('nomineeNo', 7);
            $table->unsignedBigInteger('accountRoleId');
            $table->char('status', 1)->default('U');
            $table->timestamps();

            $table->primary(['applicationNo', 'nomineeNo', 'accountRoleId']);
        });

        Schema::table('nominations',function (Blueprint $table) {
            $table->foreign('applicationNo')->references('applicationNo')->on('applications')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('nomineeNo')->references('accountNo')->on('accounts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('accountRoleId')->references('accountRoleId')->on('account_roles')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominations');
    }
};
