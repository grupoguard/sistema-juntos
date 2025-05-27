<?php

namespace App\Livewire;

use App\Models\OrderAditionalDependent;
use App\Models\Aditional;
use App\Models\Client;
use App\Models\Dependent;
use App\Models\EvidenceDocument;
use App\Models\EvidenceReturn;
use App\Models\Financial;
use App\Models\Order;
use App\Models\OrderAditional;
use App\Models\OrderDependent;
use App\Models\OrderPrice;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\EdpService;
use App\Traits\OrderFormTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderForm extends Component
{
    use WithFileUploads, OrderFormTrait;

    protected $listeners = ['clientSelected' => 'loadClient', 'loadAdditionals'];

    public function mount($clientId = null)
    {
        $this->clients = Client::where('status', 1)->orderBy('name')->get();
        $this->sellers = Seller::where('status', 1)->orderBy('name')->get();
        $this->products = Product::where('status', 1)->orderBy('name')->get();

        if ($clientId) {
            $this->loadClient($clientId);
        } else {
            $this->resetClientFields();
        }
    }

    public function saveOrder(EdpService $edpService)
    {
        DB::beginTransaction(); // Iniciar transação para garantir a integridade dos dados

        $this->validate($this->rules()); // 🔥 Valida os dados dinamicamente

        try {
            // 3️⃣ Obter o `group_id` do usuário autenticado
            $groupId = request()->user()->access()->first()->group_id;

            $cpf = preg_replace('/\D/', '', $this->client['cpf']);
            $rg = preg_replace('/\D/', '', $this->client['rg']); // Remove pontos e traços
            $phone = preg_replace('/\D/', '', $this->client['phone']); // Remove caracteres não numéricos

            // 1️⃣ Verificar se o cliente já existe
            $client = Client::updateOrCreate(
                ['cpf' => $cpf], // Supondo que 'document' seja único
                [
                    'group_id' => $groupId,
                    'name' => $this->client['name'],
                    'mom_name' => $this->client['mom_name'],
                    'date_birth' => $this->client['date_birth'],
                    'rg' => $rg ,
                    'gender' => $this->client['gender'],
                    'marital_status' => $this->client['marital_status'],
                    'phone' => $phone,
                    'email' => $this->client['email'],
                    'zipcode' => $this->client['zipcode'],
                    'address' => $this->client['address'],
                    'number' => $this->client['number'],
                    'complement' => $this->client['complement'],
                    'neighborhood' => $this->client['neighborhood'],
                    'city' => $this->client['city'],
                    'state' => $this->client['state'],
                    'obs' => '',
                    'status' => 1,
                ]
            );

            $dependentsIds = [];
            // 2️⃣ Cadastrar ou atualizar os dependentes
            if (!empty($this->dependents)) {
                foreach ($this->dependents as $dependent) {
                    $dep = Dependent::updateOrCreate(
                        ['cpf' => $dependent['cpf']], // Supondo que o CPF/RG seja único
                        [
                            'client_id' => $client->id, 
                            'name' => $dependent['name'], 
                            'mom_name' => $dependent['mom_name'],
                            'date_birth' => $dependent['date_birth'], 
                            'cpf' => $dependent['cpf'], 
                            'rg' => $dependent['rg'], 
                            'marital_status' => $dependent['marital_status'],
                            'relationship' => $dependent['relationship'], 
                        ]
                    );
                    $dependentsIds[] = $dep->id;
                }
            }

            // 4️⃣ Criar o pedido
            $order = Order::create([
                'client_id' => $client->id,
                'product_id' => $this->product_id,
                'group_id' => $groupId,
                'seller_id' => $this->seller_id,
                'charge_type' => $this->charge_type,
                'installation_number' => $this->installation_number,
                'approval_name' => $this->approval_name,
                'approval_by' => $this->approval_by,
                'evidence_date' => $this->evidence_date,
                'charge_date' => $this->charge_date,
                'accession' => $this->accession,
                'accession_payment' => $this->accession_payment,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
            ]);

            // 5️⃣ Criar registro em `order_prices`
            $product = Product::find($this->product_id);
            if (!$product) {
                throw new \Exception("Produto não encontrado.");
            }

            OrderPrice::create([
                'order_id' => $order->id,
                'product_id' => $this->product_id,
                'product_value' => $product->value,
                'dependent_value' => 0,
                'dependents_count' => count($dependentsIds),
            ]);

            // 6️⃣ Cadastrar adicionais principais (não vinculados a dependentes)
            
            if (!empty($this->selectedAdditionals)) { // Agora verificamos os selecionados
                foreach ($this->selectedAdditionals as $aditionalId) {
                    $aditional = collect($this->additionals)->firstWhere('id', $aditionalId);
                    if ($aditional) {
                        OrderAditional::create([
                            'order_id' => $order->id,
                            'aditional_id' => $aditionalId,
                            'value' => $aditional['value']
                        ]);
                    }
                }
            }

            // 7️⃣ Associar dependentes ao pedido
            if (isset($dependentsIds) && !empty($dependentsIds)) {
                foreach ($this->dependents as $index => $dependent) {
                    if (isset($dependent['additionals']) && is_array($dependent['additionals'])) {
                        foreach ($dependent['additionals'] as $additionalId) {
                            if (isset($dependentsIds[$index])) {
                                OrderAditionalDependent::create([
                                    'order_id' => $order->id,
                                    'dependent_id' => $dependentsIds[$index],
                                    'aditional_id' => $additionalId,
                                    'value' => collect($this->additionals)->where('id', $additionalId)->first()['value']
                                ]);
                            }
                        }
                    }
                }
            }

            // 9️⃣ Verificar charge_type e adicionar evidências ou financeiro
            if ($this->charge_type === 'EDP') {
                if (empty($this->evidences)) {
                    throw new \Exception("É obrigatório adicionar pelo menos um documento quando o tipo de cobrança for EDP.");
                }

                $evidencePaths = []; // Array para armazenar os caminhos dos documentos

                foreach ($this->evidences as $evidence) {
                    $documentPath = null;
            
                    if (isset($evidence['document']) && $evidence['document']) {
                        $documentPath = $evidence['document']->store('evidence_documents', 'public');
                        $evidencePaths[] = $documentPath; // Adiciona o caminho ao array
                    }
    
                    EvidenceDocument::create([
                        'order_id' => $order->id,
                        'evidence_type' => $evidence['evidence_type'],
                        'document' => $documentPath,
                    ]);
                }

                EvidenceReturn::create([
                    'order_id' => $order->id,
                    'status' => 'NÃO AUDITADO'
                ]);

                // Enviar todas as evidências para a API da EDP
                if ($order->charge_type === 'EDP' && !empty($evidencePaths)) {
                    $this->enviarEvidenciaEdp($edpService, $order, $product->code, $evidencePaths);
                }
            } elseif ($this->charge_type === 'Boleto') {
                Financial::create([
                    'order_id' => $order->id,
                    'value' => $product->value,
                    'status' => 0
                ]);
            }

            DB::commit(); // Confirma a transação no banco de dados

            session()->flash('message', 'Pedido salvo com sucesso!');
            return redirect()->route('admin.orders.index');

        } catch (\Exception $e) {
            DB::rollBack(); // Desfazer alterações em caso de erro
            session()->flash('error', 'Erro ao salvar pedido: ' . $e->getMessage());
        }
    }

    public function removeOrder($orderId)
    {
        DB::beginTransaction(); // Iniciar transação para garantir a integridade dos dados

        try {
            // 1️⃣ Buscar o pedido
            $order = Order::findOrFail($orderId);

            // 2️⃣ Remover dependentes vinculados ao pedido
            OrderDependent::where('order_id', $order->id)->delete();
            
            // 3️⃣ Remover adicionais vinculados ao pedido
            OrderAditional::where('order_id', $order->id)->delete();
            OrderAditionalDependent::where('order_id', $order->id)->delete();

            // 4️⃣ Remover preços vinculados ao pedido
            OrderPrice::where('order_id', $order->id)->delete();

            // 5️⃣ Remover evidências e auditoria (caso existam)
            EvidenceDocument::where('order_id', $order->id)->delete();
            EvidenceReturn::where('order_id', $order->id)->delete();

            // 6️⃣ Remover registros financeiros
            Financial::where('order_id', $order->id)->delete();

            // 7️⃣ Por fim, remover o próprio pedido
            $order->delete();

            DB::commit(); // Confirmar remoção no banco de dados

            session()->flash('message', 'Pedido removido com sucesso!');
            return redirect()->route('admin.orders.index');
        } catch (\Exception $e) {
            DB::rollBack(); // Desfazer alterações em caso de erro
            session()->flash('error', 'Erro ao remover pedido: ' . $e->getMessage());
        }
    }

    private function enviarEvidenciaEdp(EdpService $edpService, Order $order, $code, array $evidencePaths)
    {
        $dataEvidencia = Carbon::parse($order->evidence_date)->format('Y-m-d');

        $dadosEvidencia = [
            'CodigoProduto' => $code, // Substitua pelo código do produto da sua empresa
            'CodigoInstalacao' => $order->installation_number,
            'DataEvidencia' => $dataEvidencia,
            'NomeTitular' => strtoupper($this->removeAccents($order->client->name)),
            'NomeQuemAprovou' => strtoupper($this->removeAccents($order->approval_name)),
            'TelefoneContato' => isset($order->client->phone) ? preg_replace('/[^\d]/', '', $order->client->phone) : '',
        ];

        $arquivos = [];

        foreach ($evidencePaths as $path) {
            $fullPath = storage_path('app/public/') . $path;
            if (file_exists($fullPath)) {
                $arquivos[] = [
                    'name' => 'Arquivos[]',
                    'contents' => fopen($fullPath, 'r'),
                    'filename' => basename($path),
                ];
            } else {
                Log::error("Arquivo de evidencia não encontrado: " . $fullPath);
            }
        }

        try {
            $respostaApi = $edpService->enviarEvidencia($order->id, $dadosEvidencia, $arquivos);

            // Lidar com a resposta da API
            if ($respostaApi['Code'] == 200 && !$respostaApi['Error']) {
                // Sucesso
                session()->flash('message', 'Evidência enviada com sucesso para a API da EDP.');
            } else {
                // Falha
                session()->flash('error', 'Falha ao enviar evidência para a API da EDP: ' . $respostaApi['Message']);
            }
        } catch (Exception $e) {
            session()->flash('error', 'Erro ao enviar evidência para a API da EDP: ' . $e->getMessage());
        }
    }

    private function removeAccents($string)
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    }

    public function render()
    {
        return view('livewire.order-form', [
            'products' => Product::all(),
        ]);
    }
}