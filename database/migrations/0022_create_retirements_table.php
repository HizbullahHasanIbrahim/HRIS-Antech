<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->smallInteger('retirement_age');
            $table->date('planned_date');
            $table->dateTime('proposed_at');
            $table->string('status', 12)->default('diusulkan');
            $table->foreignId('processed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('sk_number', 100)->nullable();
            $table->date('sk_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('active_retirement_emp_id')->virtualAs("CASE WHEN status <> 'dibatalkan' THEN employee_id ELSE NULL END");
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique('active_retirement_emp_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retirements');
    }
};
