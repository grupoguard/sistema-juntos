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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code');
            $table->string('name', 100);
            $table->decimal('value', 10, 2); 
            $table->decimal('accession', 10, 2); 
            $table->unsignedTinyInteger('dependents_limit');
            $table->string('recurrence', 20);
            $table->unsignedInteger('lack');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
