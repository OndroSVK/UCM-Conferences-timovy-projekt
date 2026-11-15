<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Link to the information on personal data processing shown at the consent (text supplied by UCM).
        Schema::table('conferences', function (Blueprint $table) {
            $table->string('privacy_url')->nullable()->after('accommodation_url');
        });
    }

    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn('privacy_url');
        });
    }
};
