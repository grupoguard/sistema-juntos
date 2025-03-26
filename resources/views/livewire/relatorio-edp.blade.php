<div class="container-fluid py-4">
    <div class="nav-wrapper position-relative end-0 mb-4" wire:ignore>
        <ul class="nav nav-pills nav-fill p-1" role="tablist">
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 
                    {{ $activeTab == '#evidence-data-tab' ? 'active' : '' }}" 
                    wire:click.prevent="setActiveTab('#evidence-data-tab')" 
                    data-bs-toggle="tab" 
                    href="#evidence-data-tab" 
                    role="tab" 
                    aria-controls="preview" 
                    aria-selected="true"
                >
                    <i class="ni ni-badge text-sm me-2"></i> Evidências
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1
                    {{ $activeTab == '#financial-data-tab' ? 'active' : '' }}" 
                    wire:click.prevent="setActiveTab('#financial-data-tab')" 
                    data-bs-toggle="tab" 
                    href="#financial-data-tab" 
                    role="tab" 
                    aria-controls="code" 
                    aria-selected="false"
                >
                    <i class="fa fa-dollar" aria-hidden="true"></i> Financeiro
                </a>
            </li>                
        </ul>
    </div>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade {{ $activeTab == '#evidence-data-tab' ? 'show active' : '' }}" id="evidence-data-tab" role="tabpanel" aria-labelledby="evidence-tab" tabindex="0">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5 class="mb-0">Retornos de Evidência</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Buscar Instalação..." 
                                    wire:model.live="searchEvidences"
                                />
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            Instalação
                                        </th>
                                        <th>
                                            Código Anomalias
                                        </th>
                                        <th>
                                            Código Movimento
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evidenceReturns as $return)
                                    <tr>
                                        <td>{{ $return->installation_number }}</td>
                                        <td>{{ optional($return->anomalyCode)->description ?? optional($return->returnCode)->description ?? '-' }}</td>
                                        <td>{{ optional($return->moveCode)->description ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
        
                        {{ $evidenceReturns->links() }}
        
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade {{ $activeTab == '#financial-data-tab' ? 'show active' : '' }}" id="financial-data-tab" role="tabpanel" aria-labelledby="financial-tab" tabindex="0">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5 class="mb-0">Retornos Financeiros</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Buscar Instalação..." 
                                    wire:model.live="searchFinancial"
                                />
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            Instalação
                                        </th>
                                        <th>
                                            Código Retorno
                                        </th>
                                        <th>
                                            Código Movimento
                                        </th>
                                        <th>
                                            Data de Movimento
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($financialReturns as $return)
                                    <tr>
                                        <td>{{ $return->installation_number }}</td>
                                        <td>{{ optional($return->anomalyCode)->description ?? optional($return->returnCode)->description ?? '-' }}</td>
                                        <td>{{ optional($return->moveCode)->description ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($return->date_movement)->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
        
                        {{ $financialReturns->links() }}
        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>