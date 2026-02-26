<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uniodonto_memberships', function (Blueprint $table) {
            $table->id();

            // Códigos da Uniodonto
            $table->unsignedBigInteger('assoc_code')->index();     // Cod. Assoc.
            $table->unsignedBigInteger('user_code')->index();      // Cod. Usuario
            $table->string('card_code', 60)->index();              // Cod. CartAo (vem com colchetes no CSV)
            $table->unsignedBigInteger('plan_code')->index();      // Cod. Plano
            $table->string('plan_name', 120)->nullable();          // Plano

            // Dono do vínculo: pode ser Client ou Dependent
            $table->string('owner_type', 60)->index(); // App\Models\Client ou App\Models\Dependent
            $table->unsignedBigInteger('owner_id')->index();

            // Campos auxiliares
            $table->string('relationship', 40)->nullable(); // Grau Parentesco normalizado (titular/filho/etc)

            $table->timestamps();

            // Um cartão deve ser único
            $table->unique('card_code');

            // Evita duplicar o mesmo membro em uma associação
            $table->unique(['assoc_code', 'user_code', 'owner_type', 'owner_id'], 'uniodonto_assoc_user_owner_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniodonto_memberships');
    }
};