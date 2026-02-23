<div>
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0">Pedidos com divergência financeira</h6>
                    <small class="text-muted">
                        Valor calculado do pedido diferente do valor total em financeiro.
                    </small>
                </div>

                <div class="col-md-4 mt-2 mt-md-0">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar por pedido, nome ou CPF..."
                        wire:model.live.debounce.400ms="search"
                    >
                </div>

                <div class="col-md-2 mt-2 mt-md-0">
                    <select class="form-control" wire:model.live="perPage">
                        <option value="10">10 / pág</option>
                        <option value="20">20 / pág</option>
                        <option value="50">50 / pág</option>
                        <option value="100">100 / pág</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body pt-3">
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            @if($rows->count() === 0)
                <div class="alert alert-success mb-0">
                    Nenhuma divergência encontrada 🎉
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th># Pedido</th>
                                <th>Cliente</th>
                                <th>CPF</th>
                                <th class="text-right">Produto</th>
                                <th class="text-right">Adic. Dependentes</th>
                                <th class="text-right">Total Calculado</th>
                                <th class="text-right">Total Financeiro</th>
                                <th class="text-right">Diferença</th>
                                <th width="120">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $calculated = (float) $row->calculated_order_total;
                                    $financial = (float) $row->financial_total_value;
                                    $diff = round($calculated - $financial, 2);
                                @endphp
                                <tr>
                                    <td>#{{ $row->id }}</td>
                                    <td>{{ $row->client_name }}</td>
                                    <td>{{ $row->client_cpf }}</td>

                                    <td class="text-right">
                                        R$ {{ number_format((float) $row->product_value, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right">
                                        R$ {{ number_format((float) $row->dependents_additionals_value, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right font-weight-bold">
                                        R$ {{ number_format((float) $row->calculated_order_total, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right">
                                        R$ {{ number_format((float) $row->financial_total_value, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right">
                                        <span class="badge {{ $diff > 0 ? 'badge-warning' : 'badge-danger' }}">
                                            {{ $diff > 0 ? '+' : '' }}R$ {{ number_format($diff, 2, ',', '.') }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.orders.edit', $row->id) }}" class="btn btn-sm btn-primary">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</div>