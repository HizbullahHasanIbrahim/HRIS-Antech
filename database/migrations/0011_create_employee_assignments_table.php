<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->string('category', 10);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assignments');
    }
};
