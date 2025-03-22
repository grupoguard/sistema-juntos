<?php

namespace App\Livewire;

use App\Models\Partner;
use Livewire\Component;

class PartnerForm extends Component
{
    public $partner = [];
    public $partnerId;

    protected $rules = [
        'partner.company_name'    => 'required|string|max:100',
        'partner.fantasy_name'    => 'required|string|max:100',
        'partner.cnpj'            => 'required|string|min:14|max:18|unique:partners,cnpj',
        'partner.phone'           => 'required|string|max:15',
        'partner.email'           => 'required|email|max:50|unique:partners,email',
        'partner.whatsapp'        => 'nullable|string|max:15',
        'partner.site'            => 'nullable|string|max:255',
        'partner.zipcode'         => 'required|string|max:8',
        'partner.address'         => 'required|string|max:100',
        'partner.number'          => 'required|string|max:10',
        'partner.complement'      => 'nullable|string|max:40',
        'partner.neighborhood'    => 'required|string|max:50',
        'partner.city'            => 'required|string|max:50',
        'partner.state'           => 'required|string|max:2',
    ];

    public function mount($partnerId = null)
    {
        if ($partnerId) {
            $partner = Partner::find($partnerId);
            $this->partner = $partner->toArray();
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
        }

        session()->flash('message', 'parceiro' . ($this->partnerId ? 'atualizado' : 'cadastrado') . ' com sucesso!');
        return redirect()->route('admin.partners.edit', ['partner' => $partner->id]);
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
