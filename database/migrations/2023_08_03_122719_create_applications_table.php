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
            $table->timestamp('eDate');
            $table->char('status', 1)->default('P');
            $table->char('processedBy', 7)->nullable();
            $table->text('rejectReason')->nullable();
            $table->timestamps();

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
