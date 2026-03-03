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
        Schema::create('edp_production_files', function (Blueprint $table) {
            $table->id();

            $table->string('file_name')->unique();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_hash', 64)->nullable();

            $table->unsignedInteger('total_lines')->default(0);
            $table->unsignedInteger('total_b_records')->default(0);

            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edp_production_files');
    }
};