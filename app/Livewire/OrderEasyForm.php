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

        // Adicionais ativos (ajuste se houver filtro por produto depois)
        if (class_exists(Aditional::class)) {
            $this->additionals = Aditional::query()
                ->where('status', 1)
                ->orderBy('name')
                ->get()
                ->toArray();
        }

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
        $this->validateStep();
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
            1 => [
                'client.cpf' => 'required|string|min:11|max:14',
                'client.name' => 'required|string|max:255',
                'client.gender' => 'required|string|max:20',
                'client.rg' => 'nullable|string|max:20',
                'client.date_birth' => 'required|date',
                'client.phone' => 'required|string|max:20',
                'client.email' => 'nullable|email|max:255',
            ],
            2 => [
                'address.zipcode' => 'required|string|max:10',
                'address.address' => 'required|string|max:255',
                'address.number' => 'required|string|max:20',
                'address.complement' => 'nullable|string|max:255',
                'address.neighborhood' => 'required|string|max:255',
                'address.city' => 'required|string|max:255',
                'address.state' => 'required|string|max:2',
            ],
            3 => $this->rulesStep3(),
            4 => [
                // Dependentes opcionais; se houver, validar cada item
                'dependents.*.name' => 'nullable|string|max:255',
                'dependents.*.cpf' => 'nullable|string|max:14',
                'dependents.*.rg' => 'nullable|string|max:20',
                'dependents.*.date_birth' => 'nullable|date',
                'dependents.*.relationship' => 'nullable|string|max:50',
                'dependents.*.mom_name' => 'nullable|string|max:255',
                'dependents.*.marital_status' => 'nullable|string|max:50',
            ],
            5 => [
                'billing.charge_type' => 'required|in:Boleto',
                'billing.charge_date' => 'required',
            ],
            6 => [
                'document_file_type' => 'required|in:RG,CNH',
                // opcional por enquanto (se quiser obrigatório, troque para required sem nullable)
                // se já existe arquivo salvo no draft, não precisa exigir novo upload
            ],
            7 => [
                // idem etapa 6
            ],
            8 => [],
            default => [],
        };

        if (!empty($rules)) {
            $this->validate($rules, $this->messages());
        }

        // Regras adicionais de etapa 6/7 com fallback em arquivo já salvo
        if ($this->step === 6 && !$this->document_file && !$this->existing_document_file) {
            // Se quiser obrigatório já agora, descomente:
            // $this->addError('document_file', 'Envie uma imagem do documento (RG/CNH).');
            // throw new \Illuminate\Validation\ValidationException(validator([], []));
        }

        if ($this->step === 7 && !$this->address_proof_file && !$this->existing_address_proof_file) {
            // Se quiser obrigatório já agora, descomente:
            // $this->addError('address_proof_file', 'Envie uma imagem do comprovante de endereço.');
            // throw new \Illuminate\Validation\ValidationException(validator([], []));
        }
    }

    protected function rulesStep3(): array
    {
        $user = auth()->user();

        $rules = [
            'orderData.product_id' => 'required|integer|exists:products,id',
            'orderData.selectedAdditionals' => 'nullable|array',
            'orderData.selectedAdditionals.*' => 'integer|exists:aditionals,id',
            'orderData.accession' => 'nullable',
            'orderData.accession_payment' => 'nullable|string|max:50',
        ];

        if ($user->isSeller()) {
            // seller será travado automaticamente
            $rules['orderData.seller_id'] = 'nullable';
        } else {
            $rules['orderData.seller_id'] = 'required|integer|exists:sellers,id';
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

            'address.zipcode.required' => 'O CEP é obrigatório.',
            'address.address.required' => 'O endereço é obrigatório.',
            'address.number.required' => 'O número é obrigatório.',
            'address.neighborhood.required' => 'O bairro é obrigatório.',
            'address.city.required' => 'A cidade é obrigatória.',
            'address.state.required' => 'O estado é obrigatório.',

            'orderData.seller_id.required' => 'Selecione o consultor.',
            'orderData.product_id.required' => 'Selecione o produto.',

            'billing.charge_type.required' => 'Selecione o tipo de cobrança.',
            'billing.charge_date.required' => 'Informe o dia de pagamento.',
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
            'document_file' => 'nullable|image|max:5120',
        ]);

        if ($this->document_file) {
            // remove arquivo anterior salvo no draft, se existir
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
            'address_proof_file' => 'nullable|image|max:5120',
        ]);

        if ($this->address_proof_file) {
            // remove arquivo anterior salvo no draft, se existir
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

        // Valida principais etapas antes de finalizar
        $currentStepBackup = $this->step;

        foreach ([1, 2, 3, 5] as $stepToValidate) {
            $this->step = $stepToValidate;
            $this->validateStep();
        }

        $this->step = $currentStepBackup;

        try {
            DB::beginTransaction();

            /**
             * PONTO DE INTEGRAÇÃO RECOMENDADO:
             * Aqui você deve chamar um OrderCreationService reaproveitando sua lógica do OrderForm.
             *
             * Exemplo futuro:
             * $service = app(\App\Services\OrderCreationService::class);
             * $order = $service->createFromEasyDraft($this->buildSubmitPayload(), auth()->user());
             */

            // Enquanto você ainda não extraiu o service, vamos salvar/atualizar draft e avisar:
            $this->saveDraft();

            // Se quiser já mudar status do draft para "ENVIADO" após integrar o service:
            // OrderDraft::where('id', $this->draftId)->update(['status' => 'ENVIADO']);

            DB::commit();

            session()->flash('message', 'Resumo pronto. Próximo passo: integrar a criação final do pedido (OrderCreationService).');
        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao finalizar cadastro facilitado: ' . $e->getMessage());
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
        $productId = $this->orderData['product_id'] ?? null;

        if ($productId) {
            $product = \App\Models\Product::find($productId);

            if ($product) {
                $this->total = (float) $product->value;

                $this->additionals = \App\Models\Aditional::select('aditionals.*', 'product_aditionals.value')
                    ->join('product_aditionals', 'product_aditionals.aditional_id', '=', 'aditionals.id')
                    ->where('product_aditionals.product_id', $productId)
                    ->get()
                    ->toArray();
            } else {
                $this->total = 0;
                $this->additionals = [];
            }
        } else {
            $this->total = 0;
            $this->additionals = [];
        }

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