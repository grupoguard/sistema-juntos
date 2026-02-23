<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE financial MODIFY COLUMN status VARCHAR(50) DEFAULT 'PENDING' 
            COMMENT 'Status: PENDING, RECEIVED, CONFIRMED, OVERDUE, REFUNDED, RECEIVED_IN_CASH, CANCELED, SENDING, REPROVED'");
        
        // Adicionar payment_method EDP se não existir
        if (!Schema::hasColumn('financial', 'payment_method')) {
            Schema::table('financial', function (Blueprint $table) {
                $table->enum('payment_method', ['BOLETO', 'CREDIT_CARD', 'PIX', 'DEBIT_CARD', 'EDP'])
                    ->default('BOLETO')
                    ->after('due_date');
            });
        } else {
            DB::statement("ALTER TABLE financial MODIFY COLUMN payment_method 
                ENUM('BOLETO', 'CREDIT_CARD', 'PIX', 'DEBIT_CARD', 'EDP') DEFAULT 'BOLETO'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            //
        });
    }
};
