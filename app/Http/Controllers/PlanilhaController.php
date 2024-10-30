<?php

namespace App\Http\Controllers;

use App\Exports\FeedbackExport;
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
                'TelefoneContato' => preg_replace('/[^\d]/', '', $row['contato']),
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
            'planilha' => 'required|mimes:xlsx,xls,csv'
        ]);

        $import = new PlanilhaImport();
        Excel::import($import, $request->file('planilha'));
        $file = $import->rows;

        $data = []; // Aqui, você parseia a planilha e extrai as linhas necessárias

        $totalRecords = 0;
        $totalAmount = 0;
        $bodyContent = '';

        //dd($data);

        // Processa cada linha do body (registros do tipo B)
        foreach ($file as $row) {
            $instalacao = str_pad($row['instalacao'], 9, '0', STR_PAD_LEFT);
            $valor = (int) ($row['valor'] * 100); // Converte o valor para inteiro
            $codigoProduto = substr($row['produto'], 0, 3);
            $dataInicial = Carbon::now()->addMonth()->startOfMonth()->format('Ymd');

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

        // Monta o conteúdo completo do arquivo TXT
        $header = $this->generateHeader();
        $footer = $this->generateFooter($totalRecords, $totalAmount);
        $fileContent = $header . $bodyContent . $footer;

        // Salva o arquivo para download
        $fileName = 'arquivo_' . now()->format('Ymd_His') . '.txt';
        Storage::put("public/txt/{$fileName}", $fileContent);

        return response()->download(storage_path("app/public/txt/{$fileName}"))->deleteFileAfterSend(true);
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