<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every sending attempt (backend.md 4.6). No message bodies: only recipient, type, result.
        Schema::create('email_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient');
            $table->string('type', 40);
            $table->string('result', 20); // sent | failed
            $table->string('error', 1000)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['registration_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_log');
    }
};
