<?php

namespace App\Console\Commands;

use App\Models\Financial;
use App\Models\FinancialEdp;
use App\Models\FinancialLog;
use App\Models\LogMovement;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncEdpFinancialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'edp:sync-financials
        {--from-financial-id= : Processar financials com ID maior ou igual a este valor}
        {--to-financial-id= : Processar financials com ID menor ou igual a este valor}
        {--sleep-every=200 : Pausar a cada X registros processados}
        {--sleep-seconds=5 : Quantidade de segundos da pausa}';

    /**
     * The console command description.
     */
    protected $description = 'Atualiza os financials da EDP usando installation_number + value + due_date(date_invoice)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fromFinancialId = $this->option('from-financial-id');
        $toFinancialId = $this->option('to-financial-id');
        $sleepEvery = (int) $this->option('sleep-every');
        $sleepSeconds = (int) $this->option('sleep-seconds');

        $query = Financial::query()
            ->where('payment_method', 'EDP');

        if ($fromFinancialId !== null) {
            $query->where('id', '>=', (int) $fromFinancialId);
        }

        if ($toFinancialId !== null) {
            $query->where('id', '<=', (int) $toFinancialId);
        }

        $financials = $query->orderBy('id')->get();

        if ($financials->isEmpty()) {
            $this->info('Nenhum financial EDP encontrado.');
            return self::SUCCESS;
        }

        $processedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($financials as $financial) {
            try {
                DB::beginTransaction();

                $order = Order::query()->find($financial->order_id);

                if (!$order) {
                    DB::commit();
                    $skippedCount++;
                    $processedCount++;
                    continue;
                }

                if (empty($financial->due_date)) {
                    DB::commit();
                    $this->line("Financial {$financial->id} ignorado: due_date vazio.");
                    $skippedCount++;
                    $processedCount++;
                    continue;
                }

                $normalizedInstallationNumber = $this->normalizeInstallationNumber($order->installation_number);
                $financialValueField = $this->formatMovementValueFromFinancial($financial->value);
                $competenceYm = Carbon::parse($financial->due_date)->format('Ym');

                $movements = LogMovement::query()
                    ->whereRaw('CAST(installation_number AS UNSIGNED) = ?', [(int) $normalizedInstallationNumber])
                    ->where('value', $financialValueField)
                    ->where('date_invoice', $competenceYm)
                    ->whereIn('code_return', ['01', '03', '04', '05', '06', '07'])
                    ->orderByRaw("
                        CASE code_return
                            WHEN '01' THEN 1
                            WHEN '03' THEN 2
                            WHEN '04' THEN 3
                            WHEN '05' THEN 4
                            WHEN '07' THEN 5
                            WHEN '06' THEN 6
                            ELSE 99
                        END
                    ")
                    ->orderBy('date_movement')
                    ->orderBy('id')
                    ->get();

                if ($movements->isEmpty()) {
                    DB::commit();
                    $skippedCount++;
                    $processedCount++;
                    continue;
                }

                $financialEdp = FinancialEdp::query()->firstOrCreate(
                    ['financial_id' => $financial->id],
                    [
                        'first_log_movement_id' => null,
                        'last_log_movement_id' => null,
                        'confirmed_log_movement_id' => null,
                        'received_log_movement_id' => null,
                        'last_return_code' => null,
                        'last_status' => null,
                        'last_event_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                foreach ($movements as $movement) {
                    $alreadyLogged = FinancialLog::query()
                        ->where('financial_id', $financial->id)
                        ->where('provider', 'EDP')
                        ->where('source_type', 'LOG_MOVEMENT')
                        ->where('source_id', $movement->id)
                        ->exists();

                    if ($alreadyLogged) {
                        continue;
                    }

                    $oldStatus = $financial->status;
                    $newStatus = $this->resolveStatusByReturnCode($movement->code_return);
                    $movementDate = $this->parseMovementDate($movement->date_movement);
                    $obsMessage = $this->buildObsForReturnCode($movement);

                    $updateFinancial = [
                        'status' => $newStatus,
                        'updated_at' => now(),
                    ];

                    if ($movement->code_return === '06') {
                        $updateFinancial['paid_value'] = $this->parseMovementValueToDecimal($movement->value);
                        $updateFinancial['charge_paid'] = 1;
                    }

                    if ($obsMessage) {
                        $updateFinancial['obs'] = $this->appendObs($financial->obs, $obsMessage);
                    }

                    $financial->update($updateFinancial);

                    if ($financialEdp->first_log_movement_id === null) {
                        $financialEdp->first_log_movement_id = $movement->id;
                    }

                    $financialEdp->last_log_movement_id = $movement->id;
                    $financialEdp->last_return_code = $movement->code_return;
                    $financialEdp->last_status = $movement->code_return === '06' ? 'RECEIVED' : $newStatus;
                    $financialEdp->last_event_at = $movementDate ? Carbon::parse($movementDate) : now();

                    if ($movement->code_return === '01') {
                        $financialEdp->confirmed_log_movement_id = $movement->id;
                    }

                    if ($movement->code_return === '06') {
                        $financialEdp->received_log_movement_id = $movement->id;
                    }

                    $financialEdp->save();

                    FinancialLog::query()->create([
                        'financial_id' => $financial->id,
                        'provider' => 'EDP',
                        'source_type' => 'LOG_MOVEMENT',
                        'source_id' => $movement->id,
                        'event_name' => $this->resolveEventNameByReturnCode($movement->code_return),
                        'old_status' => $oldStatus,
                        'new_status' => $movement->code_return === '06' ? 'RECEIVED' : $newStatus,
                        'message' => $obsMessage ?: 'Atualização EDP aplicada a partir do log_movement ID ' . $movement->id,
                        'payload' => [
                            'code_return' => $movement->code_return,
                            'date_invoice' => $movement->date_invoice,
                            'date_movement' => $movement->date_movement,
                            'value' => $movement->value,
                            'financial_edp_id' => $financialEdp->id,
                        ],
                        'event_date' => $movementDate ? Carbon::parse($movementDate) : now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $updatedCount++;
                }

                DB::commit();
                $processedCount++;

                if ($sleepEvery > 0 && $processedCount > 0 && $processedCount % $sleepEvery === 0) {
                    $this->warn("Pausa de {$sleepSeconds} segundos após {$processedCount} financials processados...");
                    sleep($sleepSeconds);
                }
            } catch (\Throwable $throwable) {
                DB::rollBack();
                $this->error("Erro ao sincronizar financial {$financial->id}: {$throwable->getMessage()}");
                $errorCount++;
                $processedCount++;
            }
        }

        $this->newLine();
        $this->info('Sincronização finalizada.');
        $this->line("Financials processados: {$processedCount}");
        $this->line("Financials atualizados: {$updatedCount}");
        $this->line("Financials ignorados: {$skippedCount}");
        $this->line("Financials com erro: {$errorCount}");

        return self::SUCCESS;
    }

    private function resolveStatusByReturnCode(?string $codeReturn): string
    {
        return match ($codeReturn) {
            '01' => 'CONFIRMED',
            '03' => 'REPROVED',
            '04' => 'REPROVED',
            '05' => 'REPROVED',
            '06' => 'RECEIVED',
            '07' => 'REPROVED',
            default => 'SENDING',
        };
    }

    private function resolveEventNameByReturnCode(?string $codeReturn): string
    {
        return match ($codeReturn) {
            '01' => 'EDP_CONFIRMED',
            '03' => 'EDP_RETURN_03_NOT_BILLED',
            '04' => 'EDP_RETURN_04_REVISION_RETURN',
            '05' => 'EDP_RETURN_05_REVISION_CHARGE',
            '06' => 'EDP_RECEIVED',
            '07' => 'EDP_RETURN_07_BACK_TO_DEBIT',
            default => 'UPDATED',
        };
    }

    private function buildObsForReturnCode(LogMovement $movement): ?string
    {
        return match ($movement->code_return) {
            '01' => 'EDP: cobrança confirmada. Código de retorno 01 - Faturamento do serviço.',
            '03' => 'EDP: cobrança reprovada. Código de retorno 03 - Não faturado.',
            '04' => 'EDP: cobrança reprovada. Código de retorno 04 - Devolução por revisão.',
            '05' => 'EDP: cobrança reprovada. Código de retorno 05 - Cobrança por revisão.',
            '06' => 'EDP: cliente pagou a cobrança. Código de retorno 06 - Baixa do serviço.',
            '07' => 'EDP: cobrança reprovada. Código de retorno 07 - Volta a débito.',
            default => null,
        };
    }

    private function appendObs(?string $currentObs, ?string $message): ?string
    {
        $currentObs = trim((string) $currentObs);
        $message = trim((string) $message);

        if ($message === '') {
            return $currentObs !== '' ? $currentObs : null;
        }

        if ($currentObs === '') {
            return $message;
        }

        if (str_contains($currentObs, $message)) {
            return $currentObs;
        }

        return $currentObs . PHP_EOL . $message;
    }

    private function normalizeInstallationNumber($installationNumber): string
    {
        $installationNumber = (string) $installationNumber;
        $installationNumber = trim($installationNumber);
        $installationNumber = ltrim($installationNumber, '0');

        return $installationNumber === '' ? '0' : $installationNumber;
    }

    private function parseMovementDate(?string $dateMovement): ?string
    {
        if (!$dateMovement) {
            return null;
        }

        $dateMovement = trim($dateMovement);

        if (strlen($dateMovement) !== 8 || !ctype_digit($dateMovement) || $dateMovement === '00000000') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Ymd', $dateMovement)->format('Y-m-d');
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    private function parseMovementValueToDecimal(?string $movementValue): float
    {
        $movementValue = trim((string) $movementValue);

        if ($movementValue === '' || !preg_match('/^\d+$/', $movementValue)) {
            return 0.00;
        }

        return ((int) $movementValue) / 100;
    }

    private function formatMovementValueFromFinancial($financialValue): string
    {
        $valueInCents = (int) round(((float) $financialValue) * 100);

        return str_pad((string) $valueInCents, 15, '0', STR_PAD_LEFT);
    }
}