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
        $defaultTime = new DateTime('NOW');
        $defaultTime->modify('-8 days');

        Schema::create('email_preferences', function (Blueprint $table) {
            $table->id();
            $table->char('accountNo', 7);
            $table->integer('hours')->default(24);
            $table->timestamp('timeLastSent')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_preferences');
    }
};
