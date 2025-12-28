<?php

namespace App\Livewire\Components;

use App\Models\Client;
use Livewire\Component;

class ClientForm extends Component
{
    public $clientId;

    public $client = [
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

    protected $listeners = ['updateClientForm' => 'loadClient'];

    public function mount($clientId = null)
    {
        if ($clientId) {
            $this->loadClient($clientId);
        }
    }

    public function loadClient($clientId)
    {
        if ($clientId === "new") {
            $this->resetClientFields();
        } else {
            $client = Client::find($clientId);
            if ($client) {
                $this->clientId = $client->id;
                $this->client = [
                    'group_id' => $client->group_id,
                    'name' => $client->name,
                    'mom_name' => $client->mom_name,
                    'date_birth' => $client->date_birth,
                    'cpf' => $client->cpf,
                    'rg' => $client->rg,
                    'gender' => $client->gender,
                    'marital_status' => $client->marital_status,
                    'phone' => $client->phone,
                    'email' => $client->email,
                    'zipcode' => $client->zipcode,
                    'address' => $client->address,
                    'number' => $client->number,
                    'complement' => $client->complement,
                    'neighborhood' => $client->neighborhood,
                    'city' => $client->city,
                    'state' => $client->state,
                    'obs' => $client->obs,
                    'status' => $client->status,
                ];
            }
        }
    }

    private function resetClientFields()
    {
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
            'status' => '',
        ];
    }

    public function render()
    {
        return view('livewire.components.client-form');
    }
}
