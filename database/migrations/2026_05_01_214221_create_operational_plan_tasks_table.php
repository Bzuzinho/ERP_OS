<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operational_plan_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operational_plan_id')->constrained('operational_plans')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_milestone')->default(false);
            $table->unsignedTinyInteger('weight')->nullable();
            $table->timestamps();

            $table->unique(['operational_plan_id', 'task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_plan_tasks');
    }
};
