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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->string('name', 100);
            $table->date('date_birth')->nullable();
            $table->string('cpf', 11);
            $table->string('rg', 9)->nullable();
            $table->string('phone', 11)->nullable();
            $table->string('email', 50);
            $table->string('zipcode', 8);
            $table->string('address', 100);
            $table->string('number', 10);
            $table->string('complement', 40)->nullable();
            $table->string('neighborhood', 50);
            $table->string('city', 50);
            $table->string('state', 2);
            $table->text('obs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
