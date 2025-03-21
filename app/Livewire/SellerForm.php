<?php

namespace App\Livewire;

use App\Models\Seller;
use Livewire\Component;

class SellerForm extends Component
{
    public $seller = [];
    public $dependents = [];
    public $sellerId;

    // Ouvindo o evento vindo do SelectGroup
    protected $listeners = ['groupSelected'];

    protected $rules = [
        'seller.group_id' => 'required|integer|max:8',
        'seller.name' => 'required|string|max:100',
        'seller.date_birth' => 'required|date',
        'seller.cpf' => 'required|string|min:11|max:14|unique:sellers,cpf',
        'seller.rg' => 'nullable|string|min:9|max:12',
        'seller.phone' => 'nullable|string|max:15',
        'seller.email' => 'required|email|max:50|unique:sellers,email',
        'seller.comission_type' => 'required|integer|max:1',
        'seller.comission_value' => 'required|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
        'seller.comission_recurrence' => 'required|integer|max:1',    
        'seller.zipcode' => 'required|string|max:8',
        'seller.address' => 'required|string|max:100',
        'seller.number' => 'required|string|max:10',
        'seller.complement' => 'nullable|string|max:40',
        'seller.neighborhood' => 'required|string|max:50',
        'seller.city' => 'required|string|max:50',
        'seller.state' => 'required|string|max:2',
        'seller.obs' => 'nullable|string',
        'seller.status' => 'nullable|integer',
    ];

    public function mount($sellerId = null)
    {
        if ($sellerId) {
            $seller = Seller::find($sellerId);
            if ($seller) {
                $this->seller = $seller->toArray();
            }
        } else {
            $this->seller = [
                'group_id' => null,
                'name' => '',
                'date_birth' => '',
                'cpf' => '',
                'rg' => '',
                'phone' => '',
                'email' => '',
                'comission_type' => '',
                'comission_value' => '',
                'comission_recurrence' => '',
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
        }
    }

    public function groupSelected($groupId)
    {
        $this->seller['group_id'] = $groupId;
    }
    
    public function storeOrUpdate()
    {
        $cpf = preg_replace('/\D/', '', $this->seller['cpf']);
        $rg = preg_replace('/\D/', '', $this->seller['rg']); // Remove pontos e traços
        $phone = preg_replace('/\D/', '', $this->seller['phone']); // Remove caracteres não numéricos
        $this->seller['phone'] = $phone;
        $this->seller['cpf'] = $cpf;

        // Atualiza as regras de validação dinamicamente
        $rules = $this->rules;
        $rules['seller.cpf'] = 'required|string|min:11|max:14|unique:sellers,cpf,' . ($this->sellerId ?: 'NULL') . ',id';
        $rules['seller.email'] = 'required|email|max:255|unique:sellers,email,' . ($this->sellerId ?: 'NULL') . ',id';

        // Validação do RG
        if (!empty($rg) && !$this->sellerId && !$this->validateRg($rg)) {
            $this->addError('seller.rg', 'RG inválido.');
            return;
        }

        // Valida os dados
        $this->validate($rules);

        if ($this->sellerId) {
            $seller = Seller::findOrFail($this->sellerId);
            $seller->update($this->seller);
        } else {
            $seller = Seller::create($this->seller);
        }

        session()->flash('message', 'Consultor ' . ($this->sellerId ? 'atualizado' : 'cadastrado') . ' com sucesso!');
        return redirect()->route('admin.sellers.edit', ['seller' => $seller->id]);
    }

    public function updatedSellerCpf()
    {
        if (!$this->sellerId) {
            $cpf = preg_replace('/\D/', '', $this->seller['cpf']); // Remove caracteres não numéricos

            // Verificamos se já está cadastrado
            if (Seller::where('cpf', $cpf)->exists()) {
                $this->resetErrorBag('seller.cpf'); // Resetamos qualquer erro anterior
                $this->addError('seller.cpf', 'CPF já cadastrado.');
                return;
            }

            if (!$this->validateCpf($cpf)) {
                $this->addError('seller.cpf', 'CPF inválido.');
                return;
            }

            // Se passou em todas as validações, removemos qualquer erro anterior
            $this->resetErrorBag('seller.cpf');
        }
    }

    public function updatedSellerRg()
    {
        if (!$this->sellerId) {
            $rg = preg_replace('/\D/', '', $this->seller['rg']); // Remove caracteres não numéricos

            if (!$this->validateRg($rg)) {
                $this->addError('seller.rg', 'RG inválido.');
                return;
            }

            if (Seller::where('rg', $rg)->exists()) {
                $this->addError('seller.rg', 'RG já cadastrado.');
            } else {
                $this->resetErrorBag('seller.rg');
            }
        }
    }

    private function validateCpf($cpf)
    {
        // CPF precisa ter 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Evita CPFs com todos os dígitos iguais (ex: 111.111.111-11)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        for ($i = 0, $sum = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $firstDigit = ($sum * 10) % 11;
        $firstDigit = ($firstDigit == 10) ? 0 : $firstDigit;

        // Calcula o segundo dígito verificador
        for ($i = 0, $sum = 0; $i < 10; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $secondDigit = ($sum * 10) % 11;
        $secondDigit = ($secondDigit == 10) ? 0 : $secondDigit;

        // Valida os dígitos verificadores
        return ($cpf[9] == $firstDigit && $cpf[10] == $secondDigit);
    }

    private function validateRg($rg)
    {
        // RG geralmente tem entre 7 e 10 dígitos (sem pontos e traços)
        if (strlen($rg) < 7 || strlen($rg) > 10) {
            return false;
        }

        // RGs que possuem apenas um mesmo número são inválidos
        if (preg_match('/(\d)\1{6,9}/', $rg)) {
            return false;
        }

        return true; // Se passou nos critérios, consideramos válido
    }

    public function render()
    {
        return view('livewire.seller-form');
    }
}
