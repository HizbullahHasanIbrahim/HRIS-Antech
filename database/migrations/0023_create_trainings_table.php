<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('retirement_id')->nullable()->constrained('retirements')->restrictOnDelete();
            $table->string('title', 200);
            $table->string('training_type', 15);
            $table->string('organizer', 150)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->smallInteger('duration_hours')->nullable();
            $table->string('status', 12)->default('dijadwalkan');
            $table->string('certificate_number', 100)->nullable();
            $table->string('certificate_path', 255)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
