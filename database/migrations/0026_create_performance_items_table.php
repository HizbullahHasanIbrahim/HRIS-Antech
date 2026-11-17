<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained('performance_targets')->restrictOnDelete();
            $table->text('description');
            $table->string('unit', 30);
            $table->decimal('target_value', 10, 2);
            $table->decimal('realization_value', 10, 2)->nullable();
            $table->decimal('weight', 5, 2);
            $table->decimal('score', 5, 2)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_items');
    }
};
