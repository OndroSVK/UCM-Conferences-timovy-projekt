<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personal data shared by all registrations of one account. No age (R32).
        Schema::create('participant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('title_before', 50)->nullable();
            $table->string('title_after', 50)->nullable();
            $table->string('organization');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_profiles');
    }
};
