<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One counter row per VS prefix; locked FOR UPDATE while generating (backend.md 10).
        Schema::create('variable_symbol_sequences', function (Blueprint $table) {
            $table->string('prefix', 4)->primary();
            $table->unsignedInteger('last_value')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variable_symbol_sequences');
    }
};
