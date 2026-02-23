<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Financial;
use App\Models\FinancialHistory;
use App\Models\LogMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AtualizarPagamentosEDP extends Command
{
    protected $signature = 'financial:atualizar-pagamentos-edp 
                            {--test : Modo teste - processa apenas 10 registros}';
    
    protected $description = 'Atualiza status de pagamentos baseado nos retornos EDP (code_return = 06)';

    private $stats = [
        'total' => 0,
        'atualizados' => 0,
        'nao_encontrados' => 0,
        'ja_processados' => 0,
        'erros' => 0
    ];

    public function handle()
    {
        $this->info('💳 Atualizando pagamentos EDP...');
        $this->newLine();

        $testMode = $this->option('test');

        // Buscar movimentos de pagamento (code_return = 06)
        $query = LogMovement::where('code_return', '06')
            ->orderBy('date_movement', 'asc');

        if ($testMode) {
            $query->limit(10);
        }

        $pagamentos = $query->get();
        $this->stats['total'] = $pagamentos->count();

        $this->info("📊 Total de pagamentos: {$this->stats['total']}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($pagamentos->count());
        $progressBar->start();

        foreach ($pagamentos as $pagamento) {
            $this->processarPagamento($pagamento);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->exibirRelatorio();

        return Command::SUCCESS;
    }

    private function processarPagamento($pagamento)
    {
        try {
            // Buscar order pelo installation_number
            $order = Order::where('installation_number', $pagamento->installation_number)->first();

            if (!$order) {
                $this->stats['nao_encontrados']++;
                Log::warning("Order não encontrado para pagamento - Instalação: {$pagamento->installation_number}");
                return;
            }

            // Buscar financial correspondente
            // date_invoice: 202412 → buscar cobrança de 2024-12
            $dateInvoice = $this->parseDateInvoice($pagamento->date_invoice);
            
            $financial = Financial::where('order_id', $order->id)
                ->whereYear('due_date', $dateInvoice->year)
                ->whereMonth('due_date', $dateInvoice->month)
                ->where('payment_method', 'EDP')
                ->first();

            if (!$financial) {
                $this->stats['nao_encontrados']++;
                Log::warning("Financial não encontrado para pagamento - Instalação: {$pagamento->installation_number}, Mês: {$dateInvoice->format('m/Y')}");
                return;
            }

            // Verificar se já está como RECEIVED
            if ($financial->status === 'RECEIVED') {
                $this->stats['ja_processados']++;
                return;
            }

            // Preparar dados para atualização
            $datePagamento = $this->parseDateMovement($pagamento->date_movement);
            $valorPago = $this->parseValor($pagamento->value);
            $oldStatus = $financial->status;

            // Atualizar financial
            $financial->update([
                'status' => 'RECEIVED',
                'charge_paid' => $datePagamento->day,
                'paid_value' => $valorPago,
                'due_date' => $dateInvoice, // Mês/ano da fatura
                'obs' => $this->mergeObs(
                    $financial->obs, 
                    "Pagamento confirmado em " . $datePagamento->format('d/m/Y')
                )
            ]);

            // Registrar histórico
            FinancialHistory::create([
                'financial_id' => $financial->id,
                'old_status' => $oldStatus,
                'new_status' => 'RECEIVED',
                'reason' => 'Pagamento confirmado pela EDP',
                'changed_by' => 'EDP',
                'metadata' => [
                    'source' => 'log_movement',
                    'log_movement_id' => $pagamento->id,
                    'code_return' => '06',
                    'date_movement' => $pagamento->date_movement,
                    'valor_pago' => $valorPago,
                    'data_pagamento' => $datePagamento->format('Y-m-d'),
                    'arquivo_data' => $pagamento->arquivo_data
                ]
            ]);

            $this->stats['atualizados']++;

        } catch (\Exception $e) {
            $this->stats['erros']++;
            Log::error("Erro ao processar pagamento {$pagamento->id}: " . $e->getMessage());
        }
    }

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

    private function exibirRelatorio()
    {
        $this->info('📋 RELATÓRIO DE ATUALIZAÇÃO DE PAGAMENTOS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $this->line("Total de pagamentos processados: {$this->stats['total']}");
        $this->line("<fg=green>✓ Atualizados para RECEIVED: {$this->stats['atualizados']}</>");
        $this->line("<fg=yellow>⊘ Já processados (já RECEIVED): {$this->stats['ja_processados']}</>");
        $this->line("<fg=yellow>⊘ Cobranças não encontradas: {$this->stats['nao_encontrados']}</>");
        $this->line("<fg=red>✗ Erros: {$this->stats['erros']}</>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
