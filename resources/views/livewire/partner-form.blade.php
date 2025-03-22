<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <div class="{{ $partnerId ? 'col-lg-9' : 'col-lg-12' }}">
                    <h2 class="mb-0">{{ $partnerId ? 'Editar Parceiro' : 'Novo Parceiro' }}</h2>
                </div>
                @if($partnerId)
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="partner.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        @error('partner.status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="storeOrUpdate">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <label>Razão Social<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="partner.company_name">
                        @error('partner.company_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-6">
                        <label>Nome Fantasia<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="partner.fantasy_name">
                        @error('partner.fantasy_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>CNPJ<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cnpj" wire:model.live="partner.cnpj">
                        @error('partner.cnpj') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label>Telefone<span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" wire:model="partner.phone">
                        @error('partner.phone') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label>Celular/Whatsapp</label>
                        <input type="tel" class="form-control" id="whatsapp" wire:model="partner.whatsapp">
                        @error('partner.whatsapp') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>                    
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="partner.email">
                        @error('partner.email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label>Site</label>
                        <input type="text" class="form-control" id="site" wire:model.live="partner.site">
                        @error('partner.site') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="my-5">

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Endereço da Cooperativa</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CEP<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" data-cep id="zipcode" wire:model="partner.zipcode">
                        @error('partner.zipcode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-7">
                        <label>Endereço<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="address" wire:model="partner.address">
                        @error('partner.address') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label>Número<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="partner.number">
                        @error('partner.number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Complemento</label>
                        <input type="text" class="form-control" wire:model="partner.complement">
                        @error('partner.complement') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Bairro<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="neighborhood" wire:model="partner.neighborhood">
                        @error('partner.neighborhood') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Cidade<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="city" wire:model="partner.city">
                        @error('partner.city') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1">
                        <label>Estado<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="state" wire:model="partner.state">
                        @error('partner.state') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-lg btn-success" wire:click="storeOrUpdate">
                        {{ $partnerId ? 'Atualizar Parceiro' : 'Cadastrar Parceiro' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>