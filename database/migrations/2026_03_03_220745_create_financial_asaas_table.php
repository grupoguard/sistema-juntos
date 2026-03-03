<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_asaas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_id')
                ->constrained('financial')
                ->cascadeOnDelete();

            $table->string('asaas_payment_id', 50)->nullable()->index();
            $table->string('asaas_customer_id', 50)->nullable()->index();
            $table->string('external_reference', 100)->nullable()->index();

            $table->text('invoice_url')->nullable();
            $table->text('bank_slip_url')->nullable();
            $table->text('pix_qr_code')->nullable();
            $table->text('pix_qr_code_url')->nullable();

            $table->timestamps();

            $table->unique('financial_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_asaas');
    }
};