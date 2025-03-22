<?php

namespace App\Livewire;

use App\Models\PartnersCategorie;
use Livewire\Component;

class PartnersCategoriesList extends Component
{
    public $allCategories; // Lista de adicionais
    public $partnerCategorie = ['name' => ''];
    public $editing = false; // Flag para saber se está editando
    public $partnerCategorieId; // ID do adicional sendo editado

    public function mount()
    {
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->allCategories = PartnersCategorie::all();
    }

    public function resetForm()
    {
        $this->partnerCategorie = ['name' => '']; // Sempre inicia com status ativo
        $this->editing = false;
        $this->partnerCategorieId = null;
    }

    public function save()
    {
        $this->validate([
            'partnerCategorie.name' => 'required|string|max:255',
        ]);

        if ($this->editing) {
            $partnerCategorie = PartnersCategorie::find($this->partnerCategorieId);
            if ($partnerCategorie) {
                $partnerCategorie->update($this->partnerCategorie);
            }
        } else {
            PartnersCategorie::create($this->partnerCategorie);
        }

        $this->resetForm();
        $this->loadCategories();
    }

    public function edit($id)
    {
        $partnerCategorie = PartnersCategorie::find($id);
        if ($partnerCategorie) {
            $this->partnerCategorie = [
                'name' => $partnerCategorie->name,
            ];
            $this->partnerCategorieId = $id;
            $this->editing = true;
        }
    }

    public function delete($id)
    {
        PartnersCategorie::find($id)->delete();
        $this->loadCategories();
        if ($this->editing && $this->partnerCategorieId == $id) {
            $this->resetForm();
        }
    }

    public function render()
    {
        return view('livewire.partners-categories-list');
    }
}
