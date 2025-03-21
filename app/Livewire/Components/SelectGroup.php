<?php

namespace App\Livewire\Components;

use App\Models\Group;
use Livewire\Component;

class SelectGroup extends Component
{
    public $groups = []; // Lista de grupos
    public $group_id; // ID selecionado

    public function mount($group_id = null) // Agora recebe o valor inicial
    {
        $this->group_id = $group_id;
        $this->groups = Group::orderBy('name')->get();
    }

    public function updatedGroupId($value)
    {
        $this->group_id = $value ?: null;
        $this->dispatch('groupSelected', $this->group_id); // Dispara evento Livewire
    }

    public function render()
    {
        return view('livewire.components.select-group');
    }
}
