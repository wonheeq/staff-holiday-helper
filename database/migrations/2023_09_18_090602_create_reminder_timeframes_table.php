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
        Schema::create('reminder_timeframes', function (Blueprint $table) {
            $table->integer("schoolId", 3);
            $table->string('timeframe', 8); 
            /*
            12 hours
            1 day
            2 days
            3 days
            4 days
            5 days
            6 days
            1 week
            */
            $table->timestamps();
        });

        Schema::table('reminder_timeframes', function (Blueprint $table) {
            $table->foreign('schoolId')->references('schoolId')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_timeframes');
    }
};
