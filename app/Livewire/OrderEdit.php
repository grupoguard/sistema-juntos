<?php

namespace App\Livewire;

use App\Models\Aditional;
use App\Models\Dependent;
use App\Models\Order;
use App\Models\OrderDependent;
use App\Models\Product;
use App\Models\Seller;
use App\Traits\OrderFormTrait;
use Illuminate\Routing\Route;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class OrderEdit extends Component
{
    use WithFileUploads, OrderFormTrait;

    public $data;

    //Funções separadas já que order.[...] não carrega corretamente
    public $charge_type;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->order = Order::where('id', $this->orderId)->first();
        $this->dependents = OrderDependent::where('order_id', $orderId)
        ->get()
        ->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->dependent->name ?? '',
            'relationship' => $item->dependent->relationship ?? '',
            'cpf' => $item->dependent->cpf ?? '',
            'rg' => $item->dependent->rg ?? '',
            'date_birth' => $item->dependent->date_birth ?? '',
            'marital_status' => $item->dependent->marital_status ?: 'nao_informado',
            'mom_name' => $item->dependent->mom_name ?? '',
        ])
        ->toArray();

        //Preenhe todos os atributos do client
        $this->client = $this->order->client->toArray();
        $this->sellers = Seller::orderBy('name')->get();
        $this->products = Product::orderByDesc('status')
        ->orderBy('name')
        ->get();
        $this->charge_date = $this->order->charge_date;
        
        // Converte para string para Livewire não ter conflito de tipo
        $this->seller_id = (string) $this->order->seller_id;
        $this->product_id = (string) $this->order->product_id;
        $this->charge_type = $this->order->charge_type;
        $this->installation_number = $this->order->installation_number;
        $this->approval_name = $this->order->approval_name;
        $this->approval_by = $this->order->approval_by;
        $this->evidence_date = $this->order->evidence_date;

        //Se for null define como não cobrada
        $this->accession_payment = $this->order->accession_payment ?? 'Não cobrada';

        //Define o valor total do pedido
        $this->total = $this->order->orderPrice->product_value + $this->order->dependents_value;

        $this->dispatch('order-loaded');
    }

    public function render()
    {
        return view('livewire.order-edit', [
            'order' => $this->data,
            'charge_type' => $this->charge_type,
        ]);
    }
}
