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
        Schema::create('inventory_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('borrower_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('borrower_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->timestamp('loaned_at');
            $table->timestamp('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('loaned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('related_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('related_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('related_event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('related_space_reservation_id')->nullable()->constrained('space_reservations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_loans');
    }
};
