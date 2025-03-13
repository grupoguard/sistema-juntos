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
        Schema::create('log_register', function (Blueprint $table) {
            $table->id();
            $table->string('register_code', 1);
            $table->unsignedBigInteger('installation_number');
            $table->string('extra_value', 2)->nullable();
            $table->unsignedBigInteger('product_cod');
            $table->string('number_installment', 2);
            $table->string('value_installment', 15);
            $table->string('future1', 9)->nullable();
            $table->string('city_code', 3);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('address', 40);
            $table->string('name', 40);
            $table->string('future2', 7)->nullable();
            $table->string('code_anomaly', 2);
            $table->string('code_move', 1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_register');
    }
};
