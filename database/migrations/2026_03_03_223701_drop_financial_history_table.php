<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('financial_history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('financial_history', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }
};