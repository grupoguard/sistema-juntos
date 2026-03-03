<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            $table->dropIndex(['asaas_payment_id']);
            $table->dropIndex(['asaas_customer_id']);
            $table->dropIndex(['external_reference']);
        });

        Schema::table('financial', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_payment_id',
                'asaas_customer_id',
                'external_reference',
                'invoice_url',
                'bank_slip_url',
                'pix_qr_code',
                'pix_qr_code_url',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            $table->string('asaas_payment_id', 50)->nullable()->after('order_id');
            $table->string('asaas_customer_id', 50)->nullable()->after('asaas_payment_id');
            $table->string('external_reference', 100)->nullable()->after('payment_method');
            $table->text('invoice_url')->nullable()->after('external_reference');
            $table->text('bank_slip_url')->nullable()->after('invoice_url');
            $table->text('pix_qr_code')->nullable()->after('bank_slip_url');
            $table->text('pix_qr_code_url')->nullable()->after('pix_qr_code');

            $table->index('asaas_payment_id');
            $table->index('asaas_customer_id');
            $table->index('external_reference');
        });
    }
};