@extends('layouts.user_type.auth')

@section('content')

<div>
    <div class="container-fluid">
        <div class="card card-body blur shadow-blur">
            <div class="row">
                <div class="col-auto my-auto">
                    <div class="h-100">
                        <h5 class="mb-1">
                          {{ auth()->user()->name }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">Atualizar acessos</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <form action="{{route('admin.profile.store')}}" method="POST" role="form text-left">
                    @csrf
                    @if($errors->any())
                        <div class="mt-3  alert alert-primary alert-dismissible fade show" role="alert">
                            <span class="alert-text text-white">
                            {{$errors->first()}}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="fa fa-close" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="m-3  alert alert-success alert-dismissible fade show" id="alert-success" role="alert">
                            <span class="alert-text text-white">
                            {{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="fa fa-close" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user-name" class="form-control-label">{{ __('Nome') }}</label>
                                <div class="@error('user.name')border border-danger rounded-3 @enderror">
                                    <input class="form-control" value="{{ auth()->user()->name }}" type="text" placeholder="Name" id="user-name" name="name">
                                      @error('name')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                      @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                              @if(auth()->user()->role === 'admin')
                                <label for="user-email" class="form-control-label">{{ __('Email') }}</label>
                                <div class="@error('email')border border-danger rounded-3 @enderror">
                                    <input class="form-control" value="{{ auth()->user()->email }}" type="email" placeholder="@example.com" id="user-email" name="email" required>
                                      @error('email')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                      @enderror
                                </div>
                              @else 
                                <label for="user-email" class="form-control-label">{{ __('Email') }}</label>
                                <div class="@error('email')border border-danger rounded-3 @enderror">
                                    <input class="form-control" value="{{ auth()->user()->email }}" type="email" placeholder="@example.com" id="user-email" name="email" required disabled>
                                      @error('email')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                      @enderror
                                </div>
                              @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="password" class="form-control-label">{{ __('Nova senha') }}</label>
                              <input class="form-control" type="password" id="password" name="password">
                              @error('password')
                                  <p class="text-danger text-xs mt-2">{{ $message }}</p>
                              @enderror
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="password_confirmation" class="form-control-label">{{ __('Confirmar senha') }}</label>
                              <input class="form-control" type="password" id="password_confirmation" name="password_confirmation">
                              @error('password_confirmation')
                                  <p class="text-danger text-xs mt-2">{{ $message }}</p>
                              @enderror
                          </div>
                      </div>
                      <div class="col-12">
                        <p class="text-sm">Deixe as senhas em branco para não alterar.</p>
                      </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Salvar aterações' }}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection