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
        Schema::create('financial_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_id')->constrained('financial')->onDelete('cascade');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->text('reason')->nullable(); // Motivo da mudança
            $table->string('changed_by', 50)->default('EDP'); // EDP, ASAAS, SYSTEM, USER
            $table->json('metadata')->nullable(); // Dados extras (code_return, code_move, etc)
            $table->timestamps();

            $table->index(['financial_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_history');
    }
};
