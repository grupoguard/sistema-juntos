<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OldClientsImportService;
use Exception;

class ImportOldClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'import:old-clients {--force : Force import without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa clients da tabela staging (oldClients) para a tabela definitiva';

    public function handle(OldClientsImportService $service)
    {
        try {
            // Confirmação antes de executar (a menos que use --force)
            if (!$this->option('force') && !$this->confirm('Deseja continuar com a importação? Esta operação pode levar alguns minutos.')) {
                $this->info('Importação cancelada.');
                return Command::FAILURE;
            }

            $this->info('🚀 Iniciando importação de clientes antigos...');
            
            // Medir tempo de execução
            $startTime = microtime(true);
            
            // Executar importação
            $service->handle();
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $this->info("✅ Importação concluída com sucesso!");
            $this->info("⏱️  Tempo de execução: {$executionTime} segundos");
            
            return Command::SUCCESS;
            
        } catch (Exception $e) {
            $this->error('❌ Erro durante a importação:');
            $this->error($e->getMessage());
            
            // Log do erro para análise posterior
            \Log::error('Erro na importação de clientes antigos: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => $this->signature
            ]);
            
            return Command::FAILURE;
        }
    }
}
