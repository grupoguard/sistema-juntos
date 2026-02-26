<?php

namespace App\Livewire;

use App\Models\Aditional;
use App\Models\Client;
use App\Models\Dependent;
use App\Models\Order;
use App\Models\OrderAditional;
use App\Models\OrderAditionalDependent;
use App\Models\OrderDependent;
use App\Models\OrderPrice;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Financial;
use App\Services\EdpService;
use App\Traits\OrderFormTrait;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class OrderEdit extends Component
{
    use WithFileUploads, OrderFormTrait, AuthorizesRequests;

    public $data;

    public $charge_type;
    public $review_notes;
    public $financials = [];

    public $document_file; // novo upload RG/CNH
    public $document_file_type = 'RG'; // RG ou CNH
    public $address_proof_file; // novo upload comprovante

    public $existing_document_file; // path atual
    public $existing_document_file_type; // tipo atual
    public $existing_address_proof_file; // path atual

    protected function rules()
    {
        $rules = [
            'client.name' => 'required|string|max:100',
            'client.mom_name' => 'required|string|max:100',
            'client.date_birth' => 'required|date',
            'client.email' => 'required|email|max:50',
            'client.gender' => 'required|string|max:15',
            'client.marital_status' => 'required|string|max:50',
            'client.phone' => 'nullable|string',
            'client.zipcode' => 'required|string',
            'client.address' => 'required|string|max:100',
            'client.number' => 'required|string|max:10',
            'client.complement' => 'nullable|string|max:40',
            'client.neighborhood' => 'required|string|max:50',
            'client.city' => 'required|string|max:50',
            'client.state' => 'required|string|max:2',
            'product_id' => 'required|integer',
            'seller_id' => 'required|integer',
            'accession' => 'required|numeric|min:0',
            'accession_payment' => 'required|string|max:50',
            'discount_type' => 'nullable|string|in:R$,%',
            'discount_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'document_file' => 'nullable|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'document_file_type' => 'nullable|in:RG,CNH',
            'address_proof_file' => 'nullable|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ];

        if ($this->discount_type === '%') {
            $rules['discount_value'][] = 'max:100';
        }

        if (!empty($this->dependents)) {
            foreach ($this->dependents as $index => $dependent) {
                $rules["dependents.{$index}.cpf"] = 'required|string';
                $rules["dependents.{$index}.name"] = 'required|string|max:100';
                $rules["dependents.{$index}.mom_name"] = 'required|string|max:100';
                $rules["dependents.{$index}.date_birth"] = 'required|date';
                $rules["dependents.{$index}.marital_status"] = 'required|string|max:50';
                $rules["dependents.{$index}.relationship"] = 'required|string|max:50';
                $rules["dependents.{$index}.rg"] = 'nullable|string';
                $rules["dependents.{$index}.additionals"] = 'nullable|array';
                $rules["dependents.{$index}.additionals.*"] = 'nullable|integer';
            }
        }

        return $rules;
    }

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->order = Order::findOrFail($orderId);

        $user = auth()->user();

        if ($user->isAdmin()) {
            $this->order->markAsViewedBy($user);
            $this->order->refresh();
        }

        // trava acesso ao pedido específico
        $this->authorize('view', $this->order);
        $this->authorize('update', $this->order);

        $this->client = $this->order->client->toArray();

        // seller/coop/admin devem enxergar listas limitadas também
        $user = auth()->user();

        $this->sellers = Seller::query()
            ->when($user->isCoop(), fn ($q) => $q->whereIn('group_id', $user->getAccessibleGroupIds()))
            ->when($user->isSeller(), fn ($q) => $q->whereIn('id', $user->getAccessibleSellerIds()))
            ->orderBy('name')
            ->get();

        // produtos por enquanto como está (depois refinamos)
        $this->products = Product::orderByDesc('status')->orderBy('name')->get();

        $this->charge_date = $this->order->charge_date;

        $this->financials = Financial::where('order_id', $this->orderId)
            ->orderBy('due_date', 'asc') // mais antigo -> mais novo
            ->get();
        // Carregar dependentes COM seus adicionais
        $this->dependents = OrderDependent::where('order_id', $orderId)
            ->get()
            ->map(function ($item) {
                // Buscar adicionais deste dependente
                $dependentAdditionals = OrderAditionalDependent::where('order_id', $this->orderId)
                    ->where('dependent_id', $item->dependent_id)
                    ->pluck('aditional_id')
                    ->toArray();
                
                return [
                    'id' => $item->id,
                    'dependent_id' => $item->dependent_id, // IMPORTANTE: adicionar o ID do dependente
                    'name' => $item->dependent->name ?? '',
                    'relationship' => isset($item->dependent->relationship)
                        ? mb_strtolower($item->dependent->relationship)
                        : '',
                    'cpf' => $item->dependent->cpf ?? '',
                    'rg' => $item->dependent->rg ?? '',
                    'date_birth' => $item->dependent->date_birth ?? '',
                    'marital_status' => $item->dependent->marital_status
                        ? mb_strtolower($item->dependent->marital_status)
                        : 'nao_informado',
                    'mom_name' => $item->dependent->mom_name ?? '',
                    'additionals' => $dependentAdditionals, // Adicionar os adicionais
                ];
            })
            ->toArray();

        // Cliente
        $this->client_id = $this->order->client_id;
        $this->client = $this->order->client->toArray();
        $this->client['gender'] = isset($this->client['gender'])
            ? mb_strtolower($this->client['gender'])
            : null;
        $this->client['marital_status'] = isset($this->client['marital_status'])
            ? mb_strtolower($this->client['marital_status'])
            : null;

        // Listas
        $this->products = Product::orderByDesc('status')->orderBy('name')->get();
        
        // Pedido
        $this->seller_id = (string) $this->order->seller_id;
        $this->product_id = (string) $this->order->product_id;
        $this->charge_type = $this->order->charge_type;
        $this->installation_number = $this->order->installation_number;
        $this->approval_name = $this->order->approval_name;
        $this->approval_by = $this->order->approval_by;
        $this->evidence_date = $this->order->evidence_date;
        $this->total = $this->order->orderPrice->product_value + $this->order->dependents_value;
        $this->charge_date = $this->order->charge_date;
        $this->accession = $this->order->accession ?? 0.00;
        $this->accession_payment = $this->order->accession_payment ?? 'Não cobrada';
        $this->discount_type = $this->order->discount_type;
        $this->discount_value = $this->order->discount_value ?? 0.00;
        $this->existing_document_file = $this->order->document_file;
        $this->existing_document_file_type = $this->order->document_file_type ?: 'RG';
        $this->existing_address_proof_file = $this->order->address_proof_file;
        // default do select pra manter o tipo atual
        $this->document_file_type = $this->existing_document_file_type;

        // Carregar adicionais do produto
        $this->loadAdditionals();

        // Carregar adicionais selecionados (do titular)
        $this->selectedAdditionals = OrderAditional::where('order_id', $this->orderId)
            ->pluck('aditional_id')
            ->toArray();

        // Calcular total
        $this->recalculateTotal();

        $this->dispatch('order-loaded');
    }

    public function approveOrder()
    {
        $order = Order::findOrFail($this->orderId);
        $this->authorize('update', $order); // ou policy específica de revisão

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->update([
            'review_status' => 'APROVADO',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'review_notes' => $this->review_notes ?? null,
        ]);

        session()->flash('message', 'Pedido aprovado com sucesso!');
    }

    public function rejectOrder()
    {
        $order = Order::findOrFail($this->orderId);
        $this->authorize('update', $order);

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->update([
            'review_status' => 'REJEITADO',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'review_notes' => $this->review_notes ?? null,
        ]);

        session()->flash('message', 'Pedido rejeitado com sucesso!');
    }
        
    public function updateOrder(EdpService $edpService)
    {
        DB::beginTransaction();

        \Log::info('updateOrder chamado', [
            'orderId' => $this->orderId,
            'product_id' => $this->product_id,
            'client_name' => $this->client['name'],
        ]);

        try {
            $this->validate($this->rules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erro de validação:', $e->errors());
            session()->flash('error', 'Erro de validação: ' . json_encode($e->errors()));
            return;
        }

        try {
            $order = Order::findOrFail($this->orderId);
            $this->authorize('update', $order);
            \Log::info('Order encontrado', ['order_id' => $order->id]);
            
            // Atualizar cliente
            $phone = preg_replace('/\D/', '', $this->client['phone']);
            $client = Client::findOrFail($order->client_id);
            
            \Log::info('Dados do cliente antes', $client->toArray());
            \Log::info('Dados do cliente para atualizar', $this->client);
            
            $client->update([
                'name' => $this->client['name'],
                'mom_name' => $this->client['mom_name'],
                'date_birth' => $this->client['date_birth'],
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
            ]);

            // Substituir documento (RG/CNH)
            if ($this->document_file) {
                // apaga arquivo antigo (se existir)
                if ($order->document_file && Storage::disk('public')->exists($order->document_file)) {
                    Storage::disk('public')->delete($order->document_file);
                }

                $documentPath = $this->document_file->store('orders/documents', 'public');

                $order->update([
                    'document_file' => $documentPath,
                    'document_file_type' => $this->document_file_type ?: 'RG',
                ]);

                $this->existing_document_file = $documentPath;
                $this->existing_document_file_type = $this->document_file_type ?: 'RG';
            }

            // Substituir comprovante de endereço
            if ($this->address_proof_file) {
                if ($order->address_proof_file && Storage::disk('public')->exists($order->address_proof_file)) {
                    Storage::disk('public')->delete($order->address_proof_file);
                }

                $addressProofPath = $this->address_proof_file->store('orders/address_proofs', 'public');

                $order->update([
                    'address_proof_file' => $addressProofPath,
                ]);

                $this->existing_address_proof_file = $addressProofPath;
            }
                        
            \Log::info('Dados do cliente depois', $client->fresh()->toArray());

            // LOGS DOS DEPENDENTES
            \Log::info('=== INÍCIO PROCESSAMENTO DEPENDENTES ===');
            \Log::info('Dependentes recebidos do formulário', ['dependents' => $this->dependents]);
            \Log::info('Total de dependentes', ['count' => count($this->dependents)]);

            // Gerenciar dependentes
            $dependentsIds = [];
            $currentDependentIds = [];

            if (!empty($this->dependents)) {
                foreach ($this->dependents as $index => $dependent) {
                    \Log::info("Processando dependente {$index}", ['dependent' => $dependent]);
                    
                    $cpf = preg_replace('/\D/', '', $dependent['cpf']);
                    $rg = preg_replace('/\D/', '', $dependent['rg'] ?? '');

                    \Log::info("CPF/RG limpos", ['cpf' => $cpf, 'rg' => $rg]);

                    $dep = Dependent::updateOrCreate(
                        ['cpf' => $cpf],
                        [
                            'client_id' => $client->id,
                            'name' => $dependent['name'],
                            'mom_name' => $dependent['mom_name'],
                            'date_birth' => $dependent['date_birth'],
                            'cpf' => $cpf,
                            'rg' => $rg,
                            'marital_status' => $dependent['marital_status'],
                            'relationship' => $dependent['relationship'],
                        ]
                    );
                    
                    \Log::info("Dependente salvo/atualizado", [
                        'dependent_id' => $dep->id,
                        'name' => $dep->name,
                        'relationship' => $dep->relationship
                    ]);
                    
                    $additionals = $dependent['additionals'] ?? [];
                    \Log::info("Adicionais do dependente {$index}", ['additionals' => $additionals]);
                    
                    $dependentsIds[] = [
                        'id' => $dep->id,
                        'additionals' => $additionals
                    ];
                    $currentDependentIds[] = $dep->id;
                }
            }

            \Log::info('Dependentes processados', [
                'dependentsIds' => $dependentsIds,
                'currentDependentIds' => $currentDependentIds
            ]);

            // Remover dependentes que não estão mais no pedido
            $previousDependentIds = OrderDependent::where('order_id', $order->id)
                ->pluck('dependent_id')
                ->toArray();

            \Log::info('Dependentes anteriores', ['previousDependentIds' => $previousDependentIds]);

            $dependentsToRemove = array_diff($previousDependentIds, $currentDependentIds);
            
            \Log::info('Dependentes para remover', ['dependentsToRemove' => $dependentsToRemove]);
            
            if (!empty($dependentsToRemove)) {
                $removedOrderDeps = OrderDependent::where('order_id', $order->id)
                    ->whereIn('dependent_id', $dependentsToRemove)
                    ->delete();
                
                $removedAdditionals = OrderAditionalDependent::where('order_id', $order->id)
                    ->whereIn('dependent_id', $dependentsToRemove)
                    ->delete();
                    
                \Log::info('Dependentes removidos', [
                    'order_dependents_deleted' => $removedOrderDeps,
                    'additionals_deleted' => $removedAdditionals
                ]);
            }

            // Atualizar pedido
            $order->update([
                'product_id' => $this->product_id,
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

            \Log::info('Pedido atualizado');

            // Atualizar OrderPrice
            $product = Product::findOrFail($this->product_id);
            $product_value = $this->calculateTotalWithDiscount($product->value);

            OrderPrice::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'product_id' => $this->product_id,
                    'product_value' => $product_value,
                ]
            );

            \Log::info('OrderPrice atualizado', ['product_value' => $product_value]);

            // Atualizar adicionais do titular
            OrderAditional::where('order_id', $order->id)->delete();
            
            if (!empty($this->selectedAdditionals)) {
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

            \Log::info('Adicionais do titular atualizados', ['count' => count($this->selectedAdditionals)]);

            // Recriar relações order_dependents
            \Log::info('=== RECRIANDO ORDER_DEPENDENTS ===');
            $deletedOrderDeps = OrderDependent::where('order_id', $order->id)->delete();
            \Log::info('Order_dependents deletados', ['count' => $deletedOrderDeps]);
            
            foreach ($dependentsIds as $depData) {
                $orderDep = OrderDependent::create([
                    'order_id' => $order->id,
                    'dependent_id' => $depData['id'],
                ]);
                \Log::info('Order_dependent criado', [
                    'id' => $orderDep->id,
                    'order_id' => $order->id,
                    'dependent_id' => $depData['id']
                ]);
            }

            // Atualizar adicionais dos dependentes
            \Log::info('=== ATUALIZANDO ADICIONAIS DOS DEPENDENTES ===');
            $deletedAdditionals = OrderAditionalDependent::where('order_id', $order->id)->delete();
            \Log::info('Adicionais de dependentes deletados', ['count' => $deletedAdditionals]);
            
            foreach ($dependentsIds as $depData) {
                \Log::info('Processando adicionais do dependente', [
                    'dependent_id' => $depData['id'],
                    'additionals' => $depData['additionals']
                ]);
                
                if (!empty($depData['additionals'])) {
                    foreach ($depData['additionals'] as $additionalId) {
                        $aditional = collect($this->additionals)->firstWhere('id', $additionalId);
                        \Log::info('Adicional encontrado?', [
                            'additionalId' => $additionalId,
                            'found' => $aditional ? 'SIM' : 'NÃO',
                            'aditional' => $aditional
                        ]);
                        
                        if ($aditional) {
                            $orderAdditional = OrderAditionalDependent::create([
                                'order_id' => $order->id,
                                'dependent_id' => $depData['id'],
                                'aditional_id' => $additionalId,
                                'value' => $aditional['value']
                            ]);
                            \Log::info('OrderAditionalDependent criado', [
                                'id' => $orderAdditional->id,
                                'order_id' => $order->id,
                                'dependent_id' => $depData['id'],
                                'aditional_id' => $additionalId,
                                'value' => $aditional['value']
                            ]);
                        }
                    }
                } else {
                    \Log::info('Nenhum adicional para este dependente');
                }
            }

            \Log::info('=== FIM PROCESSAMENTO ===');
            
            $this->reset(['document_file', 'address_proof_file']);

            DB::commit();

            session()->flash('message', 'Pedido atualizado com sucesso!');
            return redirect()->route('admin.orders.edit', $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro no updateOrder: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            session()->flash('error', 'Erro ao atualizar pedido: ' . $e->getMessage());
        }
    }

    public function calculateTotalWithDiscount($productValue)
    {
        if ($this->discount_type == '%') {
            $productValue -= $productValue * ($this->discount_value * 0.01);
        } else if ($this->discount_type == 'R$') {
            $productValue -= $this->discount_value;
        }

        return $productValue;
    }

    public function render()
    {
        return view('livewire.order-edit', [
            'order' => $this->order,
            'charge_type' => $this->charge_type,
        ]);
    }
}
