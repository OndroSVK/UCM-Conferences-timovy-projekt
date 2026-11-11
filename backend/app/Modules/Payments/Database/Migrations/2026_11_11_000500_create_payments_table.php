<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Manually recorded bank transfers; several per registration (R33). No bank API (R1).
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->restrictOnDelete();
            $table->foreignId('registration_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3);
            $table->date('paid_on');
            $table->string('variable_symbol', 10);
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index(['conference_id', 'variable_symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
