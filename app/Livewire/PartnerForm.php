<?php

namespace App\Livewire;

use App\Models\Partner;
use App\Models\PartnersCategorie;
use Livewire\Component;

class PartnerForm extends Component
{
    public $partner = [];
    public $plans = [];
    public $availableCategories = [];
    public $partnerId;
    public $activeTab = '#partner-data-tab';

    protected $listeners = ['tabChanged' => 'updateTab'];

    protected $rules = [
        'partner.company_name'      => 'required|string|max:100',
        'partner.fantasy_name'      => 'required|string|max:100',
        'partner.cnpj'              => 'required|string|min:14|max:18|unique:partners,cnpj',
        'partner.phone'             => 'required|string|max:15',
        'partner.email'             => 'required|email|max:50|unique:partners,email',
        'partner.whatsapp'          => 'nullable|string|max:15',
        'partner.site'              => 'nullable|string|max:255',
        'partner.zipcode'           => 'required|string|max:8',
        'partner.address'           => 'required|string|max:100',
        'partner.number'            => 'required|string|max:10',
        'partner.complement'        => 'nullable|string|max:40',
        'partner.neighborhood'      => 'required|string|max:50',
        'partner.city'              => 'required|string|max:50',
        'partner.state'             => 'required|string|max:2',
        'plans.*.partner_id'        => 'required|integer',
        'plans.*.category_id'       => 'required|integer',
        'plans.*.particular_price'  => 'required|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
        'plans.*.juntos_price'      => 'required|numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
        'plans.*.obs'               => 'nullable|string',
    ];

    public function mount($partnerId = null)
    {
        $this->availableCategories = PartnersCategorie::all();

        if ($partnerId) {
            $partner = Partner::find($partnerId);
            $this->partner = $partner->toArray();
        
            // Carrega os planos existentes vinculados ao parceiro
            $this->plans = $partner->partnerPlans->map(function ($plan) {
                return [
                    'partner_id' => $plan->partner_id,
                    'category_id' => $plan->category_id,
                    'particular_price' => $plan->particular_price,
                    'juntos_price' => $plan->juntos_price,
                    'obs' => $plan->obs ?? '',
                    'locked' => true, // Bloqueia a edição
                ];
            })->toArray();
        } else {
            $this->partner = [
                'company_name'  => null,
                'fantasy_name'  => '',
                'cnpj'          => '',
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
            ];
        }

        $this->activeTab = session('activeTab', '#partner-data-tab');
    }

    public function addPlans()
    {
        $this->plans[] = [
            'partner_id' => $this->partnerId ?? '',
            'category_id' => '',
            'particular_price' => '',
            'juntos_price' => '',
            'obs' => '',
            'locked' => false
        ];
    }

    public function removePlan($index)
    {
        unset($this->plans[$index]);
        $this->plans = array_values($this->plans); // Reorganiza os índices
    }

    public function storeOrUpdate()
    {
        $cnpj = preg_replace('/\D/', '', $this->partner['cnpj']);
        $phone = preg_replace('/\D/', '', $this->partner['phone']);
        $whatsapp = preg_replace('/\D/', '', $this->partner['whatsapp']);
        $this->partner['cnpj'] = $cnpj;
        $this->partner['phone'] = $phone; // Salva apenas números
        $this->partner['whatsapp'] = $whatsapp; // Salva apenas números

        // Atualiza as regras de validação dinamicamente
        $rules = $this->rules;
        $rules['partner.cnpj'] = 'required|string|min:14|max:18|unique:partners,cnpj,' . ($this->partnerId ?: 'NULL') . ',id';
        $rules['partner.email'] = 'required|email|max:255|unique:partners,email,' . ($this->partnerId ?: 'NULL') . ',id';

        // Valida os dados
        $this->validate($rules);

        if ($this->partnerId) {
            $partner = Partner::findOrFail($this->partnerId);
            $partner->update($this->partner);
        } else {
            $partner = Partner::create($this->partner);
            $this->partnerId = $partner->id;
        }

        // Salvar os planos vinculados
        foreach ($this->plans as $plan) {
            $partner->partnerPlans()->updateOrCreate(
                ['category_id' => $plan['category_id']],
                [
                    'particular_price' => $plan['particular_price'],
                    'juntos_price' => $plan['juntos_price'],
                    'obs' => $plan['obs'],
                ]
            );
        }

        session()->flash('message', 'parceiro' . ($this->partnerId ? 'atualizado' : 'cadastrado') . ' com sucesso!');
        return redirect()->route('admin.partners.edit', ['partner' => $partner->id]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->dispatch('tabChanged', $tab);
    }

    public function updateTab($tab)
    {
        $this->activeTab = $tab;
        session(['activeTab' => $tab]);
    }

    public function updatedPartnerCnpj()
    {
        if (!$this->partnerId) {
            $cnpj = preg_replace('/\D/', '', $this->partner['cnpj']); // Remove caracteres não numéricos

            // Verifica se já está cadastrado
            if (Partner::where('cnpj', $cnpj)->exists()) {
                $this->resetErrorBag('partner.cnpj'); // Resetamos qualquer erro anterior
                $this->addError('partner.cnpj', 'CNPJ já cadastrado.');
                return;
            }

            if (!$this->validateCnpj($cnpj)) {
                $this->addError('partner.cnpj', 'CNPJ inválido.');
                return;
            }

            // Se passou em todas as validações, removemos qualquer erro anterior
            $this->resetErrorBag('partner.cnpj');
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
        return view('livewire.partner-form');
    }
}
