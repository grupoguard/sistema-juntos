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
        Schema::table('log_register', function (Blueprint $table) {
            $table->string('address', 40)->nullable()->change();
            $table->string('name', 40)->nullable()->change();
            $table->string('number_installment', 40)->nullable()->change();
            $table->string('value_installment', 15)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('log_register', function (Blueprint $table) {
            $table->string('address', 40)->nullable(false)->change();
            $table->string('name', 40)->nullable(false)->change();
            $table->string('number_installment', 40)->nullable(false)->change();
            $table->string('value_installment', 15)->nullable(false)->change();
        });
    }
};
