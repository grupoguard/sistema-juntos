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
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderForm extends Component
{
    use WithFileUploads;

    public $orderId;
    public Order $order;

    public $client_id;

    public $client = [
        'name' => '',
        'mom_name' => '',
        'date_birth' => '',
        'cpf' => '',
        'rg' => '',
        'gender' => '',
        'marital_status' => '',
        'phone' => '',
        'email' => '',
        'zipcode' => '',
        'address' => '',
        'number' => '',
        'complement' => '',
        'neighborhood' => '',
        'city' => '',
        'state' => '',
        'obs' => '',
        'status' => 1,
    ];

    public $seller_id;
    public $product_id;
    public $additionals = [];
    public $selectedAdditionals = [];
    public $accession;
    public $accession_payment;
    public $total = 0;
    public $dependents = [];
    public $charge_type;
    public $installation_number;
    public $approval_name;
    public $approval_by;
    public $evidence_date;
    public $evidences = [];
    public $charge_date;

    public $clients;
    public $sellers;
    public $products;

    public $group_id;
    public $discount_type;
    public $discount_value;
    public $documents = [];
    public $dependentAdditionals = [];
    

    protected $listeners = ['clientSelected' => 'loadClient', 'loadAdditionals'];

    protected function rules()
    {
        $rules = [
            'client.name' => 'required|string|max:100',
            'client.mom_name' => 'required|string|max:100',
            'client.date_birth' => 'required|date',
            'client.rg' => 'nullable|string|min:9|max:12',
            'client.gender' => 'required|string|max:15',
            'client.marital_status' => 'required|string|max:15',
            'client.phone' => 'nullable|string|max:15',
            'client.zipcode' => 'required|string|max:8',
            'client.address' => 'required|string|max:100',
            'client.number' => 'required|string|max:10',
            'client.complement' => 'nullable|string|max:40',
            'client.neighborhood' => 'required|string|max:50',
            'client.city' => 'required|string|max:50',
            'client.state' => 'required|string|max:2',
            'client.obs' => 'nullable|string',
            'client.status' => 'nullable|integer',
            'dependents' => 'nullable|array',
            'dependents.*.name' => 'required_with:dependents.*.cpf|string|max:100',
            'dependents.*.mom_name' => 'required_with:dependents.*.cpf|string|max:100',
            'dependents.*.date_birth' => 'required_with:dependents.*.cpf|date',
            'dependents.*.cpf' => 'required_with|string|size:11|unique:clients,cpf',
            'dependents.*.rg' => 'nullable|string|size:9',
            'dependents.*.marital_status' => 'required_with:dependents.*.cpf|string|max:15',
            'dependents.*.relationship' => 'required_with:dependents.*.cpf|string|max:20',
            'dependents.*.additionals' => 'nullable|integer',
            'client_id' => 'nullable|integer',
            'product_id' => 'required|integer',
            'seller_id' => 'required|integer',
            'charge_type' => 'required|string|max:20',
            'accession' => 'required|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
            'accession_payment' => 'required|string|max:20',
            'discount_type' => 'nullable|string|max:9',
            'discount_value' => 'nullable|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
        ];

        if (!$this->client_id) {
            $rules['client.cpf'] = 'required|string|min:11|max:14|unique:clients,cpf';
            $rules['client.email'] = 'required|email|max:50|unique:clients,email';
        }
    
        if ($this->charge_type === 'EDP') {
            $rules['installation_number'] = 'required|string|max:9';
            $rules['approval_name'] = 'required|string|max:50';
            $rules['approval_by'] = 'required|string|max:20';
            $rules['evidence_date'] = 'required|date';
            $rules['charge_date'] = 'nullable|integer';
            $rules['evidences'] = 'required|array|min:1';
            $rules['evidences.*.evidence_type'] = 'required|string';
            $rules['evidences.*.document'] = 'required|mimes:pdf,jpg,png,mp3,wav';
    
            $hasContrato = in_array('contrato', array_column($this->evidences, 'evidence_type'));
            $hasRequiredDocs = !empty(array_intersect(['rg', 'cpf', 'cnh'], array_column($this->evidences, 'evidence_type')));
    
            if ($hasContrato && !$hasRequiredDocs) {
                $rules['evidences.*.document'] = 'required|mimes:pdf,jpg,png';
            }
        } else {
            $rules['installation_number'] = 'nullable|string|max:9';
            $rules['approval_name'] = 'nullable|string|max:50';
            $rules['approval_by'] = 'nullable|string|max:20';
            $rules['evidence_date'] = 'nullable|date';
            $rules['charge_date'] = 'required|integer';
        }
    
        if ($this->approval_by === 'Conjuge') {
            $rules['evidences'] = 'required|array|min:1';
            $rules['evidences.*.evidence_type'] = 'required|string';
            $rules['evidences.*.document'] = 'required_if:evidences.*.evidence_type,certidao de casamento|nullable|mimes:pdf,jpg,png';
        }
    
        // Validação condicional para contrato e RG, CPF, CNH
        if (isset($this->evidences) && is_array($this->evidences)) {
            foreach ($this->evidences as $index => $evidence) {
                if (isset($evidence['evidence_type']) && $evidence['evidence_type'] === 'contrato') {
                    $hasRequiredDocs = false;
                    foreach ($this->evidences as $doc) {
                        if (isset($doc['evidence_type']) && in_array($doc['evidence_type'], ['rg', 'cpf', 'cnh'])) {
                            $hasRequiredDocs = true;
                            break;
                        }
                    }
                    if (!$hasRequiredDocs) {
                        $rules["evidences.{$index}.document"] = 'required|mimes:pdf,jpg,png';
                    }
                }
            }
        }
    
        return $rules;
    }

    public function mount($clientId = null)
    {
        $this->clients = Client::where('status', 1)->get();
        $this->sellers = Seller::where('status', 1)->get();
        $this->products = Product::where('status', 1)->get();

        if ($clientId) {
            $this->loadClient($clientId);
        } else {
            $this->resetClientFields();
        }
    }

    public function updatedClientId($value)
    {
        $this->loadClient($value);
    }

    public function loadClient($client_id)
    {
        if ($client_id === "new") {
            $this->resetClientFields();
        } else {
            $client = Client::find($client_id);
            // Verifica se o cliente já tem um pedido
            if (Order::where('client_id', $client->id)->exists()) {
                // Cliente já tem um pedido, redefina o client_id e dispare o evento
                $this->client_id = 'new';
                $this->resetClientFields();
                $this->dispatch('clientHasOrder');
                return; // Impede que os dados do cliente sejam carregados
            }
            if ($client) {
                $this->client_id = $client->id;
                $this->client = [
                    'group_id' => $client->group_id,
                    'name' => $client->name,
                    'mom_name' => $client->mom_name,
                    'date_birth' => $client->date_birth,
                    'cpf' => $client->cpf,
                    'rg' => $client->rg,
                    'gender' => $client->gender,
                    'marital_status' => $client->marital_status,
                    'phone' => $client->phone,
                    'email' => $client->email,
                    'zipcode' => $client->zipcode,
                    'address' => $client->address,
                    'number' => $client->number,
                    'complement' => $client->complement,
                    'neighborhood' => $client->neighborhood,
                    'city' => $client->city,
                    'state' => $client->state,
                    'obs' => $client->obs,
                    'status' => $client->status,
                ];
            }
        }
    }

    public function resetClientFields()
    {
        if ($this->client_id) {
            // Zerar os campos para cadastro de um novo cliente
            $this->client = [
                'group_id' => null,
                'name' => '',
                'mom_name' => '',
                'date_birth' => '',
                'cpf' => '',
                'rg' => '',
                'gender' => '',
                'marital_status' => '',
                'phone' => '',
                'email' => '',
                'zipcode' => '',
                'address' => '',
                'number' => '',
                'complement' => '',
                'neighborhood' => '',
                'city' => '',
                'state' => '',
                'obs' => '',
                'status' => 1
            ];
        } else {
            // Carregar os dados do cliente
            $client = Client::find($this->client_id);
            if ($client) {
                $this->client_id = $client->id;
                $this->client['name'] = $client->name;
                $this->client['email'] = $client->email;
                $this->client['phone'] = $client->phone;
                $this->client['address'] = $client->address;
            }
        }
    }

    public function loadAdditionals()
    {
        if ($this->product_id) {
            $product = Product::find($this->product_id);
            if ($product) {
                // Calcula o total inicial com o valor do produto
                $this->total = $product->value;

                $this->additionals = Aditional::select('aditionals.*', 'product_aditionals.value')
                    ->join('product_aditionals', 'product_aditionals.aditional_id', '=', 'aditionals.id')
                    ->where('product_aditionals.product_id', $this->product_id)
                    ->get()
                    ->toArray(); // Convertendo para array para forçar atualização do Livewire
            } else {
                $this->total = 0;
                $this->additionals = [];
            }
            
        } else {
            $this->total = 0;
            $this->additionals = [];
        }

        $this->recalculateTotal();
    }

    public function updatedProductId()
    {
        $this->loadAdditionals();
    }

    public function calculateAdditionalTotal()
    {
        $additionalTotal = 0;
        foreach ($this->selectedAdditionals as $additionalId) {
            $additional = collect($this->additionals)->where('id', $additionalId)->first();
            if ($additional) {
                $additionalTotal += $additional['value'];
            }
        }
        return $additionalTotal;
    }

    public function updatedSelectedAdditionals()
    {
        $this->recalculateTotal();
    }

    public function calculateDependentsTotal()
    {
        $dependentTotal = 0;
        foreach ($this->dependents as $dependent) {
            if (isset($dependent['additionals']) && is_array($dependent['additionals'])) {
                foreach ($dependent['additionals'] as $additionalId) {
                    $additional = collect($this->additionals)->where('id', $additionalId)->first();
                    if ($additional) {
                        $dependentTotal += $additional['value'];
                    }
                }
            }
        }
        return $dependentTotal;
    }

    public function updatedDependents()
    {
        $this->recalculateTotal();
    }

    public function recalculateTotal()
    {
        $this->total = 0;
        if ($this->product_id) {
            $product = Product::find($this->product_id);
            if ($product) {
                $this->total += $product->value;
            }
        }
        $this->total += $this->calculateAdditionalTotal();
        $this->total += $this->calculateDependentsTotal();
    }

    public function addDependent()
    {
        // Verifica se um produto foi selecionado
        if (!$this->product_id) {
            session()->flash('error', 'É necessário selecionar o produto antes de adicionar dependentes.');
            return;
        }

        $product = Product::find($this->product_id);

        // Verifica se o produto permite dependentes
        if ($product->dependents_limit <= 0) {
            session()->flash('error', 'O produto selecionado não permite dependentes.');
            return;
        }

        // Verifica se já atingiu o limite de dependentes
        if (count($this->dependents) >= $product->dependents_limit) {
            session()->flash('error', "O produto selecionado permite apenas {$product->dependents_limit} dependente(s).");
            return;
        }

        // Adiciona um novo dependente
        $this->dependents[] = [
            'name' => '', 
            'mom_name' => '',
            'date_birth' => '', 
            'cpf' => '', 
            'rg' => '', 
            'marital_status' => '',
            'relationship' => '', 
            'additionals' => []
        ];

        $this->recalculateTotal();
    }

    public function removeDependent($index)
    {
        unset($this->dependents[$index]);
        $this->dependents = array_values($this->dependents);
        $this->recalculateTotal();
    }

    public function addEvidence()
    {
        $this->evidences[] = ['evidence_type' => '', 'document' => ''];
    }

    public function removeEvidence($index)
    {
        unset($this->evidences[$index]);
        $this->evidences = array_values($this->evidences);
    }

    public function updatedInstallationNumber()
    {
        $this->installation_number = preg_replace('/\D/', '', $this->installation_number); // Remove tudo que não for número
    }
    
    public function saveOrder(EdpService $edpService)
    {
        DB::beginTransaction(); // Iniciar transação para garantir a integridade dos dados

        $this->validate($this->rules()); // 🔥 Valida os dados dinamicamente

        try {
            // 3️⃣ Obter o `group_id` do usuário autenticado
            $groupId = $groupId = auth()->user()->access()->first()->group_id;

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

    protected function messages()
    {
        return [
            // Mensagem personalizada para evidência do contrato
            'evidences.*.document.required_if' => 'É necessário adicionar um documento (RG, CPF ou CNH) junto ao contrato.',

            // Mensagem personalizada para evidência de áudio ou contrato no tipo EDP
            'evidences.*.document.required' => 'É necessário adicionar um áudio ou contrato para o tipo EDP.',

            // Mensagem genérica para evidências obrigatórias
            'evidences.*.document.mimes' => 'O documento deve ser um arquivo do tipo: PDF, JPG, PNG, MP3 ou WAV.',

            // Mensagens específicas para CPF e Email únicos
            'client.cpf.unique' => 'O CPF informado já está cadastrado.',
            'client.email.unique' => 'O email informado já está em uso.',

            // Mensagem para valores numéricos com casas decimais
            'accession.regex' => 'O valor de adesão deve ter no máximo 8 dígitos inteiros e até 2 casas decimais.',
            'discount_value.regex' => 'O valor do desconto deve ter no máximo 8 dígitos inteiros e até 2 casas decimais.',
        ];
    }
}