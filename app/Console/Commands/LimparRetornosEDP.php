<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LogRegister;
use App\Models\LogMovement;
use App\Models\RetornoArmazenado;
use App\Models\FailedReturn;
use Illuminate\Support\Facades\DB;

class LimparRetornosEDP extends Command
{
    protected $signature = 'edp:limpar-retornos 
                            {--force : Não pedir confirmação}';

    protected $description = 'Limpa todos os registros de retornos EDP para reprocessamento';

    public function handle()
    {
        $this->warn('⚠️  ATENÇÃO: Esta operação irá deletar TODOS os registros de:');
        $this->line('   - log_register (Tipo B)');
        $this->line('   - log_movement (Tipo F)');
        $this->line('   - retornos_armazenados (Controle de arquivos)');
        $this->line('   - failed_returns (Falhas registradas)');
        $this->newLine();

        $countB = LogRegister::count();
        $countF = LogMovement::count();
        $countRetornos = RetornoArmazenado::count();
        $countFailed = FailedReturn::count();

        $this->info("📊 Totais atuais:");
        $this->line("   Registros B: {$countB}");
        $this->line("   Registros F: {$countF}");
        $this->line("   Retornos armazenados: {$countRetornos}");
        $this->line("   Falhas: {$countFailed}");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Deseja continuar com a limpeza?')) {
                $this->info('Operação cancelada.');
                return Command::SUCCESS;
            }
        }

        $this->info('🗑️  Limpando registros...');

        DB::beginTransaction();

        try {
            LogRegister::truncate();
            $this->line('✓ log_register limpo');

            LogMovement::truncate();
            $this->line('✓ log_movement limpo');

            RetornoArmazenado::truncate();
            $this->line('✓ retornos_armazenados limpo');

            FailedReturn::truncate();
            $this->line('✓ failed_returns limpo');

            DB::commit();

            $this->newLine();
            $this->info('✅ Limpeza concluída com sucesso!');
            $this->info('💡 Execute agora: php artisan edp:pegar-todos-retornos');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erro ao limpar: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
