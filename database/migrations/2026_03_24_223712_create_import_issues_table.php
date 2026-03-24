<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_issues', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->index();
            $table->string('person_type', 20)->index();
            $table->string('name', 120)->nullable();
            $table->string('cpf', 11)->nullable()->index();
            $table->string('reason', 255);
            $table->json('row')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_issues');
    }
}; 