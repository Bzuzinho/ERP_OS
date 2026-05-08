<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable()->after('completed_by');
            $table->foreignId('validated_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
            $table->text('validation_notes')->nullable()->after('validated_by');
            $table->longText('observations')->nullable()->after('validation_notes');
            $table->unsignedInteger('reopen_count')->default(0)->after('observations');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by');
            $table->dropColumn(['validated_at', 'validation_notes', 'observations', 'reopen_count']);
        });
    }
};
