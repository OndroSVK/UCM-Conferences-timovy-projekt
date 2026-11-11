<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Option lists of the registration form, e.g. field "industry" (odvetvie).
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('field', 40);
            $table->string('value', 80);
            $table->string('label');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['conference_id', 'field', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
