<?php

namespace App\Livewire;

use App\Models\Group;
use App\Models\Seller;
use App\Services\UserManagementService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SellerForm extends Component
{
    public $seller = [];
    public $sellerId;
    public $createUser = true;
    public $userPassword;
    public $generatedPassword;

    // Ouvindo o evento vindo do SelectGroup
    protected $listeners = ['groupSelected'];

    protected $rules = [
        'seller.group_id' => 'required|integer|max:8',
        'seller.name' => 'required|string|max:100',
        'seller.date_birth' => 'required|date',
        'seller.cpf' => 'required|string|min:11|max:14|unique:sellers,cpf',
        'seller.rg' => 'nullable|string|min:7|max:15',
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
        'userPassword'              => 'nullable|string|min:8',
    ];

    public function mount($sellerId = null)
    {
        if ($sellerId) {
            $this->sellerId = $sellerId;
            $sellerModel = Seller::findOrFail($sellerId);
            $this->seller = $sellerModel->toArray();
            $this->createUser = false;
        } else {
            $this->seller = [
                'group_id'              => null,
                'name'                  => '',
                'date_birth'            => null,
                'cpf'                   => '',
                'rg'                    => '',
                'phone'                 => '',
                'email'                 => '',
                'comission_type'        => 1,
                'comission_value'       => 0,
                'comission_recurrence'  => false,
                'zipcode'               => '',
                'address'               => '',
                'number'                => '',
                'complement'            => '',
                'neighborhood'          => '',
                'city'                  => '',
                'state'                 => '',
                'status'                => true,
                'obs'                   => '',
            ];
        }
    }

    public function groupSelected($groupId)
    {
        $this->seller['group_id'] = $groupId;
    }
    
    public function storeOrUpdate()
    {
        // Limpar campos
        $this->seller['cpf'] = preg_replace('/\D/', '', $this->seller['cpf']);
        $this->seller['phone'] = preg_replace('/\D/', '', $this->seller['phone'] ?? '');
        $this->seller['zipcode'] = preg_replace('/\D/', '', $this->seller['zipcode']);

        // Atualizar regras para edição
        $rules = $this->rules;
        $rules['seller.cpf'] = 'required|string|size:11|unique:sellers,cpf,' . ($this->sellerId ?: 'NULL') . ',id';
        $rules['seller.email'] = 'required|email|max:50|unique:sellers,email,' . ($this->sellerId ?: 'NULL') . ',id';

        $this->validate($rules);

        try {
            DB::beginTransaction();

            if ($this->sellerId) {
                // Atualizar
                $sellerModel = Seller::findOrFail($this->sellerId);
                $sellerModel->update($this->seller);
                
                session()->flash('message', 'Vendedor atualizado com sucesso!');
                
            } else {
                // Criar
                $sellerModel = Seller::create($this->seller);
                
                // Criar usuário se solicitado
                if ($this->createUser) {
                    $userService = app(UserManagementService::class);
                    $result = $userService->createUserForSeller($sellerModel, $this->userPassword);
                    
                    $this->generatedPassword = $result['password'];
                    
                    session()->flash('message', 'Vendedor cadastrado com sucesso!');
                    session()->flash('user_created', true);
                    session()->flash('user_email', $result['user']->email);
                    session()->flash('user_password', $this->generatedPassword);
                } else {
                    session()->flash('message', 'Vendedor cadastrado com sucesso!');
                }
            }

            DB::commit();
            return redirect()->route('admin.sellers.edit', ['seller' => $sellerModel->id]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao salvar: ' . $e->getMessage());
        }
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
        $groups = Group::where('status', true)->get();
        return view('livewire.seller-form', compact('groups'));
    }

    protected function messages()
    {
        return [
            // Mensagens para 'seller.group_id'
            'seller.group_id.required' => 'A cooperativa do consultor é obrigatório.',
            'seller.group_id.integer' => 'A cooperativa do consultor deve ser um número inteiro.',
            'seller.group_id.max' => 'A cooperativa do consultor não pode exceder :max dígitos.', // Ajustei para dígitos, pois 'max' em int não é de caracteres

            // Mensagens para 'seller.name'
            'seller.name.required' => 'O nome do consultor é obrigatório.',
            'seller.name.string' => 'O nome do consultor deve ser texto.',
            'seller.name.max' => 'O nome do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.date_birth'
            'seller.date_birth.required' => 'A data de nascimento do consultor é obrigatória.',
            'seller.date_birth.date' => 'A data de nascimento do consultor deve ser uma data válida.',

            // Mensagens para 'seller.cpf'
            'seller.cpf.required' => 'O CPF do consultor é obrigatório.',
            'seller.cpf.string' => 'O CPF do consultor deve ser texto.',
            'seller.cpf.min' => 'O CPF do consultor deve ter no mínimo :min caracteres.',
            'seller.cpf.max' => 'O CPF do consultor não pode exceder :max caracteres.',
            'seller.cpf.unique' => 'O CPF informado já está cadastrado para outro consultor.',

            // Mensagens para 'seller.rg'
            'seller.rg.string' => 'O RG do consultor deve ser texto.',
            'seller.rg.min' => 'O RG do consultor deve ter no mínimo :min caracteres.',
            'seller.rg.max' => 'O RG do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.phone'
            'seller.phone.string' => 'O telefone do consultor deve ser texto.',
            'seller.phone.max' => 'O telefone do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.email'
            'seller.email.required' => 'O email do consultor é obrigatório.',
            'seller.email.email' => 'O email do consultor deve ser um endereço de e-mail válido.',
            'seller.email.max' => 'O email do consultor não pode exceder :max caracteres.',
            'seller.email.unique' => 'O email informado já está em uso por outro consultor.',

            // Mensagens para 'seller.comission_type'
            'seller.comission_type.required' => 'O tipo de comissão é obrigatório.',
            'seller.comission_type.integer' => 'O tipo de comissão deve ser um número inteiro.',
            'seller.comission_type.max' => 'O tipo de comissão não pode exceder :max dígito.',

            // Mensagens para 'seller.comission_value'
            'seller.comission_value.required' => 'O valor da comissão é obrigatório.',
            'seller.comission_value.numeric' => 'O valor da comissão deve ser numérico.',
            'seller.comission_value.regex' => 'O valor da comissão deve ter no máximo 8 dígitos inteiros e até 2 casas decimais.',

            // Mensagens para 'seller.comission_recurrence'
            'seller.comission_recurrence.required' => 'A recorrência da comissão é obrigatória.',
            'seller.comission_recurrence.integer' => 'A recorrência da comissão deve ser um número inteiro.',
            'seller.comission_recurrence.max' => 'A recorrência da comissão não pode exceder :max dígito.',

            // Mensagens para 'seller.zipcode'
            'seller.zipcode.required' => 'O CEP do consultor é obrigatório.',
            'seller.zipcode.string' => 'O CEP do consultor deve ser texto.',
            'seller.zipcode.max' => 'O CEP do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.address'
            'seller.address.required' => 'O endereço do consultor é obrigatório.',
            'seller.address.string' => 'O endereço do consultor deve ser texto.',
            'seller.address.max' => 'O endereço do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.number'
            'seller.number.required' => 'O número do endereço do consultor é obrigatório.',
            'seller.number.string' => 'O número do endereço do consultor deve ser texto.',
            'seller.number.max' => 'O número do endereço do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.complement'
            'seller.complement.string' => 'O complemento do endereço do consultor deve ser texto.',
            'seller.complement.max' => 'O complemento do endereço do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.neighborhood'
            'seller.neighborhood.required' => 'O bairro do consultor é obrigatório.',
            'seller.neighborhood.string' => 'O bairro do consultor deve ser texto.',
            'seller.neighborhood.max' => 'O bairro do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.city'
            'seller.city.required' => 'A cidade do consultor é obrigatória.',
            'seller.city.string' => 'A cidade do consultor deve ser texto.',
            'seller.city.max' => 'A cidade do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.state'
            'seller.state.required' => 'O estado do consultor é obrigatório.',
            'seller.state.string' => 'O estado do consultor deve ser texto.',
            'seller.state.max' => 'O estado do consultor não pode exceder :max caracteres.',

            // Mensagens para 'seller.obs'
            'seller.obs.string' => 'As observações do consultor devem ser texto.',

            // Mensagens para 'seller.status'
            'seller.status.integer' => 'O status do consultor deve ser um número inteiro.',
        ];
    }
}
