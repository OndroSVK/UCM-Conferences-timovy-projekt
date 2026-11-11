<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Number series per conference; locked FOR UPDATE while issuing.
        Schema::create('invoice_number_sequences', function (Blueprint $table) {
            $table->foreignId('conference_id')->primary()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('last_value')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_number_sequences');
    }
};
