<div>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist" wire:ignore>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Dados do pedido</button>
        </li>
        @if($charge_type == 'EDP')
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Evidências</button>
            </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Registro Financeiro</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <form wire:submit.prevent="updateOrder">
                <div class="container-fluid py-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="row align-items-center">
                                <div class="col-10 mb-3">
                                    <h5 class="mb-0">Dados do Cliente</h5>
                                </div>
                                <div class="col-2 text-end">
                                    @if($charge_type == 'EDP')
                                        <span class="badge bg-warning text-dark">
                                            {{ $charge_type }}
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            {{ $charge_type }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Dados do Cliente -->
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
                                        <select class="form-control" wire:model.change="client.gender">
                                            <option value="">Selecione</option>
                                            <option value="masculino">Masculino</option>
                                            <option value="feminino">Feminino</option>
                                            <option value="outros">Outros</option>
                                        </select>
                                        @error('client.gender') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-9">
                                        <label>Nome da mãe<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model.defer="client.mom_name">
                                        @error('client.mom_name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>CPF<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="cpf" wire:model.defer.live="client.cpf" disabled>
                                        @error('client.cpf') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label>RG</label>
                                        <input type="text" class="form-control" id="rg" wire:model.defer.live="client.rg" disabled>
                                        @error('client.rg') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label>Data de nascimento<span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model.defer="client.date_birth">
                                        @error('client.date_birth') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label>Celular/Whatsapp</label>
                                        <input type="tel" class="form-control" id="phone" wire:model.defer="client.phone">
                                        @error('client.phone') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>Estado Civil<span class="text-danger">*</span></label>
                                        <select class="form-control" wire:model.change="client.marital_status">
                                            <option value="">Selecione</option>
                                            <option value="solteiro">Solteiro(a)</option>
                                            <option value="casado">Casado(a)</option>
                                            <option value="divorciado">Divorciado(a)</option>
                                            <option value="viuvo">Viúvo(a)</option>
                                            <option value="uniao_estavel">União Estável</option>
                                            <option value="nao_informado">Não informado</option>
                                        </select>
                                        @error('client.marital_status') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-9">
                                        <label>Email<span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" wire:model.defer="client.email">
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
                                        <input type="number" class="form-control" data-cep id="zipcode" wire:model.defer="client.zipcode">
                                        @error('client.zipcode') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-7 ">
                                        <label>Endereço<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" data-field="address" wire:model.defer="client.address">
                                        @error('client.address') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label>Número<span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" wire:model.defer="client.number">
                                        @error('client.number') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>Complemento</label>
                                        <input type="text" class="form-control" wire:model.defer="client.complement">
                                        @error('client.complement') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                            
                                    <div class="col-md-4">
                                        <label>Bairro<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" data-field="neighborhood" wire:model.defer="client.neighborhood">
                                        @error('client.neighborhood') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                            
                                    <div class="col-md-4">
                                        <label>Cidade<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" data-field="city" wire:model.defer="client.city">
                                        @error('client.city') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                            
                                    <div class="col-md-1">
                                        <label>Estado<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" data-field="state" wire:model.defer="client.state">
                                        @error('client.state') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <hr class="my-5">

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h5 class="mb-0">Dados do Pedido</h5>
                                    </div>
                                </div>
                                
                                <!-- Seleção de Consultor -->
                                <div class="row {{ empty($additionals) ? 'align-items-center' : 'align-items-ender' }}">
                                    <div class="col-lg-3 mb-3">
                                        <label for="seller_id" class="form-label">Consultor<span class="text-danger">*</span></label>
                                        <select id="seller_id" class="form-control" wire:model="seller_id">
                                            <option value="">Selecione um consultor</option>
                                            @foreach($sellers as $seller)
                                                <option value="{{ (string) $seller['id'] }}">{{ $seller['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('seller_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                
                                    <!-- Seleção de Produto -->
                                    <div class="col-lg-9 mb-3">
                                        <label for="product_id" class="form-label">Produto<span class="text-danger">*</span></label>
                                        <select id="product_id" class="form-control" wire:model="product_id" wire:change="loadAdditionals">
                                            <option value="">Selecione um produto</option>
                                            @foreach($products as $product)
                                                <option value="{{ (string) $product->id }}" {{ $product->status !== 1 ? 'disabled' : '' }} >{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- CAMPOS DE DESCONTO -->
                                    <div class="col-lg-4 mb-3">
                                        <label for="discount_type" class="form-label">Tipo de Desconto</label>
                                        <select id="discount_type" class="form-control" wire:model.live="discount_type">
                                            <option value="">Sem desconto</option>
                                            <option value="R$">Valor (R$)</option>
                                            <option value="%">Percentual (%)</option>
                                        </select>
                                        @error('discount_type') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-lg-8 mb-3">
                                        <label for="discount_value" class="form-label">Valor do Desconto</label>
                                        <input type="number" step="0.01" class="form-control" 
                                            placeholder="{{ $discount_type === '%' ? '0.00%' : 'R$ 0.00' }}" 
                                            wire:model.live="discount_value" 
                                            min="0" 
                                            {{ empty($discount_type) ? 'disabled' : '' }}
                                            max="{{ $discount_type === '%' ? '100' : '' }}">
                                        @error('discount_value') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    
                                    <!-- Adicionais -->
                                    <div class="col-lg-3 mb-3">
                                        @if(!empty($additionals))
                                            <label class="form-label">Adicionais</label>
                                            @foreach($additionals as $additional)
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" wire:model.live="selectedAdditionals" value="{{ $additional['id'] }}">
                                                    <label class="form-check-label">{{ $additional['name'] }} - R$ {{ number_format($additional['value'], 2, ',', '.') }}</label>
                                                </div>
                                            @endforeach
                                        @else
                                            <p>Nenhum adicional disponível.</p>
                                        @endif
                                    </div>
                                    <div class="col-lg-2 mb-3">
                                        <label>Valor adesão (R$)<span class="text-danger">*</span></label>
                                       <input type="number" step="0.01" class="form-control" placeholder="R$ Adesão" wire:model="accession" min="0">
                                        @error('accession') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <label for="accession_payment" class="form-label">Pagamento adesão<span class="text-danger">*</span></label>
                                        <select id="accession_payment" class="form-control" wire:model.change="accession_payment">
                                            <option value="">Selecione um pagamento</option>
                                            <option value="PIX">PIX</option>
                                            <option value="Boleto">Boleto</option>
                                            <option value="Cartão de crédito">Cartão de crédito</option>
                                            <option value="Cartão de débito">Cartão de débito</option>
                                            <option value="Não cobrada">Não cobrada</option>
                                        </select>
                                        @error('accession_payment') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-3 mt-4">
                                        <h3>
                                            Total:
                                            <span class="total" id="total">
                                                R$ {{ number_format($total, 2, ',', '.') }}
                                            </span>
                                        </h3>
                                    </div>
                                </div>

                                <hr class="my-5">
                                {{-- Dependentes --}}
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5 class="mb-0">Dependentes</h5>
                                    </div>
                                    <div class="col-lg-6 text-end">
                                        <button type="button" class="btn bg-blue text-white" wire:click="addDependent">Adicionar Dependente</button>
                                        @if (session()->has('error'))
                                            <div class="alert alert-danger me-3 text-center text-white">
                                                {{ session('error') }}
                                            </div>
                                        @endif
                                    </div>

                                    @foreach($dependents as $index => $dependent)
                                        <div class="row align-items-end mt-4">
                                            <div class="col-md-12 mb-3">
                                                <label>Nome do dependente<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" placeholder="Nome do Dependente" wire:model="dependents.{{ $index }}.name">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label>Grau de Parentesco<span class="text-danger">*</span></label>
                                                <select class="form-control" wire:model.defer="dependents.{{ $index }}.relationship">
                                                    <option value="">Selecione</option>
                                                    <option value="mae-pai">Mãe/Pai</option>
                                                    <option value="irmao">Irmão(ã)</option>
                                                    <option value="conjuge">Cônjuge</option>
                                                    <option value="filho">Filho</option>
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
                                            <div class="col-md-2 mb-3">
                                                <label>Estado Civil<span class="text-danger">*</span></label>
                                                <select class="form-control" wire:model.live="dependents.{{ $index }}.marital_status">
                                                    <option value="">Selecione</option>
                                                    <option value="solteiro">Solteiro(a)</option>
                                                    <option value="casado">Casado(a)</option>
                                                    <option value="divorciado">Divorciado(a)</option>
                                                    <option value="viuvo">Viúvo(a)</option>
                                                    <option value="uniao_estavel">União Estável</option>
                                                    <option value="outro">Outro</option>
                                                    <option value="nao_informado">Não informado</option>
                                                </select>
                                                @error('dependents.{{ $index }}.marital_status') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-5 mb-3">
                                                <label>Nome da mãe<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" placeholder="Nome da mãe" wire:model="dependents.{{ $index }}.mom_name">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                @if(!empty($additionals))
                                                    <label class="form-label">Adicionais</label>
                                                    @foreach($additionals as $additional)
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" wire:model.change="dependents.{{ $index }}.additionals" value="{{ $additional['id'] }}">
                                                            <label class="form-check-label">{{ $additional['name'] }} - R$ {{ number_format($additional['value'], 2, ',', '.') }}</label>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p>Nenhum adicional disponível.</p>
                                                @endif
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <button type="button" class="btn btn-danger" wire:click="removeDependent({{ $index }})">X</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Botão de salvar -->
                                <div class="row">
                                    <div class="col-lg-5 text-end">
                                        <button type="submit" class="btn btn-success btn-lg">Alterar Pedido</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @if($charge_type == 'EDP')
            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                <div class="container-fluid py-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="row align-items-center">
                                <div class="col-lg-3 mb-3">
                                    <label for="installation_number" class="form-label">Número da Instalação<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="installation_number" min="1" max="9999999999" oninput="this.value = this.value.slice(0, 10)" wire:model="installation_number">
                                    @error('installation_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="approval_name" class="form-label">Nome do Titular<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="approval_name" wire:model="approval_name">
                                    @error('approval_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="approval_by" class="form-label">Autorizado por<span class="text-danger">*</span></label>
                                    <select id="approval_by" class="form-control"  wire:model.change="approval_by">
                                        <option value="">Selecione</option>
                                        <option value="Titular">Titular</option>
                                        <option value="Conjuge">Cônjuge</option>
                                    </select>
                                    @error('approval_by') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="evidence_date" class="form-label">Data da Evidência<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="evidence_date" wire:model="evidence_date">
                                    @error('evidence_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <hr class="my-5">
                            {{-- Documentos --}}
                            <div class="row mt-4 mb-5">
                                <div class="col-lg-6">
                                    <h5 class="mb-0">Enviar nova evidência</h5>
                                    @error('evidences')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-lg-6 text-end">
                                    <button type="button" class="btn bg-blue text-white" wire:click="addEvidence">Adicionar Documentos</button>
                                </div>
                                @foreach($evidences as $index => $evidence)
                                    <div class="row align-items-center mt-4">
                                        <div class="col-md-3 mb-3">
                                            <label>Tipo de evidência<span class="text-danger">*</span></label>
                                            <select class="form-control" wire:model="evidences.{{ $index }}.evidence_type">
                                                <option value="selecione">Selecione</option>
                                                <option value="audio">Audio</option>
                                                <option value="contrato">Contrato</option>
                                                <option value="certidao de casamento">Certidão de Casamento</option>
                                                <option value="cpf">CPF</option>
                                                <option value="rg">RG</option>
                                                <option value="cnh">Carteira de Motorista</option>
                                                <option value="outro">Outro</option>
                                            </select>
                                            @error('evidences.{{ $index }}.evidence_type') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <label>Arquivo cadastrado</label><br>
                                            @if ($evidence['evidence_type'] === 'audio')
                                                <audio controls>
                                                    <source src="{{ asset('storage/' . $evidence['document']) }}" type="audio/mpeg">
                                                    Seu navegador não suporta a tag de áudio.
                                                </audio>
                                            @else 
                                                <a href="{{ asset('storage/' . $evidence['document']) }}" target="_blank">
                                                    <i class="fa fa-file-pdf-o fa-2x"></i>
                                                </a>
                                            @endif
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label>Documento<span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" wire:model="evidences.{{ $index }}.document">
                                            @error('evidences.{{ $index }}.document') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button type="button" class="btn btn-danger" wire:click="removeEvidence({{ $index }})">X</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="row align-items-center">
                            <div class="col-lg-3 mb-3">
                                <label for="order.charge_type" class="form-label">Tipo de Cobrança<span class="text-danger">*</span></label>
                                <select id="charge_type" class="form-control" wire:model.change="charge_type" disabled>
                                    <option value="">Selecione</option>
                                    <option value="EDP">EDP</option>
                                    <option value="ASAAS">Asaas</option>
                                </select>
                                @error('charge_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            @if($order['charge_type'] == 'ASAAS')
                                <div class="col-lg-3 mb-3">
                                    <label for="charge_date" class="form-label">Data da Cobrança</label>
                                    <input type="number" class="form-control" id="charge_date" wire:model="charge_date">
                                    @error('charge_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">Histórico Financeiro</h5>

                                @if(!empty($financials) && count($financials) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Data Vencimento</th>
                                                    <th>Valor</th>
                                                    <th>Pago</th>
                                                    <th>Método</th>
                                                    <th>Status</th>
                                                    <th>Link</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($financials as $fin)
                                                    @php
                                                        $isPaid = in_array($fin->status, ['RECEIVED', 'CONFIRMED']);
                                                    @endphp

                                                    <tr style="background-color: {{ $isPaid ? '#e8f5e9' : '#fdecea' }};">
                                                        <td>{{ $fin->id }}</td>

                                                        <td>
                                                            {{ $fin->due_date ? \Carbon\Carbon::parse($fin->due_date)->format('d/m/Y') : '-' }}
                                                        </td>

                                                        <td>
                                                            R$ {{ number_format($fin->value, 2, ',', '.') }}
                                                        </td>

                                                        <td>
                                                            R$ {{ number_format($fin->paid_value ?? 0, 2, ',', '.') }}
                                                        </td>

                                                        <td>
                                                            {{ $fin->payment_method ?? '-' }}
                                                        </td>

                                                        <td>
                                                            <span class="badge 
                                                                {{ $isPaid ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $fin->status }}
                                                            </span>
                                                        </td>

                                                        <td class="text-center">
                                                            @if($fin->invoice_url)
                                                                <a href="{{ $fin->invoice_url }}" target="_blank" class="btn btn-sm btn-primary">
                                                                    Ver
                                                                </a>
                                                            @elseif($fin->bank_slip_url)
                                                                <a href="{{ $fin->bank_slip_url }}" target="_blank" class="btn btn-sm btn-warning">
                                                                    Boleto
                                                                </a>
                                                            @elseif($fin->pix_qr_code_url)
                                                                <a href="{{ $fin->pix_qr_code_url }}" target="_blank" class="btn btn-sm btn-success">
                                                                    Pix
                                                                </a>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        Nenhum registro financeiro encontrado para este pedido.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
