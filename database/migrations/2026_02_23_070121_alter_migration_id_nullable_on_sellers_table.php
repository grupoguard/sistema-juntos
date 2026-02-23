<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Se existir dado inválido/0 e você quiser normalizar opcionalmente:
        // DB::table('sellers')->where('migration_id', 0)->update(['migration_id' => null]);

        Schema::table('sellers', function (Blueprint $table) {
            $table->unsignedBigInteger('migration_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Se houver nulls, precisa tratar antes de voltar
        DB::table('sellers')->whereNull('migration_id')->update(['migration_id' => 0]);

        Schema::table('sellers', function (Blueprint $table) {
            $table->unsignedBigInteger('migration_id')->nullable(false)->default(0)->change();
            // Ajuste esse down conforme a definição original real da sua coluna
        });
    }
};