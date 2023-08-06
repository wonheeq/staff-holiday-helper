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
            $table->id("applicationNo");
            $table->char('accountNo', 7);
            $table->timestamp('sDate');
            $table->timestamp('eDate')->nullable(); // Had to make nullable as would not allow two default value timestamps in one schema.
            $table->char('status', 1);
            $table->char('processedBy', 7);
            $table->text('rejectReason')->nullable();
            $table->timestamps();
        });

        Schema::table('applications',function (Blueprint $table) {
            $table->foreign('accountNo')->references('accountNo')->on('accounts')->cascadeOnUpdate();
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
