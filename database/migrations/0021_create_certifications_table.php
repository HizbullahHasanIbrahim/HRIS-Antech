<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('cert_type', 50);
            $table->string('cert_number', 50)->nullable()->unique();
            $table->date('submitted_date');
            $table->date('result_date')->nullable();
            $table->string('status', 12)->default('diajukan');
            $table->string('certificate_path', 255)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
