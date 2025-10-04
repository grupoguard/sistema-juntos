<div>
    <div class="container-fluid py-4">
        <div class="card">
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
                        <a 
                            href="{{ route('admin.groups.create') }}" 
                            class="btn bg-blue text-white">
                                + Nova Cooperativa
                        </a>
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
                                <th class="text-center">
                                    Editar
                                </th>
                                <th class="text-center">
                                    Excluir
                                </th>
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
                                <td class="text-center">
                                    <a 
                                        href="{{ route('admin.groups.edit', $group->id) }}" 
                                        class="btn btn-link text-dark fs-5 p-0 mb-0">
                                        <i class="fa fa-edit me-1"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button 
                                        wire:click="confirmDelete({{ $group->id }})" 
                                        class="btn btn-link text-danger text-gradient fs-5 p-0 mb-0"
                                    >
                                        <i class="fa fa-trash me-1"></i>
                                    </button>
                                </td>
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
