<div>
    <div wire:ignore.self>
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Dados do pedido</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-docs-tab" data-bs-toggle="pill" data-bs-target="#pills-docs"
                        type="button" role="tab" aria-controls="pills-docs" aria-selected="false">
                    Documentos
                </button>
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
    </div>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <form wire:submit.prevent="updateOrder">
                @if(auth()->user()->isAdmin())
                    <!-- <div class="card mt-4">
                        <div class="card-body">
                            <h5>Análise do Pedido</h5>

                            <div class="mb-3">
                                <label>Observações da análise (opcional)</label>
                                <textarea class="form-control" wire:model="review_notes" rows="3"></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" wire:click="approveOrder">
                                    Aprovar pedido
                                </button>
                                <button type="button" class="btn btn-danger" wire:click="rejectOrder">
                                    Rejeitar pedido
                                </button>
                            </div>
                        </div>
                    </div> -->
                @endif
                <div class="container-fluid py-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="row align-items-center">
                                <div class="col-10 mb-3">
                                    <h5 class="mb-0">Dados do Cliente</h5>
                                </div>
                                <div class="col-2 text-end">
                                    <div class="row mb-3">
                                        <label>Status do pedido<span class="text-danger">*</span></label>
                                        <select class="form-control" wire:model="order_status">
                                            <option value="ativo">Ativo</option>
                                            <option value="inadimplente">Inadimplente</option>
                                            <option value="cancelado">Cancelado</option>
                                        </select>
                                        @error('order_status') <span class="text-danger">{{ $message }}</span> @enderror
                                        <small class="text-muted d-block mt-1">
                                            Cancelado não gera novas cobranças.
                                        </small>
                                    </div>
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
                                        <div class="row align-items-end mt-4" wire:key="dep-{{ $dependent['dependent_id'] ?? $index }}">
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
                                    <div class="col-lg-5">
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
        <div class="tab-pane fade" id="pills-docs" role="tabpanel" aria-labelledby="pills-docs-tab">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5 class="mb-0">Documentos e Contrato</h5>
                    </div>

                    <div class="card-body">
                        {{-- CONTRATO GERADO (visualização + PDF) --}}
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <h6>Visualização do contrato</h6>
                                <a class="btn btn-outline-primary btn-sm"
                                href="{{ route('admin.orders.contract.preview', $order->id) }}" target="_blank">
                                    Abrir visualização
                                </a>

                                <a class="btn btn-primary btn-sm"
                                href="{{ route('admin.orders.contract.pdf', $order->id) }}" target="_blank">
                                    Baixar PDF
                                </a>

                                <div class="text-muted mt-2">
                                    O contrato gerado sempre usa os dados atuais do pedido.
                                    O que vale juridicamente é o contrato assinado (link ou físico).
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h6>Contrato assinado (digital)</h6>
                                <input type="text" class="form-control" placeholder="https://..."
                                    wire:model.defer="order.signed_contract_url">

                                <small class="text-muted d-block mt-1">
                                    Cole aqui a URL do serviço de assinatura após o cliente assinar.
                                </small>

                                <hr>

                                <h6>Contrato físico (scan)</h6>

                                @if(!empty($order->signed_physical_contract_file))
                                    <a class="btn btn-outline-primary btn-sm"
                                    href="{{ Storage::url($order->signed_physical_contract_file) }}" target="_blank">
                                        Ver contrato físico atual
                                    </a>
                                @else
                                    <div class="alert alert-light border py-2 mt-2">
                                        Contrato físico não enviado.
                                    </div>
                                @endif

                                <input type="file" class="form-control mt-2" wire:model="signed_physical_contract_file"
                                    accept="image/*,application/pdf">
                                @error('signed_physical_contract_file') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <hr>

                        {{-- RG/CNH e COMPROVANTE --}}
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Documento (RG/CNH)</h6>

                                @if(!empty($order->document_file))
                                    <a href="{{ Storage::url($order->document_file) }}" target="_blank">
                                        Ver documento atual ({{ $order->document_file_type ?? 'RG' }})
                                    </a>
                                @else
                                    <div class="alert alert-warning py-2 mt-2">Nenhum documento anexado.</div>
                                @endif

                                <div class="mt-2">
                                    <label>Tipo</label>
                                    <select class="form-control" wire:model="document_file_type">
                                        <option value="RG">RG</option>
                                        <option value="CNH">CNH</option>
                                    </select>
                                </div>

                                <div class="mt-2">
                                    <label>Substituir documento</label>
                                    <input type="file" class="form-control" wire:model="document_file" accept="image/*,application/pdf">
                                    @error('document_file') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6>Comprovante de endereço</h6>

                                @if(!empty($order->address_proof_file))
                                    <a href="{{ Storage::url($order->address_proof_file) }}" target="_blank">
                                        Ver comprovante atual
                                    </a>
                                @else
                                    <div class="alert alert-warning py-2 mt-2">Nenhum comprovante anexado.</div>
                                @endif

                                <div class="mt-2">
                                    <label>Substituir comprovante</label>
                                    <input type="file" class="form-control" wire:model="address_proof_file" accept="image/*,application/pdf">
                                    @error('address_proof_file') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success btn-lg" wire:click="saveDocuments">Salvar alterações do documento</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
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
                                                    <th>Vencimento</th>
                                                    <th>Valor</th>
                                                    <th>Pago</th>
                                                    <th>Método</th>
                                                    <th>Status</th>
                                                    <th>Links</th>
                                                    <th width="180">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($financials as $fin)
                                                    @php
                                                        $isPaid = in_array($fin->status, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']);
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
                                                            <span class="badge {{ $isPaid ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $fin->status }}
                                                            </span>
                                                        </td>

                                                        <td class="text-center">
                                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                                @if($fin->invoice_url)
                                                                    <a href="{{ $fin->invoice_url }}" target="_blank" class="btn btn-sm btn-primary">
                                                                        Fatura
                                                                    </a>
                                                                @endif

                                                                @if($fin->bank_slip_url)
                                                                    <a href="{{ $fin->bank_slip_url }}" target="_blank" class="btn btn-sm btn-warning">
                                                                        Boleto
                                                                    </a>
                                                                @endif

                                                                @if($fin->pix_qr_code_url)
                                                                    <a href="{{ $fin->pix_qr_code_url }}" target="_blank" class="btn btn-sm btn-success">
                                                                        Pix
                                                                    </a>
                                                                @endif

                                                                @if(!$fin->invoice_url && !$fin->bank_slip_url && !$fin->pix_qr_code_url)
                                                                    -
                                                                @endif
                                                            </div>
                                                        </td>

                                                        <td class="text-center">
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-dark"
                                                                wire:click="openFinancialModal({{ $fin->id }})"
                                                            >
                                                                Gerenciar
                                                            </button>
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
    <div wire:ignore.self class="modal fade" id="financialManageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Gerenciar cobrança #{{ data_get($selectedFinancial, 'id') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if(session()->has('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    @if(session()->has('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @php
                        $selectedStatus = data_get($selectedFinancial, 'status');
                        $hasAsaas = filled(data_get($selectedFinancial, 'asaas_payment_id')) || filled(data_get($selectedFinancial, 'asaas_data.asaas_payment_id'));
                        $canEditRemote = in_array($selectedStatus, ['PENDING', 'OVERDUE']);
                        $canCancelRemote = !in_array($selectedStatus, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH', 'REFUNDED', 'CANCELED']);
                        $canRestoreRemote = in_array($selectedStatus, ['CANCELED']);
                        $canUndoCash = in_array($selectedStatus, ['RECEIVED_IN_CASH']);
                    @endphp

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <strong>Status</strong><br>
                                {{ data_get($selectedFinancial, 'status', '-') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <strong>Valor</strong><br>
                                R$ {{ number_format((float) data_get($selectedFinancial, 'value', 0), 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <strong>Vencimento</strong><br>
                                {{ data_get($selectedFinancial, 'due_date') ? \Carbon\Carbon::parse(data_get($selectedFinancial, 'due_date'))->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <strong>Asaas ID</strong><br>
                                {{ data_get($selectedRemotePayment, 'id', data_get($selectedFinancial, 'asaas_data.asaas_payment_id', '-')) }}
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3">Visualização pelo cliente</h6>
                                <div><strong>Fatura visualizada em:</strong> {{ data_get($selectedFinancialViewingInfo, 'invoiceViewedDate', '-') }}</div>
                                <div><strong>Boleto visualizado em:</strong> {{ data_get($selectedFinancialViewingInfo, 'boletoViewedDate', '-') }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3">Links</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @if(data_get($selectedFinancial, 'invoice_url'))
                                        <a href="{{ data_get($selectedFinancial, 'invoice_url') }}" target="_blank" class="btn btn-sm btn-primary">Fatura</a>
                                    @endif

                                    @if(data_get($selectedFinancial, 'bank_slip_url'))
                                        <a href="{{ data_get($selectedFinancial, 'bank_slip_url') }}" target="_blank" class="btn btn-sm btn-warning">Boleto</a>
                                    @endif

                                    @if(data_get($selectedFinancial, 'pix_qr_code_url'))
                                        <a href="{{ data_get($selectedFinancial, 'pix_qr_code_url') }}" target="_blank" class="btn btn-sm btn-success">Pix</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($hasAsaas)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Editar cobrança</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label>Forma de pagamento</label>
                                        <select class="form-control" wire:model="financialEdit.billing_type" {{ $canEditRemote ? '' : 'disabled' }}>
                                            <option value="BOLETO">Boleto</option>
                                            <option value="PIX">Pix</option>
                                            <option value="CREDIT_CARD">Cartão</option>
                                            <option value="UNDEFINED">Indefinido</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Vencimento</label>
                                        <input type="date" class="form-control" wire:model="financialEdit.due_date" {{ $canEditRemote ? '' : 'disabled' }}>
                                        @error('financialEdit.due_date') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label>Valor</label>
                                        <input type="number" step="0.01" class="form-control" wire:model="financialEdit.value" {{ $canEditRemote ? '' : 'disabled' }}>
                                        @error('financialEdit.value') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label>Dias para cancelamento do registro</label>
                                        <input type="number" class="form-control" wire:model="financialEdit.days_after_due_date_to_registration_cancellation" {{ $canEditRemote ? '' : 'disabled' }}>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Descrição</label>
                                        <input type="text" class="form-control" wire:model="financialEdit.description" {{ $canEditRemote ? '' : 'disabled' }}>
                                        @error('financialEdit.description') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                @if($canEditRemote)
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary" wire:click="saveFinancialChanges">
                                            Salvar alterações
                                        </button>
                                    </div>
                                @else
                                    <div class="alert alert-warning mt-3 mb-0">
                                        Esta cobrança só pode ser alterada quando estiver pendente ou vencida.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Baixa manual</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label>Data do pagamento</label>
                                        <input type="date" class="form-control" wire:model="financialReceive.payment_date">
                                        @error('financialReceive.payment_date') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label>Valor pago</label>
                                        <input type="number" step="0.01" class="form-control" wire:model="financialReceive.value">
                                        @error('financialReceive.value') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="notify_customer" wire:model="financialReceive.notify_customer">
                                            <label class="form-check-label" for="notify_customer">
                                                Notificar cliente
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button type="button" class="btn btn-success" wire:click="receiveSelectedFinancialInCash">
                                        Dar baixa manual
                                    </button>

                                    @if($canUndoCash)
                                        <button type="button" class="btn btn-outline-danger" wire:click="undoSelectedFinancialInCash">
                                            Desfazer baixa manual
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Cancelar / restaurar cobrança</h6>
                            </div>
                            <div class="card-body">
                                <label>Justificativa do cancelamento</label>
                                <textarea class="form-control" rows="3" wire:model="financialCancelJustification"></textarea>
                                @error('financialCancelJustification') <small class="text-danger">{{ $message }}</small> @enderror

                                <div class="mt-3 d-flex gap-2">
                                    @if($canCancelRemote)
                                        <button type="button" class="btn btn-danger" wire:click="cancelSelectedFinancial">
                                            Cancelar cobrança
                                        </button>
                                    @endif

                                    @if($canRestoreRemote)
                                        <button type="button" class="btn btn-outline-primary" wire:click="restoreSelectedFinancial">
                                            Restaurar cobrança
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Esta cobrança não possui vínculo com o Asaas. O histórico local ainda pode ser consultado abaixo.
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Histórico da cobrança</h6>
                        </div>
                        <div class="card-body">
                            @if(!empty($selectedFinancialLogs))
                                <div class="timeline">
                                    @foreach($selectedFinancialLogs as $log)
                                        <div class="border rounded p-3 mb-3">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $log['event_name'] }}</strong>
                                                <small>{{ $log['event_date'] }}</small>
                                            </div>

                                            <div class="mt-2">
                                                <span class="badge bg-secondary">{{ $log['provider'] }}</span>
                                                <span class="badge bg-light text-dark">{{ $log['source_type'] }}</span>
                                            </div>

                                            @if($log['old_status'] || $log['new_status'])
                                                <div class="mt-2">
                                                    <strong>Status:</strong>
                                                    {{ $log['old_status'] ?? '-' }} → {{ $log['new_status'] ?? '-' }}
                                                </div>
                                            @endif

                                            @if($log['message'])
                                                <div class="mt-2">
                                                    {{ $log['message'] }}
                                                </div>
                                            @endif

                                            @if(!empty($log['payload']))
                                                <details class="mt-2">
                                                    <summary>Ver payload</summary>
                                                    <pre class="mt-2 mb-0" style="white-space: pre-wrap;">{{ json_encode($log['payload'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                                </details>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    Nenhum log encontrado para esta cobrança.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('financial-modal-open', () => {
        const el = document.getElementById('financialManageModal');
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    });
});
</script>
@endpush