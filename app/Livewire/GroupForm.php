<?php

namespace App\Livewire;

use App\Models\Group;
use App\Services\UserManagementService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GroupForm extends Component
{
    public $group = [];
    public $groupId;
    public $createUser = false; // Checkbox para criar usuário
    public $userPassword; // Senha customizada (opcional)
    public $generatedPassword; // Senha gerada para mostrar

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
        'userPassword'          => 'nullable|string|min:8',
    ];

    public function mount($groupId = null)
    {
        if ($groupId) {
            $this->groupId = $groupId;
            $group = Group::findOrFail($groupId);
            $this->group = $group->toArray();
            $this->createUser = false; // Não criar usuário ao editar
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

        try {
            DB::beginTransaction();

            if ($this->groupId) {
                // Atualizar grupo existente
                $group = Group::findOrFail($this->groupId);
                $group->update($this->group);
                
                session()->flash('message', 'Cooperativa atualizada com sucesso!');
                
            } else {
                // Criar novo grupo
                $group = Group::create($this->group);
                
                // Criar usuário se solicitado
                if ($this->createUser) {
                    $userService = app(UserManagementService::class);
                    $result = $userService->createUserForGroup($group, $this->userPassword);
                    
                    $this->generatedPassword = $result['password'];
                    
                    session()->flash('message', 'Cooperativa cadastrada com sucesso!');
                    session()->flash('user_created', true);
                    session()->flash('user_email', $result['user']->email);
                    session()->flash('user_password', $this->generatedPassword);
                } else {
                    session()->flash('message', 'Cooperativa cadastrada com sucesso!');
                }
            }

            DB::commit();
            return redirect()->route('admin.groups.edit', ['group' => $group->id]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao salvar: ' . $e->getMessage());
        }
    }

    public function updatedGroupDocument()
    {
        if (!$this->groupId) {
            $document = preg_replace('/\D/', '', $this->group['document']);

            // Verifica se já está cadastrado
            if (Group::where('document', $document)->exists()) {
                $this->addError('group.document', 'Documento já cadastrado.');
                return;
            }

            // Validar CNPJ
            if (strlen($document) === 14 && !$this->validateCnpj($document)) {
                $this->addError('group.document', 'CNPJ inválido.');
                return;
            }

            $this->resetErrorBag('group.document');
        }
    }

    private function validateCnpj($cnpj)
    {
        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $multipliers1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $multipliers1[$i];
        }
        $firstDigit = ($sum % 11 < 2) ? 0 : (11 - $sum % 11);

        $multipliers2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $multipliers2[$i];
        }
        $secondDigit = ($sum % 11 < 2) ? 0 : (11 - $sum % 11);

        return ($cnpj[12] == $firstDigit && $cnpj[13] == $secondDigit);
    }

    public function render()
    {
        return view('livewire.group-form');
    }
}
