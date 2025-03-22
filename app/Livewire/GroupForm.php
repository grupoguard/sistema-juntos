<?php

namespace App\Livewire;

use App\Models\Group;
use Livewire\Component;

class GroupForm extends Component
{
    public $group = [];
    public $groupId;

    protected $rules = [
        'group.group_name'      => 'required|string|max:100',
        'group.name'            => 'required|string|max:100',
        'group.document'        => 'required|string|min:14|max:18|unique:groups,document',
        'group.phone'           => 'required|string|max:15',
        'group.email'           => 'required|email|max:50|unique:groups,email',
        'group.whatsapp'        => 'nullable|string|max:15',
        'group.site'            => 'nullable|string|max:255',
        'group.zipcode'         => 'required|string|max:8',
        'group.address'         => 'required|string|max:100',
        'group.number'          => 'required|string|max:10',
        'group.complement'      => 'nullable|string|max:40',
        'group.neighborhood'    => 'required|string|max:50',
        'group.city'            => 'required|string|max:50',
        'group.state'           => 'required|string|max:2',
        'group.status'          => 'nullable|integer',
        'group.obs'             => 'nullable|string',
    ];

    public function mount($groupId = null)
    {
        if ($groupId) {
            $group = Group::find($groupId);
            $this->group = $group->toArray();
        } else {
            $this->group = [
                'group_name'    => null,
                'name'          => '',
                'document'      => '',
                'phone'         => '',
                'email'         => '',
                'whatsapp'      => '',
                'site'          => '',
                'zipcode'       => '',
                'address'       => '',
                'number'        => '',
                'complement'    => '',
                'neighborhood'  => '',
                'city'          => '',
                'state'         => '',
                'status'        => 1,
                'obs'           => '',
            ];
        }
    }

    public function storeOrUpdate()
    {
        $document = preg_replace('/\D/', '', $this->group['document']);
        $phone = preg_replace('/\D/', '', $this->group['phone']);
        $whatsapp = preg_replace('/\D/', '', $this->group['whatsapp']);
        $this->group['document'] = $document;
        $this->group['phone'] = $phone; // Salva apenas números
        $this->group['whatsapp'] = $whatsapp; // Salva apenas números

        // Atualiza as regras de validação dinamicamente
        $rules = $this->rules;
        $rules['group.document'] = 'required|string|min:14|max:18|unique:groups,document,' . ($this->groupId ?: 'NULL') . ',id';
        $rules['group.email'] = 'required|email|max:255|unique:groups,email,' . ($this->groupId ?: 'NULL') . ',id';

        // Valida os dados
        $this->validate($rules);

        if ($this->groupId) {
            $group = Group::findOrFail($this->groupId);
            $group->update($this->group);
        } else {
            $group = Group::create($this->group);
        }

        session()->flash('message', 'cooperativa' . ($this->groupId ? 'atualizado' : 'cadastrado') . ' com sucesso!');
        return redirect()->route('admin.groups.edit', ['group' => $group->id]);
    }

    public function updatedgroupCnpj()
    {
        if (!$this->groupId) {
            $cnpj = preg_replace('/\D/', '', $this->group['cnpj']); // Remove caracteres não numéricos

            // Verifica se já está cadastrado
            if (Group::where('cnpj', $cnpj)->exists()) {
                $this->resetErrorBag('group.cnpj'); // Resetamos qualquer erro anterior
                $this->addError('group.cnpj', 'CNPJ já cadastrado.');
                return;
            }

            if (!$this->validateCnpj($cnpj)) {
                $this->addError('group.cnpj', 'CNPJ inválido.');
                return;
            }

            // Se passou em todas as validações, removemos qualquer erro anterior
            $this->resetErrorBag('group.cnpj');
        }
    }

    private function validateCnpj($cnpj)
    {
        // CNPJ precisa ter 14 dígitos
        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Evita CNPJs com todos os dígitos iguais (ex: 11.111.111/1111-11)
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $multipliers1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $multipliers1[$i];
        }
        $firstDigit = ($sum % 11 < 2) ? 0 : (11 - $sum % 11);

        // Calcula o segundo dígito verificador
        $multipliers2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $multipliers2[$i];
        }
        $secondDigit = ($sum % 11 < 2) ? 0 : (11 - $sum % 11);

        // Valida os dígitos verificadores
        return ($cnpj[12] == $firstDigit && $cnpj[13] == $secondDigit);
    }

    public function render()
    {
        return view('livewire.group-form');
    }
}
