<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_context')->nullable();
            $table->boolean('has_global_access')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        Schema::create('department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_context')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['department_id', 'user_id']);
        });

        $this->backfillOrganizationUserFromLegacyUsers();
    }

    public function down(): void
    {
        Schema::dropIfExists('department_user');
        Schema::dropIfExists('organization_user');
    }

    private function backfillOrganizationUserFromLegacyUsers(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('organization_user')) {
            return;
        }

        $now = now();

        $topRoleUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('roles.name', ['super_admin', 'admin_junta', 'executivo'])
            ->pluck('model_has_roles.model_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::table('users')
            ->select(['id', 'organization_id'])
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($now, $topRoleUserIds): void {
                $rows = [];

                foreach ($users as $user) {
                    $rows[] = [
                        'organization_id' => (int) $user->organization_id,
                        'user_id' => (int) $user->id,
                        'role_context' => null,
                        'has_global_access' => in_array((int) $user->id, $topRoleUserIds, true),
                        'is_default' => true,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('organization_user')->upsert(
                        $rows,
                        ['organization_id', 'user_id'],
                        ['role_context', 'has_global_access', 'is_default', 'is_active', 'updated_at'],
                    );
                }
            });
    }
};
