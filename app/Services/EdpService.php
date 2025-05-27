<?php

namespace App\Services;

use App\Models\EvidenceReturn;
use App\Models\LogMovement;
use Illuminate\Support\Facades\Http;
use App\Models\LogRegister;
use App\Models\Order;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EdpService
{
    private $baseUrl;
    private $username;
    private $password;
    private $token;

    public function __construct()
    {
        $this->baseUrl = env('EDP_API_URL');
        $this->username = env('EDP_USERNAME');
        $this->password = env('EDP_PASSWORD');
        $this->token = $this->getAccessToken();
    }

    public function getAccessToken()
    {
        $client = new Client();

         try {
            $response = $client->post("{$this->baseUrl}/api/getAccessToken", [
                'json' => [
                    'UserName' => $this->username,
                    'Password' => $this->password,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if ($data['Code'] == 200 && !$data['Error']) {
                return $data['Data']['token'];
            }

            throw new Exception($data['Message'] ?? 'Falha ao obter token de acesso');
        } catch (RequestException $e) {
            Log::error('Erro ao obter token de acesso: ' . $e->getMessage());
            throw new Exception('Falha ao obter token de acesso');
        }
    }

    public function enviarEvidencia($orderId, $dadosEvidencia, array $arquivos)
    {
        $order = Order::find($orderId);
        if (!$order || $order->charge_type !== 'EDP') {
            throw new Exception('Pedido inválido ou não aplicável');
        }

        $evidenceReturn = EvidenceReturn::where('order_id', $orderId)->first();
        if ($evidenceReturn && $evidenceReturn->status === 'APROVADO') {
            throw new Exception('Evidência já aprovada para este pedido');
        }

        $client = new Client();
        $multipartData = [
            [
                'name' => 'token',
                'contents' => $this->token,
            ],
            [
                'name' => 'CodigoProduto',
                'contents' => $dadosEvidencia['CodigoProduto'],
            ],
            [
                'name' => 'CodigoInstalacao',
                'contents' => $dadosEvidencia['CodigoInstalacao'],
            ],
            [
                'name' => 'DataEvidencia',
                'contents' => $dadosEvidencia['DataEvidencia'],
            ],
            [
                'name' => 'NomeTitular',
                'contents' => $dadosEvidencia['NomeTitular'],
            ],
            [
                'name' => 'NomeQuemAprovou',
                'contents' => $dadosEvidencia['NomeQuemAprovou'],
            ],
            [
                'name' => 'TelefoneContato',
                'contents' => $dadosEvidencia['TelefoneContato'],
            ],
        ];

        // Adiciona os arquivos ao array multipart
        $multipartData = array_merge($multipartData, $arquivos);

        try {
            $response = $client->post("{$this->baseUrl}/api/EnviarEvidencia", [
                'multipart' => $multipartData,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao enviar evidência: ' . $e->getMessage());
            throw new Exception('Erro ao enviar evidência');
        }
    }

    public function enviarArquivoProducao($arquivoTxt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->attach('anexo', file_get_contents($arquivoTxt), basename($arquivoTxt))
          ->post("{$this->baseUrl}/api/UploadArquivoParceiro", [
              'login' => $this->username
          ]);
        
        return $response->json();
    }

    public function listarArquivosRetorno($filtros = [])
    {
        $response = Http::asJson()->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post("{$this->baseUrl}/api/listarArquivoRetorno", $filtros);
        return $response->json();
    }

    public function baixarArquivoRetorno($arquivoId)
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
        ]);

        $response = $client->request('GET', '/api/ObterArquivoRetorno', [
            'form_params' => [
                'token' => $this->token,
                'ArquivoId' => $arquivoId,
            ],
        ]);

        $dadosArquivo = $response->getBody()->getContents();
        $contentType = $response->getHeaderLine('Content-Type'); // Obtém o tipo do arquivo

        // Mapeia Content-Type para extensões conhecidas
        $extensoes = [
            'application/pdf' => 'pdf',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
            'application/zip' => 'zip',
            'application/octet-stream' => 'bin', // Caso não seja identificado
        ];

        // Obtém a extensão correta (padrão binário se não for encontrada)
        $extensao = $extensoes[$contentType] ?? 'zip';

        // Criar diretório caso não exista
        $caminhoDiretorio = storage_path("app/retornos/");
        if (!file_exists($caminhoDiretorio)) {
            mkdir($caminhoDiretorio, 0755, true);
        }

        // Define o caminho do arquivo com extensão correta
        $caminhoArquivo = $caminhoDiretorio . $arquivoId . '.' . $extensao;
        file_put_contents($caminhoArquivo, $dadosArquivo);

        return $caminhoArquivo;
    }

    public function extrairArquivoZip($zipPath)
    {
        $zip = new \ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            // Cria um nome de pasta baseado no nome do arquivo ZIP (sem extensão)
            $folderName = pathinfo($zipPath, PATHINFO_FILENAME);
            $extractPath = storage_path('app/retornos/' . $folderName . '/');

            // Garante que a pasta exista
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            $zip->extractTo($extractPath);
            $zip->close();

            // Filtra os arquivos extraídos para encontrar o .txt
            $files = array_diff(scandir($extractPath), ['.', '..']);

            foreach ($files as $file) {
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'txt') {
                    return $extractPath . $file; // Retorna o caminho do TXT extraído
                }
            }
        }

        throw new Exception('Erro ao extrair ZIP ou arquivo TXT não encontrado.');
    }

    public function processarArquivoTxt($txtPath)
    {
        $lines = file($txtPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        Log::info("Processando arquivo: " . $txtPath);
        
        // Extrai a data do nome do arquivo
        $nomeArquivo = basename($txtPath);
        $arquivoData = $this->extrairDataDoArquivo($nomeArquivo);

        $totalLinhas = count($lines);
        $registrosBProcessados = 0;
        $registrosFProcessados = 0;
        $errosB = 0;
        $errosF = 0;

        Log::info("Total de linhas no arquivo: {$totalLinhas}");

        foreach ($lines as $lineNumber => $line) {
            // Pula linhas vazias, mas aceita qualquer tamanho >= 10 (mínimo para ter installation_number)
            if (empty(trim($line)) || strlen($line) < 10) {
                Log::warning("Linha " . ($lineNumber + 1) . " ignorada (muito curta ou vazia): " . strlen($line) . " caracteres");
                continue;
            }

            $tipoRegistro = substr($line, 0, 1);
            
            if ($tipoRegistro === 'B') {
                try {
                    // Usa transação para garantir consistência
                    DB::transaction(function () use ($line, $arquivoData, $lineNumber) {
                        $lineLength = strlen($line);
                        $installationNumber = substr($line, 1, 9);
                        
                        // Verifica se já existe para evitar duplicatas
                        $exists = LogRegister::where('installation_number', $installationNumber)
                                           ->where('arquivo_data', $arquivoData)
                                           ->exists();
                        
                        if (!$exists) {
                            LogRegister::create([
                                'register_code'      => substr($line, 0, 1),
                                'installation_number'=> $installationNumber,
                                'extra_value'        => $this->safeSubstr($line, 10, 2),
                                'product_cod'        => $this->safeSubstr($line, 12, 3),
                                'number_installment' => $this->safeSubstr($line, 15, 2),
                                'value_installment'  => $this->safeSubstr($line, 17, 15),
                                'future1'            => $this->safeSubstr($line, 32, 9),
                                'city_code'          => $this->safeSubstr($line, 41, 3),
                                'start_date'         => $this->formatDate($this->safeSubstr($line, 44, 8)),
                                'end_date'           => $this->formatDate($this->safeSubstr($line, 52, 8)),
                                'address'            => $this->safeSubstr($line, 60, 40),
                                'name'               => $this->safeSubstr($line, 100, 40),
                                'future2'            => $this->safeSubstr($line, 140, 17),
                                'code_anomaly'       => $this->safeSubstr($line, 157, 2),
                                'code_move'          => $this->safeSubstr($line, 159, 1),
                                'arquivo_data'       => $arquivoData,
                            ]);
                            
                            Log::info("LogRegister criado - Linha " . ($lineNumber + 1) . " - Installation: " . $installationNumber . " - Tamanho: " . $lineLength);
                        } else {
                            Log::warning("Registro B duplicado ignorado - Linha " . ($lineNumber + 1) . " - Installation: " . $installationNumber);
                        }
                    });
                    
                    $registrosBProcessados++;
                    
                } catch (Exception $e) {
                    $errosB++;
                    Log::error("ERRO LogRegister - Linha " . ($lineNumber + 1) . ": " . $e->getMessage());
                    Log::error("Dados da linha: " . $line);
                    Log::error("Tamanho da linha: " . strlen($line));
                    
                    // Log detalhado dos campos extraídos
                    Log::error("Campos extraídos:");
                    Log::error("- register_code: '" . substr($line, 0, 1) . "'");
                    Log::error("- installation_number: '" . substr($line, 1, 9) . "'");
                    if (strlen($line) > 10) Log::error("- extra_value: '" . substr($line, 10, 2) . "'");
                    if (strlen($line) > 12) Log::error("- product_cod: '" . substr($line, 12, 3) . "'");
                }
                
            } else if ($tipoRegistro === 'F') {
                try {
                    DB::transaction(function () use ($line, $arquivoData, $lineNumber) {
                        $lineLength = strlen($line);
                        $installationNumber = substr($line, 1, 9);
                        
                        LogMovement::create([
                            'register_code'      => substr($line, 0, 1),
                            'installation_number'=> $installationNumber,
                            'extra_value'        => $this->safeSubstr($line, 10, 2),
                            'product_cod'        => $this->safeSubstr($line, 12, 3),
                            'installment'        => $this->safeSubstr($line, 15, 5),
                            'reading_script'     => $this->safeSubstr($line, 20, 15),
                            'date_invoice'       => $this->safeSubstr($line, 35, 6),
                            'city_code'          => $this->safeSubstr($line, 41, 3),
                            'date_movement'      => $this->safeSubstr($line, 44, 8),
                            'value'              => $this->safeSubstr($line, 52, 15),
                            'code_return'        => $this->safeSubstr($line, 67, 2),
                            'future'             => $this->safeSubstr($line, 69, 90),
                            'code_move'          => $this->safeSubstr($line, 159, 1),
                            'arquivo_data'       => $arquivoData,
                        ]);
                        
                        Log::info("LogMovement criado - Linha " . ($lineNumber + 1) . " - Installation: " . $installationNumber . " - Tamanho: " . $lineLength);
                    });
                    
                    $registrosFProcessados++;
                    
                } catch (Exception $e) {
                    $errosF++;
                    Log::error("ERRO LogMovement - Linha " . ($lineNumber + 1) . ": " . $e->getMessage());
                    Log::error("Dados da linha: " . $line);
                    Log::error("Tamanho da linha: " . strlen($line));
                }
            } else {
                Log::warning("Tipo de registro desconhecido na linha " . ($lineNumber + 1) . ": '" . $tipoRegistro . "'");
            }
        }

        // Log do resumo final
        Log::info("=== RESUMO DO PROCESSAMENTO ===");
        Log::info("Total de linhas: {$totalLinhas}");
        Log::info("Registros B processados: {$registrosBProcessados}");
        Log::info("Registros F processados: {$registrosFProcessados}");
        Log::info("Erros em registros B: {$errosB}");
        Log::info("Erros em registros F: {$errosF}");
        Log::info("================================");
    }

    /**
     * Extrai substring de forma segura, retornando null se a posição não existir
     */
    private function safeSubstr($string, $start, $length)
    {
        if (strlen($string) <= $start) {
            return null;
        }
        
        $extracted = substr($string, $start, $length);
        return $this->nullIfEmpty($extracted);
    }

    private function nullIfEmpty($string)
    {
        $trimmed = trim($string);
        return empty($trimmed) ? null : $trimmed;
    }

    private function extrairDataDoArquivo($nomeArquivo)
    {
        if (preg_match('/BEN_(\d{8})_/', $nomeArquivo, $matches)) {
            $dataString = $matches[1];
            return $this->formatDate($dataString);
        }
        
        return null;
    }

    public function analisarEstruturaArquivo($txtPath)
    {
        $lines = file($txtPath, FILE_IGNORE_NEW_LINES);
        $totalLinhas = count($lines);
        $tiposEncontrados = [];
        
        Log::info("=== ANÁLISE ESTRUTURAL DO ARQUIVO ===");
        Log::info("Arquivo: " . $txtPath);
        Log::info("Total de linhas: " . $totalLinhas);
        
        foreach ($lines as $index => $line) {
            if (empty(trim($line))) {
                Log::warning("Linha " . ($index + 1) . " está vazia");
                continue;
            }
            
            $tipoRegistro = substr($line, 0, 1);
            $tamanhoLinha = strlen($line);
            
            if (!isset($tiposEncontrados[$tipoRegistro])) {
                $tiposEncontrados[$tipoRegistro] = 0;
            }
            $tiposEncontrados[$tipoRegistro]++;
            
            // Analisa apenas algumas linhas de cada tipo
            if ($tiposEncontrados[$tipoRegistro] <= 3) {
                Log::info("=== ANÁLISE REGISTRO {$tipoRegistro} (Linha " . ($index + 1) . ") ===");
                Log::info("Tamanho: " . $tamanhoLinha);
                
                if ($tamanhoLinha < 160) {
                    Log::warning("ATENÇÃO: Linha muito curta! Esperado >= 160 caracteres");
                }
                
                // Quebra a linha em blocos de 10 caracteres para análise
                $chunks = str_split($line, 10);
                foreach ($chunks as $i => $chunk) {
                    $start = $i * 10;
                    $end = $start + strlen($chunk) - 1;
                    Log::info("Posições {$start}-{$end}: '{$chunk}'");
                }
                Log::info("=== FIM ANÁLISE REGISTRO {$tipoRegistro} ===");
            }
        }
        
        Log::info("=== RESUMO TIPOS DE REGISTRO ===");
        foreach ($tiposEncontrados as $tipo => $quantidade) {
            Log::info("Tipo '{$tipo}': {$quantidade} registros");
        }
        Log::info("=== FIM ANÁLISE ESTRUTURAL ===");
    }
    
    private function formatDate($date)
    {
        $trimmed = trim($date);
        if (empty($trimmed) || $trimmed == '00000000' || strlen($trimmed) != 8) {
            return null;
        }

        return substr($trimmed, 0, 4) . '-' . substr($trimmed, 4, 2) . '-' . substr($trimmed, 6, 2);
    }

    public function processarArquivosEmMassa()
    {
        // 1. Listar os arquivos de retorno disponíveis
        $arquivosResponse = $this->listarArquivosRetorno();
       
        if (!isset($arquivosResponse['Data']) || !is_array($arquivosResponse['Data'])) {
            throw new Exception('Resposta inesperada da API: ' . json_encode($arquivosResponse));
        }

        $arquivos = $arquivosResponse['Data'] ?? [];

        if (empty($arquivos)) {
            throw new Exception('Nenhum arquivo de retorno disponível.');
        }

        // 2. Ordenar os arquivos do mais antigo para o mais novo
        usort($arquivos, function ($a, $b) {
            return strtotime($a['DataInclusao']) - strtotime($b['DataInclusao']);
        });

        $arquivosProcessados = 0; // Contador de arquivos processados

        // 3. Processar apenas o arquivo mais antigo que ainda não foi armazenado
        foreach ($arquivos as $arquivo) {
            $arquivoId = $arquivo['Id'];
            $nomeArquivo = $arquivo['Arquivo'];

            // Verifica se já processamos esse arquivo
            if (\App\Models\RetornoArmazenado::where('arquivo_id', $arquivoId)->exists()) {
                Log::info("Arquivo {$nomeArquivo} (ID: {$arquivoId}) já foi armazenado. Pulando...");
                continue;
            }

            try {
                Log::info("Iniciando processamento do arquivo: {$nomeArquivo} (ID: {$arquivoId})");
                
                // 4. Baixar o arquivo ZIP
                $zipPath = $this->baixarArquivoRetorno($arquivoId);

                if (!file_exists($zipPath)) {
                    throw new Exception("Erro: O arquivo ZIP não foi encontrado para o ID {$arquivoId}.");
                }

                // 5. Extrair o arquivo ZIP
                $txtPath = $this->extrairArquivoZip($zipPath);

                if (!file_exists($txtPath)) {
                    throw new Exception("Erro: O arquivo TXT não foi extraído corretamente para o ID {$arquivoId}.");
                }

                // NOVO: Analisar estrutura antes de processar
                $this->analisarEstruturaArquivo($txtPath);

                // 6. Processar o arquivo TXT
                $this->processarArquivoTxt($txtPath);

                // 7. Armazenar o arquivo na tabela retornos_armazenados
                \App\Models\RetornoArmazenado::create([
                    'arquivo_id' => $arquivoId,
                    'nome_arquivo' => $nomeArquivo,
                    'baixado_em' => now(),
                ]);

                Log::info("Arquivo de retorno ID {$arquivoId} processado e armazenado com sucesso!");
                $arquivosProcessados++;

            } catch (Exception $e) {
                Log::error("Erro ao processar o arquivo ID {$arquivoId}: " . $e->getMessage());
                Log::error("Stack trace: " . $e->getTraceAsString());
            }
        }

        return $arquivosProcessados > 0 
            ? "Processados {$arquivosProcessados} arquivo(s) com sucesso!" 
            : "Nenhum novo arquivo para processar!";
    }
}