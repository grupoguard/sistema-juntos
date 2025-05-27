<?php

namespace App\Livewire;

use App\Models\Aditional;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Traits\OrderFormTrait;
use Illuminate\Routing\Route;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class OrderEdit extends Component
{
    use WithFileUploads, OrderFormTrait;

    

    public function render()
    {
        return view('livewire.order-edit');
    }
}
