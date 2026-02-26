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
        Schema::create('asaas_unmatched_payments', function (Blueprint $table) {
            $table->id();
            $table->string('asaas_payment_id', 50)->index();
            $table->string('asaas_customer_id', 50)->nullable()->index();
            $table->string('cpf', 11)->nullable()->index();
            $table->string('reason', 120);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique('asaas_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asaas_unmatched_payments');
    }
};
