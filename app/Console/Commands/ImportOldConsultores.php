<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OldConsultoresImportService;

class ImportOldConsultores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'import:old-consultores';
    protected $description = 'Importa consultores da tabela staging (oldConsultores) para a tabela definitiva';

    public function handle(OldConsultoresImportService $service)
    {
        $this->info('Iniciando importação...');
        $service->handle();
        $this->info('Importação concluída com sucesso!');
    }
}
