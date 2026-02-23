<?php

namespace App\Services;

use App\Models\EvidenceReturn;
use App\Models\LogMovement;
use Illuminate\Support\Facades\Http;
use App\Models\LogRegister;
use App\Models\Order;
use App\Models\RetornoArmazenado;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Services\EdpParserService;
use Illuminate\Support\Facades\Cache;

class EdpService
{
    public $baseUrl;
    public $username;
    public $password;
    public $token;

    public function __construct()
    {
        $this->baseUrl = config('services.edp.base_url');
        $this->username = config('services.edp.username');
        $this->password = config('services.edp.password');
        
        // ✅ Usar cache ao invés de arquivo JSON
        $this->token = $this->getOrRefreshToken();
    }

    public function getOrRefreshToken()
    {
        // Verificar se existe token válido no cache
        $cachedToken = Cache::get('edp_access_token');
        
        if ($cachedToken) {
            return $cachedToken;
        }
        
        // Gerar novo token
        return $this->generateNewToken();
    }

    private function generateNewToken()
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
                $token = $data['Data']['token'];
                
                // ✅ Armazenar no cache por 23 horas (token vale 24h)
                Cache::put('edp_access_token', $token, now()->addHours(23));
                
                Log::info('Novo token EDP gerado');
                
                return $token;
            }

            throw new Exception($data['Message'] ?? 'Falha ao obter token de acesso');
            
        } catch (RequestException $e) {
            Log::error('Erro ao obter token de acesso: ' . $e->getMessage());
            throw new Exception('Falha ao obter token de acesso');
        }
    }

    // ✅ Método para forçar refresh (útil se o token expirar antes)
    public function refreshToken()
    {
        Cache::forget('edp_access_token');
        $this->token = $this->generateNewToken();
        return $this->token;
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

    public function baixarArquivoRetorno($arquivoId, $nomeArquivo = null)
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
        $contentType = $response->getHeaderLine('Content-Type');

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

        $nomeBase = $nomeArquivo ?? $arquivoId;
        $nomeBase = pathinfo($nomeBase, PATHINFO_FILENAME);
        
        $caminhoDiretorio = storage_path("app/retornos/");
        if (!file_exists($caminhoDiretorio)) {
            mkdir($caminhoDiretorio, 0755, true);
        }

        // ✅ Segundo a documentação: sempre retorna ZIP
        $caminhoArquivo = $caminhoDiretorio . $nomeBase . '.' . $extensao;
        file_put_contents($caminhoArquivo, $dadosArquivo);

        Log::info("Arquivo baixado: {$caminhoArquivo} (" . strlen($dadosArquivo) . " bytes)");

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
        $parser = new EdpParserService();
        
        // Extrair data do nome do arquivo se possível
        $arquivoData = $this->extrairDataDoArquivo($txtPath);

        $handle = fopen($txtPath, 'r');
        
        if (!$handle) {
            throw new \Exception("Não foi possível abrir o arquivo: {$txtPath}");
        }

        $linhaNumero = 0;

        while (($linha = fgets($handle)) !== false) {
            $linhaNumero++;
            
            try {
                $parser->processarLinha($linha, $arquivoData);
            } catch (\Exception $e) {
                Log::error("Erro na linha {$linhaNumero}: " . $e->getMessage());
            }
        }

        fclose($handle);
        
        Log::info("Arquivo processado: {$linhaNumero} linhas lidas");
    }

    private function sanitizeString($string) {
        if ($string === null || empty($string)) {
            return null;
        }
        
        $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        $string = preg_replace('/[\x00-\x1F\x7F]/', '', $string);
        $string = trim($string);
        
        return empty($string) ? null : $string;
    }

    private function nullIfEmpty($string) {
        $trimmed = trim($string);
        return empty($trimmed) ? null : $trimmed;
    }

    private function extrairDataDoArquivo($filename)
    {
        if (preg_match('/(\d{8})/', basename($filename), $matches)) {
            $dateStr = $matches[1];
            return Carbon::createFromFormat('Ymd', $dateStr)->format('Y-m-d');
        }
        
        return now()->format('Y-m-d');
    }

    public function analisarEstruturaArquivo($txtPath)
    {
        $lines = file($txtPath, FILE_IGNORE_NEW_LINES);
        
        foreach ($lines as $index => $line) {
            $tipoRegistro = substr($line, 0, 1);
            
            if ($tipoRegistro === 'B') {
                Log::info("=== ANÁLISE REGISTRO B (Linha " . ($index + 1) . ") ===");
                Log::info("Linha completa: " . $line);
                Log::info("Tamanho: " . strlen($line));
                
                // Quebra a linha em blocos de 10 caracteres para análise
                $chunks = str_split($line, 10);
                foreach ($chunks as $i => $chunk) {
                    $start = $i * 10;
                    $end = $start + strlen($chunk) - 1;
                    Log::info("Posições {$start}-{$end}: '{$chunk}'");
                }
                Log::info("=== FIM ANÁLISE ===");
            }
        }
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
            if (RetornoArmazenado::where('arquivo_id', $arquivoId)->exists()) {
                Log::info("Arquivo {$nomeArquivo} (ID: {$arquivoId}) já foi armazenado. Pulando...");
                continue;
            }

            try {
                $zipPath = $this->baixarArquivoRetorno($arquivoId, $nomeArquivo);

                if (!file_exists($zipPath)) {
                    throw new Exception("Erro: O arquivo não foi encontrado para o ID {$arquivoId}.");
                }

                 if (pathinfo($zipPath, PATHINFO_EXTENSION) === 'txt') {
                    $txtPath = $zipPath; // Já é TXT
                } else {
                    // É ZIP, precisa extrair
                    $txtPath = $this->extrairArquivoZip($zipPath);
                }

                $this->processarArquivoTxt($txtPath);

                RetornoArmazenado::create([
                    'arquivo_id' => $arquivoId,
                    'nome_arquivo' => $nomeArquivo,
                    'baixado_em' => now(),
                ]);

                Log::info("Arquivo de retorno ID {$arquivoId} processado e armazenado com sucesso!");
                $arquivosProcessados++;

            } catch (Exception $e) {
                Log::error("Erro ao processar o arquivo ID {$arquivoId}: " . $e->getMessage());
            }
        }

        return "Nenhum novo arquivo para processar!";
    }
}