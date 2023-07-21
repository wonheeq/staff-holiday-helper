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
            $table->id()->increments();
            $table->string('receiver_id');
            $table->foreign('receiver_id')->references('id')->on('users');
            $table->string('sender_id');
            $table->foreign('sender_id')->references('id')->on('users');
            $table->string('title');
            $table->text('content');
            $table->boolean('is_nominated_multiple');
            $table->boolean('acknowledged');
            $table->timestamps();
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
