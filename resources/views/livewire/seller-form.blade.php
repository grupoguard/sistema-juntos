<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                @if(session('user_created'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">✅ Vendedor e usuário criados com sucesso!</h5>
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
                <div class="{{ $sellerId ? 'col-lg-6' : 'col-lg-8' }}">
                    <h2 class="mb-0">{{ $sellerId ? 'Editar Consultor' : 'Novo Consultor' }}</h2>
                </div>
                @if($sellerId)
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="seller.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        @error('seller.status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="storeOrUpdate">
                <div class="row mb-3">
                    <div class="col-md-6 offset-lg-6 mb-4">
                        <livewire:components.select-group :group_id="$seller['group_id']" />
                        @error('seller.group_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-9">
                        <label>Nome do consultor<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="seller.name">
                        @error('seller.name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label>Data de nascimento<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model="seller.date_birth">
                        @error('seller.date_birth') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CPF<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cpf" wire:model.live="seller.cpf">
                        @error('seller.cpf') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label>RG</label>
                        <input type="text" class="form-control" id="rg" wire:model.live="seller.rg">
                        @error('seller.rg') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label>Telefone</label>
                        <input type="tel" class="form-control" id="phone" wire:model="seller.phone">
                        @error('seller.phone') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="seller.email">
                        @error('seller.email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-4">
                        <label>Tipo de comissão<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="seller.comission_type">
                            <option value="">Selecione</option>
                            <option value="0">Padrão</option>
                            <option value="1">Fixo (R$)</option>
                            <option value="2">Porcentagem (%)</option>
                        </select>
                        @error('seller.comission_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-4">
                        <label>Valor da comissão<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="seller.comission_value">
                        @error('seller.comission_value') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-4">
                        <label>Recorrência (meses)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="seller.comission_recurrence">
                        @error('seller.comission_recurrence') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if(!$sellerId)
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
                                <strong>Criar usuário para este consultor</strong>
                            </label>
                        </div>
                        <small class="text-muted">
                            O usuário terá acesso ao sistema como vendedor e poderá gerenciar seus pedidos.
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

                <hr class="my-5">

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Endereço do Consultor</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CEP<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" data-cep id="zipcode" wire:model="seller.zipcode">
                        @error('seller.zipcode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-7 ">
                        <label>Endereço<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="address" wire:model="seller.address">
                        @error('seller.address') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label>Número<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="seller.number">
                        @error('seller.number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Complemento</label>
                        <input type="text" class="form-control" wire:model="seller.complement">
                        @error('seller.complement') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Bairro<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="neighborhood" wire:model="seller.neighborhood">
                        @error('seller.neighborhood') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Cidade<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="city" wire:model="seller.city">
                        @error('seller.city') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1">
                        <label>Estado<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="state" wire:model="seller.state">
                        @error('seller.state') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-lg btn-success">
                        {{ $sellerId ? 'Atualizar Consultor' : 'Cadastrar Consultor' }}
                    </button>
                </div>
            </form>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>