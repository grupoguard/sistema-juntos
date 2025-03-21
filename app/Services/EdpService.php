<?php

namespace App\Services;

use App\Models\LogMovement;
use Illuminate\Support\Facades\Http;
use App\Models\LogRegister;
use App\Models\Order;
use Exception;
use GuzzleHttp\Client;
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
        $response = Http::asJson()->post("{$this->baseUrl}/api/getAccessToken", [
            'UserName' => $this->username,
            'Password' => $this->password
        ]);

        if ($response->successful()) {
            $data = json_decode($response->getBody(), true);

            if ($data['Code'] == 200 && !$data['Error']) {
                $token = $data['Data']['token'];
            }

            return $token ?? false;
        }

        throw new Exception('Falha ao obter token de acesso');
    }

    public function enviarEvidencia($orderId, $dadosEvidencia)
    {
        $order = Order::find($orderId);
        if (!$order || $order->charge_type !== 'EDP') {
            throw new Exception('Pedido inválido ou não aplicável');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->attach('arquivos', $dadosEvidencia['arquivo'])
          ->post("{$this->baseUrl}/api/EnviarEvidencia", $dadosEvidencia);
        
        return $response->json();
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
            $extractPath = storage_path('app/retornos/');
            $zip->extractTo($extractPath);
            $zip->close();

            // Filtra os arquivos extraídos para encontrar o .txt
            $files = array_diff(scandir($extractPath), ['.', '..']); // Remove "." e ".."
            
            foreach ($files as $file) {
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'txt') {
                    return $extractPath . $file; // Retorna o arquivo TXT correto
                }
            }
        }

        throw new Exception('Erro ao extrair ZIP ou arquivo TXT não encontrado.');
    }

    public function processarArquivoTxt($txtPath)
    {
        $lines = file($txtPath, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $tipoRegistro = substr($line, 0, 1);
    
            if ($tipoRegistro === 'B') {
                LogMovement::create([
                    'register_code'      => substr($line, 0, 1),
                    'installation_number'=> substr($line, 1, 9),
                    'extra_value'        => substr($line, 10, 2),
                    'product_cod'        => substr($line, 12, 3),
                    'installment'        => substr($line, 15, 5),
                    'reading_script'     => substr($line, 20, 15),
                    'date_invoice'       => $this->formatDate(substr($line, 35, 6)),
                    'city_code'          => substr($line, 41, 3),
                    'date_movement'      => $this->formatDate(substr($line, 44, 8)),
                    'value'              => substr($line, 52, 15),
                    'code_return'        => substr($line, 67, 2),
                    'future'             => substr($line, 69, 80),
                    'code_move'          => substr($line, 149, 1),
                ]);
            } elseif ($tipoRegistro === 'F') {
                LogRegister::create([
                    'register_code'      => substr($line, 0, 1),
                    'installation_number'=> substr($line, 1, 9),
                    'extra_value'        => substr($line, 10, 2),
                    'product_cod'        => substr($line, 12, 3),
                    'number_installment' => substr($line, 15, 2),
                    'value_installment'  => substr($line, 17, 15),
                    'future1'            => substr($line, 32, 9),
                    'city_code'          => substr($line, 41, 3),
                    'start_date'         => $this->formatDate(substr($line, 44, 8)),
                    'end_date'           => $this->formatDate(substr($line, 52, 8)),
                    'address'            => substr($line, 60, 40),
                    'name'               => substr($line, 100, 40),
                    'future2'            => substr($line, 140, 7),
                    'code_anomaly'       => substr($line, 147, 2),
                    'code_move'          => substr($line, 150, 1),
                ]);
            }
        }
    }
    
    private function formatDate($date)
    {
        if (empty(trim($date)) || $date == '00000000') {
            return null;
        }

        return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
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
            return strtotime($a['DataCriacao']) - strtotime($b['DataCriacao']);
        });

        // 3. Processar apenas o arquivo mais antigo que ainda não foi armazenado
        foreach ($arquivos as $arquivo) {
            $arquivoId = $arquivo['Id'];
            $nomeArquivo = $arquivo['Nome'];

            // Verifica se já processamos esse arquivo
            if (\App\Models\RetornoArmazenado::where('arquivo_id', $arquivoId)->exists()) {
                Log::info("Arquivo {$nomeArquivo} (ID: {$arquivoId}) já foi armazenado. Pulando...");
                continue;
            }

            try {
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

                // 6. Processar o arquivo TXT
                $this->processarArquivoTxt($txtPath);

                // 7. Armazenar o arquivo na tabela retornos_armazenados
                \App\Models\RetornoArmazenado::create([
                    'arquivo_id' => $arquivoId,
                    'nome_arquivo' => $nomeArquivo,
                    'baixado_em' => now(),
                ]);

                Log::info("Arquivo de retorno ID {$arquivoId} processado e armazenado com sucesso!");

                return "Arquivo processado com sucesso!";

            } catch (Exception $e) {
                Log::error("Erro ao processar o arquivo ID {$arquivoId}: " . $e->getMessage());
            }
        }

        return "Nenhum novo arquivo para processar!";
    }
}