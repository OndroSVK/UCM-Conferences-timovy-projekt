<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->restrictOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->nullable(); // talk | poster; null when not required
            $table->string('title', 300)->nullable();
            // Changed only by Workflow\Actions\ChangeStatus. The only place of the submission status.
            $table->string('status', 40)->nullable();
            $table->timestamps();

            // PROPOSAL: Q05 — one submission per registration in the prototype; the model allows more later.
            $table->unique('registration_id');
            $table->index(['conference_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
