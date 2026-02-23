<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LogRegister;
use App\Models\LogMovement;
use App\Models\RetornoArmazenado;
use App\Models\FailedReturn;
use Illuminate\Support\Facades\DB;

class EstatisticasEDP extends Command
{
    protected $signature = 'edp:stats';
    
    protected $description = 'Exibe estatísticas dos retornos EDP';

    public function handle()
    {
        $this->info('📊 ESTATÍSTICAS RETORNOS EDP');
        $this->line('═══════════════════════════════════════════════');
        $this->newLine();

        // Arquivos processados
        $totalArquivos = RetornoArmazenado::count();
        $this->line("📁 Arquivos processados: {$totalArquivos}");
        
        if ($totalArquivos > 0) {
            $primeiro = RetornoArmazenado::oldest('baixado_em')->first();
            $ultimo = RetornoArmazenado::latest('baixado_em')->first();
            $this->line("   Primeiro: {$primeiro->baixado_em->format('d/m/Y H:i')}");
            $this->line("   Último: {$ultimo->baixado_em->format('d/m/Y H:i')}");
        }
        $this->newLine();

        // Registros B
        $totalB = LogRegister::count();
        $this->line("📋 Registros B (Cadastramento): {$totalB}");
        
        if ($totalB > 0) {
            $movimentosB = LogRegister::select('code_move', DB::raw('count(*) as total'))
                ->groupBy('code_move')
                ->get();
            
            foreach ($movimentosB as $mov) {
                $descricao = $this->getDescricaoMovimentoB($mov->code_move);
                $this->line("   Código {$mov->code_move} ({$descricao}): {$mov->total}");
            }
        }
        $this->newLine();

        // Registros F
        $totalF = LogMovement::count();
        $this->line("💰 Registros F (Movimento): {$totalF}");
        
        if ($totalF > 0) {
            $retornosF = LogMovement::select('code_return', DB::raw('count(*) as total'))
                ->whereNotNull('code_return')
                ->groupBy('code_return')
                ->get();
            
            foreach ($retornosF as $ret) {
                $descricao = $this->getDescricaoRetornoF($ret->code_return);
                $this->line("   Código {$ret->code_return} ({$descricao}): {$ret->total}");
            }
        }
        $this->newLine();

        // Falhas
        $totalFalhas = FailedReturn::count();
        $falhasPendentes = FailedReturn::unprocessed()->count();
        
        $this->line("⚠️  Falhas: {$totalFalhas} (Pendentes: {$falhasPendentes})");
        
        if ($falhasPendentes > 0) {
            $porTipo = FailedReturn::unprocessed()
                ->select('record_type', DB::raw('count(*) as total'))
                ->groupBy('record_type')
                ->get();
            
            foreach ($porTipo as $tipo) {
                $this->line("   Tipo {$tipo->record_type}: {$tipo->total}");
            }
        }

        $this->line('═══════════════════════════════════════════════');

        return Command::SUCCESS;
    }

    private function getDescricaoMovimentoB($codigo)
    {
        $codigos = [
            '1' => 'Exclusão por alteração cadastral',
            '2' => 'Inclusão automática',
            '3' => 'Inconsistência',
            '4' => 'Processando (Parcelamento)',
            '5' => 'Exclusão por revisão',
            '6' => 'Incluído pela EDP'
        ];

        return $codigos[$codigo] ?? 'Desconhecido';
    }

    private function getDescricaoRetornoF($codigo)
    {
        $codigos = [
            '01' => 'Faturamento',
            '03' => 'Não faturado',
            '04' => 'Devolução por revisão',
            '05' => 'Cobrança por revisão',
            '06' => 'Baixa do serviço',
            '07' => 'Volta a débito'
        ];

        return $codigos[$codigo] ?? 'Desconhecido';
    }
}
