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
        Schema::create('manager_nominations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicationNo');
            $table->char('nomineeNo', 7); // Nominee to temporarily takeover management of the subordinate
            $table->char('subordinateNo', 7);
            $table->char('status', 1)->default('U');
            $table->timestamps();
        });

        Schema::table('manager_nominations',function (Blueprint $table) {
            $table->foreign('applicationNo')->references('applicationNo')->on('applications')->cascadeOnUpdate();
            $table->foreign('nomineeNo')->references('accountNo')->on('accounts')->cascadeOnUpdate();
            $table->foreign('subordinateNo')->references('accountNo')->on('accounts')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_nominations');
    }
};
