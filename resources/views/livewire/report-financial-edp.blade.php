<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Retornos de Evidência</h5>
            <h3>R${{ number_format($totalValue, 2, ',', '.') }}</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-lg-2">
                    <label for="inicial_date">Data inicial</label>
                    <input type="date" class="form-control" name="inicial_date" wire:model.live="searchInicialDate" />
                </div>
                <div class="col-lg-2">
                    <label for="final_date">Data final</label>
                    <input type="date" class="form-control" wire:model.live="searchFinalDate" />
                </div>
                <div class="col-lg-2">
                    <label for="report_type">Tipo de relatório</label>
                    <select class="form-control" wire:model.live="reportType">
                        <option value="" selected>Selecione</option>
                        <option value="01">Cobradas</option>
                        <option value="06">Pagas</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label for="report_export">Visualização</label>
                    <select class="form-control" wire:model.live="reportExport">
                        <option value="" selected>Selecione</option>
                        <option value="0">Tela</option>
                        <option value="1">Excel</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <button type="button" class="btn bg-blue text-white w-100 mt-4" 
                            wire:click="search" wire:loading.attr="disabled">
                        <span wire:loading.remove>Pesquisar</span>
                        <span wire:loading>Carregando...</span>
                    </button>
                </div>
                @error('searchInicialDate')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                @error('searchFinalDate')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                @error('reportType')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            @if(!empty($results))
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mês</th>
                                <th>Instalação</th>
                                <th>Código Retorno</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $item)
                                <tr>
                                    <td>{{ $item['mes'] }}</td>
                                    <td>{{ $item['installation_number'] }}</td>
                                    <td>{{ $item['code_return'] }}</td>
                                    <td>R$ {{ $item['valor'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ empty($searchInicialDate) ? 'Preencha os filtros e clique em "Pesquisar" para visualizar os dados.' : 'Nenhum resultado encontrado para os filtros selecionados.' }}
                </div>
            @endif
        </div>
    </div>
</div>