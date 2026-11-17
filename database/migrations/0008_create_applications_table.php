<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->restrictOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->restrictOnDelete();
            $table->dateTime('applied_at');
            $table->decimal('aik_score', 5, 2)->nullable();
            $table->string('status', 10)->default('diajukan');
            $table->dateTime('decided_at')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['vacancy_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
