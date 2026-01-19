<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                @if(session('user_created'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">✅ Cooperativa e usuário criados com sucesso!</h5>
                        <hr>
                        <p><strong>Credenciais de Acesso:</strong></p>
                        <p class="mb-1">
                            <strong>Email:</strong> {{ session('user_email') }}<br>
                            <strong>Senha:</strong> <code class="fs-5">{{ session('user_password') }}</code>
                        </p>
                        <hr>
                        <p class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>IMPORTANTE:</strong> Anote esta senha! Ela não será exibida novamente.
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="{{ $groupId ? 'col-lg-9' : 'col-lg-12' }}">
                    <h2 class="mb-0">{{ $groupId ? 'Editar Cooperativa' : 'Nova Cooperativa' }}</h2>
                </div>
                @if($groupId)
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="group.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        @error('group.status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="storeOrUpdate">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <label>Razão Social<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="group.group_name">
                        @error('group.group_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-6">
                        <label>Nome Fantasia<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="group.name">
                        @error('group.name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>CNPJ<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cnpj" wire:model.live="group.document">
                        @error('group.document') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label>Telefone<span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" wire:model="group.phone">
                        @error('group.phone') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label>Celular/Whatsapp</label>
                        <input type="tel" class="form-control" id="whatsapp" wire:model="group.whatsapp">
                        @error('group.whatsapp') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="group.email">
                        @error('group.email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label>Site</label>
                        <input type="text" class="form-control" id="site" wire:model.live="group.site">
                        @error('group.site') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if(!$groupId)
                <hr class="my-5">

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Dados de acesso</h5>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                       <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="createUser" id="createUser">
                            <label class="form-check-label" for="createUser">
                                <strong>Criar usuário para esta cooperativa</strong>
                            </label>
                        </div>
                        <small class="text-muted">
                            O usuário terá acesso ao sistema como COOP e poderá gerenciar seus vendedores e pedidos.
                        </small>
                    </div>
                    @if($createUser)
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Email do usuário:</strong> {{ $group['email'] ?? 'Preencha o email acima' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha Customizada (Opcional)</label>
                            <input type="password" wire:model="userPassword" class="form-control @error('userPassword') is-invalid @enderror" 
                                placeholder="Deixe vazio para gerar automaticamente">
                            @error('userPassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">
                                Se deixar vazio, uma senha aleatória será gerada e exibida após o cadastro.
                            </small>
                        </div>
                    @endif 
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Endereço da Cooperativa</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CEP<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" data-cep id="zipcode" wire:model="group.zipcode">
                        @error('group.zipcode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-7">
                        <label>Endereço<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="address" wire:model="group.address">
                        @error('group.address') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label>Número<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="group.number">
                        @error('group.number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Complemento</label>
                        <input type="text" class="form-control" wire:model="group.complement">
                        @error('group.complement') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Bairro<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="neighborhood" wire:model="group.neighborhood">
                        @error('group.neighborhood') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Cidade<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="city" wire:model="group.city">
                        @error('group.city') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1">
                        <label>Estado<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="state" wire:model="group.state">
                        @error('group.state') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-lg btn-success" wire:click="storeOrUpdate">
                        {{ $groupId ? 'Atualizar Cooperativa' : 'Cadastrar Cooperativa' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>