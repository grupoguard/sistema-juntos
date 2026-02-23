<?php

namespace App\Console\Commands;

use App\Models\LogRegister;
use App\Models\LogMovement;
use App\Models\Order;
use App\Models\Financial;
use App\Models\FinancialHistory;
use App\Models\AnomalyCode;
use App\Models\MoveCode;
use App\Models\ReturnCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PopularFinancialEDP extends Command
{
    protected $signature = 'financial:popular-edp-completo 
                            {--test : Modo teste - processa apenas 10 registros}
                            {--skip-register : Pula processamento do log_register}
                            {--skip-movement : Pula processamento do log_movement}';
    
    protected $description = 'Popula tabela financial com TODOS os dados EDP (log_register + log_movement)';

    private $stats = [
        // Stats log_register
        'total_register' => 0,
        'register_criados' => 0,
        'register_reprovados' => 0,
        'register_order_not_found' => 0,
        'register_erros' => 0,
        
        // Stats log_movement
        'total_movement' => 0,
        'movement_atualizados' => 0,
        'movement_not_found' => 0,
        'movement_erros' => 0
    ];

    public function handle()
    {
        $this->info('💰 Processamento COMPLETO Financial EDP');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $testMode = $this->option('test');

        // FASE 1: Processar log_register (criar cobranças)
        if (!$this->option('skip-register')) {
            $this->info('📋 FASE 1: Processando log_register (criando cobranças)...');
            $this->newLine();
            $this->processarLogRegister($testMode);
            $this->newLine();
        }

        // FASE 2: Processar log_movement (atualizar status)
        if (!$this->option('skip-movement')) {
            $this->info('💳 FASE 2: Processando log_movement (atualizando status)...');
            $this->newLine();
            $this->processarLogMovement($testMode);
            $this->newLine();
        }

        // Relatório final
        $this->exibirRelatorioFinal();

        return Command::SUCCESS;
    }

    // ===================================
    // FASE 1: LOG_REGISTER
    // ===================================
    
    private function processarLogRegister($testMode)
    {
        $query = LogRegister::orderBy('start_date', 'asc');

        if ($testMode) {
            $query->limit(10);
        }

        $registros = $query->get();
        $this->stats['total_register'] = $registros->count();

        $this->info("Total de registros: {$this->stats['total_register']}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($registros->count());
        $progressBar->start();

        foreach ($registros as $registro) {
            $this->criarCobrancaDoRegistro($registro);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function criarCobrancaDoRegistro($registro)
    {
        try {
            // Buscar order pelo installation_number
            $order = Order::where('installation_number', $registro->installation_number)->first();

            if (!$order) {
                $this->stats['register_order_not_found']++;
                Log::warning("Order não encontrado - Instalação: {$registro->installation_number}");
                return;
            }

            // Verificar se já existe cobrança para este registro
            $jaExiste = Financial::where('order_id', $order->id)
                ->whereYear('due_date', $registro->start_date?->year ?? now()->year)
                ->whereMonth('due_date', $registro->start_date?->month ?? now()->month)
                ->where('payment_method', 'EDP')
                ->exists();

            if ($jaExiste) {
                return; // Já processado
            }

            // Determinar status baseado em code_anomaly e code_move
            $statusInfo = $this->determinarStatus($registro);

            // Criar cobrança
            $financial = Financial::create([
                'order_id' => $order->id,
                'value' => $this->parseValor($registro->value_installment),
                'due_date' => $registro->start_date ?? now(),
                'payment_method' => 'EDP',
                'status' => $statusInfo['status'],
                'description' => "EDP - Cobrança " . ($registro->start_date?->format('m/Y') ?? 'N/A'),
                'obs' => $statusInfo['obs'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Registrar histórico
            FinancialHistory::create([
                'financial_id' => $financial->id,
                'old_status' => null,
                'new_status' => $statusInfo['status'],
                'reason' => $statusInfo['reason'],
                'changed_by' => 'EDP',
                'metadata' => [
                    'source' => 'log_register',
                    'log_register_id' => $registro->id,
                    'code_anomaly' => $registro->code_anomaly,
                    'code_move' => $registro->code_move,
                    'arquivo_data' => $registro->arquivo_data
                ]
            ]);

            if ($statusInfo['status'] === 'SENDING') {
                $this->stats['register_criados']++;
            } else {
                $this->stats['register_reprovados']++;
            }

        } catch (\Exception $e) {
            $this->stats['register_erros']++;
            Log::error("Erro ao criar cobrança do registro {$registro->id}: " . $e->getMessage());
        }
    }

    private function determinarStatus($registro)
    {
        $obs = "Cobrança enviada em " . ($registro->arquivo_data ?? 'N/A');
        $reason = 'Cobrança enviada para EDP';
        
        // Verificar code_anomaly
        if ($registro->code_anomaly && $registro->code_anomaly !== '00') {
            $anomaly = AnomalyCode::where('code', $registro->code_anomaly)->first();
            $descricao = $anomaly ? $anomaly->description : 'Anomalia desconhecida';
            
            return [
                'status' => 'REPROVED',
                'obs' => $obs . " | Código de anomalias {$registro->code_anomaly} - {$descricao}",
                'reason' => "Reprovado: {$descricao}"
            ];
        }

        // Verificar code_move
        if ($registro->code_move && !in_array($registro->code_move, ['2', '6'])) {
            $move = MoveCode::where('code', $registro->code_move)->first();
            $descricao = $move ? $move->description : 'Movimento desconhecido';
            
            return [
                'status' => 'REPROVED',
                'obs' => $obs . " | Reprovado pela EDP. Código de movimento {$registro->code_move} - {$descricao}",
                'reason' => "Movimento reprovado: {$descricao}"
            ];
        }

        // Tudo OK - status SENDING
        return [
            'status' => 'SENDING',
            'obs' => $obs,
            'reason' => $reason
        ];
    }

    // ===================================
    // FASE 2: LOG_MOVEMENT
    // ===================================
    
    private function processarLogMovement($testMode)
    {
        $query = LogMovement::orderBy('date_movement', 'asc');

        if ($testMode) {
            $query->limit(10);
        }

        $movimentos = $query->get();
        $this->stats['total_movement'] = $movimentos->count();

        $this->info("Total de movimentos: {$this->stats['total_movement']}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($movimentos->count());
        $progressBar->start();

        foreach ($movimentos as $movimento) {
            $this->atualizarCobrancaDoMovimento($movimento);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function atualizarCobrancaDoMovimento($movimento)
    {
        try {
            // Buscar order pelo installation_number
            $order = Order::where('installation_number', $movimento->installation_number)->first();

            if (!$order) {
                $this->stats['movement_not_found']++;
                return;
            }

            // Buscar financial correspondente
            // date_invoice: 202412 → buscar cobrança de 2024-12
            $dateInvoice = $this->parseDateInvoice($movimento->date_invoice);
            
            $financial = Financial::where('order_id', $order->id)
                ->whereYear('due_date', $dateInvoice->year)
                ->whereMonth('due_date', $dateInvoice->month)
                ->where('payment_method', 'EDP')
                ->first();

            if (!$financial) {
                $this->stats['movement_not_found']++;
                Log::warning("Financial não encontrado para movimento - Instalação: {$movimento->installation_number}, Mês: {$dateInvoice->format('m/Y')}");
                return;
            }

            // Determinar nova status baseado em code_return
            $statusUpdate = $this->determinarStatusMovimento($movimento, $financial);

            if (!$statusUpdate) {
                return; // Nenhuma atualização necessária
            }

            // Atualizar financial
            $oldStatus = $financial->status;
            $financial->update([
                'status' => $statusUpdate['new_status'],
                'charge_paid' => $statusUpdate['charge_paid'] ?? $financial->charge_paid,
                'paid_value' => $statusUpdate['paid_value'] ?? $financial->paid_value,
                'due_date' => $statusUpdate['due_date'] ?? $financial->due_date,
                'obs' => $this->mergeObs($financial->obs, $statusUpdate['obs_add'] ?? null)
            ]);

            // Registrar histórico
            FinancialHistory::create([
                'financial_id' => $financial->id,
                'old_status' => $oldStatus,
                'new_status' => $statusUpdate['new_status'],
                'reason' => $statusUpdate['reason'],
                'changed_by' => 'EDP',
                'metadata' => [
                    'source' => 'log_movement',
                    'log_movement_id' => $movimento->id,
                    'code_return' => $movimento->code_return,
                    'date_movement' => $movimento->date_movement,
                    'arquivo_data' => $movimento->arquivo_data
                ]
            ]);

            $this->stats['movement_atualizados']++;

        } catch (\Exception $e) {
            $this->stats['movement_erros']++;
            Log::error("Erro ao atualizar movimento {$movimento->id}: " . $e->getMessage());
        }
    }

    private function determinarStatusMovimento($movimento, $financial)
    {
        // CORREÇÃO: code_return ao invés de code_move
        switch ($movimento->code_return) {
            case '01': // Faturamento - SENDING → PENDING
                if ($financial->status === 'SENDING') {
                    return [
                        'new_status' => 'PENDING',
                        'reason' => 'Cobrança confirmada pela EDP',
                        'due_date' => $this->parseDateInvoice($movimento->date_invoice)
                    ];
                }
                break;

            case '06': // Pagamento - PENDING → RECEIVED
                if (in_array($financial->status, ['PENDING', 'SENDING'])) {
                    $datePagamento = $this->parseDateMovement($movimento->date_movement);
                    
                    return [
                        'new_status' => 'RECEIVED',
                        'reason' => 'Pagamento confirmado pela EDP',
                        'charge_paid' => $datePagamento->day,
                        'paid_value' => $this->parseValor($movimento->value),
                        'due_date' => $this->parseDateInvoice($movimento->date_invoice)
                    ];
                }
                break;

            case '03': // Não faturado
            case '04': // Devolução por revisão
            case '05': // Cobrança por revisão  
            case '07': // Volta a débito
                $returnCode = ReturnCode::where('code', $movimento->code_return)->first();
                $descricao = $returnCode ? $returnCode->description : 'Motivo desconhecido';
                
                return [
                    'new_status' => 'REPROVED',
                    'reason' => "Reprovado pela EDP: {$descricao}",
                    'obs_add' => "Código de retorno {$movimento->code_return} - {$descricao}"
                ];
        }

        return null; // Sem atualização
    }

    // ===================================
    // HELPERS
    // ===================================
    
    private function parseValor($valorStr)
    {
        if (!$valorStr) return 0;
        
        $valorStr = trim($valorStr);
        $valorStr = str_pad($valorStr, 15, '0', STR_PAD_LEFT);
        
        $centavos = substr($valorStr, -2);
        $reais = ltrim(substr($valorStr, 0, -2), '0') ?: '0';
        
        return (float) ($reais . '.' . $centavos);
    }

    private function parseDateInvoice($dateStr)
    {
        // 202412 → 2024-12-01
        if (!$dateStr || strlen($dateStr) !== 6) {
            return now();
        }

        $ano = substr($dateStr, 0, 4);
        $mes = substr($dateStr, 4, 2);

        return Carbon::createFromFormat('Y-m-d', "{$ano}-{$mes}-01");
    }

    private function parseDateMovement($dateStr)
    {
        // 20241218 → 2024-12-18
        if (!$dateStr || strlen($dateStr) !== 8) {
            return now();
        }

        $ano = substr($dateStr, 0, 4);
        $mes = substr($dateStr, 4, 2);
        $dia = substr($dateStr, 6, 2);

        return Carbon::createFromFormat('Y-m-d', "{$ano}-{$mes}-{$dia}");
    }

    private function mergeObs($obsOriginal, $obsAdicional)
    {
        if (!$obsAdicional) {
            return $obsOriginal;
        }

        if (!$obsOriginal) {
            return $obsAdicional;
        }

        return $obsOriginal . " | " . $obsAdicional;
    }

    private function exibirRelatorioFinal()
    {
        $this->info('📋 RELATÓRIO FINAL');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $this->line('📋 LOG_REGISTER (Criação de Cobranças):');
        $this->line("  Total processados: {$this->stats['total_register']}");
        $this->line("  <fg=green>✓ Criados (SENDING): {$this->stats['register_criados']}</>");
        $this->line("  <fg=red>✗ Reprovados: {$this->stats['register_reprovados']}</>");
        $this->line("  <fg=yellow>⊘ Order não encontrado: {$this->stats['register_order_not_found']}</>");
        $this->line("  <fg=red>✗ Erros: {$this->stats['register_erros']}</>");
        $this->newLine();
        
        $this->line('💳 LOG_MOVEMENT (Atualização de Status):');
        $this->line("  Total processados: {$this->stats['total_movement']}");
        $this->line("  <fg=green>✓ Atualizados: {$this->stats['movement_atualizados']}</>");
        $this->line("  <fg=yellow>⊘ Cobrança não encontrada: {$this->stats['movement_not_found']}</>");
        $this->line("  <fg=red>✗ Erros: {$this->stats['movement_erros']}</>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
