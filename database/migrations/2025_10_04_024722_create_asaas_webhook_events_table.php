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
        Schema::create('asaas_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100); // PAYMENT_RECEIVED, PAYMENT_CONFIRMED, etc
            $table->string('asaas_payment_id', 50)->nullable();
            $table->unsignedBigInteger('financial_id')->nullable();
            $table->json('payload'); // Payload completo do webhook
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('event_type');
            $table->index('asaas_payment_id');
            $table->index('financial_id');
            $table->index(['processed', 'created_at']);
            
            // Foreign key
            $table->foreign('financial_id')->references('id')->on('financial')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asaas_webhook_events');
    }
};
