<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportEdpProductionFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'edp:import-production-files';

    /**
     * The console command description.
     */
    protected $description = 'Importa todos os arquivos TXT da pasta storage/app/producao-edp para as tabelas edp_production_files e edp_production_records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk('local');
        $directory = 'producao-edp';

        if (!$disk->exists($directory)) {
            $this->error("A pasta {$directory} não existe em storage/app.");
            return self::FAILURE;
        }

        $files = $disk->files($directory);

        if (empty($files)) {
            $this->warn("Nenhum arquivo encontrado em storage/app/{$directory}.");
            return self::SUCCESS;
        }

        $processedFiles = 0;
        $skippedFiles = 0;
        $errorFiles = 0;

        foreach ($files as $filePath) {
            $fileName = basename($filePath);

            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'txt') {
                $this->line("Ignorando arquivo não TXT: {$fileName}");
                continue;
            }

            $alreadyProcessed = DB::table('edp_production_files')
                ->where('file_name', $fileName)
                ->exists();

            if ($alreadyProcessed) {
                $this->line("Arquivo já processado, ignorando: {$fileName}");
                $skippedFiles++;
                continue;
            }

            $this->info("Processando arquivo: {$fileName}");

            try {
                $absolutePath = storage_path('app/' . $filePath);
                $fileContent = $disk->get($filePath);

                $lines = preg_split("/\r\n|\n|\r/", $fileContent);

                $fileSize = is_file($absolutePath) ? filesize($absolutePath) : null;
                $fileHash = is_file($absolutePath) ? hash_file('sha256', $absolutePath) : null;

                DB::beginTransaction();

                $fileId = DB::table('edp_production_files')->insertGetId([
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'file_hash' => $fileHash,
                    'total_lines' => 0,
                    'total_b_records' => 0,
                    'processed_at' => null,
                    'error_message' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalLines = 0;
                $totalBRecords = 0;

                foreach ($lines as $index => $line) {
                    $lineNumber = $index + 1;
                    $line = rtrim($line, "\r\n");

                    if ($line === '') {
                        continue;
                    }

                    $totalLines++;

                    $registerCode = substr($line, 0, 1);

                    if ($registerCode !== 'B') {
                        continue;
                    }

                    $parsed = $this->parseRegistroB($line);

                    DB::table('edp_production_records')->insert([
                        'edp_production_file_id' => $fileId,
                        'line_number' => $lineNumber,
                        'register_code' => $parsed['register_code'],
                        'installation_number' => $parsed['installation_number'],
                        'extra_value_code' => $parsed['extra_value_code'],
                        'product_code' => $parsed['product_code'],
                        'number_installments' => $parsed['number_installments'],
                        'installment_value' => $parsed['installment_value'],
                        'future_field_1' => $parsed['future_field_1'],
                        'start_date' => $parsed['start_date'],
                        'end_date' => $parsed['end_date'],
                        'address' => $parsed['address'],
                        'future_field_2' => $parsed['future_field_2'],
                        'billing_status_code' => $parsed['billing_status_code'],
                        'movement_code' => $parsed['movement_code'],
                        'raw_line' => $line,
                        'financial_created_at' => null,
                        'financial_error' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalBRecords++;
                }

                DB::table('edp_production_files')
                    ->where('id', $fileId)
                    ->update([
                        'total_lines' => $totalLines,
                        'total_b_records' => $totalBRecords,
                        'processed_at' => now(),
                        'updated_at' => now(),
                    ]);

                DB::commit();

                $this->info("Arquivo processado com sucesso: {$fileName} | Linhas: {$totalLines} | Registros B: {$totalBRecords}");
                $processedFiles++;
            } catch (\Throwable $throwable) {
                DB::rollBack();

                DB::table('edp_production_files')->updateOrInsert(
                    ['file_name' => $fileName],
                    [
                        'file_path' => $filePath,
                        'file_size' => is_file(storage_path('app/' . $filePath)) ? filesize(storage_path('app/' . $filePath)) : null,
                        'file_hash' => is_file(storage_path('app/' . $filePath)) ? hash_file('sha256', storage_path('app/' . $filePath)) : null,
                        'total_lines' => 0,
                        'total_b_records' => 0,
                        'processed_at' => null,
                        'error_message' => $throwable->getMessage(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $this->error("Erro ao processar arquivo {$fileName}: {$throwable->getMessage()}");
                $errorFiles++;
            }
        }

        $this->newLine();
        $this->info("Importação finalizada.");
        $this->line("Arquivos processados: {$processedFiles}");
        $this->line("Arquivos ignorados: {$skippedFiles}");
        $this->line("Arquivos com erro: {$errorFiles}");

        return self::SUCCESS;
    }

    /**
     * Faz o parse completo do registro tipo B do arquivo de produção da EDP.
     *
     * Estrutura:
     * B01 - De 1 até 1   - Código do registro
     * B02 - De 2 até 10  - Instalação do cliente
     * B03 - De 11 até 12 - Código val. extra
     * B04 - De 13 até 15 - Código do produto
     * B05 - De 16 até 17 - Número de parcelas
     * B06 - De 18 a 32   - Valor da parcela a ser cobrado
     * B07 - De 33 a 44   - Reservado para o futuro
     * B08 - De 45 a 52   - Data inicial (AAAAMMDD)
     * B09 - De 53 a 60   - Data final (AAAAMMDD)
     * B10 - De 61 a 100  - Endereço do cliente
     * B11 - De 101 a 147 - Reservado para o futuro
     * B12 - De 148 a 149 - Código de situação cobrança
     * B13 - De 150 a 150 - Código do movimento
     */
    private function parseRegistroB(string $line): array
    {
        $registerCode = trim(substr($line, 0, 1));
        $installationNumber = trim(substr($line, 1, 9));
        $extraValueCode = trim(substr($line, 10, 2));
        $productCode = trim(substr($line, 12, 3));
        $numberInstallmentsRaw = trim(substr($line, 15, 2));
        $installmentValueRaw = trim(substr($line, 17, 15));
        $futureField1 = rtrim(substr($line, 32, 12));
        $startDateRaw = trim(substr($line, 44, 8));
        $endDateRaw = trim(substr($line, 52, 8));
        $address = rtrim(substr($line, 60, 40));
        $futureField2 = rtrim(substr($line, 100, 47));
        $billingStatusCode = trim(substr($line, 147, 2));
        $movementCode = trim(substr($line, 149, 1));

        return [
            'register_code' => $registerCode !== '' ? $registerCode : null,
            'installation_number' => $installationNumber !== '' ? $installationNumber : null,
            'extra_value_code' => $extraValueCode !== '' ? $extraValueCode : null,
            'product_code' => $productCode !== '' ? $productCode : null,
            'number_installments' => $this->parseIntegerField($numberInstallmentsRaw),
            'installment_value' => $this->parseMoneyField($installmentValueRaw),
            'future_field_1' => $futureField1 !== '' ? $futureField1 : null,
            'start_date' => $this->parseDateField($startDateRaw),
            'end_date' => $this->parseDateField($endDateRaw),
            'address' => $address !== '' ? $address : null,
            'future_field_2' => $futureField2 !== '' ? $futureField2 : null,
            'billing_status_code' => $billingStatusCode !== '' ? $billingStatusCode : null,
            'movement_code' => $movementCode !== '' ? $movementCode : null,
        ];
    }

    /**
     * Converte um campo numérico inteiro.
     * Se vier vazio ou inválido retorna null.
     */
    private function parseIntegerField(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (!ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Converte um campo monetário numérico sem separador decimal para decimal(10,2).
     * Exemplo:
     * 0000000000002990 => 29.90
     * 0000000000015000 => 150.00
     * 0000000000000000 => 0.00
     */
    private function parseMoneyField(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (!preg_match('/^\d+$/', $value)) {
            return null;
        }

        return ((int) $value) / 100;
    }

    /**
     * Converte AAAAMMDD para Y-m-d.
     *
     * Regras:
     * - null, vazio ou não numérico => null
     * - 00000000 => null
     * - mês 00 => null
     * - dia 00 => null
     * - datas inválidas => null
     */
    private function parseDateField(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (strlen($value) !== 8) {
            return null;
        }

        if (!ctype_digit($value)) {
            return null;
        }

        if ($value === '00000000') {
            return null;
        }

        $year = (int) substr($value, 0, 4);
        $month = (int) substr($value, 4, 2);
        $day = (int) substr($value, 6, 2);

        if ($year <= 0) {
            return null;
        }

        if ($month <= 0 || $month > 12) {
            return null;
        }

        if ($day <= 0 || $day > 31) {
            return null;
        }

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Ymd', $value)->format('Y-m-d');
        } catch (\Throwable $throwable) {
            return null;
        }
    }
}