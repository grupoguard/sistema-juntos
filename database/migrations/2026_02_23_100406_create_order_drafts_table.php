<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_drafts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();

            $table->string('status')->default('EM_PREENCHIMENTO');
            // EM_PREENCHIMENTO | ENVIADO | CANCELADO (se quiser)
            
            $table->unsignedTinyInteger('current_step')->default(1);

            // dados do formulário em progresso
            $table->json('payload')->nullable();

            // uploads temporários já salvos em disco
            $table->string('document_file')->nullable();
            $table->string('document_file_type')->nullable();
            $table->string('address_proof_file')->nullable();

            $table->timestamp('last_interaction_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();
            $table->foreign('seller_id')->references('id')->on('sellers')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();

            $table->index(['user_id', 'status']);
            $table->index(['current_step']);
            $table->index(['last_interaction_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_drafts');
    }
};