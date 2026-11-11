<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Committee decisions; the decided version is stored (DB02, DB12).
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->restrictOnDelete();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submission_version_id')->constrained()->restrictOnDelete();
            $table->string('decision', 40); // accepted | conditionally_accepted | rejected
            $table->text('justification'); // shown to the author
            $table->foreignId('decided_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('decided_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
