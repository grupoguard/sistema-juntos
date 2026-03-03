<?php

namespace App\Console\Commands;

use App\Models\Financial;
use App\Models\FinancialAsaas;
use App\Models\FinancialLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateFinancialsToAsaasTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Comando genérico criado apenas para importar os registros existentes em financial para financial_asaas antes de excluir os campos do banco de dados
     * @var string
     */
    protected $signature = 'financial:migrate-to-asaas-table
        {--from-id= : Processar financials com ID maior ou igual a este valor}
        {--to-id= : Processar financials com ID menor ou igual a este valor}
        {--sleep-every=200 : Pausar a cada X registros processados}
        {--sleep-seconds=5 : Quantidade de segundos da pausa}
        {--force-update : Atualiza o registro em financial_asaas mesmo se ele já existir}';

    /**
     * The console command description.
     */
    protected $description = 'Migra os campos específicos do Asaas da tabela financial para a tabela financial_asaas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fromId = $this->option('from-id');
        $toId = $this->option('to-id');
        $sleepEvery = (int) $this->option('sleep-every');
        $sleepSeconds = (int) $this->option('sleep-seconds');
        $forceUpdate = (bool) $this->option('force-update');

        $query = Financial::query()
            ->where(function ($query) {
                $query->whereNotNull('asaas_payment_id')
                    ->orWhereNotNull('asaas_customer_id')
                    ->orWhereNotNull('external_reference')
                    ->orWhereNotNull('invoice_url')
                    ->orWhereNotNull('bank_slip_url')
                    ->orWhereNotNull('pix_qr_code')
                    ->orWhereNotNull('pix_qr_code_url');
            });

        if ($fromId !== null) {
            $query->where('id', '>=', (int) $fromId);
        }

        if ($toId !== null) {
            $query->where('id', '<=', (int) $toId);
        }

        $financials = $query
            ->orderBy('id')
            ->get();

        if ($financials->isEmpty()) {
            $this->info('Nenhum financial com dados do Asaas encontrado para migrar.');
            return self::SUCCESS;
        }

        $processedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($financials as $financial) {
            try {
                DB::beginTransaction();

                $existing = FinancialAsaas::query()
                    ->where('financial_id', $financial->id)
                    ->first();

                if ($existing && !$forceUpdate) {
                    $this->line("Financial {$financial->id} já possui registro em financial_asaas. Ignorado.");
                    $skippedCount++;
                    $processedCount++;

                    DB::commit();
                    continue;
                }

                $data = [
                    'financial_id' => $financial->id,
                    'asaas_payment_id' => $financial->asaas_payment_id,
                    'asaas_customer_id' => $financial->asaas_customer_id,
                    'external_reference' => $financial->external_reference,
                    'invoice_url' => $financial->invoice_url,
                    'bank_slip_url' => $financial->bank_slip_url,
                    'pix_qr_code' => $financial->pix_qr_code,
                    'pix_qr_code_url' => $financial->pix_qr_code_url,
                    'updated_at' => now(),
                ];

                if ($existing) {
                    $existing->update($data);
                    $asaasRecord = $existing;
                    $updatedCount++;
                    $action = 'atualizado';
                } else {
                    $data['created_at'] = now();

                    $asaasRecord = FinancialAsaas::query()->create($data);
                    $createdCount++;
                    $action = 'criado';
                }

                FinancialLog::query()->create([
                    'financial_id' => $financial->id,
                    'provider' => 'ASAAS',
                    'source_type' => 'IMPORT',
                    'source_id' => $asaasRecord->id,
                    'event_name' => 'ASAAS_IMPORTED',
                    'old_status' => null,
                    'new_status' => $financial->status,
                    'message' => "Dados do Asaas {$action}s em financial_asaas a partir da tabela financial.",
                    'payload' => json_encode([
                        'asaas_payment_id' => $financial->asaas_payment_id,
                        'asaas_customer_id' => $financial->asaas_customer_id,
                        'external_reference' => $financial->external_reference,
                    ], JSON_UNESCAPED_UNICODE),
                    'event_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                $this->info("Financial {$financial->id} migrado com sucesso para financial_asaas.");
                $processedCount++;

                if ($sleepEvery > 0 && $processedCount > 0 && $processedCount % $sleepEvery === 0) {
                    $this->warn("Pausa de {$sleepSeconds} segundos após {$processedCount} registros processados...");
                    sleep($sleepSeconds);
                }
            } catch (\Throwable $throwable) {
                DB::rollBack();

                $this->error("Erro ao migrar financial {$financial->id}: {$throwable->getMessage()}");
                $errorCount++;
                $processedCount++;
            }
        }

        $this->newLine();
        $this->info('Migração finalizada.');
        $this->line("Financials processados: {$processedCount}");
        $this->line("Registros criados em financial_asaas: {$createdCount}");
        $this->line("Registros atualizados em financial_asaas: {$updatedCount}");
        $this->line("Registros ignorados: {$skippedCount}");
        $this->line("Registros com erro: {$errorCount}");

        return self::SUCCESS;
    }
}
