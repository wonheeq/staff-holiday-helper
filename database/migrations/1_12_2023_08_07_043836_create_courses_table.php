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
<<<<<<<< HEAD:database/migrations/1_12_2023_08_07_043836_create_courses_table.php
        Schema::create('courses', function (Blueprint $table) {
            $table->string('courseId', 10)->primary();
            $table->string('name', 60);
========
        Schema::create('roles', function (Blueprint $table) {
            $table->id("roleId");
            $table->string("name", 40);
>>>>>>>> wonhee:database/migrations/2023_08_04_085553_create_roles_table.php
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<<< HEAD:database/migrations/1_12_2023_08_07_043836_create_courses_table.php
        Schema::dropIfExists('courses');
========
        Schema::dropIfExists('roles');
>>>>>>>> wonhee:database/migrations/2023_08_04_085553_create_roles_table.php
    }
};
