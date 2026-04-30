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
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('location_text')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status')->default('available');
            $table->boolean('requires_approval')->default(true);
            $table->boolean('has_cleaning_required')->default(false);
            $table->boolean('has_deposit')->default(false);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->text('rules')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
