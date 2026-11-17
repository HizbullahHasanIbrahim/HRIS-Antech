<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('period_label', 20);
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('appraiser_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->string('status', 10)->default('draf');
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('rating', 30)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('active_target_key', 45)->virtualAs("CASE WHEN status <> 'ditolak' AND deleted_at IS NULL THEN CONCAT(employee_id, ':', period_label) ELSE NULL END");
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique('active_target_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_targets');
    }
};
