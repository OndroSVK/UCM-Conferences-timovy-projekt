<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every upload is a new version; older versions stay (O31).
        Schema::create('submission_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->restrictOnDelete();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('path'); // private disk, never a public URL (backend.md 4.5)
            $table->string('original_name');
            $table->unsignedInteger('size_bytes');
            $table->char('sha256', 64);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['submission_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_versions');
    }
};
