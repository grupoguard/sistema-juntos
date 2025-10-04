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
        // Ajustar o campo status para aceitar os status do Asaas
        DB::statement("ALTER TABLE financial MODIFY COLUMN status VARCHAR(50) DEFAULT 'PENDING'");
        
        // Adicionar comentário explicativo
        DB::statement("ALTER TABLE financial MODIFY COLUMN status VARCHAR(50) DEFAULT 'PENDING' 
            COMMENT 'Status: PENDING, RECEIVED, CONFIRMED, OVERDUE, REFUNDED, RECEIVED_IN_CASH, CANCELED'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para tipo original (ajuste conforme sua necessidade)
        DB::statement("ALTER TABLE financial MODIFY COLUMN status VARCHAR(50)");
    }
};
