<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('participation_category_id')->constrained()->restrictOnDelete();
            $table->string('category_code', 40);
            $table->string('industry', 80)->nullable();

            // Billing data; exact fields to be delivered by UCM (Q11).
            $table->string('billing_type', 20); // participant | university | workplace
            $table->string('billing_name');
            $table->string('billing_street');
            $table->string('billing_city', 100);
            $table->string('billing_postal_code', 20);
            $table->string('billing_country', 100);
            $table->string('billing_company_id', 20)->nullable();
            $table->string('billing_tax_id', 20)->nullable();
            $table->string('billing_vat_id', 20)->nullable();

            // Price snapshot at registration (backend.md 4.4).
            $table->unsignedInteger('price_cents');
            $table->char('currency', 3);
            $table->boolean('fee_waived')->default(false);
            $table->string('fee_waiver_note', 500)->nullable();
            $table->string('variable_symbol', 10)->unique();

            // Changed only by Workflow\Actions\ChangeStatus (backend.md 4.3).
            $table->string('status', 40)->nullable();
            $table->string('payment_status', 40)->nullable(); // null = no payment required (BO4)

            $table->timestamp('data_processing_consent_at');
            $table->timestamps();

            $table->unique(['conference_id', 'user_id']); // one registration per conference (US-04)
            $table->index(['conference_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
