<div>
    <form wire:submit.prevent="saveOrder">
        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row align-items-center">
                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Dados do Cliente</h5>
                        </div>

                        <!-- Seleção de Cliente -->
                        <div class="row mb-3">
                            <div class="col-lg-5">
                                <label for="client_id" class="form-label">Cliente</label>
                                <select id="client_id" class="form-control" wire:model.change="client_id">
                                    <option value="new">Cadastrar cliente</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }} - {{ $client->cpf }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Dados do Cliente -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label>Nome do cliente<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model.defer="client.name">
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
                                <select id="seller_id" class="form-control" wire:model.change="seller_id">
                                    <option value="">Selecione um consultor</option>
                                    @foreach($sellers as $seller)
                                        <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                    @endforeach
                                </select>
                                @error('seller_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
        
                            <!-- Seleção de Produto -->
                            <div class="col-lg-6 mb-3">
                                <label for="product_id" class="form-label">Produto<span class="text-danger">*</span></label>
                                <select id="product_id" class="form-control" wire:model.change="product_id" wire:change="loadAdditionals">
                                    <option value="">Selecione um produto</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
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
                                <input type="number" id="accession" step="0.1" class="form-control" placeholder="R$ Adesão" wire:model="accession">
                                @error('accession') <span class="text-danger">{{ $message }}</span> @enderror
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
                                @error('accession_payment') <span class="text-danger">{{ $message }}</span> @enderror
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
                                <div class="row align-items-end mt-4" wire:key="dependent-{{ $index }}">
                                    <div class="col-md-12 mb-3">
                                        <label>Nome do dependente<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="Nome do Dependente" wire:model="dependents.{{ $index }}.name">
                                        @error('dependents.'.$index.'.name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Grau de Parentesco<span class="text-danger">*</span></label>
                                        <select class="form-control" wire:model.defer="dependents.{{ $index }}.relationship">
                                            <option value="">Selecione</option>
                                            <option value="mae-pai">Mãe/Pai</option>
                                            <option value="irmao">Irmão(ã)</option>
                                            <option value="filho">Filho(a)</option>
                                            <option value="conjuge">Cônjuge</option>
                                            <option value="outro">Outro</option>
                                            <option value="nao_informado">Não Informado</option>
                                        </select>
                                        @error('dependents.'.$index.'.relationship') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>CPF<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control cpf-mask" wire:model="dependents.{{ $index }}.cpf">
                                        @error('dependents.'.$index.'.cpf') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>RG</label>
                                        <input type="text" class="form-control rg-mask" wire:model="dependents.{{ $index }}.rg">
                                        @error('dependents.'.$index.'.rg') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Data de nascimento<span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model="dependents.{{ $index }}.date_birth">
                                        @error('dependents.'.$index.'.date_birth') <span class="text-danger">{{ $message }}</span> @enderror
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
                                        </select>
                                        @error('dependents.'.$index.'.marital_status') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label>Nome da mãe<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="Nome da mãe" wire:model="dependents.{{ $index }}.mom_name">
                                        @error('dependents.'.$index.'.mom_name') <span class="text-danger">{{ $message }}</span> @enderror
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
                                            @error('dependents.'.$index.'.additionals') <span class="text-danger">{{ $message }}</span> @enderror
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
                            <h5 class="mb-0">Documentos</h5>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label>Tipo do documento</label>
                                <select class="form-control" wire:model="document_file_type">
                                    <option value="RG">RG</option>
                                    <option value="CNH">CNH</option>
                                </select>
                                @error('document_file_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label>{{ $document_file_type === 'CNH' ? 'CNH (imagem/foto)' : 'RG (imagem/foto)' }}</label>
                                <input type="file" class="form-control" wire:model="document_file" accept="image/*,application/pdf" capture="environment">
                                @error('document_file') <span class="text-danger">{{ $message }}</span> @enderror

                                <div wire:loading wire:target="document_file" class="text-muted mt-1">
                                    Enviando documento...
                                </div>
                            </div>

                            <div class="col-md-5">
                                @if ($document_file)
                                    <label>Pré-visualização documento</label>
                                    <div class="border rounded p-2">
                                        @if (str_starts_with($document_file->getMimeType(), 'image/'))
                                            <img src="{{ $document_file->temporaryUrl() }}" class="img-fluid rounded">
                                        @elseif ($document_file->getMimeType() === 'application/pdf')
                                            <div class="text-muted">
                                                PDF selecionado: <strong>{{ $document_file->getClientOriginalName() }}</strong><br>
                                                Salve para visualizar.
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Comprovante de endereço (imagem/foto)</label>
                                <input type="file" class="form-control" wire:model="address_proof_file" accept="image/*,application/pdf" capture="environment">
                                @error('address_proof_file') <span class="text-danger">{{ $message }}</span> @enderror

                                <div wire:loading wire:target="address_proof_file" class="text-muted mt-1">
                                    Enviando comprovante...
                                </div>
                            </div>

                            <div class="col-md-6">
                                @if ($address_proof_file)
                                    <label>Pré-visualização comprovante</label>
                                    <div class="border rounded p-2">

                                        @if (str_starts_with($address_proof_file->getMimeType(), 'image/'))
                                            <img src="{{ $address_proof_file->temporaryUrl() }}"
                                                alt="Comprovante"
                                                class="img-fluid rounded">
                                        @elseif ($address_proof_file->getMimeType() === 'application/pdf')
                                            <div class="text-muted">
                                                PDF selecionado: <strong>{{ $address_proof_file->getClientOriginalName() }}</strong><br>
                                                Salve para visualizar.
                                            </div>
                                        @else
                                            <div class="text-muted">
                                                Arquivo selecionado: <strong>{{ $address_proof_file->getClientOriginalName() }}</strong><br>
                                                Salve para visualizar.
                                            </div>
                                        @endif

                                    </div>

                                @elseif(!empty($existing_address_proof_file))
                                    <label>Comprovante atual</label>
                                    <div class="border rounded p-2">
                                        <a href="{{ Storage::url($existing_address_proof_file) }}" target="_blank">
                                            Visualizar comprovante atual
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <hr class="my-5">

                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Dados de cobrança</h5>
                        </div>

                        <!-- Tipo de Cobrança -->
                        <div class="row">
                            <div class="col-lg-3 mb-3">
                                <label for="charge_type" class="form-label">Tipo de Cobrança<span class="text-danger">*</span></label>
                                <select id="charge_type" class="form-control" wire:model.change="charge_type">
                                    <option value="">Selecione</option>
                                    <option value="EDP">EDP</option>
                                    <option value="BOLETO">Boleto</option>
                                </select>
                                @error('charge_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Botão de salvar -->
                        <div class="row">
                            <div class="col-lg-7">

                            </div>
                            <div class="col-lg-5 text-end">
                                <button type="submit" class="btn btn-success btn-lg">Salvar Pedido</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="clientHasOrderModal" tabindex="-1" aria-labelledby="clientHasOrderModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientHasOrderModalLabel">Cliente já possui pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Este cliente já possui um pedido e não pode cadastrar um novo.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @push('scripts')
        <script>
            window.addEventListener('clientHasOrder', event => {
                var myModal = new bootstrap.Modal(document.getElementById('clientHasOrderModal'));
                myModal.show();
            });
            
            // Função para aplicar máscaras em elementos específicos
            function applyMasksToNewElements(container) {
                $(container).find('.cpf-mask').each(function() {
                    if (!$(this).data('mask')) {
                        $(this).mask('000.000.000-00', {reverse: true});
                    }
                });
                
                $(container).find('.rg-mask').each(function() {
                    if (!$(this).data('mask')) {
                        $(this).mask('00.000.000-0');
                    }
                });
            }
            
            // Observer para detectar novos elementos
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                applyMasksToNewElements(node);
                            }
                        });
                    }
                });
            });
            
            // Iniciar observação
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            Livewire.hook('message.processed', (message, component) => {
                setTimeout(function() {
                    window.applyMasks();
                }, 50);
            });
        </script>
    @endpush
</div>