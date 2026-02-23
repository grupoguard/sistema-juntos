<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FailedReturn;

class VerFalhaEDP extends Command
{
    protected $signature = 'edp:ver-falha {id}';
    
    protected $description = 'Mostra detalhes de uma falha específica';

    public function handle()
    {
        $id = $this->argument('id');
        $falha = FailedReturn::find($id);

        if (!$falha) {
            $this->error("Falha #{$id} não encontrada.");
            return Command::FAILURE;
        }

        $this->info("═══════════════════════════════════════════════");
        $this->info("  DETALHES DA FALHA #{$falha->id}");
        $this->info("═══════════════════════════════════════════════");
        $this->newLine();

        $this->line("<fg=yellow>Tipo de Registro:</> {$falha->record_type}");
        $this->line("<fg=yellow>Data do Arquivo:</> " . ($falha->arquivo_data?->format('d/m/Y') ?? 'N/A'));
        $this->line("<fg=yellow>Criado em:</> {$falha->created_at->format('d/m/Y H:i:s')}");
        $this->line("<fg=yellow>Processado:</> " . ($falha->processed ? 'Sim' : 'Não'));
        
        if ($falha->processed) {
            $this->line("<fg=yellow>Processado em:</> {$falha->processed_at->format('d/m/Y H:i:s')}");
            if ($falha->resolution_note) {
                $this->line("<fg=yellow>Nota:</> {$falha->resolution_note}");
            }
        }

        $this->newLine();
        $this->line("<fg=red>ERRO:</>");
        $this->line($falha->error_message);

        $this->newLine();
        $this->line("<fg=cyan>LINHA COMPLETA:</>");
        $this->line($falha->line_content);

        $this->newLine();
        $this->line("<fg=cyan>ANÁLISE DA LINHA:</>");
        $this->analisarLinha($falha->line_content, $falha->record_type);

        $this->newLine();
        $this->info("═══════════════════════════════════════════════");

        return Command::SUCCESS;
    }

    private function analisarLinha($linha, $tipo)
    {
        $this->line("Tamanho total: " . strlen($linha) . " caracteres");
        $this->newLine();

        if ($tipo === 'B') {
            $this->line("Posições esperadas (Registro B):");
            $this->line("001-001: Código (B)         = '" . substr($linha, 0, 1) . "'");
            $this->line("002-010: Instalação         = '" . substr($linha, 1, 9) . "'");
            $this->line("011-012: Valor extra        = '" . substr($linha, 10, 2) . "'");
            $this->line("013-015: Produto            = '" . substr($linha, 12, 3) . "'");
            $this->line("016-017: Nº parcelas        = '" . substr($linha, 15, 2) . "'");
            $this->line("018-032: Valor parcela      = '" . substr($linha, 17, 15) . "'");
            $this->line("042-044: Município          = '" . substr($linha, 41, 3) . "'");
            $this->line("045-052: Data inicial       = '" . substr($linha, 44, 8) . "'");
            $this->line("053-060: Data final         = '" . substr($linha, 52, 8) . "'");
            $this->line("061-100: Endereço           = '" . substr($linha, 60, 40) . "'");
            $this->line("101-140: Nome               = '" . substr($linha, 100, 40) . "'");
            $this->line("141-147: Futuro             = '" . substr($linha, 140, 7) . "'");
            $this->line("148-149: Cód. anomalia      = '" . substr($linha, 147, 2) . "'");
            $this->line("150-150: Cód. movimento     = '" . substr($linha, 149, 1) . "'");
        } elseif ($tipo === 'F') {
            $this->line("Posições esperadas (Registro F):");
            $this->line("001-001: Código (F)         = '" . substr($linha, 0, 1) . "'");
            $this->line("002-010: Instalação         = '" . substr($linha, 1, 9) . "'");
            $this->line("011-012: Valor extra        = '" . substr($linha, 10, 2) . "'");
            $this->line("013-015: Produto            = '" . substr($linha, 12, 3) . "'");
            $this->line("016-020: Parcela            = '" . substr($linha, 15, 5) . "'");
            $this->line("021-035: Roteiro leitura    = '" . substr($linha, 20, 15) . "'");
            $this->line("036-041: Data faturamento   = '" . substr($linha, 35, 6) . "'");
            $this->line("042-044: Município          = '" . substr($linha, 41, 3) . "'");
            $this->line("045-052: Data movimento     = '" . substr($linha, 44, 8) . "'");
            $this->line("053-067: Valor              = '" . substr($linha, 52, 15) . "'");
            $this->line("068-069: Cód. retorno       = '" . substr($linha, 67, 2) . "'");
            $this->line("070-149: Futuro             = '" . substr($linha, 69, 80) . "'");
            $this->line("150-150: Cód. movimento     = '" . substr($linha, 149, 1) . "'");
        }
    }
}
