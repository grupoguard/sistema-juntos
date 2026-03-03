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
        Schema::create('edp_production_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('edp_production_file_id')
                ->constrained('edp_production_files')
                ->cascadeOnDelete();

            $table->unsignedInteger('line_number');

            $table->string('register_code', 1);
            $table->string('installation_number', 9)->index();
            $table->string('extra_value_code', 2)->nullable();
            $table->string('product_code', 3)->nullable();
            $table->unsignedSmallInteger('number_installments')->nullable();
            $table->decimal('installment_value', 10, 2)->nullable();
            $table->string('future_field_1', 12)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('address', 40)->nullable();
            $table->string('future_field_2', 47)->nullable();
            $table->string('billing_status_code', 2)->nullable();
            $table->string('movement_code', 1)->nullable();

            $table->text('raw_line');

            $table->timestamp('financial_created_at')->nullable();
            $table->text('financial_error')->nullable();

            $table->timestamps();

            $table->unique(['edp_production_file_id', 'line_number'], 'edp_production_records_file_line_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edp_production_records');
    }
};