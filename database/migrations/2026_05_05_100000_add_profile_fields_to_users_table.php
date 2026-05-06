<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nif')->nullable()->after('email');
            $table->string('phone')->nullable()->after('nif');
            $table->text('address')->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('address');
            $table->string('avatar_path')->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nif', 'phone', 'address', 'birth_date', 'avatar_path']);
        });
    }
};
