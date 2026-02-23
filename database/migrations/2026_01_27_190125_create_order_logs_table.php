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
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id');

            $table->string('table_ajust', 50); // tabela afetada

            $table->json('obj_antes_alteracao')->nullable();
            $table->json('obj_depois_alteracao')->nullable();

            $table->string('order_status', 30)->nullable();

            $table->timestamp('created_at')->useCurrent();

            // índices
            $table->index('user_id');
            $table->index('order_id');
            $table->index('table_ajust');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logs');
    }
};
