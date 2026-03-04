<?php

namespace App\Console\Commands;

use App\Models\Financial;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildEdpFinancialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'edp:rebuild-financials
        {--only-reset : Apenas limpa financials/logs/tabelas da EDP, sem recriar}
        {--from-record-id= : Recriar a partir deste ID de edp_production_records}
        {--to-record-id= : Recriar até este ID de edp_production_records}
        {--sleep-every=200 : Pausar a cada X registros recriados}
        {--sleep-seconds=5 : Quantidade de segundos da pausa}';

    /**
     * The console command description.
     */
    protected $description = 'Apaga financials da EDP e recria a partir de edp_production_records, preenchendo due_date com start_date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('Este command vai apagar e recriar os financials da EDP.');
        $this->newLine();

        $this->resetEdpFinancialData();

        if ($this->option('only-reset')) {
            $this->info('Reset concluído. Nenhum financial foi recriado porque a opção --only-reset foi utilizada.');
            return self::SUCCESS;
        }

        $created = $this->recreateEdpFinancials();

        $this->newLine();
        $this->info('Processamento finalizado.');
        $this->line("Financials EDP recriados: {$created}");

        return self::SUCCESS;
    }

    /**
     * Limpa todos os dados EDP incorretos para reprocessamento.
     */
    private function resetEdpFinancialData(): void
    {
        DB::beginTransaction();

        try {
            DB::table('financial_logs')
                ->where('provider', 'EDP')
                ->delete();

            DB::table('financial_edp')->delete();

            Financial::query()
                ->where('payment_method', 'EDP')
                ->delete();

            DB::commit();

            $this->info('Financial logs da EDP removidos.');
            $this->info('Tabela financial_edp limpa.');
            $this->info('Financials da EDP removidos.');
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }

    /**
     * Recria os financials EDP a partir de edp_production_records.
     */
    private function recreateEdpFinancials(): int
    {
        $fromRecordId = $this->option('from-record-id');
        $toRecordId = $this->option('to-record-id');
        $sleepEvery = (int) $this->option('sleep-every');
        $sleepSeconds = (int) $this->option('sleep-seconds');

        $query = DB::table('edp_production_records')
            ->orderBy('id');

        if ($fromRecordId !== null) {
            $query->where('id', '>=', (int) $fromRecordId);
        }

        if ($toRecordId !== null) {
            $query->where('id', '<=', (int) $toRecordId);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            $this->warn('Nenhum registro encontrado em edp_production_records para recriação.');
            return 0;
        }

        $createdCount = 0;
        $processedCount = 0;

        foreach ($records as $record) {
            try {
                DB::beginTransaction();

                $normalizedInstallationNumber = $this->normalizeInstallationNumber($record->installation_number);

                $order = Order::query()
                    ->where('installation_number', (int) $normalizedInstallationNumber)
                    ->first();

                if (!$order) {
                    DB::commit();
                    $processedCount++;
                    continue;
                }

                $description = 'EDP arquivo retorno (' . ($record->start_date ?? 'sem data inicial') . ')';

                $alreadyExists = Financial::query()
                    ->where('order_id', $order->id)
                    ->where('payment_method', 'EDP')
                    ->where('value', $record->installment_value)
                    ->where('due_date', $record->start_date)
                    ->where('description', $description)
                    ->exists();

                if ($alreadyExists) {
                    DB::commit();
                    $processedCount++;
                    continue;
                }

                Financial::query()->create([
                    'order_id' => $order->id,
                    'value' => $record->installment_value,
                    'paid_value' => null,
                    'charge_date' => null,
                    'due_date' => $record->start_date,
                    'payment_method' => 'EDP',
                    'description' => $description,
                    'obs' => null,
                    'charge_paid' => null,
                    'status' => 'SENDING',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                $createdCount++;
                $processedCount++;

                $this->line("Financial EDP recriado para instalação {$record->installation_number} com due_date {$record->start_date}.");

                if ($sleepEvery > 0 && $processedCount > 0 && $processedCount % $sleepEvery === 0) {
                    $this->warn("Pausa de {$sleepSeconds} segundos após {$processedCount} registros processados...");
                    sleep($sleepSeconds);
                }
            } catch (\Throwable $throwable) {
                DB::rollBack();
                $this->error("Erro ao recriar financial a partir do edp_production_record {$record->id}: {$throwable->getMessage()}");
            }
        }

        return $createdCount;
    }

    /**
     * Remove zeros à esquerda do número de instalação.
     */
    private function normalizeInstallationNumber($installationNumber): string
    {
        $installationNumber = (string) $installationNumber;
        $installationNumber = trim($installationNumber);
        $installationNumber = ltrim($installationNumber, '0');

        return $installationNumber === '' ? '0' : $installationNumber;
    }
}