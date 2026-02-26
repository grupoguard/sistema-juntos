<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove as FKs atuais
            $table->dropForeign(['client_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['group_id']);
            $table->dropForeign(['seller_id']);

            // Recria com RESTRICT
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->restrictOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->restrictOnDelete();

            $table->foreign('seller_id')
                ->references('id')
                ->on('sellers')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove as FKs com restrict
            $table->dropForeign(['client_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['group_id']);
            $table->dropForeign(['seller_id']);

            // Volta para cascade (rollback)
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->cascadeOnDelete();

            $table->foreign('seller_id')
                ->references('id')
                ->on('sellers')
                ->cascadeOnDelete();
        });
    }
};