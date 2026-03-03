<?php

namespace App\Console\Commands;

use App\Models\Financial;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateFinancialsFromEdpProductionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'edp:create-financials-from-production
        {--from-id= : Processar registros com ID maior ou igual a este valor}
        {--to-id= : Processar registros com ID menor ou igual a este valor}';

    /**
     * The console command description.
     */
    protected $description = 'Cria registros na tabela financial a partir dos registros da tabela edp_production_records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fromId = $this->option('from-id');
        $toId = $this->option('to-id');

        $query = DB::table('edp_production_records')
            ->whereNull('financial_created_at');

        if ($fromId !== null) {
            $query->where('id', '>=', (int) $fromId);
        }

        if ($toId !== null) {
            $query->where('id', '<=', (int) $toId);
        }

        $records = $query
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            $this->info('Nenhum registro pendente para criar financial.');
            return self::SUCCESS;
        }

        $createdCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $processedCount = 0;

        foreach ($records as $record) {
            try {
                DB::beginTransaction();

                $normalizedInstallationNumber = $this->normalizeInstallationNumber($record->installation_number);

                $order = Order::query()
                    ->where('installation_number', $normalizedInstallationNumber)
                    ->first();

                if (!$order) {
                    DB::table('edp_production_records')
                        ->where('id', $record->id)
                        ->update([
                            'financial_error' => 'Pedido não encontrado para o installation_number informado. Valor original: ' . $record->installation_number . ' | Valor normalizado: ' . $normalizedInstallationNumber,
                            'updated_at' => now(),
                        ]);

                    DB::commit();

                    $this->warn("Registro {$record->id} ignorado: pedido não encontrado para instalação {$record->installation_number} (normalizada: {$normalizedInstallationNumber}).");
                    $skippedCount++;
                    $processedCount++;
                    continue;
                }

                $description = 'EDP arquivo retorno (' . ($record->start_date ?? 'sem data inicial') . ')';

                $existingFinancial = Financial::query()
                    ->where('order_id', $order->id)
                    ->where('value', $record->installment_value)
                    ->where('payment_method', 'EDP')
                    ->where('description', $description)
                    ->where('status', 'SENDING')
                    ->whereNull('due_date')
                    ->first();

                if ($existingFinancial) {
                    DB::table('edp_production_records')
                        ->where('id', $record->id)
                        ->update([
                            'financial_created_at' => now(),
                            'financial_error' => null,
                            'updated_at' => now(),
                        ]);

                    DB::commit();

                    $this->line("Registro {$record->id} já possuía financial compatível. Marcado como processado.");
                    $skippedCount++;
                    $processedCount++;
                    continue;
                }

                Financial::query()->create([
                    'order_id' => $order->id,
                    'asaas_payment_id' => null,
                    'asaas_customer_id' => null,
                    'value' => $record->installment_value,
                    'paid_value' => null,
                    'charge_date' => null,
                    'due_date' => null,
                    'payment_method' => 'EDP',
                    'external_reference' => null,
                    'invoice_url' => null,
                    'bank_slip_url' => null,
                    'pix_qr_code' => null,
                    'pix_qr_code_url' => null,
                    'description' => $description,
                    'obs' => null,
                    'charge_paid' => null,
                    'status' => 'SENDING',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('edp_production_records')
                    ->where('id', $record->id)
                    ->update([
                        'financial_created_at' => now(),
                        'financial_error' => null,
                        'updated_at' => now(),
                    ]);

                DB::commit();

                $this->info("Financial criado com sucesso para o registro {$record->id} e pedido {$order->id}.");
                $createdCount++;
                $processedCount++;

                if ($createdCount > 0 && $createdCount % 200 === 0) {
                    $this->newLine();
                    $this->warn("Pausa de 5 segundos após {$createdCount} financials criados...");
                    sleep(5);
                }
            } catch (\Throwable $throwable) {
                DB::rollBack();

                DB::table('edp_production_records')
                    ->where('id', $record->id)
                    ->update([
                        'financial_error' => $throwable->getMessage(),
                        'updated_at' => now(),
                    ]);

                $this->error("Erro ao processar registro {$record->id}: {$throwable->getMessage()}");
                $errorCount++;
                $processedCount++;
            }
        }

        $this->newLine();
        $this->info('Processamento finalizado.');
        $this->line("Registros processados: {$processedCount}");
        $this->line("Financials criados: {$createdCount}");
        $this->line("Registros ignorados: {$skippedCount}");
        $this->line("Registros com erro: {$errorCount}");

        return self::SUCCESS;
    }

    /**
     * Normaliza o número da instalação removendo zeros à esquerda.
     * Como o campo orders.installation_number é int, os zeros iniciais são perdidos no banco.
     */
    private function normalizeInstallationNumber($installationNumber): int
    {
        $installationNumber = (string) $installationNumber;
        $installationNumber = trim($installationNumber);
        $installationNumber = ltrim($installationNumber, '0');

        if ($installationNumber === '') {
            return 0;
        }

        return (int) $installationNumber;
    }
}