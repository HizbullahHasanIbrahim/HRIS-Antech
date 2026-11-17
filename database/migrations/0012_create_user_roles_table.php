<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('departments')->restrictOnDelete();
            $table->unsignedBigInteger('unit_id_coalesced')->virtualAs('COALESCE(unit_id, 0)');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['user_id', 'role_id', 'unit_id_coalesced'], 'uq_user_role_unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
