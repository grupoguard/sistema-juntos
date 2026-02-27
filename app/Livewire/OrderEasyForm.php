<?php

namespace App\Livewire;

use App\Models\Aditional;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderDraft;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use App\Models\OrderAditional;
use App\Models\OrderAditionalDependent;
use App\Models\OrderDependent;
use App\Models\OrderPrice;
use App\Models\Dependent;
use App\Models\Financial;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderEasyForm extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public $draftId = null;
    public $step = 1;

    // Dados por etapa
    public $client = [];
    public $address = [];
    public $orderData = [];
    public $dependents = [];
    public $billing = [];

    // Uploads
    public $document_file; // temp upload
    public $document_file_type = 'RG'; // RG | CNH
    public $address_proof_file; // temp upload

    // Paths salvos no draft
    public $existing_document_file = null;
    public $existing_address_proof_file = null;

    // Catálogos / listas
    public $sellers = [];
    public $products = [];
    public $additionals = [];

    // UI helpers
    public $clientFound = false;
    public $clientLookupMessage = null;

    public $total = 0;

    protected $listeners = [
        // caso depois você queira integrar com select custom/livewire child
    ];

    public function mount($draftId = null)
    {
        $this->authorize('create', Order::class);

        $user = auth()->user();

        // Produtos ativos
        $this->products = Product::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->toArray();

        // Sellers visíveis conforme role
        $this->sellers = Seller::query()
            ->where('status', 1)
            ->when($user->isCoop(), fn ($q) => $q->whereIn('group_id', $user->getAccessibleGroupIds()))
            ->when($user->isSeller(), fn ($q) => $q->whereIn('id', $user->getAccessibleSellerIds()))
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->resetDefaultState();

        // SELLER: trava seller_id automaticamente
        if ($user->isSeller()) {
            $sellerId = method_exists($user, 'currentSellerId') ? $user->currentSellerId() : null;
            $this->orderData['seller_id'] = $sellerId;
        }

        // Carregar draft, se informado
        if ($draftId) {
            $this->loadDraft($draftId);
        }
    }

    protected function resetDefaultState(): void
    {
        $this->client = [
            'cpf' => '',
            'name' => '',
            'gender' => '',
            'rg' => '',
            'date_birth' => '',
            'phone' => '',
            'email' => '',
            'mom_name' => '',
            'marital_status' => '',
        ];

        $this->address = [
            'zipcode' => '',
            'address' => '',
            'number' => '',
            'complement' => '',
            'neighborhood' => '',
            'city' => '',
            'state' => '',
        ];

        $this->orderData = [
            'seller_id' => null,
            'product_id' => null,
            'selectedAdditionals' => [],
            'accession' => null,
            'accession_payment' => null,
        ];

        $this->billing = [
            'charge_type' => 'Boleto',
            'charge_date' => null, // dia de pagamento
        ];

        $this->dependents = [];
    }

    public function loadDraft($draftId): void
    {
        $draft = OrderDraft::findOrFail($draftId);

        $user = auth()->user();

        // Admin pode abrir qualquer draft; demais apenas o próprio
        if (!$user->isAdmin() && (int) $draft->user_id !== (int) $user->id) {
            abort(403);
        }

        $payload = $draft->payload ?? [];

        $this->draftId = $draft->id;
        $this->step = $draft->current_step ?: 1;

        $this->client = array_merge($this->client, $payload['client'] ?? []);
        $this->address = array_merge($this->address, $payload['address'] ?? []);
        $this->orderData = array_merge($this->orderData, $payload['orderData'] ?? []);
        $this->dependents = $payload['dependents'] ?? [];
        $this->billing = array_merge($this->billing, $payload['billing'] ?? []);

        $this->existing_document_file = $draft->document_file;
        $this->document_file_type = $draft->document_file_type ?: 'RG';
        $this->existing_address_proof_file = $draft->address_proof_file;

        // Reforça travamento seller para SELLER
        if ($user->isSeller() && method_exists($user, 'currentSellerId')) {
            $this->orderData['seller_id'] = $user->currentSellerId();
        }
    }

    // ===============================
    // Navegação entre etapas
    // ===============================

    public function nextStep(): void
    {
        // Se falhar validação, o Livewire já exibe os erros e NÃO continua.
        $this->validateStep();

        // Só salva se validou
        $this->saveDraft();

        if ($this->step < 8) {
            $this->step++;
        }

        $this->saveDraft();
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->saveDraft();
        }
    }

    public function goToStep($step): void
    {
        $step = (int) $step;
        if ($step < 1 || $step > 8) {
            return;
        }

        // opcional: impedir "pular" etapas sem validar
        // por enquanto vamos permitir navegar para trás e até o passo atual
        if ($step <= $this->step) {
            $this->step = $step;
            $this->saveDraft();
        }
    }

    // ===============================
    // Validação por etapa
    // ===============================

    protected function validateStep(): void
    {
        $rules = match ($this->step) {
            // 1) Cliente
            1 => [
                'client.cpf' => 'required|string|min:11|max:14',
                'client.name' => 'required|string|max:255',
                'client.gender' => 'required|string|max:20',
                'client.rg' => 'nullable|string|max:20',
                'client.date_birth' => 'required|date',
                'client.phone' => 'required|string|max:20',
                'client.email' => 'nullable|email|max:255',
                'client.mom_name' => 'required|string|max:255',
                'client.marital_status' => 'required|string|max:50',
            ],

            // 2) Endereço
            2 => [
                'address.zipcode' => 'required|string|max:10',
                'address.address' => 'required|string|max:255',
                'address.number' => 'required|string|max:20',
                'address.complement' => 'nullable|string|max:255',
                'address.neighborhood' => 'required|string|max:255',
                'address.city' => 'required|string|max:255',
                'address.state' => 'required|string|size:2',
            ],

            // 3) Pedido
            3 => $this->rulesStep3(),

            // 4) Dependentes (opcional, mas se preencheu 1 campo, obriga o resto)
            4 => $this->rulesStep4Dependents(),

            // 5) Cobrança
            5 => [
                'billing.charge_type' => 'required|in:Boleto',
                'billing.charge_date' => 'required|in:10,20,30',
            ],

            // 6) Documento (OBRIGATÓRIO pra avançar)
            6 => [
                'document_file_type' => 'required|in:RG,CNH',
                'document_file' => $this->existing_document_file ? 'nullable' : 'required',
            ],

            // 7) Comprovante (OBRIGATÓRIO pra avançar)
            7 => [
                'address_proof_file' => $this->existing_address_proof_file ? 'nullable' : 'required',
            ],

            // 8) Resumo (sem regras)
            8 => [],

            default => [],
        };

        if (!empty($rules)) {
            $this->validate($rules, $this->messages());
        }

        // Etapa 6: exige doc se ainda não existe salvo
        if ($this->step === 6 && !$this->document_file && !$this->existing_document_file) {
            $this->addError('document_file', 'Envie uma imagem do documento (RG ou CNH) para continuar.');
            throw \Illuminate\Validation\ValidationException::withMessages([
                'document_file' => 'Envie uma imagem do documento (RG ou CNH) para continuar.',
            ]);
        }

        // Etapa 7: exige comprovante se ainda não existe salvo
        if ($this->step === 7 && !$this->address_proof_file && !$this->existing_address_proof_file) {
            $this->addError('address_proof_file', 'Envie uma imagem do comprovante de endereço para continuar.');
            throw \Illuminate\Validation\ValidationException::withMessages([
                'address_proof_file' => 'Envie uma imagem do comprovante de endereço para continuar.',
            ]);
        }
    }

    protected function rulesStep3(): array
    {
        $user = auth()->user();

        $rules = [
            'orderData.product_id' => 'required|integer|exists:products,id',
            'orderData.selectedAdditionals' => 'nullable|array',
            'orderData.selectedAdditionals.*' => 'integer|exists:aditionals,id',

            // ✅ obrigatórios:
            'orderData.accession' => 'required|numeric|min:0',
            'orderData.accession_payment' => 'required|string|max:50',
        ];

        if ($user->isSeller()) {
            // seller travado
            $rules['orderData.seller_id'] = 'nullable';
        } else {
            $rules['orderData.seller_id'] = 'required|integer|exists:sellers,id';
        }

        return $rules;
    }

    protected function rulesStep4Dependents(): array
    {
        $rules = [];

        if (empty($this->dependents)) {
            return $rules; // etapa opcional
        }

        foreach ($this->dependents as $index => $dep) {
            // Se o dependente está "em branco", não valida
            $hasAny =
                !empty(trim($dep['name'] ?? '')) ||
                !empty(preg_replace('/\D/', '', $dep['cpf'] ?? '')) ||
                !empty(trim($dep['rg'] ?? '')) ||
                !empty(trim($dep['date_birth'] ?? '')) ||
                !empty(trim($dep['relationship'] ?? '')) ||
                !empty(trim($dep['mom_name'] ?? '')) ||
                !empty(trim($dep['marital_status'] ?? ''));

            if (!$hasAny) {
                continue;
            }

            // ✅ Se começou, obriga campos mínimos
            $rules["dependents.{$index}.name"] = 'required|string|max:255';
            $rules["dependents.{$index}.cpf"] = 'required|string|min:11|max:14';
            $rules["dependents.{$index}.date_birth"] = 'required|date';
            $rules["dependents.{$index}.relationship"] = 'required|string|max:50';
            $rules["dependents.{$index}.mom_name"] = 'required|string|max:255';
            $rules["dependents.{$index}.marital_status"] = 'required|string|max:50';

            // opcionais
            $rules["dependents.{$index}.rg"] = 'nullable|string|max:20';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'client.cpf.required' => 'O CPF é obrigatório.',
            'client.name.required' => 'O nome é obrigatório.',
            'client.gender.required' => 'O gênero é obrigatório.',
            'client.date_birth.required' => 'A data de nascimento é obrigatória.',
            'client.phone.required' => 'O WhatsApp é obrigatório.',
            'client.mom_name.required' => 'O nome da mãe é obrigatório.',
            'client.marital_status.required' => 'O estado civil é obrigatório.',

            'address.zipcode.required' => 'O CEP é obrigatório.',
            'address.address.required' => 'O endereço é obrigatório.',
            'address.number.required' => 'O número é obrigatório.',
            'address.neighborhood.required' => 'O bairro é obrigatório.',
            'address.city.required' => 'A cidade é obrigatória.',
            'address.state.required' => 'O estado é obrigatório.',

            'orderData.seller_id.required' => 'Selecione o consultor.',
            'orderData.product_id.required' => 'Selecione o produto.',
            'orderData.accession.required' => 'O valor da adesão é obrigatório.',
            'orderData.accession.numeric' => 'O valor da adesão precisa ser numérico.',
            'orderData.accession_payment.required' => 'O pagamento da adesão é obrigatório.',

            'billing.charge_type.required' => 'Selecione o tipo de cobrança.',
            'billing.charge_date.required' => 'Informe o dia de pagamento.',

            'document_file.required' => 'Envie o documento (RG/CNH).',
            'address_proof_file.required' => 'Envie o comprovante de endereço.',
        ];
    }

    // ===============================
    // Draft
    // ===============================

    protected function saveDraft(): void
    {
        $user = auth()->user();

        // Resolve seller/group para facilitar filtros futuros
        $resolvedSellerId = $this->resolveDraftSellerId();
        $resolvedGroupId = $this->resolveDraftGroupId($resolvedSellerId);

        $payload = [
            'client' => $this->client,
            'address' => $this->address,
            'orderData' => $this->orderData,
            'dependents' => $this->dependents,
            'billing' => $this->billing,
        ];

        $attributes = [
            'user_id' => $user->id,
            'group_id' => $resolvedGroupId,
            'seller_id' => $resolvedSellerId,
            'status' => 'EM_PREENCHIMENTO',
            'current_step' => $this->step,
            'payload' => $payload,
            'document_file' => $this->existing_document_file,
            'document_file_type' => $this->document_file_type,
            'address_proof_file' => $this->existing_address_proof_file,
            'last_interaction_at' => now(),
        ];

        if ($this->draftId) {
            $draft = OrderDraft::find($this->draftId);

            if ($draft) {
                $draft->update($attributes);
            } else {
                $draft = OrderDraft::create($attributes);
            }
        } else {
            $draft = OrderDraft::create($attributes);
        }

        $this->draftId = $draft->id;
    }

    protected function resolveDraftSellerId(): ?int
    {
        $user = auth()->user();

        if ($user->isSeller() && method_exists($user, 'currentSellerId')) {
            return (int) $user->currentSellerId();
        }

        return !empty($this->orderData['seller_id']) ? (int) $this->orderData['seller_id'] : null;
    }

    protected function resolveDraftGroupId(?int $sellerId = null): ?int
    {
        $user = auth()->user();

        if ($user->isCoop()) {
            $allowed = $user->getAccessibleGroupIds();
            return !empty($allowed) ? (int) $allowed[0] : null;
        }

        if ($user->isSeller()) {
            $sellerId = $sellerId ?: (method_exists($user, 'currentSellerId') ? $user->currentSellerId() : null);
        }

        if ($sellerId) {
            $seller = Seller::find($sellerId);
            return $seller?->group_id ? (int) $seller->group_id : null;
        }

        return null;
    }

    public function saveAndExit()
    {
        $this->saveDraft();

        session()->flash('message', 'Rascunho salvo com sucesso!');

        return redirect()->route('admin.orders.index');
    }

    // ===============================
    // CPF / Cliente
    // ===============================

    public function lookupClientByCpf(): void
    {
        $cpf = preg_replace('/\D/', '', $this->client['cpf'] ?? '');

        $this->clientFound = false;
        $this->clientLookupMessage = null;

        if (strlen($cpf) !== 11) {
            $this->clientLookupMessage = 'Informe um CPF válido com 11 dígitos.';
            return;
        }

        $user = auth()->user();

        $query = Client::query()->where('cpf', $cpf);

        if ($user->isCoop()) {
            $query->whereIn('group_id', $user->getAccessibleGroupIds());
        }

        if ($user->isSeller()) {
            $query->whereIn('group_id', $user->getAccessibleGroupIds());
        }

        $client = $query->first();

        if (!$client) {
            $this->clientLookupMessage = 'Cliente não encontrado. Você pode continuar preenchendo normalmente.';
            return;
        }

        $this->clientFound = true;
        $this->clientLookupMessage = 'Cliente encontrado. Os campos foram preenchidos e podem ser editados.';

        // Preenche dados do cliente
        $this->client['name'] = $client->name ?? '';
        $this->client['gender'] = $client->gender ?? '';
        $this->client['rg'] = $client->rg ?? '';
        $this->client['date_birth'] = $client->date_birth ? date('Y-m-d', strtotime($client->date_birth)) : '';
        $this->client['phone'] = $client->phone ?? '';
        $this->client['email'] = $client->email ?? '';
        $this->client['mom_name'] = $client->mom_name ?? '';
        $this->client['marital_status'] = $client->marital_status ?? '';

        // Preenche endereço
        $this->address['zipcode'] = $client->zipcode ?? '';
        $this->address['address'] = $client->address ?? '';
        $this->address['number'] = $client->number ?? '';
        $this->address['complement'] = $client->complement ?? '';
        $this->address['neighborhood'] = $client->neighborhood ?? '';
        $this->address['city'] = $client->city ?? '';
        $this->address['state'] = $client->state ?? '';

        $this->saveDraft();
    }

    public function updatedClientCpf($value): void
    {
        // mantém só para UX; busca manual via botão evita consulta a cada tecla
        $this->client['cpf'] = $value;
    }

    // ===============================
    // Dependentes (etapa 4)
    // ===============================

    public function addDependent(): void
    {
       $productId = $this->orderData['product_id'] ?? null;

        if (!$productId) {
            session()->flash('error', 'É necessário selecionar o produto antes de adicionar dependentes.');
            return;
        }

        $product = \App\Models\Product::find($productId);

        if (!$product) {
            session()->flash('error', 'Produto não encontrado.');
            return;
        }

        if ((int) $product->dependents_limit <= 0) {
            session()->flash('error', 'O produto selecionado não permite dependentes.');
            return;
        }

        if (count($this->dependents) >= (int) $product->dependents_limit) {
            session()->flash('error', "O produto selecionado permite apenas {$product->dependents_limit} dependente(s).");
            return;
        }

        $this->dependents[] = [
            'name' => '',
            'mom_name' => '',
            'date_birth' => '',
            'cpf' => '',
            'rg' => '',
            'marital_status' => '',
            'relationship' => '',
            'additionals' => [],
        ];

        $this->recalculateEasyTotal();
        $this->saveDraft();
    }

    public function removeDependent($index): void
    {
        if (isset($this->dependents[$index])) {
            unset($this->dependents[$index]);
            $this->dependents = array_values($this->dependents);

            $this->recalculateEasyTotal();
            $this->saveDraft();
        }
    }

    // ===============================
    // Uploads (etapas 6 e 7)
    // ===============================

    public function updatedDocumentFile()
    {
        $this->validate([
            'document_file' => 'nullable|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        if ($this->document_file) {
            if ($this->existing_document_file && Storage::disk('public')->exists($this->existing_document_file)) {
                Storage::disk('public')->delete($this->existing_document_file);
            }

            $this->existing_document_file = $this->document_file->store('order_drafts/documents', 'public');
            $this->saveDraft();
        }
    }

    public function updatedAddressProofFile()
    {
        $this->validate([
            'address_proof_file' => 'nullable|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        if ($this->address_proof_file) {
            if ($this->existing_address_proof_file && Storage::disk('public')->exists($this->existing_address_proof_file)) {
                Storage::disk('public')->delete($this->existing_address_proof_file);
            }

            $this->existing_address_proof_file = $this->address_proof_file->store('order_drafts/address_proofs', 'public');
            $this->saveDraft();
        }
    }

    // ===============================
    // Finalização (Etapa 8)
    // ===============================

    public function submitOrder()
    {
        $this->authorize('create', Order::class);

        // 1) Validar etapas necessárias antes de finalizar (inclui uploads)
        $currentStepBackup = $this->step;

        foreach ([1, 2, 3, 4, 5, 6, 7] as $stepToValidate) {
            $this->step = $stepToValidate;
            $this->validateStep();
        }

        $backup = $this->step;

        $this->step = 6;
        $this->validateStep();

        if ($this->document_file) {
            $this->validate([
                'document_file' => 'mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ], $this->messages());
        }

        $this->step = 7;
        $this->validateStep();

        if ($this->address_proof_file) {
            $this->validate([
                'address_proof_file' => 'mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ], $this->messages());
        }

        $this->step = $currentStepBackup;

        // 2) Se existirem dependentes parcialmente preenchidos, obrigar os campos mínimos
        $dependentsToSave = [];
        foreach (($this->dependents ?? []) as $idx => $dep) {
            $hasAny =
                !empty(trim($dep['name'] ?? '')) ||
                !empty(trim($dep['cpf'] ?? '')) ||
                !empty(trim($dep['rg'] ?? '')) ||
                !empty(trim($dep['date_birth'] ?? '')) ||
                !empty(trim($dep['relationship'] ?? '')) ||
                !empty(trim($dep['mom_name'] ?? ''));

            if (!$hasAny) {
                continue;
            }

            // valida mínimo para não estourar no final
            if (empty(trim($dep['cpf'] ?? ''))) {
                $this->addError("dependents.$idx.cpf", "Informe o CPF do dependente " . ($idx + 1) . ".");
            }
            if (empty(trim($dep['name'] ?? ''))) {
                $this->addError("dependents.$idx.name", "Informe o nome do dependente " . ($idx + 1) . ".");
            }
            if (empty(trim($dep['date_birth'] ?? ''))) {
                $this->addError("dependents.$idx.date_birth", "Informe a data de nascimento do dependente " . ($idx + 1) . ".");
            }
            if (empty(trim($dep['relationship'] ?? ''))) {
                $this->addError("dependents.$idx.relationship", "Informe o parentesco do dependente " . ($idx + 1) . ".");
            }

            $dependentsToSave[] = $dep;
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            // mantém na etapa atual e mostra os erros
            throw ValidationException::withMessages($this->getErrorBag()->toArray());
        }

        // 3) Salvar draft antes de criar o pedido final
        $this->saveDraft();

        try {
            DB::beginTransaction();

            $user = auth()->user();

            // Resolver seller / group no mesmo padrão do saveOrder
            $sellerId = $this->resolveDraftSellerId();
            $groupId  = $this->resolveDraftGroupId($sellerId);

            if ($user->isAdmin()) {
                if (empty($sellerId)) {
                    throw new \Exception('Selecione um consultor.');
                }
                $seller = Seller::findOrFail($sellerId);
                $groupId = (int) $seller->group_id;
            }

            if ($user->isCoop()) {
                $allowedGroupIds = $user->getAccessibleGroupIds();
                if (empty($allowedGroupIds)) {
                    throw new \Exception('Usuário COOP sem cooperativa vinculada.');
                }

                $groupId = (int) $allowedGroupIds[0];

                if (!empty($sellerId)) {
                    $seller = Seller::query()
                        ->where('id', $sellerId)
                        ->where('group_id', $groupId)
                        ->first();

                    if (! $seller) {
                        throw new \Exception('Consultor inválido para esta cooperativa.');
                    }
                }
            }

            if ($user->isSeller()) {
                $seller = Seller::findOrFail($user->currentSellerId());
                $sellerId = (int) $seller->id;
                $groupId  = (int) $seller->group_id;
            }

            // Cliente (limpeza)
            $cpf   = preg_replace('/\D/', '', $this->client['cpf'] ?? '');
            $rg    = preg_replace('/\D/', '', $this->client['rg'] ?? '');
            $phone = preg_replace('/\D/', '', $this->client['phone'] ?? '');
            $zip   = preg_replace('/\D/', '', $this->address['zipcode'] ?? '');

            if (strlen($cpf) !== 11) {
                throw new \Exception('CPF inválido.');
            }

            // Atualiza/cria cliente (cpf + group_id)
            $client = Client::updateOrCreate(
                [
                    'cpf' => $cpf,
                    'group_id' => $groupId,
                ],
                [
                    'group_id' => $groupId,
                    'name' => $this->client['name'] ?? '',
                    'mom_name' => $this->client['mom_name'] ?? '',
                    'date_birth' => $this->client['date_birth'] ?? null,
                    'rg' => $rg,
                    'gender' => $this->client['gender'] ?? '',
                    'marital_status' => $this->client['marital_status'] ?? '',
                    'phone' => $phone,
                    'email' => $this->client['email'] ?? null,

                    'zipcode' => $zip,
                    'address' => $this->address['address'] ?? '',
                    'number' => $this->address['number'] ?? '',
                    'complement' => $this->address['complement'] ?? null,
                    'neighborhood' => $this->address['neighborhood'] ?? '',
                    'city' => $this->address['city'] ?? '',
                    'state' => $this->address['state'] ?? '',

                    'obs' => '',
                    'status' => 1,
                ]
            );

            // Produto
            $productId = (int) ($this->orderData['product_id'] ?? 0);
            $product = Product::findOrFail($productId);

            // Criar pedido (Order)
            $order = Order::create([
                'client_id' => $client->id,
                'product_id' => $productId,
                'group_id' => $groupId,
                'seller_id' => $sellerId,

                'charge_type' => $this->billing['charge_type'] ?? 'Boleto',
                'charge_date' => $this->billing['charge_date'] ?? null,

                'accession' => $this->orderData['accession'] ?? null,
                'accession_payment' => $this->orderData['accession_payment'] ?? null,

                // pendente até admin abrir (você já marca como visto no OrderEdit)
                'review_status' => 'PENDENTE',
                'admin_viewed_at' => null,
            ]);

            // OrderPrice (não usar dependent_value — você removeu isso)
            OrderPrice::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'product_value' => $product->value,
            ]);

            // Adicionais do titular
            $selectedAdditionals = $this->orderData['selectedAdditionals'] ?? [];
            if (!empty($selectedAdditionals)) {
                foreach ($selectedAdditionals as $aditionalId) {
                    $aditionalId = (int) $aditionalId;

                    // no EasyForm, $this->additionals vem de product_aditionals com "value"
                    $aditional = collect($this->additionals)->firstWhere('id', $aditionalId);

                    if ($aditional) {
                        OrderAditional::create([
                            'order_id' => $order->id,
                            'aditional_id' => $aditionalId,
                            'value' => (float) ($aditional['value'] ?? 0),
                        ]);
                    }
                }
            }

            // Dependentes + adicionais de dependente
            $dependentIds = [];

            foreach ($dependentsToSave as $dep) {
                $depCpf = preg_replace('/\D/', '', $dep['cpf'] ?? '');
                $depRg  = preg_replace('/\D/', '', $dep['rg'] ?? '');

                $dependent = Dependent::updateOrCreate(
                    ['cpf' => $depCpf],
                    [
                        'client_id' => $client->id,
                        'name' => $dep['name'] ?? '',
                        'mom_name' => $dep['mom_name'] ?? '',
                        'date_birth' => $dep['date_birth'] ?? null,
                        'cpf' => $depCpf,
                        'rg' => $depRg,
                        'marital_status' => $dep['marital_status'] ?? null,
                        'relationship' => $dep['relationship'] ?? null,
                    ]
                );

                $dependentIds[] = $dependent->id;

                OrderDependent::create([
                    'order_id' => $order->id,
                    'dependent_id' => $dependent->id,
                ]);

                $depAdditionals = $dep['additionals'] ?? [];
                if (!empty($depAdditionals) && is_array($depAdditionals)) {
                    foreach ($depAdditionals as $aditionalId) {
                        $aditionalId = (int) $aditionalId;
                        $aditional = collect($this->additionals)->firstWhere('id', $aditionalId);

                        if ($aditional) {
                            OrderAditionalDependent::create([
                                'order_id' => $order->id,
                                'dependent_id' => $dependent->id,
                                'aditional_id' => $aditionalId,
                                'value' => (float) ($aditional['value'] ?? 0),
                            ]);
                        }
                    }
                }
            }

            // Uploads: copiar do draft (order_drafts/...) para orders/... e gravar no Order
            // Assim você pode apagar draft depois sem perder arquivos.
            $docPath = $this->existing_document_file;
            if ($docPath && Storage::disk('public')->exists($docPath)) {
                $newDocPath = 'orders/documents/' . basename($docPath);
                if (!Storage::disk('public')->exists($newDocPath)) {
                    Storage::disk('public')->copy($docPath, $newDocPath);
                }
                $order->document_file = $newDocPath;
                $order->document_file_type = $this->document_file_type ?: 'RG';
            }

            $proofPath = $this->existing_address_proof_file;
            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                $newProofPath = 'orders/address_proofs/' . basename($proofPath);
                if (!Storage::disk('public')->exists($newProofPath)) {
                    Storage::disk('public')->copy($proofPath, $newProofPath);
                }
                $order->address_proof_file = $newProofPath;
            }

            $order->save();

            // Financeiro: cria 1 registro usando o total real calculado
            // Regra: valor = product_value + soma adicionais (titular + dependentes)
            $orderTotal = (float) $this->total;

            // define próximo vencimento com base no dia escolhido
            $chargeDay = (int) ($this->billing['charge_date'] ?? 0);
            if ($chargeDay < 1 || $chargeDay > 31) {
                $chargeDay = 10;
            }

            $due = Carbon::now();
            // tenta manter no mês atual, senão joga para o próximo mês
            $candidate = $due->copy()->day(min($chargeDay, $due->daysInMonth));
            if ($candidate->isPast()) {
                $candidate = $due->copy()->addMonthNoOverflow()->day(min($chargeDay, $due->copy()->addMonthNoOverflow()->daysInMonth));
            }

            Financial::create([
                'order_id' => $order->id,
                'value' => $orderTotal,
                'status' => 0,
                'due_date' => $candidate->toDateString(), // se sua coluna for date/datetime
            ]);

            // Atualizar draft como convertido (e vincular order_id se existir)
            if ($this->draftId) {
                $draft = OrderDraft::find($this->draftId);
                if ($draft) {
                    $draft->status = 'CONVERTIDO';
                    $draft->current_step = 8;

                    if (Schema::hasColumn($draft->getTable(), 'order_id')) {
                        $draft->order_id = $order->id;
                    }

                    $draft->save();
                }
            }

            DB::commit();

            session()->flash('message', 'Pedido criado com sucesso!');
            return redirect()->route('admin.orders.edit', $order->id);

        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao criar pedido: ' . $e->getMessage());
            return;
        }
    }

    public function buildSubmitPayload(): array
    {
        return [
            'client' => $this->client,
            'address' => $this->address,
            'orderData' => $this->orderData,
            'dependents' => $this->dependents,
            'billing' => $this->billing,
            'document_file' => $this->existing_document_file,
            'document_file_type' => $this->document_file_type,
            'address_proof_file' => $this->existing_address_proof_file,
            'draft_id' => $this->draftId,
        ];
    }

    // ===============================
    // Helpers de UI
    // ===============================

    public function isSellerUser(): bool
    {
        return auth()->check() && auth()->user()->isSeller();
    }

    public function getSelectedProductNameProperty(): ?string
    {
        $productId = $this->orderData['product_id'] ?? null;
        if (!$productId) return null;

        $product = collect($this->products)->firstWhere('id', (int) $productId);
        return $product['name'] ?? null;
    }

    public function updatedOrderDataProductId($value)
    {
        $this->loadAdditionalsForEasyForm();
    }

    public function loadAdditionalsForEasyForm()
    {
        $productId = (int) ($this->orderData['product_id'] ?? 0);

        // quando muda o produto, zera os adicionais selecionados (titular e dependentes)
        $this->orderData['selectedAdditionals'] = [];

        foreach ($this->dependents as $i => $dep) {
            $this->dependents[$i]['additionals'] = [];
        }

        if (!$productId) {
            $this->additionals = [];
            $this->recalculateEasyTotal();
            $this->saveDraft();
            return;
        }

        $product = Product::find($productId);

        if (!$product) {
            $this->additionals = [];
            $this->recalculateEasyTotal();
            $this->saveDraft();
            return;
        }

        // ✅ SOMENTE adicionais vinculados ao produto
        $this->additionals = Aditional::query()
            ->select('aditionals.id', 'aditionals.name', 'product_aditionals.value')
            ->join('product_aditionals', 'product_aditionals.aditional_id', '=', 'aditionals.id')
            ->where('product_aditionals.product_id', $productId)
            ->where('aditionals.status', 1)
            ->orderBy('aditionals.name')
            ->get()
            ->toArray();

        $this->recalculateEasyTotal();
        $this->saveDraft();
    }

    public function updatedOrderDataSelectedAdditionals()
    {
        $this->recalculateEasyTotal();
        $this->saveDraft();
    }

    public function calculateEasyAdditionalTotal()
    {
        $additionalTotal = 0;

        foreach (($this->orderData['selectedAdditionals'] ?? []) as $additionalId) {
            $additional = collect($this->additionals)->firstWhere('id', (int) $additionalId);

            if ($additional) {
                $additionalTotal += (float) ($additional['value'] ?? 0);
            }
        }

        return $additionalTotal;
    }

    public function calculateEasyDependentsTotal()
    {
        $dependentTotal = 0;

        foreach ($this->dependents as $dependent) {
            if (isset($dependent['additionals']) && is_array($dependent['additionals'])) {
                foreach ($dependent['additionals'] as $additionalId) {
                    $additional = collect($this->additionals)->firstWhere('id', (int) $additionalId);

                    if ($additional) {
                        $dependentTotal += (float) ($additional['value'] ?? 0);
                    }
                }
            }
        }

        return $dependentTotal;
    }

    public function recalculateEasyTotal()
    {
        $this->total = 0;

        $productId = $this->orderData['product_id'] ?? null;
        if ($productId) {
            $product = \App\Models\Product::find($productId);
            if ($product) {
                $this->total += (float) $product->value;
            }
        }

        $this->total += $this->calculateEasyAdditionalTotal();
        $this->total += $this->calculateEasyDependentsTotal();
    }

    public function fetchAddressByCep()
    {
        $cep = preg_replace('/\D/', '', $this->address['zipcode'] ?? '');

        if (strlen($cep) !== 8) {
            $this->addError('address.zipcode', 'Informe um CEP válido com 8 dígitos.');
            return;
        }

        try {
            $response = Http::timeout(10)->get("https://viacep.com.br/ws/{$cep}/json/");

            if (!$response->ok()) {
                session()->flash('error', 'Não foi possível consultar o CEP.');
                return;
            }

            $data = $response->json();

            if (isset($data['erro']) && $data['erro'] === true) {
                session()->flash('error', 'CEP não encontrado.');
                return;
            }

            $this->address['address'] = $data['logradouro'] ?? $this->address['address'];
            $this->address['neighborhood'] = $data['bairro'] ?? $this->address['neighborhood'];
            $this->address['city'] = $data['localidade'] ?? $this->address['city'];
            $this->address['state'] = $data['uf'] ?? $this->address['state'];
            $this->address['complement'] = $data['complemento'] ?? $this->address['complement'];

            $this->saveDraft();

        } catch (\Throwable $e) {
            session()->flash('error', 'Erro ao consultar CEP: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.order-easy-form');
    }
    
}