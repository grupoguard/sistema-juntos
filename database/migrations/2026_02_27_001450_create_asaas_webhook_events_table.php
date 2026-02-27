<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asaas_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 60)->unique();
            $table->string('event_type', 120)->index();
            $table->string('asaas_payment_id', 50)->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asaas_webhook_events');
    }
};