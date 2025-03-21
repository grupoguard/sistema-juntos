
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-blue" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="align-items-center justify-content-center d-flex m-0 text-wrap" href="{{ route('admin.dashboard') }}">
        <img src="{{ asset('assets/img/logo.png') }}" class="w-50" alt="Juntos Benefícios">
      </a>
  </div>
  <hr class="horizontal light mt-0">
  <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('dashboard') ? 'active' : '') }}" href="{{ route('admin.dashboard') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-th-large"></i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Operação</h6>
      </li>

      <!-- <li class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" aria-expanded="false" href="#vrExamples">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-file-export"></i>
          </div>
          <span class="nav-link-text ms-1">Evidências</span>
        </a>
        <div class="collapse" id="vrExamples" style="">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item ">
              <a class="nav-link" href="">
                Cadastrar Evidências
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="">
                Listar Evidências
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="">
                Disparo Planilha EDP
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="">
                Gerar Arquivo Processamento
              </a>
            </li>
          </ul>
        </div>
      </li> -->
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('admin.orders.index') ? 'active' : '') }} " href="{{ route('admin.orders.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-plus-circle"></i>
          </div>
          <span class="nav-link-text ms-1">Pedidos</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('admin.clients.index') ? 'active' : '') }} " href="{{ route('admin.clients.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-users"></i>
          </div>
          <span class="nav-link-text ms-1">Clientes</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-building"></i>
          </div>
          <span class="nav-link-text ms-1">Cooperativas</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-user-plus"></i>
          </div>
          <span class="nav-link-text ms-1">Consultores</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" aria-expanded="false" href="#produtos">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-medkit"></i>
          </div>
          <span class="nav-link-text ms-1">Produtos</span>
        </a>
        <div class="collapse" id="produtos" style="">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item ">
              <a class="nav-link" href="javascript:;">
                Gerenciar produtos
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="javascript:;">
                Gerenciar adicionais
              </a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Gestão</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-dollar"></i>
          </div>
          <span class="nav-link-text ms-1">Financeiro</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-file-pdf-o"></i>
          </div>
          <span class="nav-link-text ms-1">Relatórios</span>
        </a>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Rede de Parceiros</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-handshake-o"></i>
          </div>
          <span class="nav-link-text ms-1">Parceiros</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-th-list"></i>
          </div>
          <span class="nav-link-text ms-1">Categorias</span>
        </a>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Configurações</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" aria-expanded="false" href="#edp">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-bolt"></i>
          </div>
          <span class="nav-link-text ms-1">EDP</span>
        </a>
        <div class="collapse" id="edp" style="">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item ">
              <a class="nav-link" href="javascript:;">
                Códigos
              </a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="javascript:;">
                Calendário
              </a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-toggle="collapse" aria-expanded="false" href="#comission">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-money"></i>
          </div>
          <span class="nav-link-text ms-1">Comissão</span>
        </a>
        <div class="collapse" id="comission" style="">
          <ul class="nav nav-sm flex-column">
            <li class="nav-item ">
              <a class="nav-link" href="javascript:;">
                Gerenciar comissões
              </a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">Adminitrador</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-user-circle"></i>
          </div>
          <span class="nav-link-text ms-1">Usuários</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-address-card"></i>
          </div>
          <span class="nav-link-text ms-1">Editar Perfil</span>
        </a>
      </li>
    </ul>
  </div>
</aside>
