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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 100);
            $table->string('name', 100);
            $table->string('document', 14);
            $table->string('phone', 11);
            $table->string('email', 50)->nullable();
            $table->string('whatsapp', 11)->nullable();
            $table->string('site', 255)->nullable();
            $table->string('zipcode', 8);
            $table->string('address', 100)->nullable();
            $table->string('number', 10);
            $table->string('complement', 40)->nullable();
            $table->string('neighborhood', 50);
            $table->string('city', 50);
            $table->string('state', 2);
            $table->boolean('status')->default(true);
            $table->text('obs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
