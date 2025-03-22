<?php

namespace App\Livewire;

use App\Models\Aditional;
use App\Models\Product;
use Livewire\Component;

class ProductForm extends Component
{
    public $product = [];
    public $additionals = [];
    public $availableAdditionals = []; // Adicionais disponíveis no banco
    public $productId;

    protected $rules = [
        'product.code'              => 'required|integer',
        'product.name'              => 'required|string|max:100',
        'product.value'             => 'required|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
        'product.accession'         => 'required|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
        'product.dependents_limit'  => 'required|integer',
        'product.recurrence'        => 'required|string|max:20',
        'product.lack'              => 'required|integer',
        'product.status'            => 'nullable|integer',
        'additionals.*.aditional_id'=> 'required|integer',
        'additionals.*.value'       => 'required|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
    ];

    public function mount($productId = null)
    {
        $this->availableAdditionals = Aditional::all(); // Busca adicionais do banco

        if ($productId) {
            $product = Product::with('additionals')->find($productId);
            if ($product) {
                $this->product = $product->toArray();
                $this->additionals = $product->additionals->map(function ($additional) {
                    return [
                        'aditional_id' => $additional->id,
                        'value' => $additional->pivot->value ?? '', // Se tiver um valor associado
                        'locked' => true, // Bloqueia a edição
                    ];
                })->toArray();
            }
        } else {
            $this->product = [
                'code'              => '',
                'name'              => '',
                'value'             => '',
                'accession'         => '',
                'dependents_limit'  => '',
                'recurrence'        => '',
                'lack'              => '',
                'status'            => 1,
            ];

            $this->additionals = [];
        }
    }

    public function addAdditionals()
    {
        $this->additionals[] = ['aditional_id' => '', 'value' => '', 'locked' => false];
    }

    public function removeAdditionals($index)
    {
        unset($this->additionals[$index]);
        $this->additionals = array_values($this->additionals);
    }

    public function storeOrUpdate()
    {
        $rules = $this->rules;
        $this->validate($rules);

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($this->product);
            $product->additionals()->detach(); // Remove adicionais antigos
        } else {
            $product = Product::create($this->product);
        }

        foreach ($this->additionals as $additional) {
            $product->additionals()->attach($additional['aditional_id'], ['value' => $additional['value']]);
        }

        session()->flash('message', 'produto' . ($this->productId ? 'atualizado' : 'cadastrado') . ' com sucesso!');
        return redirect()->route('admin.products.edit', ['product' => $product->id]);
    }

    public function render()
    {
        return view('livewire.product-form');
    }
}
