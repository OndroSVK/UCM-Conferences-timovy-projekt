<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Test invoices (R20, R26). Not a payment status (R33). O32 is still open.
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->restrictOnDelete();
            $table->foreignId('registration_id')->constrained()->restrictOnDelete();
            $table->string('type', 20); // invoice | credit_note (credit note: full version)
            $table->string('number', 30)->nullable()->unique();
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3);
            $table->string('status', 20); // issued | failed
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->string('failure_reason', 1000)->nullable();
            $table->timestamps();

            // Exactly one invoice per registration (backend.md M8).
            $table->unique(['registration_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
