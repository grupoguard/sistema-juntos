<div>
    <form wire:submit.prevent="saveOrder">
        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row align-items-center">
                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Dados do Cliente</h5>
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
                                <input type="text" class="form-control" id="cpf" wire:model.defer.live="client.cpf">
                                @error('client.cpf') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label>RG</label>
                                <input type="text" class="form-control" id="rg" wire:model.defer.live="client.rg">
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
                            <div class="col-lg-6 mb-3">
                                <label for="product_id" class="form-label">Produto<span class="text-danger">*</span></label>
                                <select id="product_id" class="form-control" wire:model="product_id" wire:change="loadAdditionals">
                                    <option value="">Selecione um produto</option>
                                    @foreach($products as $product)
                                        <option value="{{ (string) $product->id }}" {{ $product->status !== 1 ? 'disabled' : '' }} >{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Adicionais -->
                            <div class="col-lg-3 mb-3">
                                @if(!empty($additionals))
                                    <label class="form-label">Adicionais</label>
                                    @foreach($additionals as $additional)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model.change="selectedAdditionals" value="{{ $additional['id'] }}">
                                            <label class="form-check-label">{{ $additional['name'] }} - R$ {{ number_format($additional['value'], 2, ',', '.') }}</label>
                                        </div>
                                    @endforeach
                                @else
                                    <p>Nenhum adicional disponível.</p>
                                @endif
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label>Valor adesão (R$)<span class="text-danger">*</span></label>
                                <input type="number" id="order.accession" step="0.1" class="form-control" placeholder="R$ Adesão" wire:ignore="order.accession" value="{{ $this->order->accession }}" min="0">
                                @error('order.accession') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-lg-5 mb-3">
                                <label for="accession_payment" class="form-label">Pagamento adesão<span class="text-danger">*</span></label>
                                <select id="accession_payment" class="form-control" wire:model.change="accession_payment">
                                    <option value="">Selecione um pagamento</option>
                                    <option value="PIX">PIX</option>
                                    <option value="Boleto">Boleto</option>
                                    <option value="Cartão de crédito">Cartão de crédito</option>
                                    <option value="Cartão de débito">Cartão de débito</option>
                                    <option value="Não cobrada">Não cobrada</option>
                                </select>
                                @error('order.accession_payment') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-lg-4 mt-4">
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
                                        <select class="form-control" wire:model.change="dependents.{{ $index }}.marital_status">
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

                        <hr class="my-5">

                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Dados de cobrança</h5>
                        </div>

                        <!-- Tipo de Cobrança -->
                        <div class="row">
                            <div class="col-lg-3 mb-3">
                                <label for="order.charge_type" class="form-label">Tipo de Cobrança<span class="text-danger">*</span></label>
                                <select id="order.charge_type" class="form-control" wire:model.change="order.charge_type">
                                    <option value="">Selecione</option>
                                    <option value="EDP">EDP</option>
                                    <option value="BOLETO">Boleto</option>
                                </select>
                                @error('order.charge_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Campos Condicionais -->
                        @if($order['charge_type'] == 'EDP')
                            <div class="row">
                                <div class="col-lg-3 mb-3">
                                    <label for="order.installation_number" class="form-label">Número da Instalação<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="order.installation_number" min="1" max="999999999" oninput="this.value = this.value.slice(0, 9)" wire:model="order.installation_number">
                                    @error('order.installation_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="order.approval_name" class="form-label">Nome do Titular<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="order.approval_name" wire:model="order.approval_name">
                                    @error('order.approval_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="order.approval_by" class="form-label">Autorizado por<span class="text-danger">*</span></label>
                                    <select id="order.approval_by" class="form-control"  wire:model.change="order.approval_by">
                                        <option value="">Selecione</option>
                                        <option value="Titular">Titular</option>
                                        <option value="Conjuge">Cônjuge</option>
                                    </select>
                                    @error('order.approval_by') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="order.evidence_date" class="form-label">Data da Evidência<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="order.evidence_date" wire:model="order.evidence_date">
                                    @error('order.evidence_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <hr class="my-5">
                            {{-- Documentos --}}
                            <div class="row mt-4 mb-5">
                                <div class="col-lg-6">
                                    <h5 class="mb-0">Adicionar documentos</h5>
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
                        @endif

                        @if($order['charge_type'] == 'BOLETO')
                            <div class="col-lg-3 mb-3">
                                <label for="order.charge_date" class="form-label">Data da Cobrança</label>
                                <input type="number" class="form-control" id="order.charge_date" wire:model="order.charge_date">
                                @error('order.charge_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Botão de salvar -->
                        <div class="row">
                            <div class="col-lg-7">

                            </div>
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