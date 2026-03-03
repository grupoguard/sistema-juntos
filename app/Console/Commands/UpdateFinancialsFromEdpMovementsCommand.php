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
        Schema::create('financial_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_id')
                ->constrained('financial')
                ->cascadeOnDelete();

            $table->enum('provider', [
                'ASAAS',
                'EDP',
                'SYSTEM',
                'MANUAL',
            ])->nullable()->index();

            $table->enum('source_type', [
                'IMPORT',
                'WEBHOOK',
                'API',
                'LOG_MOVEMENT',
                'LOG_REGISTER',
                'MANUAL',
                'SYSTEM',
                'COMMAND',
            ])->nullable()->index();

            $table->unsignedBigInteger('source_id')->nullable()->index();

            $table->enum('event_name', [
                'CREATED',
                'UPDATED',
                'STATUS_CHANGED',
                'ASAAS_IMPORTED',
                'ASAAS_PAYMENT_CREATED',
                'ASAAS_PAYMENT_CONFIRMED',
                'ASAAS_PAYMENT_RECEIVED',
                'ASAAS_PAYMENT_OVERDUE',
                'ASAAS_PAYMENT_REFUNDED',
                'EDP_SENT',
                'EDP_CONFIRMED',
                'EDP_REPROVED',
                'EDP_RECEIVED',
                'EDP_RETURN_03_NOT_BILLED',
                'EDP_RETURN_04_REVISION_RETURN',
                'EDP_RETURN_05_REVISION_CHARGE',
                'EDP_RETURN_06_PAYMENT',
                'EDP_RETURN_07_BACK_TO_DEBIT',
                'MIGRATED',
                'OBS_UPDATED',
            ])->index();

            $table->string('old_status', 50)->nullable()->index();
            $table->string('new_status', 50)->nullable()->index();

            $table->text('message')->nullable();
            $table->json('payload')->nullable();

            $table->dateTime('event_date')->nullable()->index();

            $table->timestamps();

            $table->index(['financial_id', 'event_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_logs');
    }
};