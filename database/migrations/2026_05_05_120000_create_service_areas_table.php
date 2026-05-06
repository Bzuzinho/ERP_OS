<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
        });

        Schema::create('service_area_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_area_id')->constrained('service_areas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['service_area_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_area_user');
        Schema::dropIfExists('service_areas');
    }
};
