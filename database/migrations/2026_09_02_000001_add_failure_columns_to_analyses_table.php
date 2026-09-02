<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->timestamp('failed_at')->nullable()->after('completed_at');
            $table->text('error')->nullable()->after('failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropColumn(['failed_at', 'error']);
        });
    }
};
