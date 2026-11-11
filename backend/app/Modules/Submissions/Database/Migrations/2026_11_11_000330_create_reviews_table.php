<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prepared for the full version only (R31): no feature writes this table in the prototype.
        // A reviewer recommendation is not a decision; review status is separate from submission status.
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->restrictOnDelete();
            $table->foreignId('submission_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 40); // assigned | draft | submitted
            $table->string('recommendation', 40)->nullable();
            $table->text('comments_to_author')->nullable();
            $table->date('due_on')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_version_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
