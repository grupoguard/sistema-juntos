<?php

namespace App\Livewire;

use App\Http\Requests\DocumentRequest;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ClientForm extends Component
{
    public $client = [];
    public $dependents = [];
    public $clientId;

    // Ouvindo o evento vindo do SelectGroup
    protected $listeners = ['groupSelected'];

    protected $rules = [
        'client.group_id' => 'required|integer|max:8',
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
        'dependents.*.name' => 'required|string|max:100',
        'dependents.*.mom_name' => 'required|string|max:100',
        'dependents.*.date_birth' => 'required|date',
        'dependents.*.cpf' => 'required|string|size:11|unique:clients,cpf',
        'dependents.*.rg' => 'required|string|size:9',
        'dependents.*.marital_status' => 'required|string|max:15',
        'dependents.*.relationship' => 'required|string|max:20',
    ];

    public function mount($clientId = null)
    {
        if ($clientId) {
            $client = Client::with('dependents')->find($clientId);
            if ($client) {
                $this->client = $client->toArray();
                $this->dependents = $client->dependents->toArray();
            }
        } else {
            $this->client = [
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
                'status' => 1,
            ];

            $this->dependents = [];
        }
    }

    public function groupSelected($groupId)
    {
        $this->client['group_id'] = $groupId;
    }
    
    public function addDependent()
    {
        $this->dependents[] = [
            'name' => '', 
            'mom_name' => '',
            'date_birth' => '', 
            'cpf' => '', 
            'rg' => '', 
            'marital_status' => '',
            'relationship' => ''
        ];
    }

    public function removeDependent($index)
    {
        unset($this->dependents[$index]);
        $this->dependents = array_values($this->dependents);
    }

    public function storeOrUpdate()
    {
        $cpf = preg_replace('/\D/', '', $this->client['cpf']);
        $rg = preg_replace('/\D/', '', $this->client['rg']); // Remove pontos e traços
        $phone = preg_replace('/\D/', '', $this->client['phone']); // Remove caracteres não numéricos
        $this->client['phone'] = $phone; // Salva apenas números

        // Atualiza as regras de validação dinamicamente
        $rules = $this->rules;
        $rules['client.cpf'] = 'required|string|min:11|max:14|unique:clients,cpf,' . ($this->clientId ?: 'NULL') . ',id';
        $rules['client.email'] = 'required|email|max:255|unique:clients,email,' . ($this->clientId ?: 'NULL') . ',id';

        // Validação do RG
        if (!$this->clientId && !empty($rg) && !$this->validateRg($rg)) {
            $this->addError('client.rg', 'RG inválido.');
            return;
        }

        // Validação dinâmica para dependentes
        foreach ($this->dependents as $index => $dependent) {
            $rules["dependents.$index.cpf"] = 'required|string|size:11|unique:dependents,cpf';
        }

        // Valida os dados
        $this->validate($rules);

        if ($this->clientId) {
            $client = Client::findOrFail($this->clientId);
            $client->update($this->client);
            $client->dependents()->delete(); // Remove dependentes antigos
        } else {
            $client = Client::create($this->client);
        }

        foreach ($this->dependents as $dependent) {
            $client->dependents()->create($dependent);
        }

        session()->flash('message', 'Cliente ' . ($this->clientId ? 'atualizado' : 'cadastrado') . ' com sucesso!');
        return redirect()->route('admin.clients.edit', ['client' => $client->id]);
    }

    public function updatedClientCpf()
    {
        if (!$this->clientId) {
            $cpf = preg_replace('/\D/', '', $this->client['cpf']); // Remove caracteres não numéricos

            // Verificamos se já está cadastrado
            if (Client::where('cpf', $cpf)->exists()) {
                $this->resetErrorBag('client.cpf'); // Resetamos qualquer erro anterior
                $this->addError('client.cpf', 'CPF já cadastrado.');
                return;
            }

            if (!$this->validateCpf($cpf)) {
                $this->addError('client.cpf', 'CPF inválido.');
                return;
            }

            // Se passou em todas as validações, removemos qualquer erro anterior
            $this->resetErrorBag('client.cpf');
        }
    }

    public function updatedClientRg()
    {
        if (!$this->clientId) {
            $rg = preg_replace('/\D/', '', $this->client['rg']); // Remove caracteres não numéricos

            if (!empty($rg) && !$this->validateRg($rg)) {
                $this->addError('client.rg', 'RG inválido.');
                return;
            }

            if (!empty($rg) && Client::where('rg', $rg)->exists()) {
                $this->addError('client.rg', 'RG já cadastrado.');
            } else {
                $this->resetErrorBag('client.rg');
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
        return view('livewire.client-form');
    }
}
