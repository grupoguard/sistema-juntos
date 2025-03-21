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
        Schema::table('dependents', function (Blueprint $table) {
            $table->string('mom_name', 100)->after('name');
            $table->string('marital_status', 15)->after('rg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dependents', function (Blueprint $table) {
            $table->dropColumn(['mom_name', 'marital_status']);
        });
    }
};
