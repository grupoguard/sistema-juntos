<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">Pedido #{{ $order->id }}</h5>
            <small class="text-muted">
                Review: <strong>{{ $order->review_status }}</strong>
            </small>
        </div>

        <div class="d-flex gap-2">
            @can('update', $order)
                <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-warning">
                    Editar
                </a>
            @endcan

            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </div>
    </div>

    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist" wire:ignore>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                type="button" role="tab" aria-controls="pills-home" aria-selected="true">
                Dados do pedido
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-docs-tab" data-bs-toggle="pill" data-bs-target="#pills-docs"
                type="button" role="tab" aria-controls="pills-docs" aria-selected="false">
                Documentos
            </button>
        </li>

        @if($charge_type == 'EDP')
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
                    type="button" role="tab" aria-controls="pills-profile" aria-selected="false">
                    Evidências
                </button>
            </li>
        @endif

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact"
                type="button" role="tab" aria-controls="pills-contact" aria-selected="false">
                Registro Financeiro
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">

        {{-- TAB 1 --}}
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="row align-items-center">
                            <div class="col-10 mb-3">
                                <h5 class="mb-0">Dados do Cliente</h5>
                            </div>
                            <div class="col-2 text-end">
                                @if($charge_type == 'EDP')
                                    <span class="badge bg-warning text-dark">{{ $charge_type }}</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $charge_type }}</span>
                                @endif
                            </div>

                            {{-- Cliente --}}
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label>Nome do cliente</label>
                                    <input type="text" class="form-control" value="{{ $client['name'] ?? '' }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-3">
                                    <label>Gênero</label>
                                    <input type="text" class="form-control" value="{{ $client['gender'] ?? '' }}" readonly>
                                </div>
                                <div class="col-lg-9">
                                    <label>Nome da mãe</label>
                                    <input type="text" class="form-control" value="{{ $client['mom_name'] ?? '' }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>CPF</label>
                                    <input type="text" class="form-control" value="{{ $client['cpf'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label>RG</label>
                                    <input type="text" class="form-control" value="{{ $client['rg'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label>Data de nascimento</label>
                                    <input type="text" class="form-control" value="{{ $client['date_birth'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label>Celular/Whatsapp</label>
                                    <input type="text" class="form-control" value="{{ $client['phone'] ?? '' }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Estado Civil</label>
                                    <input type="text" class="form-control" value="{{ $client['marital_status'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-9">
                                    <label>Email</label>
                                    <input type="text" class="form-control" value="{{ $client['email'] ?? '' }}" readonly>
                                </div>
                            </div>

                            <hr class="my-5">

                            {{-- Endereço --}}
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5 class="mb-0">Endereço do Cliente</h5>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>CEP</label>
                                    <input type="text" class="form-control" value="{{ $client['zipcode'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-7">
                                    <label>Endereço</label>
                                    <input type="text" class="form-control" value="{{ $client['address'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label>Número</label>
                                    <input type="text" class="form-control" value="{{ $client['number'] ?? '' }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Complemento</label>
                                    <input type="text" class="form-control" value="{{ $client['complement'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Bairro</label>
                                    <input type="text" class="form-control" value="{{ $client['neighborhood'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Cidade</label>
                                    <input type="text" class="form-control" value="{{ $client['city'] ?? '' }}" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label>Estado</label>
                                    <input type="text" class="form-control" value="{{ $client['state'] ?? '' }}" readonly>
                                </div>
                            </div>

                            <hr class="my-5">

                            {{-- Pedido --}}
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5 class="mb-0">Dados do Pedido</h5>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3 mb-3">
                                    <label>Consultor (ID)</label>
                                    <input class="form-control" value="{{ $seller_id }}" readonly>
                                </div>

                                <div class="col-lg-9 mb-3">
                                    <label>Produto (ID)</label>
                                    <input class="form-control" value="{{ $product_id }}" readonly>
                                </div>

                                <div class="col-lg-4 mb-3">
                                    <label>Tipo desconto</label>
                                    <input class="form-control" value="{{ $discount_type ?? '' }}" readonly>
                                </div>

                                <div class="col-lg-8 mb-3">
                                    <label>Valor desconto</label>
                                    <input class="form-control" value="{{ $discount_value }}" readonly>
                                </div>

                                <div class="col-lg-3 mb-3">
                                    <label>Adicionais (titular)</label>
                                    <div class="border rounded p-2">
                                        @if(!empty($selectedAdditionals))
                                            <small class="text-muted">IDs selecionados:</small>
                                            <div>{{ implode(', ', $selectedAdditionals) }}</div>
                                        @else
                                            <span class="text-muted">Nenhum</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-2 mb-3">
                                    <label>Valor adesão</label>
                                    <input class="form-control" value="{{ number_format($accession, 2, ',', '.') }}" readonly>
                                </div>

                                <div class="col-lg-4 mb-3">
                                    <label>Pagamento adesão</label>
                                    <input class="form-control" value="{{ $accession_payment }}" readonly>
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
                                <div class="col-12">
                                    <h5 class="mb-3">Dependentes</h5>

                                    @if(!empty($dependents))
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Nome</th>
                                                        <th>Parentesco</th>
                                                        <th>CPF</th>
                                                        <th>RG</th>
                                                        <th>Nascimento</th>
                                                        <th>Estado Civil</th>
                                                        <th>Nome da mãe</th>
                                                        <th>Adicionais (IDs)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dependents as $dep)
                                                        <tr>
                                                            <td>{{ $dep['name'] }}</td>
                                                            <td>{{ $dep['relationship'] }}</td>
                                                            <td>{{ $dep['cpf'] }}</td>
                                                            <td>{{ $dep['rg'] }}</td>
                                                            <td>{{ $dep['date_birth'] }}</td>
                                                            <td>{{ $dep['marital_status'] }}</td>
                                                            <td>{{ $dep['mom_name'] }}</td>
                                                            <td>{{ !empty($dep['additionals']) ? implode(', ', $dep['additionals']) : '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">Nenhum dependente vinculado.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="alert alert-light border mt-4">
                                <strong>Observação:</strong> Esta tela é somente leitura.
                                @cannot('update', $order)
                                    <span class="text-muted">Edição bloqueada (somente se REPROVADO).</span>
                                @endcannot
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2 - EVIDÊNCIAS (EDP) --}}
        @if($charge_type == 'EDP')
            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                <div class="container-fluid py-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="row align-items-center">
                                <div class="col-lg-3 mb-3">
                                    <label>Número da Instalação</label>
                                    <input class="form-control" value="{{ $installation_number }}" readonly>
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label>Nome do Titular</label>
                                    <input class="form-control" value="{{ $approval_name }}" readonly>
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label>Autorizado por</label>
                                    <input class="form-control" value="{{ $approval_by }}" readonly>
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label>Data da Evidência</label>
                                    <input class="form-control" value="{{ $evidence_date }}" readonly>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                Se você quiser, eu adapto aqui para listar as evidências já cadastradas (se você me confirmar o relacionamento/tabela que você usa para listar).
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- TAB 3 - DOCS --}}
        <div class="tab-pane fade" id="pills-docs" role="tabpanel" aria-labelledby="pills-docs-tab">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5 class="mb-0">Documentos e Contrato</h5>
                    </div>

                    <div class="card-body">

                        {{-- Contrato --}}
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
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h6>Contrato assinado (digital)</h6>

                                <input type="text" class="form-control" value="{{ $signed_contract_url ?? '' }}" readonly>
                                <small class="text-muted d-block mt-1">
                                    (somente leitura)
                                </small>

                                <hr>

                                <h6>Contrato físico (scan)</h6>

                                @if(!empty($order->signed_physical_contract_file))
                                    <a class="btn btn-outline-primary btn-sm"
                                       href="{{ Storage::url($order->signed_physical_contract_file) }}" target="_blank">
                                        Ver contrato físico
                                    </a>
                                @else
                                    <div class="alert alert-light border py-2 mt-2">
                                        Contrato físico não enviado.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        {{-- RG/CNH + comprovante --}}
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Documento (RG/CNH)</h6>

                                @if(!empty($existing_document_file))
                                    <a href="{{ Storage::url($existing_document_file) }}" target="_blank">
                                        Ver documento ({{ $existing_document_file_type ?? 'RG' }})
                                    </a>
                                @else
                                    <div class="alert alert-warning py-2 mt-2">Nenhum documento anexado.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <h6>Comprovante de endereço</h6>

                                @if(!empty($existing_address_proof_file))
                                    <a href="{{ Storage::url($existing_address_proof_file) }}" target="_blank">
                                        Ver comprovante
                                    </a>
                                @else
                                    <div class="alert alert-warning py-2 mt-2">Nenhum comprovante anexado.</div>
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-light border mt-4">
                            Esta aba é somente leitura.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4 - FINANCEIRO --}}
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="row align-items-center">
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
                                                        <td>{{ $fin->due_date ? \Carbon\Carbon::parse($fin->due_date)->format('d/m/Y') : '-' }}</td>
                                                        <td>R$ {{ number_format($fin->value, 2, ',', '.') }}</td>
                                                        <td>R$ {{ number_format($fin->paid_value ?? 0, 2, ',', '.') }}</td>
                                                        <td>{{ $fin->payment_method ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge {{ $isPaid ? 'bg-success' : 'bg-danger' }}">
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