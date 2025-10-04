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
        Schema::create('asaas_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 100); // create_customer, create_payment, webhook_received, etc
            $table->string('asaas_id', 50)->nullable(); // ID retornado pelo Asaas
            $table->string('entity_type', 50)->nullable(); // customer, payment, transfer, etc
            $table->unsignedBigInteger('entity_id')->nullable(); // ID da entidade local (client_id, financial_id)
            $table->json('request_data')->nullable(); // Dados enviados para o Asaas
            $table->json('response_data')->nullable(); // Dados recebidos do Asaas
            $table->enum('status', ['success', 'error'])->default('success');
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable(); // IP de origem (útil para webhooks)
            $table->timestamps();
            
            // Índices
            $table->index('action');
            $table->index('asaas_id');
            $table->index(['entity_type', 'entity_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asaas_logs');
    }
};
