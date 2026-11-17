<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_rank_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('rank_name', 30);
            $table->decimal('credit_points', 8, 2)->nullable();
            $table->boolean('is_accelerated')->default(false);
            $table->string('status', 12)->default('diajukan');
            $table->dateTime('submitted_at');
            $table->foreignId('approver_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->dateTime('decided_at')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('sk_number', 100)->nullable();
            $table->date('sk_date')->nullable();
            $table->text('decision_note')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_rank_histories');
    }
};
