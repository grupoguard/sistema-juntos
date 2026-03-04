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
    protected $description = 'Atualiza os financials da EDP usando installation_number + value + due_date(date_invoice), considerando baixa 06 única ou somada';

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

                $updatedThisFinancial = false;

                /**
                 * FASE 1:
                 * Buscar confirmação exata da cobrança (code_return = 01)
                 * usando instalação + competência + valor exato.
                 */
                $confirmedMovement = LogMovement::query()
                    ->whereRaw('CAST(installation_number AS UNSIGNED) = ?', [(int) $normalizedInstallationNumber])
                    ->where('date_invoice', $competenceYm)
                    ->where('value', $financialValueField)
                    ->where('code_return', '01')
                    ->orderBy('date_movement')
                    ->orderBy('id')
                    ->first();

                if ($confirmedMovement) {
                    $alreadyLogged = FinancialLog::query()
                        ->where('financial_id', $financial->id)
                        ->where('provider', 'EDP')
                        ->where('source_type', 'LOG_MOVEMENT')
                        ->where('source_id', $confirmedMovement->id)
                        ->exists();

                    if (!$alreadyLogged) {
                        $oldStatus = $financial->status;
                        $movementDate = $this->parseMovementDate($confirmedMovement->date_movement);
                        $obsMessage = 'EDP: cobrança confirmada. Código de retorno 01 - Faturamento do serviço.';

                        $financial->update([
                            'status' => 'CONFIRMED',
                            'obs' => $this->appendObs($financial->obs, $obsMessage),
                            'updated_at' => now(),
                        ]);

                        if ($financialEdp->first_log_movement_id === null) {
                            $financialEdp->first_log_movement_id = $confirmedMovement->id;
                        }

                        $financialEdp->last_log_movement_id = $confirmedMovement->id;
                        $financialEdp->confirmed_log_movement_id = $confirmedMovement->id;
                        $financialEdp->last_return_code = '01';
                        $financialEdp->last_status = 'CONFIRMED';
                        $financialEdp->last_event_at = $movementDate ? Carbon::parse($movementDate) : now();
                        $financialEdp->save();

                        FinancialLog::query()->create([
                            'financial_id' => $financial->id,
                            'provider' => 'EDP',
                            'source_type' => 'LOG_MOVEMENT',
                            'source_id' => $confirmedMovement->id,
                            'event_name' => 'EDP_CONFIRMED',
                            'old_status' => $oldStatus,
                            'new_status' => 'CONFIRMED',
                            'message' => $obsMessage,
                            'payload' => [
                                'code_return' => $confirmedMovement->code_return,
                                'date_invoice' => $confirmedMovement->date_invoice,
                                'date_movement' => $confirmedMovement->date_movement,
                                'value' => $confirmedMovement->value,
                                'financial_edp_id' => $financialEdp->id,
                            ],
                            'event_date' => $movementDate ? Carbon::parse($movementDate) : now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $updatedCount++;
                        $updatedThisFinancial = true;

                        // recarrega o financial para usar o status mais recente na fase 2
                        $financial->refresh();
                    }
                }

                /**
                 * FASE 2:
                 * Buscar pagamentos (code_return = 06) pela competência.
                 * Pode vir um ou vários registros.
                 */
                $receivedMovements = LogMovement::query()
                    ->whereRaw('CAST(installation_number AS UNSIGNED) = ?', [(int) $normalizedInstallationNumber])
                    ->where('date_invoice', $competenceYm)
                    ->where('code_return', '06')
                    ->orderBy('date_movement')
                    ->orderBy('id')
                    ->get();

                if ($receivedMovements->isNotEmpty()) {
                    $sumReceived = $receivedMovements->sum(function ($movement) {
                        return $this->parseMovementValueToDecimal($movement->value);
                    });

                    $singleMovement = $receivedMovements->count() === 1 ? $receivedMovements->first() : null;
                    $latestMovement = $receivedMovements->last();
                    $movementDate = $latestMovement ? $this->parseMovementDate($latestMovement->date_movement) : null;

                    $shouldMarkReceived = false;
                    $obsMessage = null;

                    if ($singleMovement) {
                        $singleValue = $this->parseMovementValueToDecimal($singleMovement->value);

                        if ($singleValue == (float) $financial->value) {
                            $shouldMarkReceived = true;
                            $obsMessage = 'EDP: cliente pagou a cobrança. Código de retorno 06 - Baixa do serviço. Valor recebido igual ao valor da cobrança.';
                        } else {
                            $shouldMarkReceived = true;
                            $obsMessage = 'EDP: cliente pagou a cobrança. Código de retorno 06 - Baixa do serviço. Valor recebido diferente do valor da cobrança.';
                        }
                    } else {
                        if ($sumReceived >= (float) $financial->value) {
                            $shouldMarkReceived = true;
                            $obsMessage = 'EDP: cliente pagou a cobrança. Código de retorno 06 - Baixa do serviço em múltiplos registros. Soma dos pagamentos igual ou superior ao valor da cobrança.';
                        }
                    }

                    if ($shouldMarkReceived) {
                        $alreadyLoggedReceived = FinancialLog::query()
                            ->where('financial_id', $financial->id)
                            ->where('provider', 'EDP')
                            ->where('event_name', 'EDP_RECEIVED')
                            ->exists();

                        if (!$alreadyLoggedReceived) {
                            $oldStatus = $financial->status;

                            $financial->update([
                                'status' => 'RECEIVED',
                                'paid_value' => $sumReceived,
                                'charge_paid' => 1,
                                'obs' => $this->appendObs($financial->obs, $obsMessage),
                                'updated_at' => now(),
                            ]);

                            if ($financialEdp->first_log_movement_id === null && $latestMovement) {
                                $financialEdp->first_log_movement_id = $latestMovement->id;
                            }

                            if ($latestMovement) {
                                $financialEdp->last_log_movement_id = $latestMovement->id;
                                $financialEdp->received_log_movement_id = $latestMovement->id;
                            }

                            $financialEdp->last_return_code = '06';
                            $financialEdp->last_status = 'RECEIVED';
                            $financialEdp->last_event_at = $movementDate ? Carbon::parse($movementDate) : now();
                            $financialEdp->save();

                            FinancialLog::query()->create([
                                'financial_id' => $financial->id,
                                'provider' => 'EDP',
                                'source_type' => 'LOG_MOVEMENT',
                                'source_id' => $latestMovement?->id,
                                'event_name' => 'EDP_RECEIVED',
                                'old_status' => $oldStatus,
                                'new_status' => 'RECEIVED',
                                'message' => $obsMessage,
                                'payload' => [
                                    'date_invoice' => $competenceYm,
                                    'received_movements_ids' => $receivedMovements->pluck('id')->values()->all(),
                                    'received_movements_count' => $receivedMovements->count(),
                                    'sum_received' => $sumReceived,
                                    'financial_value' => (float) $financial->value,
                                    'financial_edp_id' => $financialEdp->id,
                                ],
                                'event_date' => $movementDate ? Carbon::parse($movementDate) : now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $updatedCount++;
                            $updatedThisFinancial = true;
                        }
                    }
                }

                /**
                 * FASE 3:
                 * Se não confirmou nem recebeu, verificar reprovação.
                 * Só reprova se não tiver confirmação nem pagamento.
                 */
                if (!$confirmedMovement && $receivedMovements->isEmpty()) {
                    $reprovedMovement = LogMovement::query()
                        ->whereRaw('CAST(installation_number AS UNSIGNED) = ?', [(int) $normalizedInstallationNumber])
                        ->where('date_invoice', $competenceYm)
                        ->where('value', $financialValueField)
                        ->whereIn('code_return', ['03', '04', '05', '07'])
                        ->orderBy('date_movement')
                        ->orderBy('id')
                        ->first();

                    if ($reprovedMovement) {
                        $alreadyLogged = FinancialLog::query()
                            ->where('financial_id', $financial->id)
                            ->where('provider', 'EDP')
                            ->where('source_type', 'LOG_MOVEMENT')
                            ->where('source_id', $reprovedMovement->id)
                            ->exists();

                        if (!$alreadyLogged) {
                            $oldStatus = $financial->status;
                            $newStatus = 'REPROVED';
                            $movementDate = $this->parseMovementDate($reprovedMovement->date_movement);
                            $obsMessage = $this->buildObsForReprovedCode($reprovedMovement->code_return);

                            $financial->update([
                                'status' => $newStatus,
                                'obs' => $this->appendObs($financial->obs, $obsMessage),
                                'updated_at' => now(),
                            ]);

                            if ($financialEdp->first_log_movement_id === null) {
                                $financialEdp->first_log_movement_id = $reprovedMovement->id;
                            }

                            $financialEdp->last_log_movement_id = $reprovedMovement->id;
                            $financialEdp->last_return_code = $reprovedMovement->code_return;
                            $financialEdp->last_status = $newStatus;
                            $financialEdp->last_event_at = $movementDate ? Carbon::parse($movementDate) : now();
                            $financialEdp->save();

                            FinancialLog::query()->create([
                                'financial_id' => $financial->id,
                                'provider' => 'EDP',
                                'source_type' => 'LOG_MOVEMENT',
                                'source_id' => $reprovedMovement->id,
                                'event_name' => $this->resolveEventNameByReprovedCode($reprovedMovement->code_return),
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'message' => $obsMessage,
                                'payload' => [
                                    'code_return' => $reprovedMovement->code_return,
                                    'date_invoice' => $reprovedMovement->date_invoice,
                                    'date_movement' => $reprovedMovement->date_movement,
                                    'value' => $reprovedMovement->value,
                                    'financial_edp_id' => $financialEdp->id,
                                ],
                                'event_date' => $movementDate ? Carbon::parse($movementDate) : now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $updatedCount++;
                            $updatedThisFinancial = true;
                        }
                    }
                }

                DB::commit();

                if (!$updatedThisFinancial) {
                    $skippedCount++;
                }

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

    private function resolveEventNameByReprovedCode(?string $codeReturn): string
    {
        return match ($codeReturn) {
            '03' => 'EDP_RETURN_03_NOT_BILLED',
            '04' => 'EDP_RETURN_04_REVISION_RETURN',
            '05' => 'EDP_RETURN_05_REVISION_CHARGE',
            '07' => 'EDP_RETURN_07_BACK_TO_DEBIT',
            default => 'UPDATED',
        };
    }

    private function buildObsForReprovedCode(?string $codeReturn): ?string
    {
        return match ($codeReturn) {
            '03' => 'EDP: cobrança reprovada. Código de retorno 03 - Não faturado.',
            '04' => 'EDP: cobrança reprovada. Código de retorno 04 - Devolução por revisão.',
            '05' => 'EDP: cobrança reprovada. Código de retorno 05 - Cobrança por revisão.',
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