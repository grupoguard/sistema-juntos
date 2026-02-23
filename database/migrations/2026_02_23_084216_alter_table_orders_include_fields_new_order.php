<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Uploads
            $table->string('document_file')->nullable()->after('discount_value'); 
            // RG/CNH (imagem)
            $table->string('document_file_type')->nullable()->after('document_file'); 
            // 'RG' | 'CNH' (opcional, útil no futuro)

            $table->string('address_proof_file')->nullable()->after('document_file_type');

            // Fluxo de revisão/admin
            $table->string('review_status')->default('PENDENTE')->after('address_proof_file');
            // PENDENTE | APROVADO | REJEITADO

            $table->timestamp('admin_viewed_at')->nullable()->after('review_status');
            $table->unsignedBigInteger('admin_viewed_by')->nullable()->after('admin_viewed_at');

            $table->timestamp('reviewed_at')->nullable()->after('admin_viewed_by');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->text('review_notes')->nullable()->after('reviewed_by');

            $table->foreign('admin_viewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();

            // índices para dashboard/pendências
            $table->index(['review_status']);
            $table->index(['admin_viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['admin_viewed_by']);
            $table->dropForeign(['reviewed_by']);

            $table->dropIndex(['review_status']);
            $table->dropIndex(['admin_viewed_at']);

            $table->dropColumn([
                'document_file',
                'document_file_type',
                'address_proof_file',
                'review_status',
                'admin_viewed_at',
                'admin_viewed_by',
                'reviewed_at',
                'reviewed_by',
                'review_notes',
            ]);
        });
    }
};