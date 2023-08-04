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
        Schema::create('messages', function (Blueprint $table) {
            $table->id("messageId");
            $table->unsignedBigInteger("applicationNo");
            $table->char('receiverNo', 7);
            $table->char('senderNo', 7)->nullable();
            $table->string('subject', 40);
            $table->text('content');
            $table->boolean('acknowledged');
            $table->timestamps();
        });

        Schema::table('messages',function (Blueprint $table) {
            $table->foreign('receiverNo')->references('accountNo')->on('accounts')->cascadeOnUpdate();
            $table->foreign('senderNo')->references('accountNo')->on('accounts')->cascadeOnUpdate();
            $table->foreign('applicationNo')->references('applicationNo')->on('applications')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};