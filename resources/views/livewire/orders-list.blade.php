<div>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Pedidos</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Buscar pedido..." 
                            wire:model.live="search"
                        >
                    </div>
                    <div class="col-md-9 text-end">
                        <a 
                            href="{{ route('admin.orders.create') }}" 
                            class="btn bg-blue text-white">
                                + Novo pedido
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Produto</th>
                                <th>Consultor</th>
                                <th>Data</th>
                                <th class="text-center">
                                    Editar
                                </th>
                                <th class="text-center">
                                    Excluir
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->client->name }}</td>
                                    <td>{{ $order->product->name }}</td>
                                    <td>{{ $order->seller->name }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <a 
                                            href="{{ route('admin.orders.edit', $order->id) }}"
                                            class="btn btn-link text-dark fs-5 p-0 mb-0">
                                            <i class="fa fa-edit me-1"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <button 
                                            wire:click="confirmDelete({{ $order->id }})" 
                                            class="btn btn-danger btn-sm"
                                        >
                                            <i class="fa fa-trash me-1"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $orders->links() }}

            </div>
        </div>
    </div>
    @if($confirmingDelete)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja excluir este pedido? Esta ação não pode ser desfeita.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmingDelete', false)">Cancelar</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteOrder()" >Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
