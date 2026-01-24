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
        Schema::table('financial', function (Blueprint $table) {
            //IDs Asaas
            $table->string('asaas_payment_id', 50)->nullable()->unique()->after('order_id');
            $table->string('asaas_customer_id', 50)->nullable()->after('asaas_payment_id');
            
            //Data de vencimento
            $table->date('due_date')->nullable()->after('charge_date');

            //Método de pagamento
            $table->enum('payment_method', ['BOLETO', 'CREDIT_CARD', 'PIX', 'DEBIT_CARD'])
                  ->default('BOLETO')
                  ->after('due_date');

            //Referência externa única (para evitar duplicatas)
            $table->string('external_reference', 100)->nullable()->unique()->after('payment_method');

            //URLs e dados adicionais
            $table->text('invoice_url')->nullable()->after('external_reference');
            $table->text('bank_slip_url')->nullable()->after('invoice_url');
            $table->text('pix_qr_code')->nullable()->after('bank_slip_url');
            $table->text('pix_qr_code_url')->nullable()->after('pix_qr_code');

            // Descrição da cobrança
            $table->string('description', 500)->nullable()->after('pix_qr_code_url');

            // Índices para melhor performance
            $table->index('asaas_payment_id');
            $table->index('asaas_customer_id');
            $table->index('external_reference');
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_payment_id',
                'asaas_customer_id',
                'due_date',
                'payment_method',
                'external_reference',
                'invoice_url',
                'bank_slip_url',
                'pix_qr_code',
                'pix_qr_code_url',
                'description',
            ]);
        });
    }
};
