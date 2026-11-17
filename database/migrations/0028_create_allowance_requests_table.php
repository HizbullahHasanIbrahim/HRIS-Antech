<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('allowance_type_id')->constrained('allowance_types')->restrictOnDelete();
            $table->string('period_label', 20);
            $table->decimal('amount', 14, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 12)->default('diajukan');
            $table->foreignId('approver_id')->constrained('employees')->restrictOnDelete();
            $table->dateTime('submitted_at');
            $table->dateTime('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('active_allowance_key', 64)->virtualAs("CASE WHEN status NOT IN ('ditolak', 'dibatalkan') THEN CONCAT(employee_id, ':', allowance_type_id, ':', period_label) ELSE NULL END");
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique('active_allowance_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowance_requests');
    }
};
