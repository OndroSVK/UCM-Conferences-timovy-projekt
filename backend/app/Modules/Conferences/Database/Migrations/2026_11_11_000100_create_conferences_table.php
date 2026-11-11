<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain', 63)->unique(); // e.g. "ans2027" for ans2027.ucm.sk (R22, R23)
            $table->unsignedSmallInteger('year');
            $table->string('venue')->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->date('registration_deadline');
            $table->date('payment_deadline');
            $table->date('submission_deadline');
            $table->char('currency', 3);
            // Payment instructions (R1); payee is the issuer of invoices.
            $table->string('iban', 34);
            $table->string('bic', 11)->nullable();
            $table->string('payee_name');
            // Invoice issuer (FPV UCM); values verified by the faculty before production (O29).
            $table->string('issuer_name');
            $table->string('issuer_address');
            $table->string('issuer_company_id', 20)->nullable();
            $table->string('issuer_tax_id', 20)->nullable();
            $table->string('issuer_vat_id', 20)->nullable();
            $table->string('accommodation_url')->nullable(); // only a link (R34)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};
