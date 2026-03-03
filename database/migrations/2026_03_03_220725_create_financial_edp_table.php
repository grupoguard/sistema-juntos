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
        Schema::create('financial_edp', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_id')
                ->constrained('financial')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('first_log_movement_id')->nullable()->index();
            $table->unsignedBigInteger('last_log_movement_id')->nullable()->index();
            $table->unsignedBigInteger('confirmed_log_movement_id')->nullable()->index();
            $table->unsignedBigInteger('received_log_movement_id')->nullable()->index();

            $table->string('last_return_code', 2)->nullable()->index();
            $table->string('last_status', 50)->nullable()->index();
            $table->dateTime('last_event_at')->nullable()->index();

            $table->timestamps();

            $table->unique('financial_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_edp');
    }
};