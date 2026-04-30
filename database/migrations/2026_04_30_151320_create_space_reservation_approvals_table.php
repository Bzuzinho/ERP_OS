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
        Schema::create('space_reservation_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_reservation_id')->constrained('space_reservations')->cascadeOnDelete();
            $table->string('action');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_reservation_approvals');
    }
};
