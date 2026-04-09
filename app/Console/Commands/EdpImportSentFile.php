<?php

namespace App\Console\Commands;

use App\Models\Financial;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EdpImportSentFile extends Command
{
    protected $signature = 'edp:import-sent-file
        {--file= : Caminho completo do .txt enviado para a EDP}
        {--test : Não grava no banco}
        {--only-import : Só cria os financials a partir do arquivo enviado}
        {--only-reconcile : Só cruza com os log_movements já existentes}
        {--from-date=2026-03-01 : Data mínima dos movimentos de retorno para reconciliar}
        {--limit= : Limita linhas B importadas}';

    protected $description = 'Importa um arquivo de produção EDP já enviado e cria financials faltantes; depois cruza com os retornos já processados';

    public function handle(): int
    {
        $file = (string) $this->option('file');
        $test = (bool) $this->option('test');
        $onlyImport = (bool) $this->option('only-import');
        $onlyReconcile = (bool) $this->option('only-reconcile');
        $fromDate = (string) $this->option('from-date');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if (!$onlyReconcile) {
            if (!$file) {
                $this->error('Informe --file=/caminho/do/arquivo.txt');
                return self::FAILURE;
            }

            if (!file_exists($file)) {
                $this->error("Arquivo não encontrado: {$file}");
                return self::FAILURE;
            }
        }

        if ($test) {
            $this->warn('MODO TESTE: não gravará no banco.');
        }

        if (!$onlyReconcile) {
            $this->info('Fase 1: importando arquivo de produção enviado...');
            $this->importSentFile($file, $test, $limit);
        }

        if (!$onlyImport) {
            $this->newLine();
            $this->info('Fase 2: reconciliando com log_movements já processados...');
            $this->reconcileMovements($fromDate, $test);
        }

        $this->newLine();
        $this->info('Concluído.');
        return self::SUCCESS;
    }

    private function importSentFile(string $file, bool $test, ?int $limit): void
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new \RuntimeException("Não foi possível abrir {$file}");
        }

        $count = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }

            $type = substr($line, 0, 1);

            // Mesmo padrão do parser atual: A/Z ignorados; aqui só precisamos dos B enviados. 
            if ($type === 'A' || $type === 'Z') {
                continue;
            }

            if ($type !== 'B') {
                continue;
            }

            $count++;
            if ($limit && $count > $limit) {
                break;
            }

            $parsed = $this->parseBLine($line);

            if (empty($parsed['installation_number'])) {
                $this->warn("Linha B ignorada: instalação vazia");
                $skipped++;
                continue;
            }

            $normalizedInstallation = $this->normalizeInstallationNumber($parsed['installation_number']);

            $order = Order::query()
                ->where('charge_type', 'EDP')
                ->whereRaw('CAST(installation_number AS UNSIGNED) = ?', [(int) $normalizedInstallation])
                ->first();

            if (!$order) {
                $this->warn("Sem pedido EDP para instalação {$parsed['installation_number']}");
                $skipped++;
                continue;
            }

            if (!$parsed['due_date']) {
                $this->warn("Sem due_date válido para instalação {$parsed['installation_number']}");
                $skipped++;
                continue;
            }

            $value = $parsed['value'];
            $dueDate = $parsed['due_date'];

            // Idempotência:
            // se já existe financial do mesmo pedido, mesmo vencimento, mesmo valor -> só garante financial_edp
            $financial = Financial::query()
                ->where('order_id', $order->id)
                ->whereDate('due_date', $dueDate)
                ->where('value', $value)
                ->first();

            if ($test) {
                if ($financial) {
                    $this->line("DRY: atualizaria financial existente order {$order->id} due {$dueDate} value {$value}");
                } else {
                    $this->line("DRY: criaria financial order {$order->id} due {$dueDate} value {$value}");
                }
                continue;
            }

            DB::transaction(function () use ($financial, $order, $dueDate, $value, $parsed, &$created, &$updated) {
                if (!$financial) {
                    $financialId = DB::table('financial')->insertGetId([
                        'order_id' => $order->id,
                        'value' => $value,
                        'paid_value' => null,
                        'charge_date' => (int) Carbon::parse($dueDate)->day,
                        'due_date' => $dueDate,
                        'payment_method' => 'BOLETO',
                        'description' => "Importado de arquivo de produção EDP",
                        'obs' => "Instalação {$parsed['installation_number']} | produto {$parsed['product_cod']}",
                        'charge_paid' => 0,
                        'status' => 'SENDING',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('financial_edp')->insert([
                        'financial_id' => $financialId,
                        'first_log_movement_id' => null,
                        'last_log_movement_id' => null,
                        'confirmed_log_movement_id' => null,
                        'received_log_movement_id' => null,
                        'last_return_code' => null,
                        'last_status' => 'SENDING',
                        'last_event_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('financial_logs')->insert([
                        'financial_id' => $financialId,
                        'provider' => 'EDP',
                        'source_type' => 'IMPORT',
                        'source_id' => null,
                        'event_name' => 'CREATED',
                        'old_status' => null,
                        'new_status' => 'SENDING',
                        'message' => 'Financial criado a partir de arquivo de produção EDP já enviado',
                        'payload' => json_encode($parsed),
                        'event_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $created++;
                } else {
                    DB::table('financial')
                        ->where('id', $financial->id)
                        ->update([
                            'charge_date' => (int) Carbon::parse($dueDate)->day,
                            'due_date' => $dueDate,
                            'status' => $financial->status === 'CANCELED' ? 'CANCELED' : 'SENDING',
                            'updated_at' => now(),
                        ]);

                    $edp = DB::table('financial_edp')->where('financial_id', $financial->id)->first();

                    if (!$edp) {
                        DB::table('financial_edp')->insert([
                            'financial_id' => $financial->id,
                            'first_log_movement_id' => null,
                            'last_log_movement_id' => null,
                            'confirmed_log_movement_id' => null,
                            'received_log_movement_id' => null,
                            'last_return_code' => null,
                            'last_status' => 'SENDING',
                            'last_event_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('financial_edp')
                            ->where('financial_id', $financial->id)
                            ->update([
                                'last_status' => 'SENDING',
                                'last_event_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }

                    DB::table('financial_logs')->insert([
                        'financial_id' => $financial->id,
                        'provider' => 'EDP',
                        'source_type' => 'IMPORT',
                        'source_id' => null,
                        'event_name' => 'UPDATED',
                        'old_status' => $financial->status,
                        'new_status' => $financial->status === 'CANCELED' ? 'CANCELED' : 'SENDING',
                        'message' => 'Financial existente ajustado/importado a partir de arquivo de produção EDP já enviado',
                        'payload' => json_encode($parsed),
                        'event_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $updated++;
                }
            });
        }

        fclose($handle);

        $this->info("Arquivo importado. Criados: {$created} | Atualizados: {$updated} | Ignorados: {$skipped}");
    }

    private function reconcileMovements(string $fromDate, bool $test): void
    {
        $movements = DB::table('log_movements')
            ->whereDate('arquivo_data', '>=', $fromDate)
            ->orderBy('arquivo_data')
            ->orderBy('id')
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($movements as $movement) {
            $installation = $this->normalizeInstallationNumber((string) $movement->installation_number);

            if ($installation === '') {
                $skipped++;
                continue;
            }

            $order = Order::query()
                ->where('charge_type', 'EDP')
                ->whereRaw('CAST(installation_number AS UNSIGNED) = ?', [(int) $installation])
                ->first();

            if (!$order) {
                $skipped++;
                continue;
            }

            // Tenta casar pelo mês da fatura e ordem mais recente do vencimento
            $targetFinancial = Financial::query()
                ->where('order_id', $order->id)
                ->orderByDesc('due_date')
                ->first();

            if (!$targetFinancial) {
                $skipped++;
                continue;
            }

            $mapped = $this->mapReturnCodeToStatus((string) $movement->code_return);

            if ($test) {
                $this->line("DRY: financial {$targetFinancial->id} -> {$mapped['status']} (retorno {$movement->code_return})");
                continue;
            }

            DB::transaction(function () use ($targetFinancial, $movement, $mapped, &$updated) {
                $oldStatus = $targetFinancial->status;

                DB::table('financial')
                    ->where('id', $targetFinancial->id)
                    ->update([
                        'status' => $mapped['status'],
                        'charge_paid' => $mapped['paid'] ? 1 : 0,
                        'paid_value' => $mapped['paid'] ? (float) $targetFinancial->value : $targetFinancial->paid_value,
                        'updated_at' => now(),
                    ]);

                $edp = DB::table('financial_edp')->where('financial_id', $targetFinancial->id)->first();

                if ($edp) {
                    DB::table('financial_edp')
                        ->where('financial_id', $targetFinancial->id)
                        ->update([
                            'first_log_movement_id' => $edp->first_log_movement_id ?: $movement->id,
                            'last_log_movement_id' => $movement->id,
                            'confirmed_log_movement_id' => $mapped['confirmed'] ? $movement->id : $edp->confirmed_log_movement_id,
                            'received_log_movement_id' => $mapped['paid'] ? $movement->id : $edp->received_log_movement_id,
                            'last_return_code' => $movement->code_return,
                            'last_status' => $mapped['status'],
                            'last_event_at' => $movement->arquivo_data ? Carbon::parse($movement->arquivo_data)->endOfDay() : now(),
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('financial_edp')->insert([
                        'financial_id' => $targetFinancial->id,
                        'first_log_movement_id' => $movement->id,
                        'last_log_movement_id' => $movement->id,
                        'confirmed_log_movement_id' => $mapped['confirmed'] ? $movement->id : null,
                        'received_log_movement_id' => $mapped['paid'] ? $movement->id : null,
                        'last_return_code' => $movement->code_return,
                        'last_status' => $mapped['status'],
                        'last_event_at' => $movement->arquivo_data ? Carbon::parse($movement->arquivo_data)->endOfDay() : now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('financial_logs')->insert([
                    'financial_id' => $targetFinancial->id,
                    'provider' => 'EDP',
                    'source_type' => 'IMPORT',
                    'source_id' => $movement->id,
                    'event_name' => $mapped['event_name'],
                    'old_status' => $oldStatus,
                    'new_status' => $mapped['status'],
                    'message' => "Reconciliado a partir de log_movement {$movement->id} / retorno {$movement->code_return}",
                    'payload' => json_encode([
                        'movement_id' => $movement->id,
                        'installation_number' => $movement->installation_number,
                        'code_return' => $movement->code_return,
                        'date_invoice' => $movement->date_invoice,
                        'date_movement' => $movement->date_movement,
                        'value' => $movement->value,
                    ]),
                    'event_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $updated++;
            });
        }

        $this->info("Reconciliação concluída. Atualizados: {$updated} | Ignorados: {$skipped}");
    }

    private function parseBLine(string $line): array
    {
        // Mesmo layout documentado no seu EdpParserService:
        // installation 1..9, product 12..14, value 17..31, start_date 44..51 etc.
        $installation = trim(substr($line, 1, 9));
        $extraValue = trim(substr($line, 10, 2));
        $productCod = trim(substr($line, 12, 3));
        $numberInstallment = trim(substr($line, 15, 2));
        $valueRaw = trim(substr($line, 17, 15));
        $startDateRaw = trim(substr($line, 44, 8));
        $endDateRaw = trim(substr($line, 52, 8));
        $codeAnomaly = trim(substr($line, 157, 2));
        $codeMove = trim(substr($line, 159, 1));

        return [
            'installation_number' => $this->normalizeInstallationNumber($installation),
            'extra_value' => $extraValue !== '' ? $extraValue : null,
            'product_cod' => $productCod !== '' ? $productCod : null,
            'number_installment' => $numberInstallment !== '' ? $numberInstallment : null,
            'value' => $this->parseMoney15($valueRaw),
            // aqui eu trato como AAAAMMDD, como documentado no layout
            'due_date' => $this->parseYmd($startDateRaw),
            'end_date' => $this->parseYmd($endDateRaw),
            'code_anomaly' => $codeAnomaly !== '' ? $codeAnomaly : null,
            'code_move' => $codeMove !== '' ? $codeMove : null,
            'raw' => $line,
        ];
    }

    private function parseMoney15(?string $raw): float
    {
        $digits = preg_replace('/\D/', '', (string) $raw);
        if ($digits === '') {
            return 0.0;
        }

        return round(((float) $digits) / 100, 2);
    }

    private function parseYmd(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '' || $raw === '00000000' || strlen($raw) !== 8) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Ymd', $raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function mapReturnCodeToStatus(string $code): array
    {
        // Ajuste fino se sua regra de negócio for diferente.
        return match ($code) {
            '06' => [
                'status' => 'RECEIVED',
                'paid' => true,
                'confirmed' => true,
                'event_name' => 'EDP_RETURN_06_PAYMENT',
            ],
            '05' => [
                'status' => 'CONFIRMED',
                'paid' => false,
                'confirmed' => true,
                'event_name' => 'EDP_RETURN_05_REVISION_CHARGE',
            ],
            '04' => [
                'status' => 'PENDING',
                'paid' => false,
                'confirmed' => false,
                'event_name' => 'EDP_RETURN_04_REVISION_RETURN',
            ],
            '03' => [
                'status' => 'REPROVED',
                'paid' => false,
                'confirmed' => false,
                'event_name' => 'EDP_RETURN_03_NOT_BILLED',
            ],
            '07' => [
                'status' => 'SENDING',
                'paid' => false,
                'confirmed' => false,
                'event_name' => 'EDP_RETURN_07_BACK_TO_DEBIT',
            ],
            default => [
                'status' => 'PENDING',
                'paid' => false,
                'confirmed' => false,
                'event_name' => 'UPDATED',
            ],
        };
    }

    private function normalizeInstallationNumber(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // mantém só dígitos
        $value = preg_replace('/\D/', '', $value);

        if ($value === '') {
            return null;
        }

        // remove zeros à esquerda
        $value = ltrim($value, '0');

        // se virar vazio, assume 0
        return $value === '' ? '0' : $value;
    }
}