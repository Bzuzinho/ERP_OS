<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'service_area_id')) {
                $table->foreignId('service_area_id')->nullable()->after('department_id')->constrained('service_areas')->nullOnDelete();
            }

            if (! Schema::hasColumn('tickets', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('service_area_id')->constrained('teams')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }

            if (Schema::hasColumn('tickets', 'service_area_id')) {
                $table->dropForeign(['service_area_id']);
                $table->dropColumn('service_area_id');
            }
        });
    }
};
