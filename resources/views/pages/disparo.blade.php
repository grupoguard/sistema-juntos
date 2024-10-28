
@extends('layouts.user_type.auth')

@section('content')
  <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header p-3 pb-0">
                        <div class="row">
                            <div class="col-12 d-flex align-items-center">
                                <h6 class="mb-0">Disparos de evidências em massa</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <p class="text-sm">
                            Bsta subir uma planilha seguindo <a href="#">esse formato</a> e o sistema realizará o envio em massa para a EDP!
                        </p> 

                        <form action="{{ route('evidences.send') }}"  method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="example-text-input" class="form-control-label">Selecione a planilha:</label>
                                <input class="form-control" type="file" name="planilha" required>
                            </div>
    
                            <button class="btn btn-icon btn-3 btn-warning" type="button">
                                <span class="btn-inner--text">Enviar</span>
                                <span class="btn-inner--icon"><i class="ni ni-button-play"></i></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

@endsection

