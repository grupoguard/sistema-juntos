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
use App\Models\ProductAditional;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderForm extends Component
{
    use WithFileUploads;

    public $clients;
    public $sellers;
    public $products;

    public $client_id;
    public $product_id;
    public $seller_id;
    public $group_id;
    public $charge_type;
    public $installation_number;
    public $approval_name;
    public $approval_by;
    public $evidence_date;
    public $charge_date;
    public $accession;
    public $accession_payment;
    public $discount_type;
    public $discount_value;

    public $additionals = [];
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
    public $dependents = [];
    public $documents = [];
    public $evidences = [];
    public $selectedAdditionals = [];

    protected $listeners = ['clientSelected' => 'loadClient', 'loadAdditionals'];

    protected function rules()
    {
        $rules = [
            'client.name' => 'required|string|max:100',
            'client.mom_name' => 'required|string|max:100',
            'client.date_birth' => 'required|date',
            'client.cpf' => 'required|string|min:11|max:14|unique:clients,cpf',
            'client.rg' => 'nullable|string|min:9|max:12',
            'client.gender' => 'required|string|max:15',
            'client.marital_status' => 'required|string|max:15',
            'client.phone' => 'nullable|string|max:15',
            'client.email' => 'required|email|max:50|unique:clients,email',
            'client.zipcode' => 'required|string|max:8',
            'client.address' => 'required|string|max:100',
            'client.number' => 'required|string|max:10',
            'client.complement' => 'nullable|string|max:40',
            'client.neighborhood' => 'required|string|max:50',
            'client.city' => 'required|string|max:50',
            'client.state' => 'required|string|max:2',
            'client.obs' => 'nullable|string',
            'client.status' => 'nullable|integer',
            'dependents.*.name' => 'nullable|string|max:100',
            'dependents.*.mom_name' => 'nullable|string|max:100',
            'dependents.*.date_birth' => 'nullable|date',
            'dependents.*.cpf' => 'nullable|string|size:11|unique:clients,cpf',
            'dependents.*.rg' => 'nullable|string|size:9',
            'dependents.*.marital_status' => 'nullable|string|max:15',
            'dependents.*.relationship' => 'nullable|string|max:20',
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

        // **📌 Aplicando regras condicionais quando charge_type = 'EDP'**
        if ($this->charge_type === 'EDP') {
            $rules['installation_number'] = 'required|string|max:9';
            $rules['approval_name'] = 'required|string|max:50';
            $rules['approval_by'] = 'required|string|max:20';
            $rules['evidence_date'] = 'required|date';
            $rules['charge_date'] = 'nullable|integer';
            $rules['evidences.*.evidence_type'] = 'required|string|max:20';
            $rules['evidences.*.document'] = 'required|string|max:255';
        } else {
            $rules['installation_number'] = 'nullable|string|max:9';
            $rules['approval_name'] = 'nullable|string|max:50';
            $rules['approval_by'] = 'nullable|string|max:20';
            $rules['evidence_date'] = 'nullable|date';
            $rules['charge_date'] = 'required|integer';
            $rules['evidences.*.evidence_type'] = 'nullable|string|max:20';
            $rules['evidences.*.document'] = 'nullable|string|max:255';
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
        $this->dispatch('updateClientForm', $value); // Envia evento para ClientForm
    }

    public function loadClient($client_id)
    {
        if ($client_id === "new") {
            $this->resetClientFields();
        } else {
            $client = Client::find($client_id);
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

    public function loadAdditionals()
    {
        if ($this->product_id) {
            $this->additionals = Aditional::select('aditionals.*', 'product_aditionals.value')
                ->join('product_aditionals', 'product_aditionals.aditional_id', '=', 'aditionals.id')
                ->where('product_aditionals.product_id', $this->product_id)
                ->get()
                ->toArray(); // Convertendo para array para forçar atualização do Livewire
        } else {
            $this->additionals = [];
        }
    }

    public function resetClientFields()
    {
        if ($this->client_id) {
            // Zerar os campos para cadastro de um novo cliente
            $this->reset(
                [
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
                ]
            );
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

    public function updatedProductId()
    {
        $this->loadAdditionals();
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
    }

    public function removeDependent($index)
    {
        unset($this->dependents[$index]);
        $this->dependents = array_values($this->dependents);
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
    
    public function saveOrder()
    {
        DB::beginTransaction(); // Iniciar transação para garantir a integridade dos dados

        $this->validate($this->rules()); // 🔥 Valida os dados dinamicamente

        try {
            // 3️⃣ Obter o `group_id` do usuário autenticado
            $groupId = auth()->user()->access()->first()->group_id; 

            // 1️⃣ Verificar se o cliente já existe
            $client = Client::updateOrCreate(
                ['cpf' => $this->client['cpf']], // Supondo que 'document' seja único
                [
                    'group_id' => $groupId,
                    'name' => $this->client['name'],
                    'mom_name' => $this->client['mom_name'],
                    'date_birth' => $this->client['date_birth'],
                    'rg' => $this->client['rg'],
                    'gender' => $this->client['gender'],
                    'marital_status' => $this->client['marital_status'],
                    'phone' => $this->client['phone'],
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

            // 2️⃣ Cadastrar ou atualizar os dependentes
            $dependentsIds = [];
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
            OrderPrice::create([
                'order_id' => $order->id,
                'product_id' => $this->product_id,
                'product_value' => $product->value,
                'dependent_value' => 0,
                'dependents_count' => count($dependentsIds),
            ]);

            // 6️⃣ Cadastrar adicionais principais (não vinculados a dependentes)
            foreach ($this->additionals as $additional) {
                OrderAditional::create([
                    'order_id' => $order->id,
                    'aditional_id' => $additional['id'],
                    'value' => $additional['value']
                ]);
            }

            // 7️⃣ Associar dependentes ao pedido
            foreach ($dependentsIds as $dependentId) {
                OrderDependent::create([
                    'order_id' => $order->id,
                    'dependent_id' => $dependentId
                ]);
            }

            // 8️⃣ Adicionais vinculados a dependentes
            foreach ($this->dependentAdditionals as $dependentAdditional) {
                OrderAditionalDependent::create([
                    'order_id' => $order->id,
                    'dependent_id' => $dependentAdditional['dependent_id'],
                    'aditional_id' => $dependentAdditional['aditional_id'],
                    'value' => $dependentAdditional['value']
                ]);
            }

            // 9️⃣ Verificar charge_type e adicionar evidências ou financeiro
            if ($this->charge_type === 'EDP') {
                if (empty($this->evidences)) {
                    throw new \Exception("É obrigatório adicionar pelo menos um documento quando o charge_type for EDP.");
                }

                foreach ($this->evidences as $evidence) {
                    EvidenceDocument::create([
                        'order_id' => $order->id,
                        'evidence_type' => $evidence['evidence_type'],
                        'document' => $evidence['document']
                    ]);
                }

                EvidenceReturn::create([
                    'order_id' => $order->id,
                    'status' => 'NÃO AUDITADO'
                ]);
            } elseif ($this->charge_type === 'Boleto') {
                Financial::create([
                    'order_id' => $order->id,
                    'value' => $product->value,
                    'status' => 0
                ]);
            }

            DB::commit(); // Confirma a transação no banco de dados

            session()->flash('message', 'Pedido salvo com sucesso!');
            return redirect()->route('orders.index');

        } catch (\Exception $e) {
            DB::rollBack(); // Desfazer alterações em caso de erro
            session()->flash('error', 'Erro ao salvar pedido: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.order-form', [
            'products' => Product::all(),
        ]);
    }
}
