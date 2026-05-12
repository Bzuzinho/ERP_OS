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
        Schema::create('resource_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_request_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->decimal('quantity_requested', 10, 2);
            $table->decimal('quantity_approved', 10, 2)->nullable();
            $table->decimal('quantity_delivered', 10, 2)->nullable();
            $table->decimal('quantity_returned', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->foreign('resource_request_id')->references('id')->on('resource_requests')->onDelete('cascade');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            $table->index(['resource_request_id']);
            $table->index(['inventory_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_request_items');
    }
};
