<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gathering_items', function (Blueprint $table) {
            $table->string('item_id')->primary();
            $table->unsignedTinyInteger('gathering_level')->default(0);
            $table->unsignedTinyInteger('stars')->default(0);
            $table->enum('source', ['gathering', 'fishing'])->default('gathering');
            $table->timestamps();
            // Sem FK para items — sync filtra por existência em application code
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gathering_items');
    }
};
