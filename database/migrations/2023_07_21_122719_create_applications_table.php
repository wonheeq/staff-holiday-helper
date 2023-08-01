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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('accountNo');
            $table->foreign('accountNo')->references('id')->on('users');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->char('status', 1);
            $table->string('processedBy');
            $table->foreign('processedBy')->references('id')->on('users');
            $table->string('rejectReason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
