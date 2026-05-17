<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_lookup', function (Blueprint $table) {
            $table->string('item_id')->primary();
            $table->unsignedBigInteger('crp_id')->nullable();
            $table->unsignedBigInteger('bsm_id')->nullable();
            $table->unsignedBigInteger('arm_id')->nullable();
            $table->unsignedBigInteger('gsm_id')->nullable();
            $table->unsignedBigInteger('ltw_id')->nullable();
            $table->unsignedBigInteger('wvr_id')->nullable();
            $table->unsignedBigInteger('alc_id')->nullable();
            $table->unsignedBigInteger('cul_id')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_lookup');
    }
};
