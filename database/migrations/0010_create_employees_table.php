<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->unique();
            $table->string('nidn', 20)->nullable()->unique();
            $table->string('full_name', 150);
            $table->string('category', 10);
            $table->char('gender', 1);
            $table->string('birth_place', 100);
            $table->date('birth_date');
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('highest_education', 10);
            $table->foreignId('unit_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->unsignedBigInteger('application_id')->nullable()->unique();
            $table->string('employment_status', 10)->default('aktif');
            $table->date('join_date');
            $table->date('end_date')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
            $table->fullText('full_name');

            $table->foreign('application_id')->references('id')->on('applications')->restrictOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::dropIfExists('employees');
    }
};
