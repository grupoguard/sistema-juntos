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
        Schema::table('retornos_armazenados', function (Blueprint $table) {
            if (!Schema::hasColumn('retornos_armazenados', 'processado')) {
                $table->boolean('processado')->default(false)->after('baixado_em');
            }
            if (!Schema::hasColumn('retornos_armazenados', 'processado_em')) {
                $table->timestamp('processado_em')->nullable()->after('processado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retornos_armazenados', function (Blueprint $table) {
            $table->dropColumn(['processado', 'processado_em']);
        });;
    }
};
