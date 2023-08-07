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
            $table->string('unitId', 8)->nullable();
            $table->string('majorId', 12)->nullable();
            $table->string('courseId', 10)->nullable();
            $table->integer('schoolId')->length(3);            
            $table->timestamps();
        });

        Schema::table('account_roles',function (Blueprint $table) {
            $table->foreign('accountNo')->references('accountNo')->on('accounts')->cascadeOnUpdate();
            $table->foreign('roleId')->references('roleId')->on('roles')->cascadeOnUpdate();
            $table->foreign('unitId')->references('unitId')->on('units')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('majorId')->references('majorId')->on('majors')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('courseId')->references('courseId')->on('courses')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('schoolId')->references('schoolId')->on('schools')->cascadeOnUpdate();
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
