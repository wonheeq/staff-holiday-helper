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
        Schema::create('accountRoles', function (Blueprint $table) {
            $table->id('accountRoleId');
            $table->unsignedBigInteger('accountNo');
            $table->unsignedBigInteger('roleId');
            /* 
            $table->unsignedBigInteger('unitId')->nullable();
            $table->unsignedBigInteger('majorId')->nullable();
            $table->unsignedBigInteger('courseId')->nullable();
            $table->unsignedBigInteger('schoolId');
            */
            $table->timestamps();
        });

        Schema::table('accountRoles',function (Blueprint $table) {
            $table->foreign('accountNo')->references('accountNo')->on('accounts')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('roleId')->references('roleId')->on('roles')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accountRoles');
    }
};
