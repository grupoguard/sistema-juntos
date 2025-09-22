<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <div class="{{ $clientId ? 'col-lg-6' : 'col-lg-8' }}">
                    <h2 class="mb-0">{{ $clientId ? 'Editar Cliente' : 'Novo Cliente' }}</h2>
                </div>
                <div class="{{ $clientId ? 'col-lg-3' : 'col-lg-4' }}">
                    <livewire:components.select-group :group_id="$client['group_id']" />
                    @error('client.group_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                @if($clientId)
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="client.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        @error('client.status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="storeOrUpdate">
                <div class="row mb-3">
                    <div class="col-12">
                        <label>Nome do cliente<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="client.name">
                        @error('client.name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-3">
                        <label>Gênero<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="client.gender">
                            <option value="">Selecione</option>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                            <option value="outros">Outros</option>
                        </select>
                        @error('client.gender') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-9">
                        <label>Nome da mãe<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="client.mom_name">
                        @error('client.mom_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CPF<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cpf" wire:model.live="client.cpf">
                        @error('client.cpf') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label>RG</label>
                        <input type="text" class="form-control" id="rg" wire:model.live="client.rg">
                        @error('client.rg') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label>Data de nascimento<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model="client.date_birth">
                        @error('client.date_birth') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label>Celular/Whatsapp</label>
                        <input type="tel" class="form-control" id="phone" wire:model="client.phone">
                        @error('client.phone') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Estado Civil<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="client.marital_status">
                            <option value="">Selecione</option>
                            <option value="solteiro">Solteiro(a)</option>
                            <option value="casado">Casado(a)</option>
                            <option value="separado">Separado(a)</option>
                            <option value="divorciado">Divorciado(a)</option>
                            <option value="viuvo">Viúvo(a)</option>
                            <option value="uniao_estavel">União Estável</option>
                            <option value="nao_informado">Não informado</option>
                        </select>
                        @error('client.marital_status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-9">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="client.email">
                        @error('client.email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="my-5">

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Endereço do Cliente</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CEP<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" data-cep id="zipcode" wire:model="client.zipcode">
                        @error('client.zipcode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-7 ">
                        <label>Endereço<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="address" wire:model="client.address">
                        @error('client.address') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label>Número<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="client.number">
                        @error('client.number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Complemento</label>
                        <input type="text" class="form-control" wire:model="client.complement">
                        @error('client.complement') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Bairro<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="neighborhood" wire:model="client.neighborhood">
                        @error('client.neighborhood') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Cidade<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="city" wire:model="client.city">
                        @error('client.city') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1">
                        <label>Estado<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="state" wire:model="client.state">
                        @error('client.state') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="my-5">

                <div class="row mt-3">
                    <div class="col-lg-6">
                        <h5 class="mb-0">Dependentes</h5>
                    </div>
                    <div class="col-lg-6 text-end">
                        <button type="button" class="btn bg-blue text-white" wire:click="addDependent">+ Adicionar Dependente</button>
                    </div>
                </div>

                @foreach($dependents as $index => $dependent)
                    <div class="row align-items-end mt-4">
                        <div class="col-md-12 mb-3">
                            <label>Nome do dependente<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Nome do Dependente" wire:model="dependents.{{ $index }}.name">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Grau de Parentesco<span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="dependents.{{ $index }}.relationship">
                                <option value="">Selecione</option>
                                <option value="solteiro">Mãe/Pai</option>
                                <option value="casado">Irmão(ã)</option>
                                <option value="separado">Filho(a)</option>
                                <option value="separado">Cônjuge</option>
                                <option value="outro">Outro</option>
                            </select>
                            @error('dependents.{{ $index }}.relationship') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>CPF<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="dependents.{{ $index }}.cpf">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>RG</label>
                            <input type="text" class="form-control" wire:model="dependents.{{ $index }}.rg">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Data de nascimento<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" wire:model="dependents.{{ $index }}.date_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Estado Civil<span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="dependents.{{ $index }}.marital_status">
                                <option value="">Selecione</option>
                                <option value="solteiro">Solteiro(a)</option>
                                <option value="casado">Casado(a)</option>
                                <option value="separado">Separado(a)</option>
                                <option value="divorciado">Divorciado(a)</option>
                                <option value="viuvo">Viúvo(a)</option>
                                <option value="uniao_estavel">União Estável</option>
                                <option value="outro">Outro</option>
                            </select>
                            @error('dependents.{{ $index }}.marital_status') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nome da mãe<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Nome da mãe" wire:model="dependents.{{ $index }}.mom_name">
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-danger" wire:click="removeDependent({{ $index }})">X</button>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-lg btn-success" wire:click="storeOrUpdate">
                        {{ $clientId ? 'Atualizar Cliente' : 'Cadastrar Cliente' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>