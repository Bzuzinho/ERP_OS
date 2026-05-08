<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            if (! Schema::hasColumn('service_areas', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('organization_id')->constrained('service_areas')->nullOnDelete();
            }

            if (! Schema::hasColumn('service_areas', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('parent_id')->constrained('departments')->nullOnDelete();
            }

            if (! Schema::hasColumn('service_areas', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            if (Schema::hasColumn('service_areas', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }

            if (Schema::hasColumn('service_areas', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
        });
    }
};
