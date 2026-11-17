<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('work_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_hours', 4, 2);
            $table->text('reason');
            $table->string('status', 12)->default('diajukan');
            $table->foreignId('approver_id')->constrained('employees')->restrictOnDelete();
            $table->dateTime('submitted_at');
            $table->dateTime('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
