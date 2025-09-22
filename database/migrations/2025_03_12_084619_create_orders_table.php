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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->string('charge_type', 20);
            $table->string('installation_number', 9)->nullable();
            $table->string('approval_name', 50)->nullable();
            $table->string('approval_by', 20)->nullable();
            $table->date('evidence_date');
            $table->unsignedSmallInteger('charge_date');
            $table->decimal('accession', 10, 2);
            $table->string('accession_payment', 50)->nullable(); //Forma de pagamento da adesão
            $table->enum('discount_type', ['R$', '%'])->nullable(); // Tipo de desconto (real ou percentual)
            $table->decimal('discount_value', 10, 2)->nullable(); // Valor do desconto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
