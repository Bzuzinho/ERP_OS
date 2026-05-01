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
        Schema::create('recurring_operation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_operation_id')->constrained('recurring_operations')->cascadeOnDelete();
            $table->timestamp('run_at');
            $table->string('status')->default('pending');
            $table->foreignId('generated_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('generated_event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_operation_runs');
    }
};
