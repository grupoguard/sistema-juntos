<div class="container-fluid py-4 ">
    <div class="row">
        <!-- Formulário -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $editing ? 'Editar Adicional' : 'Novo Adicional' }}</h4>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label>Nome do Adicional:</label>
                            <input type="text" class="form-control" wire:model="aditional.name">
                            @error('aditional.name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        @if($editing)
                            <div class="mb-3">
                                <label>Status:</label>
                                <select class="form-control" wire:model="aditional.status">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-lg btn-success">{{ $editing ? 'Atualizar' : 'Salvar' }}</button>
                        @if($editing)
                            <button type="button" class="btn btn-warning ms-2" wire:click="resetForm">Cancelar</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Adicionais -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h4>Adicionais Cadastrados</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Status</th>
                                <th>Editar</th>
                                <th>Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allAdditionals as $aditional)
                                <tr>
                                    <td>{{ $aditional->name }}</td>
                                    <td>
                                        @if($aditional->status)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-danger">Inativo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="edit({{ $aditional->id }})">✏️</button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" wire:click="delete({{ $aditional->id }})" onclick="confirm('Tem certeza que deseja excluir?') || event.stopImmediatePropagation()">🗑</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($allAdditionals->isEmpty())
                        <p class="text-center mt-3">Nenhum adicional cadastrado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
