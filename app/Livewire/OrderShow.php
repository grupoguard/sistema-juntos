<?php

namespace App\Livewire;

use App\Models\Dependent;
use App\Models\Financial;
use App\Models\Order;
use App\Models\OrderAditional;
use App\Models\OrderAditionalDependent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class OrderShow extends Component
{
    use AuthorizesRequests;

    public int $orderId;
    public Order $order;

    public array $client = [];
    public array $dependents = [];
    public array $selectedAdditionals = [];

    public string $seller_id = '';
    public string $product_id = '';
    public ?string $charge_type = null;

    public $financials = [];

    public float $total = 0.0;

    // arquivos atuais (para mostrar link)
    public ?string $existing_document_file = null;
    public ?string $existing_document_file_type = null;
    public ?string $existing_address_proof_file = null;

    // Campos EDP (apenas exibição)
    public ?string $installation_number = null;
    public ?string $approval_name = null;
    public ?string $approval_by = null;
    public ?string $evidence_date = null;

    // Campos gerais
    public ?int $charge_date = null;
    public float $accession = 0.0;
    public ?string $accession_payment = null;
    public ?string $discount_type = null;
    public float $discount_value = 0.0;

    public ?string $signed_contract_url = null;

    public function mount($orderId)
    {
        $this->orderId = (int) $orderId;

        $this->order = Order::with([
            'client',
            'product',
            'seller',
            'orderPrice',
        ])->findOrFail($this->orderId);

        // trava acesso ao pedido específico
        $this->authorize('view', $this->order);

        $user = auth()->user();
        if ($user->isAdmin()) {
            $this->order->markAsViewedBy($user);
            $this->order->refresh();
        }

        // Client
        $this->client = $this->order->client?->toArray() ?? [];
        if (isset($this->client['gender'])) $this->client['gender'] = mb_strtolower($this->client['gender']);
        if (isset($this->client['marital_status'])) $this->client['marital_status'] = mb_strtolower($this->client['marital_status']);

        // Pedido (espelha seu OrderEdit, mas sem listas editáveis)
        $this->seller_id = (string) $this->order->seller_id;
        $this->product_id = (string) $this->order->product_id;
        $this->charge_type = $this->order->charge_type;

        $this->installation_number = $this->order->installation_number;
        $this->approval_name = $this->order->approval_name;
        $this->approval_by = $this->order->approval_by;
        $this->evidence_date = $this->order->evidence_date;

        $this->charge_date = $this->order->charge_date;
        $this->accession = (float) ($this->order->accession ?? 0);
        $this->accession_payment = $this->order->accession_payment ?? 'Não cobrada';
        $this->discount_type = $this->order->discount_type;
        $this->discount_value = (float) ($this->order->discount_value ?? 0);

        $this->signed_contract_url = $this->order->signed_contract_url;

        // Documentos
        $this->existing_document_file = $this->order->document_file;
        $this->existing_document_file_type = $this->order->document_file_type ?: 'RG';
        $this->existing_address_proof_file = $this->order->address_proof_file;

        // Financeiros
        $this->financials = Financial::where('order_id', $this->orderId)
            ->orderBy('due_date', 'asc')
            ->get();

        // Dependentes + adicionais dependentes (mesma lógica que você já usa)
        $this->dependents = OrderAditionalDependent::query()
            ->where('order_id', $this->orderId)
            ->select('dependent_id')
            ->distinct()
            ->get()
            ->map(function ($row) {
                $depModel = Dependent::find($row->dependent_id);

                $dependentAdditionals = OrderAditionalDependent::query()
                    ->where('order_id', $this->orderId)
                    ->where('dependent_id', $row->dependent_id)
                    ->pluck('aditional_id')
                    ->toArray();

                return [
                    'dependent_id'   => $row->dependent_id,
                    'name'           => $depModel->name ?? '',
                    'relationship'   => $depModel?->relationship ? mb_strtolower($depModel->relationship) : '',
                    'cpf'            => $depModel->cpf ?? '',
                    'rg'             => $depModel->rg ?? '',
                    'date_birth'     => $depModel->date_birth ?? '',
                    'marital_status' => $depModel?->marital_status ? mb_strtolower($depModel->marital_status) : 'nao_informado',
                    'mom_name'       => $depModel->mom_name ?? '',
                    'additionals'    => $dependentAdditionals,
                ];
            })
            ->toArray();

        // Adicionais do titular (somente ids, só pra exibir listagem)
        $this->selectedAdditionals = OrderAditional::where('order_id', $this->orderId)
            ->pluck('aditional_id')
            ->toArray();

        $this->recalculateTotal();
    }

    private function recalculateTotal(): void
    {
        $productValue = (float) optional($this->order->orderPrice)->product_value;
        $dependentsValue = (float) ($this->order->dependents_value ?? 0);

        $total = $productValue + $dependentsValue;

        // desconto (se houver)
        if ($this->discount_type === '%') {
            $total -= ($total * ((float)$this->discount_value / 100));
        } elseif ($this->discount_type === 'R$') {
            $total -= (float)$this->discount_value;
        }

        // adesão entra no total? (no seu edit você já soma em outro lugar; aqui deixei igual ao "total do pedido base")
        $this->total = max(0, (float) $total);
    }

    public function render()
    {
        return view('livewire.order-show');
    }
}