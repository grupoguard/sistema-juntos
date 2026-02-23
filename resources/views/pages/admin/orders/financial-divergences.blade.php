@extends('layouts.user_type.auth')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Divergências Financeiras de Pedidos</h4>
            <p class="text-sm text-muted mb-0">
                Pedidos em que o valor calculado do pedido está diferente do valor cobrado no financeiro.
            </p>
        </div>
    </div>

    @livewire('order-financial-divergences-list')
@endsection