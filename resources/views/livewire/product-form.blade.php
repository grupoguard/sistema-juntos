<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <div class="{{ $productId ? 'col-lg-9' : 'col-lg-12' }}">
                    <h2 class="mb-0">{{ $productId ? 'Editar Produto' : 'Novo Produto' }}</h2>
                </div>
                @if($productId)
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="product.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        @error('product.status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="storeOrUpdate">
                <div class="row mb-3">
                    <div class="col-lg-3">
                        <label>Código do produto<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="code" wire:model.live="product.code">
                        @error('product.code') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-9">
                        <label>Nome do produto<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="product.name">
                        @error('product.name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-2">
                        <label>Valor (R$)<span class="text-danger">*</span></label>
                        <input type="number" step="0.1" class="form-control" wire:model="product.value">
                        @error('product.value') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-2">
                        <label>Adesão (R$)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="product.accession">
                        @error('product.accession') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-2">
                        <label>Limite de dependentes<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="product.dependents_limit">
                        @error('product.dependents_limit') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-3">
                        <label>Recorrência<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="product.recurrence">
                            <option value="">Selecione</option>
                            <option value="mensal">Mensal</option>
                            <option value="anual">Anual</option>
                        </select>
                        @error('product.recurrence') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-lg-3">
                        <label>Carência<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="product.lack">
                        @error('product.lack') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="my-5">

                <div class="row mt-3">
                    <div class="col-lg-6">
                        <h5 class="mb-0">Adicionais</h5>
                    </div>
                    <div class="col-lg-6 text-end">
                        <button type="button" class="btn bg-blue text-white" wire:click="addAdditionals">+ Vincular Adicional</button>
                    </div>
                </div>
                
                @foreach($additionals as $index => $additional)
                    <div class="row align-items-end mt-4">
                        <div class="col-md-7 mb-3">
                            <label>Adicional<span class="text-danger">*</span></label>
                            @if($additional['locked'])
                                <input type="text" class="form-control" value="{{ $availableAdditionals->firstWhere('id', $additional['aditional_id'])?->name }}" readonly>
                            @else
                                <select class="form-control" wire:model="additionals.{{ $index }}.aditional_id">
                                    <option value="">Selecione</option>
                                    @foreach($availableAdditionals as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('additionals.' . $index . '.aditional_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Valor (R$)</label>
                            <input type="number" step="0.1" class="form-control" wire:model="additionals.{{ $index }}.value" {{ $additional['locked'] ? 'readonly' : '' }}>
                            @error('additionals.' . $index . '.value') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-danger" wire:click="removeAdditionals({{ $index }})">X</button>
                        </div>
                    </div>
                @endforeach

                <hr class="my-5">

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-lg btn-success" wire:click="storeOrUpdate">
                        {{ $productId ? 'Atualizar Produto' : 'Cadastrar Produto' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>