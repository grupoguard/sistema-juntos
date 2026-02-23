<?php

namespace App\Console\Commands;

use App\Services\EdpService;
use Illuminate\Console\Command;

class ProcessarArquivoEDP extends Command
{
   protected $signature = 'edp:pegar-todos-retornos 
                            {--local-first : Processa arquivos locais antes da API}';

    protected $description = 'Processa arquivos de retorno EDP (API ou locais+API)';

    public function handle()
    {
        // Se --local-first, processar locais primeiro
        if ($this->option('local-first')) {
            $this->info('📁 Processando arquivos locais primeiro...');
            $this->call('edp:processar-locais');
            $this->newLine();
        }

        // Depois processar da API
        $this->info('📡 Buscando arquivos da API EDP...');
        
        $edpService = new EdpService();

        try {
            $resultado = $edpService->processarArquivosEmMassa();
            $this->info($resultado);
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
