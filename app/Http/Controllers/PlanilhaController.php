<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlanilhaImport;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class PlanilhaController extends Controller
{
    private $googleDrive;
    private $tokenFile = 'private/tokens/token.json';

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

            $obj = [
                'Token' => $token,
                'CodigoProduto' => substr($row['produto'], 0, 3),
                'CodigoInstalacao' => str_pad($row['instalacao'], 9, '0', STR_PAD_LEFT),
                'DataEvidencia' => \Carbon\Carbon::createFromFormat('d/m/Y', $row['dt_evidencia'])->format('Y-m-d'),
                'NomeTitular' => strtoupper($this->removeAccents($row['titular_conta_energia'])),
                'NomeQuemAprovou' => strtoupper($this->removeAccents($row['autorizado_por'])),
                'TelefoneContato' => preg_replace('/[^\d]/', '', $row['contato']),
                'Arquivos' => $arquivos,
            ];
            $result[] = $obj;
        }

        foreach ($result as $evidencia) {
            $this->enviarParaAPI($evidencia);
        }

        return view('upload', ['result' => $result])->with('success', 'Planilha processada com sucesso!');
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

    private function downloadFile($downloadLink, $fileName)
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

        // Salvar o arquivo
        $path = 'evidencias/' . $fileName;
        Storage::disk('public')->put($path, $response->getBody());

        return $path;
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
        } catch (\Exception $e) {
            Log::error('Erro ao enviar dados para a API: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao enviar evidências para a API'], 500);
        }

        return json_decode($response->getBody(), true);
    }

    private function removeAccents($string)
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    }
}