<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uniodonto_import_logs', function (Blueprint $table) {
            $table->id();

            $table->string('level', 20)->index(); // info|warning|error
            $table->string('action', 120)->index(); // client_upserted, dependent_upserted, order_upserted, ...
            $table->string('message', 500);

            // Para rastrear a família e o registro
            $table->unsignedBigInteger('assoc_code')->nullable()->index();
            $table->string('card_code', 60)->nullable()->index();
            $table->string('cpf', 11)->nullable()->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('dependent_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();

            $table->json('context')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniodonto_import_logs');
    }
};