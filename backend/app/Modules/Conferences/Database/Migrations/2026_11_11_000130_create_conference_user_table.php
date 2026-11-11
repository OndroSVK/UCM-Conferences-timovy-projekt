<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff role per conference (BR4, BR8). extra_permissions = delegation, e.g. ["payments.record"].
        Schema::create('conference_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 40);
            $table->json('extra_permissions')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['conference_id', 'user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_user');
    }
};
