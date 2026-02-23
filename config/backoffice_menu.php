<?php

return [
    [
        'type' => 'link',
        'label' => 'Dashboard',
        'icon' => 'fa fa-th-large',
        'route' => 'admin.dashboard',
        'active' => ['admin.dashboard'],
        'permission' => null, // todos autenticados do painel
    ],

    [
        'type' => 'section',
        'label' => 'Operação',
    ],

    [
        'type' => 'link',
        'label' => 'Pedidos',
        'icon' => 'fa fa-plus-circle',
        'route' => 'admin.orders.index',
        'active' => ['admin.orders.*'],
        'permission' => 'orders.view',
    ],
    [
        'type' => 'link',
        'label' => 'Clientes',
        'icon' => 'fa fa-users',
        'route' => 'admin.clients.index',
        'active' => ['admin.clients.*'],
        'permission' => 'clients.view',
    ],
    [
        'type' => 'link',
        'label' => 'Cooperativas',
        'icon' => 'fa fa-building',
        'route' => 'admin.groups.index',
        'active' => ['admin.groups.*'],
        'permission' => 'groups.view',
    ],
    [
        'type' => 'link',
        'label' => 'Consultores',
        'icon' => 'fa fa-user-plus',
        'route' => 'admin.sellers.index',
        'active' => ['admin.sellers.*'],
        'permission' => 'sellers.view',
    ],

    [
        'type' => 'collapse',
        'label' => 'Produtos',
        'icon' => 'fa fa-medkit',
        'id' => 'produtos',
        'children' => [
            [
                'label' => 'Gerenciar produtos',
                'route' => 'admin.products.index',
                'active' => ['admin.products.*'],
                'permission' => 'products.view',
            ],
            [
                'label' => 'Gerenciar adicionais',
                'route' => 'admin.aditionals.index',
                'active' => ['admin.aditionals.*'],
                'permission' => 'aditionals.view',
            ],
        ],
    ],

    [
        'type' => 'section',
        'label' => 'Gestão',
    ],

    [
        'type' => 'link',
        'label' => 'Financeiro',
        'icon' => 'fa fa-dollar',
        'route' => null,
        'url' => 'javascript:;',
        'active' => [],
        'permission' => 'financial.view',
    ],

    [
        'type' => 'collapse',
        'label' => 'Relatórios',
        'icon' => 'fa fa-medkit',
        'id' => 'reports',
        'children' => [
            [
                'label' => 'Atualizações EDP',
                'route' => 'admin.reports.edp',
                'active' => ['admin.reports.edp*', 'admin.reports.edp'],
                'permission' => 'reports.edp.view',
            ],
            [
                'label' => 'Recebimentos EDP',
                'route' => 'admin.reports.financial',
                'active' => ['admin.reports.financial*', 'admin.reports.financial'],
                'permission' => 'reports.financial.view',
            ],
        ],
    ],

    [
        'type' => 'section',
        'label' => 'Rede de Parceiros',
    ],

    [
        'type' => 'link',
        'label' => 'Parceiros',
        'icon' => 'fa fa-handshake-o',
        'route' => 'admin.partners.index',
        'active' => ['admin.partners.*'],
        'permission' => 'partners.view',
    ],
    [
        'type' => 'link',
        'label' => 'Categorias',
        'icon' => 'fa fa-th-list',
        'route' => 'admin.partner_categories.index',
        'active' => ['admin.partner_categories.*'],
        'permission' => 'partner_categories.view',
    ],

    [
        'type' => 'section',
        'label' => 'Configurações',
    ],

    [
        'type' => 'collapse',
        'label' => 'EDP',
        'icon' => 'fa fa-bolt',
        'id' => 'edp',
        'children' => [
            [
                'label' => 'Códigos',
                'url' => 'javascript:;',
                'permission' => 'edp.codes.view',
            ],
            [
                'label' => 'Calendário',
                'url' => 'javascript:;',
                'permission' => 'edp.calendar.view',
            ],
        ],
    ],

    [
        'type' => 'collapse',
        'label' => 'Comissão',
        'icon' => 'fa fa-money',
        'id' => 'comission',
        'children' => [
            [
                'label' => 'Gerenciar comissões',
                'url' => 'javascript:;',
                'permission' => 'commissions.view',
            ],
        ],
    ],

    [
        'type' => 'section',
        'label' => 'Administrador',
    ],

    [
        'type' => 'link',
        'label' => 'Usuários',
        'icon' => 'fa fa-user-circle',
        'route' => 'admin.users.index', // ajuste se existir
        'active' => ['admin.users.*'],
        'permission' => 'users.view',
    ],
    [
        'type' => 'link',
        'label' => 'Editar Perfil',
        'icon' => 'fa fa-address-card',
        'route' => 'admin.profile',
        'active' => ['admin.profile'],
        'permission' => null, // todo usuário autenticado do painel
    ],
];