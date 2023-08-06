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
        Schema::create('account_roles', function (Blueprint $table) {
            $table->id('accountRoleId');
            $table->char('accountNo', 7);
            $table->unsignedBigInteger('roleId');
            /* 
            $table->unsignedBigInteger('unitId')->nullable();
            $table->unsignedBigInteger('majorId')->nullable();
            $table->unsignedBigInteger('courseId')->nullable();
            $table->unsignedBigInteger('schoolId');
            */
            $table->timestamps();
        });

        Schema::table('account_roles',function (Blueprint $table) {
            $table->foreign('accountNo')->references('accountNo')->on('accounts')->cascadeOnUpdate();
            $table->foreign('roleId')->references('roleId')->on('roles')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_roles');
    }
};
