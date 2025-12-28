<?php

namespace App\Console\Commands;

use App\Services\OldFinancialImportService;
use Illuminate\Console\Command;

class ImportOldFinancial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:import-old-financial';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiiza os pedidos da tabela staging (oldFinancial) para a tabela definitiva';

    /**
     * Execute the console command.
     */
    public function handle(OldFinancialImportService $financial)
    {
        $this->info('Iniciando importação...');
        $financial->handle();
        $this->info('Importação concluída com sucesso!');
    }
}
