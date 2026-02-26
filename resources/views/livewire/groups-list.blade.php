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
                <h5 class="mb-0">Lista de Cooperativas</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Buscar cooperativa..." 
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
                        @can('groups.create')
                            <a href="{{ route('admin.groups.create') }}" class="btn bg-blue text-white">
                                + Nova Cooperativa
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    ID
                                </th>
                                <th>
                                    Nome
                                </th>
                                <th>
                                    Telefone
                                </th>
                                <th>
                                    Status
                                </th>
                                @can('groups.edit')
                                    <th class="text-center">Editar</th>
                                @endcan

                                @can('groups.delete')
                                    <th class="text-center">Excluir</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $group)
                            <tr>
                                <td>
                                    {{ $group->id }}
                                </td>
                                <td>
                                    {{ $group->name }}
                                </td>
                                <td>
                                    {{ $group->phone }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $group->status ? 'success' : 'danger' }}">
                                        {{ $group->status ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                               @can('groups.edit')
                                    <td class="text-center">
                                        <a href="{{ route('admin.groups.edit', $group->id) }}" class="btn btn-link text-dark fs-5 p-0 mb-0">
                                            <i class="fa fa-edit me-1"></i>
                                        </a>
                                    </td>
                                @endcan

                                @can('groups.delete')
                                    <td class="text-center">
                                        <button wire:click="confirmDelete({{ $group->id }})" class="btn btn-link text-danger text-gradient fs-5 p-0 mb-0">
                                            <i class="fa fa-trash me-1"></i>
                                        </button>
                                    </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$groups" />

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
                        Tem certeza que deseja excluir esta cooperativa? Esta ação não pode ser desfeita.
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
