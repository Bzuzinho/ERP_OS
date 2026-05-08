<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_area_user', function (Blueprint $table) {
            if (! Schema::hasColumn('service_area_user', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->after('user_id')->constrained('organizations')->nullOnDelete();
            }

            if (! Schema::hasColumn('service_area_user', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_primary');
            }

            if (! Schema::hasColumn('service_area_user', 'role_context')) {
                $table->string('role_context')->nullable()->after('role');
            }
        });

        $this->backfillOrganizationId();
    }

    public function down(): void
    {
        Schema::table('service_area_user', function (Blueprint $table) {
            if (Schema::hasColumn('service_area_user', 'organization_id')) {
                $table->dropConstrainedForeignId('organization_id');
            }

            if (Schema::hasColumn('service_area_user', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('service_area_user', 'role_context')) {
                $table->dropColumn('role_context');
            }
        });
    }

    private function backfillOrganizationId(): void
    {
        DB::table('service_area_user')
            ->select(['id', 'service_area_id'])
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                $serviceAreaIds = $rows->pluck('service_area_id')->all();
                $organizationByServiceArea = DB::table('service_areas')
                    ->whereIn('id', $serviceAreaIds)
                    ->pluck('organization_id', 'id');

                foreach ($rows as $row) {
                    DB::table('service_area_user')
                        ->where('id', $row->id)
                        ->update([
                            'organization_id' => $organizationByServiceArea[$row->service_area_id] ?? null,
                            'is_active' => true,
                        ]);
                }
            });
    }
};
