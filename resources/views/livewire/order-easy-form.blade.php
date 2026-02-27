<div class="container-fluid py-4">
    {{-- Alerts --}}
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Cabeçalho --}}
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="mb-0">Cadastro Facilitado de Pedido</h4>
                    <small class="text-muted">
                        Preenchimento passo a passo (ideal para celular)
                    </small>
                </div>

                <div class="mt-2 mt-md-0">
                    @if($draftId)
                        <span class="badge badge-info">Rascunho #{{ $draftId }}</span>
                    @else
                        <span class="badge badge-secondary">Novo rascunho</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- Indicador de etapas --}}
            <div class="mb-3">
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ ($step / 8) * 100 }}%;"
                         aria-valuenow="{{ $step }}" aria-valuemin="1" aria-valuemax="8"></div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Etapa {{ $step }} de 8</small>
                </div>
            </div>

            {{-- Navegação rápida (somente para trás / etapa atual) --}}
            <div class="mb-0">
                <div class="d-flex flex-wrap">
                    @php
                        $stepNames = [
                            1 => 'Cliente',
                            2 => 'Endereço',
                            3 => 'Pedido',
                            4 => 'Dependentes',
                            5 => 'Cobrança',
                            6 => 'Documento',
                            7 => 'Comprovante',
                            8 => 'Resumo',
                        ];
                    @endphp

                    @foreach($stepNames as $num => $label)
                        <button type="button"
                                class="btn btn-sm mr-2 mb-2 {{ $step == $num ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="goToStep({{ $num }})"
                                {{ $num > $step ? 'disabled' : '' }}>
                            {{ $num }}. {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Erros gerais --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Existem campos inválidos:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Conteúdo por etapa --}}
    <div class="card">
        <div class="card-body">

            {{-- ETAPA 1 - Dados do cliente --}}
            @if($step === 1)
                <h5 class="mb-4">1. Dados do Cliente</h5>

                <div class="row">
                    <div class="col-md-4">
                        <label>CPF <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model.defer="client.cpf" placeholder="Digite o CPF">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" wire:click="lookupClientByCpf">
                                    Buscar
                                </button>
                            </div>
                        </div>
                        @error('client.cpf') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-8 d-flex align-items-end">
                        @if($clientLookupMessage)
                            <div class="alert {{ $clientFound ? 'alert-info' : 'alert-secondary' }} w-100 mb-0 mt-3 mt-md-0">
                                {{ $clientLookupMessage }}
                            </div>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label>Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="client.name">
                        @error('client.name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label>Gênero <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="client.gender">
                            <option value="">Selecione</option>
                            <option value="MASCULINO">Masculino</option>
                            <option value="FEMININO">Feminino</option>
                            <option value="OUTRO">Outro</option>
                        </select>
                        @error('client.gender') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label>RG</label>
                        <input type="text" class="form-control" wire:model.defer="client.rg">
                        @error('client.rg') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label>Data de nascimento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model.defer="client.date_birth">
                        @error('client.date_birth') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label>WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="client.phone">
                        @error('client.phone') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" class="form-control" wire:model.defer="client.email">
                        @error('client.email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-9">
                        <label>Nome da mãe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="client.mom_name">
                        @error('client.mom_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label>Estado civil <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="client.marital_status">
                            <option value="">Selecione</option>
                            <option value="solteiro">Solteiro(a)</option>
                            <option value="casado">Casado(a)</option>
                            <option value="divorciado">Divorciado(a)</option>
                            <option value="viuvo">Viúvo(a)</option>
                            <option value="uniao_estavel">União Estável</option>
                            <option value="nao_informado">Não informado</option>
                        </select>
                        @error('client.marital_status') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                            @endif

            {{-- ETAPA 2 - Endereço --}}
            @if($step === 2)
                <h5 class="mb-4">2. Endereço do Cliente</h5>

                <div class="alert alert-light border">
                    <strong>Observação:</strong> se o cliente já tiver endereço cadastrado, os campos aparecem preenchidos e continuam editáveis.
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>CEP <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model.defer="address.zipcode" placeholder="Digite o CEP">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" wire:click="fetchAddressByCep">
                                    Buscar CEP
                                </button>
                            </div>
                        </div>
                        @error('address.zipcode') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-7">
                        <label>Endereço <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.address">
                        @error('address.address') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-2">
                        <label>Número <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.number">
                        @error('address.number') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label>Complemento</label>
                        <input type="text" class="form-control" wire:model.defer="address.complement">
                        @error('address.complement') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Bairro <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.neighborhood">
                        @error('address.neighborhood') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Cidade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.city">
                        @error('address.city') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-1">
                        <label>UF <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.state" maxlength="2">
                        @error('address.state') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            @endif

            {{-- ETAPA 3 - Dados do pedido --}}
            @if($step === 3)
                <h5 class="mb-4">3. Dados do Pedido</h5>

                <div class="row">
                    <div class="col-md-6">
                        <label>Consultor <span class="text-danger">*</span></label>

                        @if($this->isSellerUser())
                            @php
                                $selectedSeller = collect($sellers)->firstWhere('id', (int)($orderData['seller_id'] ?? 0));
                            @endphp
                            <input type="text" class="form-control" value="{{ $selectedSeller['name'] ?? 'Consultor vinculado ao usuário' }}" disabled>
                            <small class="text-muted">Como você está logado como consultor, este campo é definido automaticamente.</small>
                        @else
                            <select class="form-control" wire:model.defer="orderData.seller_id">
                                <option value="">Selecione</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller['id'] }}">{{ $seller['name'] }}</option>
                                @endforeach
                            </select>
                            @error('orderData.seller_id') <small class="text-danger">{{ $message }}</small> @enderror
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label>Produto <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.change="orderData.product_id">
                            <option value="">Selecione</option>
                            @foreach($products as $product)
                                <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                            @endforeach
                        </select>
                        @error('orderData.product_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label>Adicionais</label>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            @forelse($additionals as $aditional)
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           value="{{ $aditional['id'] }}"
                                           id="aditional_{{ $aditional['id'] }}"
                                           wire:model.defer="orderData.selectedAdditionals">
                                    <label class="form-check-label" for="aditional_{{ $aditional['id'] }}">
                                        {{ $aditional['name'] }}
                                        @if(isset($aditional['value']))
                                            - R$ {{ number_format((float)$aditional['value'], 2, ',', '.') }}
                                        @endif
                                    </label>
                                </div>
                            @empty
                                <small class="text-muted">Nenhum adicional disponível.</small>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label>Valor da adesão</label>
                        <input type="text" class="form-control" wire:model.defer="orderData.accession">
                        @error('orderData.accession') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label>Pagamento da adesão</label>
                        <select class="form-control" wire:model.defer="orderData.accession_payment">
                            <option value="">Selecione</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">Pix</option>
                            <option value="cartao">Cartão</option>
                            <option value="boleto">Boleto</option>
                            <option value="nao_cobrado">Não cobrado</option>
                        </select>
                        @error('orderData.accession_payment') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            @endif

            {{-- ETAPA 4 - Dependentes (opcional) --}}
            @if($step === 4)
                <h5 class="mb-4">4. Dependentes (Opcional)</h5>

                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary" wire:click="addDependent">
                        + Adicionar dependente
                    </button>
                </div>

                @forelse($dependents as $index => $dependent)
                    <div class="card mb-3 border">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Dependente {{ $index + 1 }}</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeDependent({{ $index }})">
                                Remover
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.{{ $index }}.name">
                                </div>
                                <div class="col-md-3">
                                    <label>CPF</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.{{ $index }}.cpf">
                                </div>
                                <div class="col-md-3">
                                    <label>RG</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.{{ $index }}.rg">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <label>Data de nascimento</label>
                                    <input type="date" class="form-control" wire:model.defer="dependents.{{ $index }}.date_birth">
                                </div>
                                <div class="col-md-3">
                                    <label>Parentesco</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.{{ $index }}.relationship">
                                </div>
                                <div class="col-md-6">
                                    <label>Nome da mãe</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.{{ $index }}.mom_name">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label>Estado civil</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.{{ $index }}.marital_status">
                                </div>
                                @if(!empty($additionals))
                                    <div class="col-md-6">
                                        <label class="mb-1"><strong>Adicionais do dependente</strong></label>
                                        @foreach($additionals as $aditional)
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                    type="checkbox"
                                                    value="{{ $aditional['id'] }}"
                                                    id="dep_{{ $index }}_add_{{ $aditional['id'] }}"
                                                    wire:model.defer="dependents.{{ $index }}.additionals">
                                                <label class="form-check-label" for="dep_{{ $index }}_add_{{ $aditional['id'] }}">
                                                    {{ $aditional['name'] }} - R$ {{ number_format((float)($aditional['value'] ?? 0), 2, ',', '.') }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @error("dependents.$index.name") <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                @empty
                    <div class="alert alert-light border">
                        Nenhum dependente adicionado. Esta etapa é opcional.
                    </div>
                @endforelse
            @endif

            {{-- ETAPA 5 - Cobrança --}}
            @if($step === 5)
                <h5 class="mb-4">5. Dados de Cobrança</h5>

                <div class="row">
                    <div class="col-md-4">
                        <label>Tipo de cobrança <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="billing.charge_type">
                            <option value="Boleto">Boleto</option>
                        </select>
                        @error('billing.charge_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label>Dia de pagamento <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="billing.charge_date">
                            <option value="">Selecione</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                        </select>
                        @error('billing.charge_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            @endif

            {{-- ETAPA 6 - Documento RG/CNH --}}
            @if($step === 6)
                <h5 class="mb-4">6. Documento (RG ou CNH)</h5>

                <div class="row">
                    <div class="col-md-3">
                        <label>Tipo do documento</label>
                        <select class="form-control" wire:model="document_file_type">
                            <option value="RG">RG</option>
                            <option value="CNH">CNH</option>
                        </select>
                        @error('document_file_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-9">
                        <label>Enviar imagem/foto do {{ $document_file_type }}</label>
                        <input type="file"
                               class="form-control"
                               wire:model="document_file"
                               accept="image/*"
                               capture="environment">
                        @error('document_file') <small class="text-danger">{{ $message }}</small> @enderror

                        <div wire:loading wire:target="document_file" class="text-muted mt-2">
                            Enviando documento...
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        @if($document_file)
                            <div class="border rounded p-2">
                                <label class="d-block">Pré-visualização</label>
                                <img src="{{ $document_file->temporaryUrl() }}" class="img-fluid rounded" style="max-height: 420px;">
                            </div>
                        @elseif($existing_document_file)
                            <div class="border rounded p-2">
                                <label class="d-block">Documento já salvo no rascunho</label>
                                <a href="{{ Storage::url($existing_document_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Visualizar documento
                                </a>
                            </div>
                        @else
                            <div class="alert alert-light border">
                                Nenhum documento enviado ainda.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ETAPA 7 - Comprovante de endereço --}}
            @if($step === 7)
                <h5 class="mb-4">7. Comprovante de Endereço</h5>

                <div class="row">
                    <div class="col-md-12">
                        <label>Enviar imagem/foto do comprovante</label>
                        <input type="file"
                               class="form-control"
                               wire:model="address_proof_file"
                               accept="image/*"
                               capture="environment">
                        @error('address_proof_file') <small class="text-danger">{{ $message }}</small> @enderror

                        <div wire:loading wire:target="address_proof_file" class="text-muted mt-2">
                            Enviando comprovante...
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        @if($address_proof_file)
                            <div class="border rounded p-2">
                                <label class="d-block">Pré-visualização</label>
                                <img src="{{ $address_proof_file->temporaryUrl() }}" class="img-fluid rounded" style="max-height: 420px;">
                            </div>
                        @elseif($existing_address_proof_file)
                            <div class="border rounded p-2">
                                <label class="d-block">Comprovante já salvo no rascunho</label>
                                <a href="{{ Storage::url($existing_address_proof_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Visualizar comprovante
                                </a>
                            </div>
                        @else
                            <div class="alert alert-light border">
                                Nenhum comprovante enviado ainda.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ETAPA 8 - Resumo --}}
            @if($step === 8)
                <h5 class="mb-4">8. Resumo do Cadastro</h5>

                <div class="alert alert-info">
                    Revise as informações abaixo e clique em <strong>Enviar pedido</strong>.
                    <br>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card border mb-3">
                            <div class="card-header"><strong>Cliente</strong></div>
                            <div class="card-body">
                                <p class="mb-1"><strong>CPF:</strong> {{ $client['cpf'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Nome:</strong> {{ $client['name'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Gênero:</strong> {{ $client['gender'] ?? '-' }}</p>
                                <p class="mb-1"><strong>RG:</strong> {{ $client['rg'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Nascimento:</strong> {{ $client['date_birth'] ?? '-' }}</p>
                                <p class="mb-1"><strong>WhatsApp:</strong> {{ $client['phone'] ?? '-' }}</p>
                                <p class="mb-0"><strong>Email:</strong> {{ $client['email'] ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header"><strong>Endereço</strong></div>
                            <div class="card-body">
                                <p class="mb-1"><strong>CEP:</strong> {{ $address['zipcode'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Endereço:</strong> {{ $address['address'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Número:</strong> {{ $address['number'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Complemento:</strong> {{ $address['complement'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Bairro:</strong> {{ $address['neighborhood'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Cidade:</strong> {{ $address['city'] ?? '-' }}</p>
                                <p class="mb-0"><strong>UF:</strong> {{ $address['state'] ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border mb-3">
                            <div class="card-header"><strong>Pedido</strong></div>
                            <div class="card-body">
                                @php
                                    $selectedSeller = collect($sellers)->firstWhere('id', (int)($orderData['seller_id'] ?? 0));
                                    $selectedProduct = collect($products)->firstWhere('id', (int)($orderData['product_id'] ?? 0));
                                    $selectedAdditionalsLabels = collect($additionals)
                                        ->whereIn('id', collect($orderData['selectedAdditionals'] ?? [])->map(fn($v) => (int)$v)->toArray())
                                        ->pluck('name')
                                        ->toArray();
                                @endphp

                                <p class="mb-1"><strong>Consultor:</strong> {{ $selectedSeller['name'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Produto:</strong> {{ $selectedProduct['name'] ?? '-' }}</p>
                                <p class="mb-1"><strong>Adicionais:</strong>
                                    @if(!empty($selectedAdditionalsLabels))
                                        {{ implode(', ', $selectedAdditionalsLabels) }}
                                    @else
                                        Nenhum
                                    @endif
                                </p>
                                <p class="mb-1"><strong>Valor adesão:</strong> {{ $orderData['accession'] ?? '-' }}</p>
                                <p class="mb-0"><strong>Pagamento adesão:</strong> {{ $orderData['accession_payment'] ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header"><strong>Cobrança</strong></div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Tipo:</strong> {{ $billing['charge_type'] ?? '-' }}</p>
                                <p class="mb-0"><strong>Dia de pagamento:</strong> {{ $billing['charge_date'] ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header"><strong>Documentos</strong></div>
                            <div class="card-body">
                                <p class="mb-1">
                                    <strong>Documento ({{ $document_file_type ?? 'RG' }}):</strong>
                                    @if($existing_document_file)
                                        <a href="{{ Storage::url($existing_document_file) }}" target="_blank">Visualizar</a>
                                    @else
                                        Não enviado
                                    @endif
                                </p>
                                <p class="mb-0">
                                    <strong>Comprovante:</strong>
                                    @if($existing_address_proof_file)
                                        <a href="{{ Storage::url($existing_address_proof_file) }}" target="_blank">Visualizar</a>
                                    @else
                                        Não enviado
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-header"><strong>Dependentes</strong></div>
                            <div class="card-body">
                                @if(!empty($dependents))
                                    <ul class="mb-0 pl-3">
                                        @foreach($dependents as $dep)
                                            <li>
                                                {{ $dep['name'] ?? 'Sem nome' }}
                                                @if(!empty($dep['relationship'])) - {{ $dep['relationship'] }} @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">Nenhum dependente informado.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Rodapé / Navegação --}}
        <div class="card-footer">
            <div class="d-flex flex-wrap justify-content-between">
                {{-- <div class="mb-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="saveAndExit">
                        Salvar rascunho e sair
                    </button>
                </div> --}}

                <div class="mb-2">
                    @if($step > 1)
                        <button type="button" class="btn btn-outline-primary" wire:click="previousStep">
                            Voltar
                        </button>
                    @endif

                    @if($step < 8)
                        <button type="button" class="btn btn-primary" wire:click="nextStep">
                            Avançar
                        </button>
                    @else
                        <button type="button" class="btn btn-success" wire:click="submitOrder">
                            Enviar pedido
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>