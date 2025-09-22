<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <div class="{{ $sellerId ? 'col-lg-6' : 'col-lg-8' }}">
                    <h2 class="mb-0">{{ $sellerId ? 'Editar Consultor' : 'Novo Consultor' }}</h2>
                </div>
                <div class="{{ $sellerId ? 'col-lg-3' : 'col-lg-4' }}">
                    <livewire:components.select-group :group_id="$seller['group_id']" />
                    @error('seller.group_id') <span class="text-danger">{{ $message }}</span> @enderror
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
                    <button type="submit" class="btn btn-lg btn-success" wire:click="storeOrUpdate">
                        {{ $sellerId ? 'Atualizar Consultor' : 'Cadastrar Consultor' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>