<?php

namespace App\Livewire;

use App\Models\Aditional;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Routing\Route;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class OrderEdit extends Component
{
    use WithFileUploads;

    public $orderId;

    /*** ORDER DATA */
    public $order = [
        'client_id' => '',
        'product_id' => '',
        'group_id' => '',
        'seller_id' => '',
        'charge_type' => '',
        'installation_number' => '',
        'approval_name' => '',
        'approval_by' => '',
        'evidence_date' => '',
        'charge_date' => '',
        'accession' => '',
        'accession_payment' => '',
        'discount_type' => '',
        'discount_value' => '',
    ];

    public $dependents = [];
    public $additionals = [];

    /*** CLIENT DATA */
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

    /*** RECEIVE ALL */
    public $product_id;
    public $clients;
    public $sellers;
    public $products;

    public $selectedAdditionals = [];

    public $total = 0;

    public $evidences = [];

    public $documents = [];
    public $dependentAdditionals = [];

    public function mount(Route $route)
    {
        $this->orderId = $route->parameter('order');
        $this->sellers = Seller::where('status', 1)->orderBy('name')->get();
        $this->products = Product::where('status', 1)->orderBy('name')->get();

        if ($this->orderId) {
            $this->order = Order::where('id', $this->orderId)->first()->toArray();

            $order = Order::with([
                'client',
                'product',
                'group',
                'seller',
                'dependents',
                'orderPrice',
                'orderAditionals',
                'evidences'
            ])->find($this->orderId);

            // Atribui os dados do cliente
            $this->client = $order->client->toArray();
            $this->dependents = $order->dependents->toArray();
            $this->evidences = $order->evidences->toArray();
        } else {
            // Lidar com o caso em que o ID do pedido não é fornecido
            abort(404);
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

    public function render()
    {
        return view('livewire.order-edit');
    }
}
