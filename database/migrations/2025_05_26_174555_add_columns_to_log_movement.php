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
        Schema::table('log_movement', function (Blueprint $table) {
            $table->date('arquivo_data')->nullable()->after('code_move');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_movement', function (Blueprint $table) {
            $table->dropColumn('arquivo_data');
        });
    }
};
