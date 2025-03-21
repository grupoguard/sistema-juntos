<?php

namespace App\Console\Commands;

use App\Services\EdpService;
use Illuminate\Console\Command;

class ProcessarArquivoEDP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edp:pegar-todos-retornos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $edpService = new EdpService();

        try {
            $this->info($edpService->processarArquivosEmMassa());
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
        }
    }
}
