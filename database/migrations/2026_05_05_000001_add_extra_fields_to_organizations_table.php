<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('phone_secondary')->nullable()->after('phone');
            $table->string('fax')->nullable()->after('phone_secondary');
            $table->string('website')->nullable()->after('fax');
            $table->string('postal_code')->nullable()->after('address');
            $table->string('city')->nullable()->after('postal_code');
            $table->string('county')->nullable()->after('city');       // Concelho
            $table->string('district')->nullable()->after('county');   // Distrito
            $table->string('country')->default('Portugal')->after('district');
            $table->string('president_name')->nullable()->after('country');
            $table->string('iban')->nullable()->after('president_name');
            $table->string('facebook_url')->nullable()->after('iban');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->text('description')->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'phone_secondary',
                'fax',
                'website',
                'postal_code',
                'city',
                'county',
                'district',
                'country',
                'president_name',
                'iban',
                'facebook_url',
                'instagram_url',
                'description',
            ]);
        });
    }
};
