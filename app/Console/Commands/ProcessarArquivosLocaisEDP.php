<?php

namespace App\Console\Commands;

use App\Services\EdpService;
use App\Services\EdpParserService;
use App\Models\RetornoArmazenado;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessarArquivosLocaisEDP extends Command
{
    protected $signature = 'edp:processar-locais 
                            {--test : Modo teste - processa apenas 5 arquivos}
                            {--force : Força reprocessamento de arquivos já processados}';
    
    protected $description = 'Processa arquivos EDP locais da pasta storage/app/retornos/';

    private $edpService;
    private $stats = [
        'total' => 0,
        'processados' => 0,
        'ja_processados' => 0,
        'erros' => 0
    ];

    public function handle()
    {
        $this->edpService = new EdpService();
        
        $this->info('📁 Processando arquivos locais EDP...');
        $this->newLine();

        $testMode = $this->option('test');
        $force = $this->option('force');

        // Buscar todos os arquivos na pasta
        $pasta = storage_path('app/retornos/');
        
        if (!File::isDirectory($pasta)) {
            $this->error("Pasta não encontrada: {$pasta}");
            return Command::FAILURE;
        }

        $arquivos = $this->buscarArquivos($pasta);
        
        if (empty($arquivos)) {
            $this->error("Nenhum arquivo encontrado em: {$pasta}");
            return Command::FAILURE;
        }

        // Ordenar por data (mais antigo primeiro)
        $arquivos = $this->ordenarArquivosPorData($arquivos);

        $this->stats['total'] = count($arquivos);
        
        $this->info("📊 Total de arquivos encontrados: " . count($arquivos));
        
        if ($testMode) {
            $arquivos = array_slice($arquivos, 0, 5);
            $this->warn("⚠️  MODO TESTE: Processando apenas 5 arquivos");
        }
        
        $this->newLine();

        // Processar cada arquivo
        $progressBar = $this->output->createProgressBar(count($arquivos));
        $progressBar->start();

        foreach ($arquivos as $arquivo) {
            $this->processarArquivo($arquivo, $force);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Exibir relatório
        $this->exibirRelatorio();

        return Command::SUCCESS;
    }

    private function buscarArquivos($pasta)
    {
        $extensoes = ['*.txt', '*.zip', '*.gz', '*.rar'];
        $arquivos = [];

        foreach ($extensoes as $extensao) {
            $encontrados = File::glob($pasta . $extensao);
            $arquivos = array_merge($arquivos, $encontrados);
        }

        return $arquivos;
    }

    private function ordenarArquivosPorData($arquivos)
    {
        usort($arquivos, function($a, $b) {
            $dataA = $this->extrairDataDoNome($a);
            $dataB = $this->extrairDataDoNome($b);
            
            return $dataA <=> $dataB;
        });

        return $arquivos;
    }

    private function extrairDataDoNome($caminhoArquivo)
    {
        $nomeArquivo = basename($caminhoArquivo);
        
        // Formato: 408_JUNTOS_CLUBE_BEN_20241030_233246.txt.gz
        // Extrair: 20241030
        if (preg_match('/(\d{8})/', $nomeArquivo, $matches)) {
            return $matches[1];
        }

        // Se não encontrar data, retorna 0 (vai pro final)
        return '00000000';
    }

    private function processarArquivo($caminhoArquivo, $force)
    {
        try {
            $nomeArquivo = basename($caminhoArquivo);
            $arquivoId = md5($nomeArquivo); // Gerar ID único baseado no nome

            // Verificar se já foi processado
            if (!$force && RetornoArmazenado::where('arquivo_id', $arquivoId)->exists()) {
                $this->stats['ja_processados']++;
                Log::info("Arquivo {$nomeArquivo} já processado. Pulando...");
                return;
            }

            // Descompactar se necessário
            $txtPath = $this->descompactarSeNecessario($caminhoArquivo);

            if (!$txtPath) {
                throw new \Exception("Não foi possível descompactar: {$nomeArquivo}");
            }

            // Processar o arquivo TXT
            $this->edpService->processarArquivoTxt($txtPath);

            // Registrar como processado
            RetornoArmazenado::updateOrCreate(
                ['arquivo_id' => $arquivoId],
                [
                    'nome_arquivo' => $nomeArquivo,
                    'baixado_em' => now(),
                    'processado' => true,
                    'processado_em' => now()
                ]
            );

            $this->stats['processados']++;
            Log::info("Arquivo {$nomeArquivo} processado com sucesso!");

            // Limpar arquivo temporário se foi descompactado
            if ($txtPath !== $caminhoArquivo && File::exists($txtPath)) {
                File::delete($txtPath);
            }

        } catch (\Exception $e) {
            $this->stats['erros']++;
            Log::error("Erro ao processar arquivo {$caminhoArquivo}: " . $e->getMessage());
        }
    }

    private function descompactarSeNecessario($caminhoArquivo)
    {
        $extensao = strtolower(pathinfo($caminhoArquivo, PATHINFO_EXTENSION));

        switch ($extensao) {
            case 'txt':
                // Já é TXT, retorna direto
                return $caminhoArquivo;

            case 'gz':
                return $this->descompactarGz($caminhoArquivo);

            case 'zip':
                return $this->descompactarZip($caminhoArquivo);

            case 'rar':
                return $this->descompactarRar($caminhoArquivo);

            default:
                throw new \Exception("Extensão não suportada: {$extensao}");
        }
    }

    private function descompactarGz($caminhoGz)
    {
        // Caminho de saída (remover .gz)
        $txtPath = preg_replace('/\.gz$/', '', $caminhoGz);

        // Se já existe TXT descompactado, usar ele
        if (File::exists($txtPath)) {
            return $txtPath;
        }

        // Descompactar
        $gz = gzopen($caminhoGz, 'rb');
        if (!$gz) {
            throw new \Exception("Não foi possível abrir GZ: {$caminhoGz}");
        }

        $conteudo = '';
        while (!gzeof($gz)) {
            $conteudo .= gzread($gz, 4096);
        }
        gzclose($gz);

        // Salvar TXT
        File::put($txtPath, $conteudo);

        return $txtPath;
    }

    private function descompactarZip($caminhoZip)
    {
        $zip = new \ZipArchive;
        
        if ($zip->open($caminhoZip) !== TRUE) {
            throw new \Exception("Não foi possível abrir ZIP: {$caminhoZip}");
        }

        // Extrair em pasta temporária
        $folderName = pathinfo($caminhoZip, PATHINFO_FILENAME);
        $extractPath = storage_path('app/retornos/temp_' . $folderName . '/');

        if (!File::isDirectory($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Buscar arquivo TXT dentro da pasta
        $txtFiles = File::glob($extractPath . '*.txt');

        if (empty($txtFiles)) {
            throw new \Exception("Nenhum TXT encontrado no ZIP: {$caminhoZip}");
        }

        return $txtFiles[0];
    }

    private function descompactarRar($caminhoRar)
    {
        // Verificar se extensão RAR está disponível
        if (!class_exists('RarArchive')) {
            throw new \Exception("Extensão RAR não instalada no PHP");
        }

        $rar = \RarArchive::open($caminhoRar);
        
        if (!$rar) {
            throw new \Exception("Não foi possível abrir RAR: {$caminhoRar}");
        }

        $folderName = pathinfo($caminhoRar, PATHINFO_FILENAME);
        $extractPath = storage_path('app/retornos/temp_' . $folderName . '/');

        if (!File::isDirectory($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $entries = $rar->getEntries();
        
        foreach ($entries as $entry) {
            $entry->extract($extractPath);
        }

        $rar->close();

        // Buscar TXT
        $txtFiles = File::glob($extractPath . '*.txt');

        if (empty($txtFiles)) {
            throw new \Exception("Nenhum TXT encontrado no RAR: {$caminhoRar}");
        }

        return $txtFiles[0];
    }

    private function exibirRelatorio()
    {
        $this->info('📋 RELATÓRIO DE PROCESSAMENTO');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("Total de arquivos: {$this->stats['total']}");
        $this->line("<fg=green>✓ Processados: {$this->stats['processados']}</>");
        $this->line("<fg=yellow>⊘ Já processados: {$this->stats['ja_processados']}</>");
        $this->line("<fg=red>✗ Erros: {$this->stats['erros']}</>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($this->stats['erros'] > 0) {
            $this->newLine();
            $this->warn("⚠️  Verifique os logs para detalhes dos erros:");
            $this->line("   tail -100 storage/logs/laravel.log");
        }
    }
}