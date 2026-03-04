<?php

namespace App\Console\Commands;

use App\Models\LogMovement;
use App\Models\LogRegister;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class AuditEdpReturnFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'edp:audit-return-files
        {--path=retornos : Caminho relativo dentro de storage/app}
        {--only-missing : Exibe apenas os registros ausentes}
        {--limit-files= : Limita a quantidade de arquivos processados}
        {--stop-on-error : Interrompe no primeiro erro grave}';

    /**
     * The console command description.
     */
    protected $description = 'Audita todos os arquivos de retorno EDP da pasta storage/app/retornos e verifica se todos os registros B/F existem no banco';

    /**
     * Layout do Registro Tipo B (espelhando o EdpParserService atual)
     */
    private array $layoutRegistroB = [
        'register_code'       => ['pos' => 0,   'len' => 1],
        'installation_number' => ['pos' => 1,   'len' => 9],
        'extra_value'         => ['pos' => 10,  'len' => 2],
        'product_cod'         => ['pos' => 12,  'len' => 3],
        'number_installment'  => ['pos' => 15,  'len' => 2],
        'value_installment'   => ['pos' => 17,  'len' => 15],
        'future1'             => ['pos' => 32,  'len' => 9],
        'city_code'           => ['pos' => 41,  'len' => 3],
        'start_date'          => ['pos' => 44,  'len' => 8],
        'end_date'            => ['pos' => 52,  'len' => 8],
        'address'             => ['pos' => 60,  'len' => 50],
        'name'                => ['pos' => 110, 'len' => 40],
        'future2'             => ['pos' => 150, 'len' => 7],
        'code_anomaly'        => ['pos' => 157, 'len' => 2],
        'code_move'           => ['pos' => 159, 'len' => 1],
    ];

    /**
     * Layout do Registro Tipo F (espelhando o EdpParserService atual)
     */
    private array $layoutRegistroF = [
        'register_code'       => ['pos' => 0,   'len' => 1],
        'installation_number' => ['pos' => 1,   'len' => 9],
        'extra_value'         => ['pos' => 10,  'len' => 2],
        'product_cod'         => ['pos' => 12,  'len' => 3],
        'installment'         => ['pos' => 15,  'len' => 5],
        'reading_script'      => ['pos' => 20,  'len' => 15],
        'date_invoice'        => ['pos' => 35,  'len' => 6],
        'city_code'           => ['pos' => 41,  'len' => 3],
        'date_movement'       => ['pos' => 44,  'len' => 8],
        'value'               => ['pos' => 52,  'len' => 15],
        'code_return'         => ['pos' => 67,  'len' => 2],
        'future'              => ['pos' => 69,  'len' => 90],
        'code_move'           => ['pos' => 159, 'len' => 1],
    ];

    private string $auditLogPath;
    private string $auditCsvPath;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $relativePath = trim((string) $this->option('path'));
        $fullPath = storage_path('app/' . $relativePath);
        $limitFiles = $this->option('limit-files') !== null ? (int) $this->option('limit-files') : null;
        $stopOnError = (bool) $this->option('stop-on-error');
        $onlyMissing = (bool) $this->option('only-missing');

        if (!is_dir($fullPath)) {
            $this->error("Diretório não encontrado: {$fullPath}");
            return self::FAILURE;
        }

        $timestamp = now()->format('Ymd_His');
        $this->auditLogPath = storage_path("logs/edp_return_audit_{$timestamp}.log");
        $this->auditCsvPath = storage_path("app/edp_return_audit_{$timestamp}.csv");

        $this->initializeCsv();

        $stats = [
            'files_processed' => 0,
            'lines_processed' => 0,
            'records_b_found' => 0,
            'records_b_missing' => 0,
            'records_f_found' => 0,
            'records_f_missing' => 0,
            'errors' => 0,
        ];

        $files = $this->listAllFiles($fullPath);

        if ($limitFiles !== null) {
            $files = array_slice($files, 0, $limitFiles);
        }

        if (empty($files)) {
            $this->warn("Nenhum arquivo encontrado em {$fullPath}");
            return self::SUCCESS;
        }

        foreach ($files as $filePath) {
            try {
                $stats['files_processed']++;

                $this->line("Processando: {$filePath}");

                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $filename = basename($filePath);

                if ($extension === 'zip') {
                    $this->processZipFile($filePath, $filename, $stats, $onlyMissing);
                    continue;
                }

                if ($extension === 'gz') {
                    $this->processGzFile($filePath, $filename, $stats, $onlyMissing);
                    continue;
                }

                if ($extension === 'txt') {
                    $this->processTxtFile($filePath, $filename, $stats, $onlyMissing);
                    continue;
                }

                $this->writeAuditLog("IGNORADO | Arquivo com extensão não suportada: {$filePath}");
            } catch (\Throwable $throwable) {
                $stats['errors']++;

                $this->writeAuditLog("ERRO | Arquivo: {$filePath} | Mensagem: {$throwable->getMessage()}");
                $this->error("Erro ao processar {$filePath}: {$throwable->getMessage()}");

                if ($stopOnError) {
                    break;
                }
            }
        }

        $this->newLine();
        $this->info('Auditoria finalizada.');
        $this->line("Arquivos processados: {$stats['files_processed']}");
        $this->line("Linhas processadas: {$stats['lines_processed']}");
        $this->line("Registros B encontrados: {$stats['records_b_found']}");
        $this->line("Registros B ausentes: {$stats['records_b_missing']}");
        $this->line("Registros F encontrados: {$stats['records_f_found']}");
        $this->line("Registros F ausentes: {$stats['records_f_missing']}");
        $this->line("Erros: {$stats['errors']}");
        $this->line("Log da auditoria: {$this->auditLogPath}");
        $this->line("CSV da auditoria: {$this->auditCsvPath}");

        return self::SUCCESS;
    }

    private function listAllFiles(string $directory): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function processZipFile(string $filePath, string $displayName, array &$stats, bool $onlyMissing): void
    {
        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Não foi possível abrir o ZIP: {$filePath}");
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            if (!$entryName || str_ends_with($entryName, '/')) {
                continue;
            }

            $entryExtension = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));

            if ($entryExtension !== 'txt') {
                continue;
            }

            $content = $zip->getFromIndex($i);

            if ($content === false) {
                $this->writeAuditLog("ERRO | Não foi possível ler entrada do ZIP: {$displayName} :: {$entryName}");
                continue;
            }

            $arquivoData = $this->extractArquivoDataFromName($entryName);
            $lines = preg_split("/\r\n|\n|\r/", $content);

            $this->processLines($lines, "{$displayName}::{$entryName}", $arquivoData, $stats, $onlyMissing);
        }

        $zip->close();
    }

    private function processGzFile(string $filePath, string $displayName, array &$stats, bool $onlyMissing): void
    {
        $handle = gzopen($filePath, 'rb');

        if (!$handle) {
            throw new \RuntimeException("Não foi possível abrir o arquivo GZ: {$filePath}");
        }

        $lines = [];

        while (!gzeof($handle)) {
            $lines[] = gzgets($handle);
        }

        gzclose($handle);

        $arquivoData = $this->extractArquivoDataFromName($displayName);

        $this->processLines($lines, $displayName, $arquivoData, $stats, $onlyMissing);
    }

    private function processTxtFile(string $filePath, string $displayName, array &$stats, bool $onlyMissing): void
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new \RuntimeException("Não foi possível ler o arquivo TXT: {$filePath}");
        }

        $lines = preg_split("/\r\n|\n|\r/", $content);
        $arquivoData = $this->extractArquivoDataFromName($displayName);

        $this->processLines($lines, $displayName, $arquivoData, $stats, $onlyMissing);
    }

    private function processLines(array $lines, string $sourceName, ?string $arquivoData, array &$stats, bool $onlyMissing): void
    {
        foreach ($lines as $lineNumber => $line) {
            $line = rtrim((string) $line, "\r\n");
            $line = rtrim($line);

            if ($line === '') {
                continue;
            }

            $stats['lines_processed']++;

            $tipoRegistro = substr($line, 0, 1);

            if (in_array($tipoRegistro, ['A', 'Z'], true)) {
                continue;
            }

            if ($tipoRegistro === 'B') {
                $this->auditRegistroB($line, $sourceName, $lineNumber + 1, $arquivoData, $stats, $onlyMissing);
                continue;
            }

            if ($tipoRegistro === 'F') {
                $this->auditRegistroF($line, $sourceName, $lineNumber + 1, $arquivoData, $stats, $onlyMissing);
                continue;
            }

            $this->writeAuditLog("TIPO_DESCONHECIDO | Fonte: {$sourceName} | Linha: " . ($lineNumber + 1) . " | Tipo: {$tipoRegistro}");
        }
    }

    private function auditRegistroB(string $linha, string $sourceName, int $lineNumber, ?string $arquivoData, array &$stats, bool $onlyMissing): void
    {
        $dados = $this->extrairCampos($linha, $this->layoutRegistroB);

        if (empty($dados['installation_number'])) {
            $stats['errors']++;
            $this->writeAuditLog("ERRO_B | Fonte: {$sourceName} | Linha: {$lineNumber} | Instalação vazia");
            return;
        }

        $dados['start_date'] = $this->converterData($dados['start_date']);
        $dados['end_date'] = $this->converterData($dados['end_date']);
        $dados['arquivo_data'] = $arquivoData;

        foreach ($dados as $key => $value) {
            if (is_string($value)) {
                $dados[$key] = trim($value);
            }
        }

        $exists = LogRegister::query()
            ->where('register_code', $dados['register_code'])
            ->where('installation_number', $dados['installation_number'])
            ->where(function ($q) use ($dados) {
                $this->whereNullable($q, 'extra_value', $dados['extra_value']);
                $this->whereNullable($q, 'product_cod', $dados['product_cod']);
                $this->whereNullable($q, 'number_installment', $dados['number_installment']);
                $this->whereNullable($q, 'value_installment', $dados['value_installment']);
                $this->whereNullable($q, 'future1', $dados['future1']);
                $this->whereNullable($q, 'city_code', $dados['city_code']);
                $this->whereNullable($q, 'start_date', $dados['start_date']);
                $this->whereNullable($q, 'end_date', $dados['end_date']);
                $this->whereNullable($q, 'address', $dados['address']);
                $this->whereNullable($q, 'name', $dados['name']);
                $this->whereNullable($q, 'future2', $dados['future2']);
                $this->whereNullable($q, 'code_anomaly', $dados['code_anomaly']);
                $this->whereNullable($q, 'code_move', $dados['code_move']);
            })
            ->exists();

        if ($exists) {
            $stats['records_b_found']++;

            if (!$onlyMissing) {
                $this->writeAuditLog("B_OK | Fonte: {$sourceName} | Linha: {$lineNumber} | Instalação: {$dados['installation_number']}");
            }

            return;
        }

        $stats['records_b_missing']++;

        $message = "B_MISSING | Fonte: {$sourceName} | Linha: {$lineNumber} | Instalação: {$dados['installation_number']} | Dados: " . json_encode($dados, JSON_UNESCAPED_UNICODE);
        $this->writeAuditLog($message);
        $this->appendCsv('B', $sourceName, $lineNumber, $dados['installation_number'], $linha, $dados);
    }

    private function auditRegistroF(string $linha, string $sourceName, int $lineNumber, ?string $arquivoData, array &$stats, bool $onlyMissing): void
    {
        $dados = $this->extrairCampos($linha, $this->layoutRegistroF);

        if (empty($dados['installation_number'])) {
            $stats['errors']++;
            $this->writeAuditLog("ERRO_F | Fonte: {$sourceName} | Linha: {$lineNumber} | Instalação vazia");
            return;
        }

        $dados['arquivo_data'] = $arquivoData;

        if (empty($dados['code_return'])) {
            $dados['code_return'] = '01';
        }

        foreach ($dados as $key => $value) {
            if (is_string($value)) {
                $dados[$key] = trim($value);
            }
        }

        $exists = LogMovement::query()
            ->where('register_code', $dados['register_code'])
            ->where('installation_number', $dados['installation_number'])
            ->where(function ($q) use ($dados) {
                $this->whereNullable($q, 'extra_value', $dados['extra_value']);
                $this->whereNullable($q, 'product_cod', $dados['product_cod']);
                $this->whereNullable($q, 'installment', $dados['installment']);
                $this->whereNullable($q, 'reading_script', $dados['reading_script']);
                $this->whereNullable($q, 'date_invoice', $dados['date_invoice']);
                $this->whereNullable($q, 'city_code', $dados['city_code']);
                $this->whereNullable($q, 'date_movement', $dados['date_movement']);
                $this->whereNullable($q, 'value', $dados['value']);
                $this->whereNullable($q, 'code_return', $dados['code_return']);
                $this->whereNullable($q, 'future', $dados['future']);
                $this->whereNullable($q, 'code_move', $dados['code_move']);
            })
            ->exists();

        if ($exists) {
            $stats['records_f_found']++;

            if (!$onlyMissing) {
                $this->writeAuditLog("F_OK | Fonte: {$sourceName} | Linha: {$lineNumber} | Instalação: {$dados['installation_number']}");
            }

            return;
        }

        $stats['records_f_missing']++;

        $message = "F_MISSING | Fonte: {$sourceName} | Linha: {$lineNumber} | Instalação: {$dados['installation_number']} | Dados: " . json_encode($dados, JSON_UNESCAPED_UNICODE);
        $this->writeAuditLog($message);
        $this->appendCsv('F', $sourceName, $lineNumber, $dados['installation_number'], $linha, $dados);
    }

    private function whereNullable($query, string $column, mixed $value): void
    {
        if ($value === null || $value === '') {
            $query->whereNull($column);
        } else {
            $query->where($column, $value);
        }
    }

    private function extrairCampos(string $linha, array $layout): array
    {
        $dados = [];

        foreach ($layout as $campo => $config) {
            $pos = $config['pos'];
            $len = $config['len'];

            $valor = substr($linha, $pos, $len);
            $valor = trim($valor);
            $valor = $valor === '' ? null : $this->removerAcentos($valor);

            $dados[$campo] = $valor === '' ? null : $valor;
        }

        return $dados;
    }

    /**
     * Espelha o comportamento atual do EdpParserService.
     */
    private function converterData(?string $dataStr): ?string
    {
        if (empty($dataStr) || strlen($dataStr) !== 8) {
            return null;
        }

        try {
            $dia = substr($dataStr, 0, 2);
            $mes = substr($dataStr, 2, 2);
            $ano = substr($dataStr, 4, 4);

            return Carbon::createFromFormat('d/m/Y', "{$dia}/{$mes}/{$ano}")->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function removerAcentos(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }

        $string = iconv('ISO-8859-1', 'ASCII//TRANSLIT//IGNORE', $string);

        return preg_replace('/[^\x20-\x7E]/', '', $string);
    }

    private function extractArquivoDataFromName(string $filename): ?string
    {
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $filename, $matches)) {
            return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
        }

        if (preg_match('/(\d{8})/', $filename, $matches)) {
            $raw = $matches[1];

            try {
                return Carbon::createFromFormat('Ymd', $raw)->format('Y-m-d');
            } catch (\Throwable $throwable) {
                return null;
            }
        }

        return null;
    }

    private function writeAuditLog(string $message): void
    {
        file_put_contents($this->auditLogPath, '[' . now()->format('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
        Log::channel(config('logging.default'))->info($message);
    }

    private function initializeCsv(): void
    {
        $dir = dirname($this->auditCsvPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $handle = fopen($this->auditCsvPath, 'w');

        fputcsv($handle, [
            'record_type',
            'source_name',
            'line_number',
            'installation_number',
            'raw_line',
            'parsed_data_json',
        ], ';');

        fclose($handle);
    }

    private function appendCsv(string $recordType, string $sourceName, int $lineNumber, ?string $installationNumber, string $rawLine, array $dados): void
    {
        $handle = fopen($this->auditCsvPath, 'a');

        fputcsv($handle, [
            $recordType,
            $sourceName,
            $lineNumber,
            $installationNumber,
            $rawLine,
            json_encode($dados, JSON_UNESCAPED_UNICODE),
        ], ';');

        fclose($handle);
    }
}