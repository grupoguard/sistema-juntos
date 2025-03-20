@extends('layouts.user_type.auth')

@section('content')

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Cadastro de Cliente</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Nome</label>
                            <input type="text" class="form-control" wire:model="client.name">
                            @error('client.name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" wire:model="client.email">
                            @error('client.email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" wire:click="addDependent">+ Adicionar Dependente</button>
                    </div>

                    @foreach($dependents as $index => $dependent)
                        <div class="row mt-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" placeholder="Nome do Dependente" wire:model="dependents.{{ $index }}.name">
                            </div>
                            <div class="col-md-5">
                                <input type="date" class="form-control" wire:model="dependents.{{ $index }}.date_birth">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger" wire:click="removeDependent({{ $index }})">X</button>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection