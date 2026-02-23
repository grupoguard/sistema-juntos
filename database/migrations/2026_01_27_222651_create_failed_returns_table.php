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
        Schema::create('failed_returns', function (Blueprint $table) {
            $table->id();
            $table->string('record_type', 10); // B, F, UNKNOWN, ERROR
            $table->text('line_content'); // Linha completa que falhou
            $table->text('error_message'); // Mensagem de erro
            $table->date('arquivo_data')->nullable(); // Data do arquivo de origem
            $table->boolean('processed')->default(false); // Se já foi reprocessado
            $table->timestamp('processed_at')->nullable();
            $table->text('resolution_note')->nullable(); // Nota sobre a resolução
            $table->timestamps();

            $table->index(['processed', 'record_type']);
            $table->index('arquivo_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_returns');
    }
};
