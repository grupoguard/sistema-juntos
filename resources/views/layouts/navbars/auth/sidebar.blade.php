
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-blue" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="align-items-center justify-content-center d-flex m-0 text-wrap" href="{{ route('dashboard') }}">
        <img src="{{ asset('assets/img/logo.png') }}" class="w-50" alt="Juntos Benefícios">
      </a>
  </div>
  <hr class="horizontal light mt-0">
  <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('dashboard') ? 'active' : '') }}" href="{{ url('dashboard') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-th-large"></i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Operação</h6>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed {{ (Request::is('evidences') ? 'active' : '') }}" data-bs-toggle="collapse" aria-expanded="false" href="#vrExamples">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-file-export"></i>
          </div>
          <span class="nav-link-text ms-1">Evidências</span>
        </a>
        <div class="collapse" id="vrExamples" style="">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item ">
              <a class="nav-link" href="{{ url('evidences') }}">
                Cadastrar Evidências
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="{{ url('evidences') }}">
                Listar Evidências
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="{{ route('evidences.disparo') }}">
                Disparo Planilha EDP
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="{{ route('evidences.processamento') }}">
                Gerar Arquivo Processamento
              </a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('clientes') ? 'active' : '') }} " href="{{ url('clientes') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-user-circle"></i>
          </div>
          <span class="nav-link-text ms-1">Clientes</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('consultores') ? 'active' : '') }} " href="{{ url('consultores') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-user-friends"></i>
          </div>
          <span class="nav-link-text ms-1">Consultores</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('planos') ? 'active' : '') }} " href="{{ url('planos') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-box"></i>
          </div>
          <span class="nav-link-text ms-1">Planos</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed {{ (Request::is('cooperatives') ? 'active' : '') }}" data-bs-toggle="collapse" aria-expanded="false" href="#cooperatives">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-building"></i>
          </div>
          <span class="nav-link-text ms-1">Cooperativas</span>
        </a>
        <div class="collapse" id="cooperatives" style="">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item ">
              <a class="nav-link" href="{{ url('cooperatives') }}">
                Listar Cooperativa
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="{{ url('cooperatives') }}">
                Cadastrar Cooperativa
              </a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Gestão</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('financeiro') ? 'active' : '') }} " href="{{ url('financeiro') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <span class="nav-link-text ms-1">Financeiro</span>
        </a>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Rede de Parceiros</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('parceiros') ? 'active' : '') }} " href="{{ url('parceiros') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-handshake"></i>
          </div>
          <span class="nav-link-text ms-1">Parceiros</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('categorias') ? 'active' : '') }} " href="{{ url('categorias') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-list"></i>
          </div>
          <span class="nav-link-text ms-1">Categorias</span>
        </a>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Perfil</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-user-cog"></i>
          </div>
          <span class="nav-link-text ms-1">Editar Perfil</span>
        </a>
      </li>
    </ul>
  </div>
</aside>
