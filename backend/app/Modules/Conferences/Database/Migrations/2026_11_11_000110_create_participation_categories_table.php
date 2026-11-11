<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participation_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40); // speaker | poster_author | passive_participant
            $table->string('name');
            $table->unsignedInteger('price_cents'); // never hard-coded (R32)
            $table->string('submission_requirement', 20); // abstract | poster | none
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['conference_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participation_categories');
    }
};
