<div class="container-fluid py-4 ">
    <div class="row">
        <!-- Formulário -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $editing ? 'Editar Categoria' : 'Nova Categoria' }}</h4>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label>Nome da Categoria:</label>
                            <input type="text" class="form-control" wire:model="partnerCategorie.name">
                            @error('partnerCategorie.name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

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
                    <h4>Categorias Cadastrados</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Editar</th>
                                <th>Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allCategories as $partnerCategorie)
                                <tr>
                                    <td>{{ $partnerCategorie->name }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="edit({{ $partnerCategorie->id }})">✏️</button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" wire:click="delete({{ $partnerCategorie->id }})" onclick="confirm('Tem certeza que deseja excluir?') || event.stopImmediatePropagation()">🗑</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($allCategories->isEmpty())
                        <p class="text-center mt-3">Nenhuma categoria cadastrada.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
