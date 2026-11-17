<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('bpjs_type', 20);
            $table->string('membership_number', 30)->unique();
            $table->date('registered_date');
            $table->string('status', 10)->default('aktif');
            $table->date('end_date')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->unsignedBigInteger('active_employee_id')->virtualAs("CASE WHEN status = 'aktif' AND deleted_at IS NULL THEN employee_id ELSE NULL END");
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['active_employee_id', 'bpjs_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_memberships');
    }
};
