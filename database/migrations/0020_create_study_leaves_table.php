<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('degree_level', 5)->default('S3');
            $table->string('university', 150);
            $table->string('study_program', 150);
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('status', 15)->default('direncanakan');
            $table->string('scholarship_name', 150)->nullable();
            $table->string('scholarship_status', 10)->nullable()->default('belum');
            $table->text('mentoring_notes')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_leaves');
    }
};
