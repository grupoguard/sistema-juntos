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
        $databaseName = DB::getDatabaseName();

        $indexes = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'financial'
        ", [$databaseName]);

        $indexNames = collect($indexes)->pluck('INDEX_NAME')->toArray();

        $possibleIndexes = [
            'financial_asaas_payment_id_index',
            'financial_asaas_customer_id_index',
            'financial_external_reference_index',
        ];

        foreach ($possibleIndexes as $indexName) {
            if (in_array($indexName, $indexNames, true)) {
                DB::statement("ALTER TABLE financial DROP INDEX {$indexName}");
            }
        }

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