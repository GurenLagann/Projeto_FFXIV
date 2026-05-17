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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('item_id');
            $table->unsignedTinyInteger('job_id');
            $table->unsignedSmallInteger('craft_level')->default(1);
            $table->unsignedSmallInteger('yields')->default(1);
            $table->boolean('can_be_hq')->default(true);
            $table->unsignedTinyInteger('stars')->default(0);
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
