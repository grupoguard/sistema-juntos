<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            $table->text('obs')->nullable()->after('description');
            $table->index('obs'); // Para facilitar busca por divergências
        });
    }

    public function down(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            $table->dropColumn('obs');
        });
    }
};
