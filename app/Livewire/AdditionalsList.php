<?php

namespace App\Livewire;

use App\Models\Aditional;
use Livewire\Component;
use Livewire\WithPagination;

class AdditionalsList extends Component
{
    public $allAdditionals; // Lista de adicionais
    public $aditional = ['name' => '', 'status' => true]; // Definir status padrão como true
    public $editing = false; // Flag para saber se está editando
    public $aditionalId; // ID do adicional sendo editado

    public function mount()
    {
        $this->loadAdditionals();
    }

    public function loadAdditionals()
    {
        $this->allAdditionals = Aditional::all();
    }

    public function resetForm()
    {
        $this->aditional = ['name' => '', 'status' => true]; // Sempre inicia com status ativo
        $this->editing = false;
        $this->aditionalId = null;
    }

    public function save()
    {
        $this->validate([
            'aditional.name' => 'required|string|max:255',
            'aditional.status' => 'boolean',
        ]);

        if ($this->editing) {
            $aditional = Aditional::find($this->aditionalId);
            if ($aditional) {
                $aditional->update($this->aditional);
            }
        } else {
            Aditional::create($this->aditional);
        }

        $this->resetForm();
        $this->loadAdditionals();
    }

    public function edit($id)
    {
        $aditional = Aditional::find($id);
        if ($aditional) {
            $this->aditional = [
                'name' => $aditional->name,
                'status' => $aditional->status,
            ];
            $this->aditionalId = $id;
            $this->editing = true;
        }
    }

    public function delete($id)
    {
        Aditional::find($id)->delete();
        $this->loadAdditionals();
        if ($this->editing && $this->aditionalId == $id) {
            $this->resetForm();
        }
    }

    public function render()
    {
        return view('livewire.additionals-list');
    }
}
