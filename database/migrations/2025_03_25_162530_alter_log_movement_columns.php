<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('log_movement', function (Blueprint $table) {
            $table->dropColumn('product_cod'); // Remove a coluna antiga
        });
    
        Schema::table('log_movement', function (Blueprint $table) {
            $table->string('product_cod', 3)->nullable(); // Recria como string(3)
            $table->string('installment', 5)->nullable()->change();
            $table->string('reading_script', 15)->nullable()->change();
            $table->string('date_invoice', 6)->nullable()->change();
            $table->string('value', 15)->nullable()->change();
            $table->string('code_return', 2)->nullable()->change();
            $table->string('code_move', 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('log_movement', function (Blueprint $table) {
            $table->dropColumn('product_cod'); // Remove a coluna alterada
        });
    
        Schema::table('log_movement', function (Blueprint $table) {
            $table->unsignedBigInteger('product_cod'); // Restaura como BigInt
            $table->string('installment', 5)->nullable(false)->change();
            $table->string('reading_script', 15)->nullable(false)->change();
            $table->string('date_invoice', 8)->nullable(false)->change();
            $table->string('value', 15)->nullable(false)->change();
            $table->string('code_return', 2)->nullable(false)->change();
            $table->string('code_move', 2)->nullable(false)->change();
        });
    }
};
