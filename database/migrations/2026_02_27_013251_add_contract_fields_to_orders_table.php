<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // link do contrato assinado (serviço externo)
            $table->string('signed_contract_url')->nullable()->after('address_proof_file');

            // scan do contrato físico assinado
            $table->string('signed_physical_contract_file')->nullable()->after('signed_contract_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['signed_contract_url', 'signed_physical_contract_file']);
        });
    }
};