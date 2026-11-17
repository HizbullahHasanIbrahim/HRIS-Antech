<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workload_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('academic_year', 9);
            $table->string('semester', 6);
            $table->decimal('teaching_credit', 5, 2)->default(0);
            $table->decimal('research_credit', 5, 2)->default(0);
            $table->decimal('service_credit', 5, 2)->default(0);
            $table->decimal('support_credit', 5, 2)->default(0);
            $table->decimal('total_credit', 6, 2)->storedAs('teaching_credit + research_credit + service_credit + support_credit');
            $table->string('status', 10)->default('draf');
            $table->foreignId('validator_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->dateTime('validated_at')->nullable();
            $table->smallInteger('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->text('revision_reason')->nullable();
            $table->unsignedBigInteger('current_employee_id')->virtualAs('CASE WHEN is_current = 1 THEN employee_id ELSE NULL END');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['current_employee_id', 'academic_year', 'semester'], 'uq_workload_current');
            $table->unique(['employee_id', 'academic_year', 'semester', 'version'], 'uq_workload_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workload_reports');
    }
};
