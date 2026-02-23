<?php

namespace App\Console\Commands;

use App\Services\EdpService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class AnalisarArquivosEDP extends Command
{
    protected $signature = 'edp:analisar-arquivos 
                            {--export : Exportar para CSV}
                            {--download= : Baixar arquivo específico por ID}';
    
    protected $description = 'Analisa todos os arquivos de retorno EDP sem processar';

    public function handle()
    {
        $edpService = new EdpService();
        
        if ($downloadId = $this->option('download')) {
            $this->downloadEAnalisarArquivo($downloadId, $edpService);
            return Command::SUCCESS;
        }

        $this->info('🔍 Analisando todos os arquivos disponíveis na EDP...');
        $this->newLine();

        // Listar arquivos
        $arquivosResponse = $edpService->listarArquivosRetorno();
        
        if (!isset($arquivosResponse['Data']) || !is_array($arquivosResponse['Data'])) {
            $this->error('Erro ao listar arquivos: ' . json_encode($arquivosResponse));
            return Command::FAILURE;
        }

        $arquivos = $arquivosResponse['Data'];
        
        $this->info("📊 Total de arquivos: " . count($arquivos));
        $this->newLine();

        // Analisar cada arquivo
        $analise = [];
        $progressBar = $this->output->createProgressBar(count($arquivos));
        $progressBar->start();

        foreach ($arquivos as $arquivo) {
            $info = $this->analisarArquivo($arquivo, $edpService);
            $analise[] = $info;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Exibir estatísticas
        $this->exibirEstatisticas($analise);

        // Exibir tabela
        $this->exibirTabela($analise);

        if ($this->option('export')) {
            $this->exportarCSV($analise);
        }

        return Command::SUCCESS;
    }

    private function analisarArquivo($arquivo, $edpService)
    {
        $arquivoId = $arquivo['Id'];
        $nomeArquivo = $arquivo['Arquivo'];
        $dataInclusao = $arquivo['DataInclusao'];

        $info = [
            'id' => $arquivoId,
            'nome' => $nomeArquivo,
            'data_inclusao' => $dataInclusao,
            'content_type' => null,
            'tamanho' => null,
            'tipo_real' => null,
            'primeiros_bytes' => null,
            'erro' => null
        ];

        try {
            $client = new Client([
                'base_uri' => $edpService->baseUrl, // ✅ Usar do service
            ]);

            $response = $client->request('POST', '/api/ObterArquivoRetorno', [
                'multipart' => [
                    [
                        'name' => 'token',
                        'contents' => $edpService->token,
                    ],
                    [
                        'name' => 'ArquivoId',
                        'contents' => $arquivoId,
                    ],
                ],
                'http_errors' => false
            ]);

            // ← ADICIONAR ISSO PARA VER O STATUS CODE
            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                $info['erro'] = "HTTP {$statusCode}: " . $response->getBody()->getContents();
                return $info;
            }

            $conteudo = $response->getBody()->getContents();
            
            // ← VER SE VEIO VAZIO
            if (empty($conteudo)) {
                $info['erro'] = "Resposta vazia da API";
                return $info;
            }
            
            $info['content_type'] = $response->getHeaderLine('Content-Type');
            $info['tamanho'] = strlen($conteudo);
            $info['primeiros_bytes'] = $this->getPrimeirosBytes($conteudo);
            $info['tipo_real'] = $this->detectarTipoReal($conteudo);

        } catch (\Exception $e) {
            // ← CAPTURAR MENSAGEM DE ERRO COMPLETA
            $info['erro'] = $e->getMessage();
        }

        return $info;
    }

    private function getPrimeirosBytes($conteudo)
    {
        $bytes = substr($conteudo, 0, 20);
        $hex = bin2hex($bytes);
        $ascii = $this->bytesToAscii($bytes);
        
        return [
            'hex' => $hex,
            'ascii' => $ascii
        ];
    }

    private function bytesToAscii($bytes)
    {
        $ascii = '';
        for ($i = 0; $i < strlen($bytes); $i++) {
            $char = $bytes[$i];
            $ascii .= (ord($char) >= 32 && ord($char) <= 126) ? $char : '.';
        }
        return $ascii;
    }

    private function detectarTipoReal($conteudo)
    {
        // ZIP começa com PK (50 4B)
        if (substr($conteudo, 0, 2) === 'PK') {
            return 'ZIP';
        }

        // JSON começa com { ou [
        if (substr($conteudo, 0, 1) === '{' || substr($conteudo, 0, 1) === '[') {
            return 'JSON';
        }

        // TXT geralmente começa com A (registro cabeçalho EDP)
        if (substr($conteudo, 0, 1) === 'A') {
            return 'TXT-EDP';
        }

        // Se tiver caracteres não-ASCII logo no início, pode ser binário corrompido
        $primeiros = substr($conteudo, 0, 50);
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $primeiros)) {
            return 'BINÁRIO';
        }

        return 'DESCONHECIDO';
    }

    private function exibirEstatisticas($analise)
    {
        $this->info('📈 ESTATÍSTICAS:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        // Por tipo real
        $porTipo = [];
        foreach ($analise as $info) {
            $tipo = $info['tipo_real'] ?? 'ERRO';
            $porTipo[$tipo] = ($porTipo[$tipo] ?? 0) + 1;
        }

        foreach ($porTipo as $tipo => $count) {
            $this->line("  {$tipo}: {$count}");
        }

        // Erros
        $erros = array_filter($analise, fn($i) => !empty($i['erro']));
        if (!empty($erros)) {
            $this->newLine();
            $this->warn("⚠️  Erros: " . count($erros));
        }

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
    }

    private function exibirTabela($analise)
    {
        $tableData = [];
        
        foreach (array_slice($analise, 0, 50) as $info) {
            $tableData[] = [
                substr($info['id'], 0, 8) . '...',
                substr($info['nome'], 0, 30),
                $info['tipo_real'] ?? 'N/A',
                $info['content_type'] ?? 'N/A',
                number_format($info['tamanho'] ?? 0) . ' bytes',
                $info['primeiros_bytes']['ascii'] ?? 'N/A'
            ];
        }

        $this->table(
            ['ID', 'Nome', 'Tipo Real', 'Content-Type', 'Tamanho', 'Preview'],
            $tableData
        );

        if (count($analise) > 50) {
            $this->line("\n... e mais " . (count($analise) - 50) . " arquivos.");
        }
    }

    private function exportarCSV($analise)
    {
        $filename = storage_path('app/analise_edp_' . now()->format('Y-m-d_His') . '.csv');
        $file = fopen($filename, 'w');

        // Header
        fputcsv($file, [
            'ID',
            'Nome',
            'Data Inclusão',
            'Tipo Real',
            'Content-Type',
            'Tamanho',
            'Primeiros Bytes (HEX)',
            'Primeiros Bytes (ASCII)',
            'Erro'
        ]);

        // Dados
        foreach ($analise as $info) {
            fputcsv($file, [
                $info['id'],
                $info['nome'],
                $info['data_inclusao'],
                $info['tipo_real'] ?? '',
                $info['content_type'] ?? '',
                $info['tamanho'] ?? '',
                $info['primeiros_bytes']['hex'] ?? '',
                $info['primeiros_bytes']['ascii'] ?? '',
                $info['erro'] ?? ''
            ]);
        }

        fclose($file);
        $this->info("\n✓ Análise exportada: {$filename}");
    }

    private function downloadEAnalisarArquivo($arquivoId, $edpService)
    {
        $this->info("📥 Baixando e analisando arquivo: {$arquivoId}");
        $this->newLine();

        try {
            $client = new Client([
                'base_uri' => config('services.edp.base_url'),
            ]);

            $response = $client->request('POST', '/api/ObterArquivoRetorno', [
                'form_params' => [
                    'token' => config('services.edp.token'),
                    'ArquivoId' => $arquivoId,
                ],
            ]);

            $conteudo = $response->getBody()->getContents();
            
            $this->line("Content-Type: " . $response->getHeaderLine('Content-Type'));
            $this->line("Tamanho: " . number_format(strlen($conteudo)) . " bytes");
            $this->newLine();

            // Detectar tipo
            $tipo = $this->detectarTipoReal($conteudo);
            $this->line("Tipo detectado: <fg=cyan>{$tipo}</>");
            $this->newLine();

            // Primeiros 200 caracteres
            $this->line("📄 Primeiros 200 caracteres:");
            $this->line(substr($conteudo, 0, 200));
            $this->newLine();

            // Salvar arquivo
            $filename = storage_path("app/analise_{$arquivoId}.txt");
            file_put_contents($filename, $conteudo);
            $this->info("✓ Arquivo salvo: {$filename}");

        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
        }
    }
}