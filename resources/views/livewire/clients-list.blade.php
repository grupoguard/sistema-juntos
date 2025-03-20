<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Lista de Clientes</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Buscar cliente..." wire:model="search">
                </div>
                <div class="col-md-4">
                    <select class="form-control" wire:model="statusFilter">
                        <option value="">Todos</option>
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.clients.create') }}" class="btn bg-blue text-white">+ Novo Cliente</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td>{{ $client->id }}</td>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->email }}</td>
                            <td>
                                <span class="badge bg-{{ $client->status ? 'success' : 'danger' }}">
                                    {{ $client->status ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <button wire:click="delete({{ $client->id }})" class="btn btn-sm btn-danger">Excluir</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $clients->links() }}

        </div>
    </div>
</div>
