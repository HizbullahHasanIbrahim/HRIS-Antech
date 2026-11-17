<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('departments')->restrictOnDelete();
            $table->string('title', 150);
            $table->string('category', 10);
            $table->text('description')->nullable();
            $table->text('min_qualification');
            $table->smallInteger('quota');
            $table->date('open_date');
            $table->date('close_date');
            $table->string('status', 10)->default('draf');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
