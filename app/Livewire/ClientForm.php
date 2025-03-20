<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;

class ClientForm extends Component
{
    public $client;
    public $dependents = [];

    protected $rules = [
        'client.name' => 'required|string|max:100',
        'client.email' => 'required|email|max:50|unique:clients,email',
        'client.phone' => 'nullable|string|max:11',
        'client.cpf' => 'required|string|size:11|unique:clients,cpf',
        'client.address' => 'required|string|max:100',
        'client.city' => 'required|string|max:50',
        'client.state' => 'required|string|max:2',
        'dependents.*.name' => 'required|string|max:100',
        'dependents.*.date_birth' => 'required|date',
    ];

    public function addDependent()
    {
        $this->dependents[] = ['name' => '', 'date_birth' => ''];
    }

    public function removeDependent($index)
    {
        unset($this->dependents[$index]);
        $this->dependents = array_values($this->dependents);
    }

    public function save()
    {
        $this->validate();

        $client = Client::create($this->client);

        foreach ($this->dependents as $dependent) {
            $client->dependents()->create($dependent);
        }

        session()->flash('message', 'Cliente cadastrado com sucesso!');
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.client-form');
    }
}
