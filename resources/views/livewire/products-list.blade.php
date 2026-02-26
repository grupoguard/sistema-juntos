<div>
    <div class="container-fluid py-4">
        <div class="card">
            @if ($successMessage)
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ $successMessage }}
                    <button type="button" class="btn-close" wire:click="$set('successMessage', null)"></button>
                </div>
            @endif

            @if ($errorMessage)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errorMessage }}
                    <button type="button" class="btn-close" wire:click="$set('errorMessage', null)"></button>
                </div>
            @endif
            <div class="card-header pb-0">
                <h5 class="mb-0">Lista de Produtos</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Nome/Código/Valor" 
                            wire:model.live="search"
                        >
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" wire:model.lazy="statusFilter">
                            <option value="">
                                Todos
                            </option>
                            <option value="1">
                                Ativo
                            </option>
                            <option value="0">
                                Inativo
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        @can('products.create')
                            <a href="{{ route('admin.products.create') }}" class="btn bg-blue text-white">
                                + Novo Produto
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    Código
                                </th>
                                <th>
                                    Nome
                                </th>
                                <th>
                                    Valor
                                </th>
                                <th>
                                    Status
                                </th>
                                @can('products.edit')
                                    <th class="text-center">Editar</th>
                                @endcan

                                @can('products.delete')
                                    <th class="text-center">Excluir</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    {{ $product->code }}
                                </td>
                                <td>
                                    {{ $product->name }}
                                </td>
                                <td>
                                    R${{ number_format($product->value, 2, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $product->status ? 'success' : 'danger' }}">
                                        {{ $product->status ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                @can('products.edit')
                                    <td class="text-center">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-link text-dark fs-5 p-0 mb-0">
                                            <i class="fa fa-edit me-1"></i>
                                        </a>
                                    </td>
                                @endcan

                                @can('products.delete')
                                    <td class="text-center">
                                        <button wire:click="confirmDelete({{ $product->id }})" class="btn btn-link text-danger text-gradient fs-5 p-0 mb-0">
                                            <i class="fa fa-trash me-1"></i>
                                        </button>
                                    </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$products" />

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
                        Tem certeza que deseja excluir este producte? Esta ação não pode ser desfeita.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmingDelete', false)">Cancelar</button>
                        <button type="button" class="btn btn-danger" wire:click="delete()">Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
