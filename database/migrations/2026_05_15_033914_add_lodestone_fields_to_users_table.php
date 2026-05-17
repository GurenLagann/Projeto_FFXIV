<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('lodestone_id')->nullable()->unique()->after('email');
            $table->string('character_name')->nullable()->after('lodestone_id');
            $table->string('character_server')->nullable()->after('character_name');
            $table->string('character_avatar')->nullable()->after('character_server');
            $table->string('verification_code')->nullable()->after('character_avatar');
            $table->timestamp('character_verified_at')->nullable()->after('verification_code');
            $table->json('job_levels')->nullable()->after('character_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'lodestone_id', 'character_name', 'character_server',
                'character_avatar', 'verification_code', 'character_verified_at', 'job_levels',
            ]);
        });
    }
};
