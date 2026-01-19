<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            $table->dropUnique('financial_external_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('financial', function (Blueprint $table) {
            $table->dropIndex(['external_reference']);
        });
    }
};
