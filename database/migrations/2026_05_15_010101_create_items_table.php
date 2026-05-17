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
        Schema::create('items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('level')->default(0);
            $table->unsignedTinyInteger('job_id')->nullable();
            $table->unsignedTinyInteger('stars')->default(0);
            $table->string('icon')->nullable();
            $table->boolean('is_craftable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
