<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->index('job_id');
            $table->index('craft_level');
            $table->index(['job_id', 'craft_level']);
        });

        Schema::table('recipe_materials', function (Blueprint $table) {
            $table->index('recipe_id');
            $table->index('material_id');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex(['job_id']);
            $table->dropIndex(['craft_level']);
            $table->dropIndex(['job_id', 'craft_level']);
        });

        Schema::table('recipe_materials', function (Blueprint $table) {
            $table->dropIndex(['recipe_id']);
            $table->dropIndex(['material_id']);
        });
    }
};
