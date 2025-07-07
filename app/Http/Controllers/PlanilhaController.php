<?php

namespace App\Http\Controllers;

set_time_limit(0); // Remove limite de execução
ini_set('max_execution_time', 0); // Garante que não há limite no PHP

use App\Exports\FeedbackExport;
use App\Imports\OptimizedPlanilhaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlanilhaImport;
use App\Traits\TxtLayoutTrait;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class PlanilhaController extends Controller
{
    private $googleDrive;
    private $tokenFile = 'private/tokens/token.json';
    use TxtLayoutTrait;

    public function __construct()
    {
        // Instanciar o Google Drive com as credenciais da Service Account
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/private/credentials.json'));
        $client->addScope(GoogleDrive::DRIVE);
        $this->googleDrive = new GoogleDrive($client);
    }

    public function index()
    {
        return view('pages.evidences');
    }

    public function disparo()
    {
        return view('pages.disparo');
    }

    public function processamento()
    {
        return view('pages.processamento');
    }

    public function getToken()
    {
        // Verifica se o token já está salvo no arquivo e se ainda é válido
        if (Storage::exists($this->tokenFile)) {
            $tokenData = json_decode(Storage::get($this->tokenFile), true);
            $expirationDate = \Carbon\Carbon::parse($tokenData['TokenExpireDate']);

            // Verifica se o token ainda está válido
            if ($expirationDate->isFuture()) {
                return $tokenData['token'];
            }
        }

        // Caso o token esteja expirado ou não exista, faz a requisição para gerar um novo
        $client = new Client();
        $response = $client->post('https://web-edp-sgcvt-prd.azurewebsites.net/api/getAccessToken', [
            'json' => [
                'UserName' => 'juntosviniciusapi',
                'Password' => 'Junto$321'
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data['Code'] == 200 && !$data['Error']) {
            $newTokenData = [
                'token' => $data['Data']['token'],
                'TokenExpireDate' => $data['Data']['TokenExpireDate']
            ];

            // Salva o novo token e a data de expiração no arquivo
            Storage::put($this->tokenFile, json_encode($newTokenData));

            return $newTokenData['token'];
        }

        throw new \Exception("Falha ao obter o token");
    }

    public function upload(Request $request)
    {
        $request->validate([
            'planilha' => 'required|mimes:xlsx,xls,csv'
        ]);

        $token = $this->getToken();

        $import = new PlanilhaImport();
        Excel::import($import, $request->file('planilha'));
        $dadosImportados = $import->rows;

        $result = [];

        foreach ($dadosImportados as $row) {
            $folderLink = $row['evidencia'];
            $folderId = $this->extractFolderIdFromLink($folderLink);

            if (!$folderId) {
                Log::error('ID da pasta não encontrado no link: ' . $folderLink);
                continue;
            }

            // Faz o download de todos os arquivos da pasta
            $arquivos = $this->downloadFilesFromDriveFolder($folderId);
            try {
                $dataEvidencia = \Carbon\Carbon::createFromFormat('d/m/Y', $row['dt_evidencia'])->format('Y-m-d');
            } catch (\Exception $e) {
                $excelDate = $row['dt_evidencia'];
                $dataEvidencia = \Carbon\Carbon::parse('1900-01-01')->addDays($excelDate - 2)->format('Y-m-d');
            }

            $obj = [
                'Token' => $token,
                'CodigoProduto' => substr($row['produto'], 0, 3),
                'CodigoInstalacao' => str_pad($row['instalacao'], 9, '0', STR_PAD_LEFT),
                'DataEvidencia' => $dataEvidencia,
                'NomeTitular' => strtoupper($this->removeAccents($row['titular_conta_energia'])),
                'NomeQuemAprovou' => strtoupper($this->removeAccents($row['autorizado_por'])),
                'TelefoneContato' => isset($row['contato']) ? preg_replace('/[^\d]/', '', $row['contato']) : '',
                'Arquivos' => $arquivos,
            ];

            $result[] = $this->enviarParaAPI($obj);
        }

        session(['feedback_resultados' => $result]);

        return redirect()->route('evidences.disparo')->with('resultados', $result);
    }

    private function extractFolderIdFromLink($folderLink)
    {
        preg_match('/folders\/(.*?)($|\?)/', $folderLink, $matches);
        return $matches[1] ?? null;
    }

    private function downloadFilesFromDriveFolder($folderId)
    {
        $arquivos = [];

        try {
            $files = $this->googleDrive->files->listFiles([
                'q' => "'{$folderId}' in parents",
                'fields' => 'files(id, name)',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao acessar o Google Drive: ' . $e->getMessage());
            return [];
        }

        foreach ($files->getFiles() as $file) {
            $downloadLink = $this->getFileDownloadLink($file->id);
            $filePath = $this->downloadFile($downloadLink, $file->name);
            $arquivos[] = $filePath;
        }

        return $arquivos;
    }

    private function getFileDownloadLink($fileId)
    {
        return "https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media";
    }

    private function downloadFile($downloadLink, $originalFileName)
    {
        $client = new Client();
        $response = $client->get($downloadLink, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->googleDrive->getClient()->fetchAccessTokenWithAssertion()['access_token'],
            ]
        ]);

        if (!Storage::disk('public')->exists('evidencias')) {
            Storage::disk('public')->makeDirectory('evidencias');
        }

        // Base para o novo nome do arquivo, com o sufixo "_edp"
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME) . '_edp';
        $path = 'evidencias/' . $fileNameWithoutExtension . '.' . $fileExtension;

        // Verifica se o arquivo já existe e ajusta o nome com o sufixo numérico se necessário
        $counter = 1;
        while (Storage::disk('public')->exists($path)) {
            $path = 'evidencias/' . $fileNameWithoutExtension . '_' . $counter . '.' . $fileExtension;
            $counter++;
        }

        // Salva o arquivo com o nome ajustado
        Storage::disk('public')->put($path, $response->getBody());

        return $path; // Retorna o caminho final do arquivo no storage
    }

    public function enviarParaAPI($evidencia)
    {
        $client = new Client();
        $arquivos = $evidencia['Arquivos'];
        
        $multipartData = [
            [
                'name' => 'token',
                'contents' => $evidencia['Token'],
            ],
            [
                'name' => 'CodigoProduto',
                'contents' => $evidencia['CodigoProduto'],
            ],
            [
                'name' => 'CodigoInstalacao',
                'contents' => $evidencia['CodigoInstalacao'],
            ],
            [
                'name' => 'DataEvidencia',
                'contents' => $evidencia['DataEvidencia'],
            ],
            [
                'name' => 'NomeTitular',
                'contents' => $evidencia['NomeTitular'],
            ],
            [
                'name' => 'NomeQuemAprovou',
                'contents' => $evidencia['NomeQuemAprovou'],
            ],
            [
                'name' => 'TelefoneContato',
                'contents' => $evidencia['TelefoneContato'],
            ],
        ];

        foreach ($arquivos as $filePath) {
            $fullPath = storage_path('app/public/') . $filePath;
            if (file_exists($fullPath)) {
                $multipartData[] = [
                    'name' => 'Arquivos[]',
                    'contents' => fopen($fullPath, 'r'),
                    'filename' => basename($filePath),
                ];
            } else {
                Log::error('Arquivo não existe ' . $fullPath);
            }
        }

        try {
            $response = $client->post('https://web-edp-sgcvt-prd.azurewebsites.net/api/EnviarEvidencia', [
                'multipart' => $multipartData,
            ]);

            // Captura e decodifica o corpo da resposta
            $responseData = json_decode($response->getBody(), true);
            Log::info('Resposta da API: ' . json_encode($responseData));

            return [
                'cliente' => $evidencia['NomeTitular'],
                'instalacao' => $evidencia['CodigoInstalacao'],
                'status' => !$responseData['Error'] ? 'success' : 'danger',
                'mensagem' => $responseData['Message'],
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao enviar dados para a API: ' . $e->getMessage());
            return [
                'cliente' => $evidencia['NomeTitular'],
                'instalacao' => $evidencia['CodigoInstalacao'],
                'status' => 'danger',
                'mensagem' => $e->getMessage(),
            ];
        }
    }

    private function removeAccents($string)
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    }

    public function gerarTxt(Request $request)
    {
        $request->validate([
            'planilha' => 'required|mimes:csv'
        ]);

        $startTime = microtime(true);
        Log::info("Iniciando processamento corrigido");

        $fileName = 'arquivo_' . now()->format('Ymd_His') . '.txt';
        $filePath = storage_path("app/public/txt/{$fileName}");
        $csvPath = $request->file('planilha')->getPathname();
        
        $fileHandle = fopen($filePath, 'w');
        $csvHandle = fopen($csvPath, 'r');
        
        if (!$fileHandle || !$csvHandle) {
            throw new \Exception('Erro ao abrir arquivos');
        }

        try {
            // Header
            fwrite($fileHandle, $this->generateHeader());
            
            $totalRecords = 0;
            $totalAmount = 0;
            $dataInicial = Carbon::now()->addMonth()->startOfMonth()->format('Ymd');
            
            // Pular header do CSV
            $headerLine = fgetcsv($csvHandle);
            Log::info("Header do CSV", ['header' => $headerLine]);
            
            $batchLines = [];
            $batchSize = 1000;
            $lineNumber = 1;

            
            while (($row = fgetcsv($csvHandle)) !== false) {
                $lineNumber++;
                
                // Debug das primeiras 5 linhas
                if ($lineNumber <= 6) {
                    Log::info("Linha {$lineNumber}", [
                        'row_count' => count($row),
                        'instalacao' => $row[0] ?? 'N/A',
                        'titular' => $row[1] ?? 'N/A',
                        'autorizado' => $row[2] ?? 'N/A',
                        'produto' => $row[3] ?? 'N/A',
                        'contato' => $row[4] ?? 'N/A',
                        'data' => $row[5] ?? 'N/A',
                        'valor' => $row[6] ?? 'N/A',
                        'raw_row' => $row
                    ]);
                }
                
                $instalacao = trim($row[0] ?? '');
                $codigoProduto = trim($row[3] ?? ''); // CORRIGIDO: agora valida o produto correto
                $valorRaw = trim($row[6] ?? '');
                
                if (empty($instalacao) || empty($codigoProduto) || empty($valorRaw)) {
                    Log::info("Linha {$lineNumber} ignorada - dados vazios", [
                        'instalacao' => $instalacao,
                        'produto' => $codigoProduto,
                        'valor' => $valorRaw
                    ]);
                    continue;
                }

                // CORRIGIDO: Tratamento do valor com vírgula decimal
                $valor = (float) str_replace(',', '.', $valorRaw);
                
                $instalacao = str_pad($instalacao, 9, '0', STR_PAD_LEFT);
                $valorInt = (int) ($valor * 100);

                $linha = sprintf(
                    "B%-9s64%-3s01%015d%-12s%s%-8s%-40s%-47s012",
                    $instalacao,
                    $codigoProduto,
                    $valorInt,
                    "",
                    $dataInicial,
                    "00000000",
                    "",
                    ""
                );
                
                $batchLines[] = $linha;
                $totalRecords++;
                $totalAmount += $valor;
                
                // Debug das primeiras linhas processadas
                if ($totalRecords <= 5) {
                    Log::info("Linha B gerada #{$totalRecords}", [
                        'linha' => $linha,
                        'instalacao' => $instalacao,
                        'codigo_produto' => $codigoProduto,
                        'valor_original' => $valorRaw,
                        'valor_convertido' => $valor,
                        'valor_int' => $valorInt
                    ]);
                }
                
                // Escrever em lotes
                if (count($batchLines) >= $batchSize) {
                    $content = implode("\n", $batchLines) . "\n";
                    fwrite($fileHandle, $content);
                    Log::info("Escrito lote", [
                        'lines_in_batch' => count($batchLines),
                        'total_records_so_far' => $totalRecords
                    ]);
                    $batchLines = [];
                }
                
                if ($totalRecords % 1000 == 0) {
                    Log::info("Processadas {$totalRecords} linhas válidas");
                }
            }
            
            // Escrever últimas linhas
            if (!empty($batchLines)) {
                $content = implode("\n", $batchLines) . "\n";
                fwrite($fileHandle, $content);
                Log::info("Escrito último lote", ['lines_in_batch' => count($batchLines)]);
            }
            
            // Footer
            fwrite($fileHandle, $this->generateFooter($totalRecords, $totalAmount));
            
        } finally {
            fclose($fileHandle);
            fclose($csvHandle);
        }

        $totalTime = microtime(true) - $startTime;
        Log::info("Processamento finalizado", [
            'total_time_seconds' => round($totalTime, 2),
            'records_processed' => $totalRecords,
            'total_amount' => $totalAmount,
            'file_size' => filesize($filePath)
        ]);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function generateHeader()
    {
        return sprintf(
            "A2%-20s%-20s408%-20s%s%-76s\n",
            "", 
            "BANDEIRANTE",
            substr("JUNTOS CLUBE DE BENEFICIO", 0, 20),
            Carbon::now()->format('Ymd'),
            ""
        );
    }

    public function generateFooter($totalRecords, $totalAmount)
    {
        return sprintf(
            "Z%06d%017d%-126s\n",
            $totalRecords,
            $totalAmount * 100,
            ""
        );
    }

    private function processChunk($chunk, &$totalRecords, &$totalAmount)
    {
        $bodyContent = '';
        $dataInicial = Carbon::now()->addMonth()->startOfMonth()->format('Ymd');
        
        foreach ($chunk as $row) {
            $instalacao = str_pad($row['instalacao'], 9, '0', STR_PAD_LEFT);
            $valor = (int) ($row['valor'] * 100);
            $codigoProduto = substr($row['produto'], 0, 3);

            $bodyContent .= sprintf(
                "B%-9s64%-3s01%015d%-12s%s%-8s%-40s%-47s012\n",
                $instalacao,
                $codigoProduto,
                $valor,
                "",
                $dataInicial,
                "00000000",
                "",
                ""
            );

            $totalRecords++;
            $totalAmount += $row['valor'];
        }
        
        return $bodyContent;
    }

    public function downloadFeedback()
    {
        $feedbackData = session('feedback_resultados');

        if (!$feedbackData) {
            return redirect()->route('evidences.disparo')->with('error', 'Nenhum feedback disponível para download.');
        }

        return Excel::download(new FeedbackExport($feedbackData), 'feedback_evidencias.xlsx');
    }
}