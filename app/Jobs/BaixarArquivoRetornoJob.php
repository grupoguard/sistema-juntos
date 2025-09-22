<?php

namespace App\Jobs;

use App\Models\RetornoArmazenado;
use App\Models\RetornoArquivo;
use App\Models\RetornosArmazenados;
use App\Services\EdpService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BaixarArquivoRetornoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Número de tentativas antes de falhar
    public $timeout = 120; // Tempo máximo de execução (segundos)

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Não podemos injetar serviços aqui porque jobs são serializados.
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $edpService = app(EdpService::class); // Obtendo o serviço do container do Laravel

        // 1. Listar arquivos de retorno disponíveis
        $arquivosResponse = $edpService->listarArquivosRetorno();

        if (!isset($arquivosResponse['Data']) || !is_array($arquivosResponse['Data'])) {
            Log::error('Erro ao listar arquivos de retorno: Resposta inesperada.');
            return;
        }

        $arquivos = $arquivosResponse['Data'];

        if (empty($arquivos)) {
            Log::info('Nenhum arquivo de retorno disponível para baixar.');
            return;
        }

        // 2. Filtrar arquivos que ainda não foram processados
        foreach ($arquivos as $arquivo) {
            $arquivoId = $arquivo['Id'];
            $nomeArquivo = $arquivo['Nome'];

            if (RetornoArmazenado::where('arquivo_id', $arquivoId)->exists()) {
                Log::info("Arquivo {$nomeArquivo} (ID: {$arquivoId}) já foi processado. Pulando...");
                continue;
            }

            try {
                // 3. Baixar o arquivo ZIP
                $zipPath = $edpService->baixarArquivoRetorno($arquivoId);

                if (!file_exists($zipPath)) {
                    throw new Exception("Erro: O arquivo ZIP não foi encontrado.");
                }

                // 4. Extrair o arquivo ZIP
                $txtPath = $edpService->extrairArquivoZip($zipPath);

                if (!file_exists($txtPath)) {
                    throw new Exception("Erro: O arquivo TXT não foi extraído corretamente.");
                }

                // 5. Processar o arquivo TXT
                $edpService->processarArquivoTxt($txtPath);

                // 6. Registrar que o arquivo foi processado
                RetornoArmazenado::create([
                    'arquivo_id' => $arquivoId,
                    'nome_arquivo' => $nomeArquivo,
                    'baixado_em' => now(),
                ]);

                Log::info("Arquivo {$nomeArquivo} (ID: {$arquivoId}) baixado e processado com sucesso!");

            } catch (Exception $e) {
                Log::error("Erro ao processar o arquivo ID {$arquivoId}: " . $e->getMessage());
            }
        }

        Log::info('Todos os arquivos disponíveis já foram processados.');
    }
}
