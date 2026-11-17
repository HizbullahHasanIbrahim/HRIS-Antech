<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->restrictOnDelete();
            $table->string('stage_name', 50);
            $table->smallInteger('stage_order');
            $table->decimal('score', 5, 2)->nullable();
            $table->string('result', 10)->default('menunggu');
            $table->foreignId('assessed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('assessed_at')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['application_id', 'stage_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_stages');
    }
};
