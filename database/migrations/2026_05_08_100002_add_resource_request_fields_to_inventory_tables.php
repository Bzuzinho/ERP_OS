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
        // Add resource_request tracking to inventory_loans
        if (Schema::hasTable('inventory_loans')) {
            Schema::table('inventory_loans', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_loans', 'resource_request_id')) {
                    $table->unsignedBigInteger('resource_request_id')->nullable()->after('organization_id');
                    $table->foreign('resource_request_id')->references('id')->on('resource_requests')->onDelete('set null');
                }
                if (!Schema::hasColumn('inventory_loans', 'resource_request_item_id')) {
                    $table->unsignedBigInteger('resource_request_item_id')->nullable()->after('resource_request_id');
                    $table->foreign('resource_request_item_id')->references('id')->on('resource_request_items')->onDelete('set null');
                }
            });
        }

        // Add resource_request tracking to inventory_movements
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_movements', 'resource_request_id')) {
                    $table->unsignedBigInteger('resource_request_id')->nullable()->after('organization_id');
                    $table->foreign('resource_request_id')->references('id')->on('resource_requests')->onDelete('set null');
                }
                if (!Schema::hasColumn('inventory_movements', 'resource_request_item_id')) {
                    $table->unsignedBigInteger('resource_request_item_id')->nullable()->after('resource_request_id');
                    $table->foreign('resource_request_item_id')->references('id')->on('resource_request_items')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_loans')) {
            Schema::table('inventory_loans', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_loans', 'resource_request_id')) {
                    $table->dropForeign(['resource_request_id']);
                }
                if (Schema::hasColumn('inventory_loans', 'resource_request_item_id')) {
                    $table->dropForeign(['resource_request_item_id']);
                }
                if (Schema::hasColumn('inventory_loans', 'resource_request_id')) {
                    $table->dropColumn('resource_request_id');
                }
                if (Schema::hasColumn('inventory_loans', 'resource_request_item_id')) {
                    $table->dropColumn('resource_request_item_id');
                }
            });
        }

        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_movements', 'resource_request_id')) {
                    $table->dropForeign(['resource_request_id']);
                }
                if (Schema::hasColumn('inventory_movements', 'resource_request_item_id')) {
                    $table->dropForeign(['resource_request_item_id']);
                }
                if (Schema::hasColumn('inventory_movements', 'resource_request_id')) {
                    $table->dropColumn('resource_request_id');
                }
                if (Schema::hasColumn('inventory_movements', 'resource_request_item_id')) {
                    $table->dropColumn('resource_request_item_id');
                }
            });
        }
    }
};
