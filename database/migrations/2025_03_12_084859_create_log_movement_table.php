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
        Schema::create('log_movement', function (Blueprint $table) {
            $table->id();
            $table->string('register_code', 1);
            $table->string('installation_number', 9);
            $table->string('extra_value', 2)->nullable();
            $table->unsignedBigInteger('product_cod');
            $table->string('installment', 5);
            $table->string('reading_script', 15);
            $table->date('date_invoice');
            $table->string('city_code', 3);
            $table->date('date_movement');
            $table->string('value', 15);
            $table->string('code_return', 2);
            $table->string('future', 80)->nullable();
            $table->string('code_move', 1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_movement');
    }
};
