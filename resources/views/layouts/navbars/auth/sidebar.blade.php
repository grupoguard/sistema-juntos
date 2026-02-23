@php
    $menu = config('backoffice_menu');
    $user = auth()->user();

    // Filtra itens visíveis
    $visibleMenu = [];
    foreach ($menu as $item) {
        if (\App\Helpers\MenuHelper::canSeeItem($item, $user)) {
            $visibleMenu[] = $item;
        }
    }
@endphp

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-blue" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="align-items-center justify-content-center d-flex m-0 text-wrap" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/img/logo.png') }}" class="w-50" alt="Juntos Benefícios">
        </a>
    </div>

    <hr class="horizontal light mt-0">

    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            @php $lastWasSection = false; @endphp

            @foreach($visibleMenu as $index => $item)
                @if(($item['type'] ?? null) === 'section')
                    @php
                        // Mostra a seção somente se existir item visível depois dela antes da próxima seção
                        $hasVisibleAfter = false;
                        for ($i = $index + 1; $i < count($visibleMenu); $i++) {
                            if (($visibleMenu[$i]['type'] ?? null) === 'section') {
                                break;
                            }
                            $hasVisibleAfter = true;
                            break;
                        }
                    @endphp

                    @if($hasVisibleAfter)
                        <li class="nav-item mt-2">
                            <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white">
                                {{ $item['label'] }}
                            </h6>
                        </li>
                    @endif
                    @continue
                @endif

                @if(($item['type'] ?? null) === 'link')
                    @php
                        $isActive = \App\Helpers\MenuHelper::isActive($item);
                        $href = isset($item['route']) && $item['route']
                            ? route($item['route'])
                            : ($item['url'] ?? 'javascript:;');
                    @endphp

                    <li class="nav-item">
                        <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $href }}">
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="{{ $item['icon'] ?? 'fa fa-circle' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endif

                @if(($item['type'] ?? null) === 'collapse')
                    @php
                        $isOpen = \App\Helpers\MenuHelper::collapseIsOpen($item);
                        $children = collect($item['children'] ?? [])
                            ->filter(fn($child) => \App\Helpers\MenuHelper::hasPermission($child, $user))
                            ->values();
                    @endphp

                    @if($children->count())
                        <li class="nav-item">
                            <a class="nav-link collapsed {{ $isOpen ? '' : '' }}"
                               data-bs-toggle="collapse"
                               aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                               href="#{{ $item['id'] }}">
                                <div class="icon icon-shape icon-sm shadow border-radius-md bg-dark text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="{{ $item['icon'] ?? 'fa fa-folder' }}"></i>
                                </div>
                                <span class="nav-link-text ms-1">{{ $item['label'] }}</span>
                            </a>

                            <div class="collapse {{ $isOpen ? 'show' : '' }}" id="{{ $item['id'] }}">
                                <ul class="nav nav-sm flex-column">
                                    @foreach($children as $child)
                                        @php
                                            $childActive = \App\Helpers\MenuHelper::isActive($child);
                                            $childHref = isset($child['route']) && $child['route']
                                                ? route($child['route'])
                                                : ($child['url'] ?? 'javascript:;');
                                        @endphp

                                        <li class="nav-item">
                                            <a class="nav-link {{ $childActive ? 'active' : '' }}" href="{{ $childHref }}">
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
    </div>
</aside>